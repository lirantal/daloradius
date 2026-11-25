#!/usr/bin/env bash
# daloRADIUS - Debian application and schema upgrade helper
#
# This script intentionally upgrades only the daloRADIUS checkout and database.
# It does not upgrade the operating system or rewrite Apache configuration.
# The installed daloRADIUS configuration is loaded, never rewritten.

set -Eeuo pipefail
IFS=$'\n\t'
umask 077

SCRIPT_NAME="$(basename "$0")"
readonly SCRIPT_NAME
APP_ROOT="/var/www/daloradius"
REMOTE="origin"
TARGET_REF="origin/master"
LOCATION="default"
APPLY=false
REF_EXPLICIT=false
CHECK_EXPLICIT=false

CONFIG_FILE=""
LOCK_FILE="/run/lock/daloradius-upgrade.lock"
CURRENT_COMMIT=""
TARGET_COMMIT=""
UPGRADE_STARTED=false
APACHE_RESTORE_REQUIRED=false
MIGRATIONS_PENDING=false
STAGE_DIR=""
RUNNER=""

log() {
    printf '[%s] %s\n' "$SCRIPT_NAME" "$*"
}

warn() {
    printf '[%s] WARNING: %s\n' "$SCRIPT_NAME" "$*" >&2
}

fail() {
    printf '[%s] ERROR: %s\n' "$SCRIPT_NAME" "$*" >&2
    exit 1
}

git_app() {
    git -c "safe.directory=$APP_ROOT" -C "$APP_ROOT" "$@"
}

usage() {
    cat <<'EOF'
Usage:
  setup/upgrade.sh --check [options]
  setup/upgrade.sh --apply [options]

Upgrade the application and schema of a standard Debian daloRADIUS installation.

Options:
  --check                 Preview the target and migration history (the default).
  --apply                 Apply the planned upgrade. Without this, no service,
                          application file, or database schema is changed.
  --root-dir PATH         daloRADIUS checkout (default: /var/www/daloradius).
  --remote NAME           Git remote to fetch (default: origin).
  --ref REF               Git ref or commit to deploy (default: REMOTE/master).
  --location NAME         Named CONFIG_LOCATIONS entry to migrate (default: default).
  -h, --help              Show this help.

Database migrations use the installed application configuration and account.
The existing daloradius.conf.php is left unchanged. Review new settings in the
release notes and configuration sample; add any required settings yourself.
Backups and recovery are the administrator's responsibility. This script does
not create backups or automatically roll back application/schema changes.
Before --apply, back up the database, configuration and local application data
using your normal tools, and verify that you can restore them.

--apply stops Apache during the upgrade, interrupting the web portals.
FreeRADIUS and MariaDB services are not managed by this helper.

Examples:
  setup/upgrade.sh --check --ref origin/master
  setup/upgrade.sh --apply --ref COMMIT_PRINTED_BY_CHECK
EOF
}

cleanup() {
    [[ -z "$STAGE_DIR" ]] || rm -rf -- "$STAGE_DIR"
}

handle_failure() {
    [[ "$APPLY" == true ]] || return 0
    if [[ "$UPGRADE_STARTED" == true ]]; then
        systemctl stop apache2 || warn "Could not stop Apache."
        warn "Application/schema changes may have started; no automatic rollback was attempted."
        warn "Source revision: $CURRENT_COMMIT; planned target: $TARGET_COMMIT."
        warn "Inspect the current checkout and migration history. Repair or restore using your own backups."
        warn "Keep Apache stopped until recovery is verified."
        return
    fi
    if [[ "$APACHE_RESTORE_REQUIRED" == true ]]; then
        if systemctl start apache2; then
            APACHE_RESTORE_REQUIRED=false
            log "Restored Apache to its previous active state."
        else
            warn "Could not restart Apache; run 'systemctl start apache2' after reviewing the failure."
        fi
    fi
}

on_error() {
    local rc=$?
    local line="$1"
    warn "Command failed at line $line."
    exit "$rc"
}

on_signal() {
    local rc=$1
    local signal=$2
    warn "Received $signal; aborting the upgrade."
    exit "$rc"
}

on_exit() {
    local rc=$?
    trap - EXIT ERR INT TERM
    if ((rc != 0)); then
        handle_failure || true
    fi
    cleanup
    exit "$rc"
}

trap on_exit EXIT
trap 'on_error "$LINENO" "$BASH_COMMAND"' ERR
trap 'on_signal 130 INT' INT
trap 'on_signal 143 TERM' TERM

require_argument() {
    [[ $# -ge 2 && -n "$2" ]] || fail "Missing argument for $1."
}

parse_args() {
    while (($#)); do
        case "$1" in
            --apply)
                [[ "$CHECK_EXPLICIT" == false ]] || \
                    fail "--apply and --check cannot be used together."
                APPLY=true
                shift
                ;;
            --root-dir)
                require_argument "$@"
                APP_ROOT=$2
                shift 2
                ;;
            --remote)
                require_argument "$@"
                REMOTE=$2
                shift 2
                ;;
            --ref)
                require_argument "$@"
                TARGET_REF=$2
                REF_EXPLICIT=true
                shift 2
                ;;
            --location)
                require_argument "$@"
                LOCATION=$2
                shift 2
                ;;
            -h|--help)
                usage
                exit 0
                ;;
            --check)
                [[ "$APPLY" == false ]] || \
                    fail "--apply and --check cannot be used together."
                CHECK_EXPLICIT=true
                shift
                ;;
            *)
                fail "Unknown option: $1"
                ;;
        esac
    done

    if [[ "$REF_EXPLICIT" == false ]]; then
        TARGET_REF="$REMOTE/master"
    fi
}

require_commands() {
    local command
    for command in "$@"; do
        command -v "$command" >/dev/null 2>&1 || fail "Required command not found: $command"
    done
}

validate_host() {
    [[ "$(id -u)" -eq 0 ]] || fail "This script must be run as root."
    [[ -r /etc/os-release ]] || fail "Cannot identify the operating system."
    # shellcheck disable=SC1091
    . /etc/os-release
    [[ "${ID:-}" == "debian" || "${ID_LIKE:-}" == *debian* ]] || \
        fail "This script supports Debian-based systems only."
}

validate_paths() {
    [[ -d "$APP_ROOT" ]] || fail "daloRADIUS directory does not exist: $APP_ROOT"
    [[ ! -L "$APP_ROOT" ]] || fail "Refusing to operate on a symlinked application root: $APP_ROOT"
    APP_ROOT=$(realpath -e "$APP_ROOT")
    CONFIG_FILE="$APP_ROOT/app/common/includes/daloradius.conf.php"

    [[ -d "$APP_ROOT/.git" ]] || fail "Not a Git checkout: $APP_ROOT"
    [[ -f "$CONFIG_FILE" ]] || fail "Configuration file not found: $CONFIG_FILE"
    [[ ! -L "$CONFIG_FILE" ]] || fail "Refusing to operate on a symlinked configuration file."
    [[ -f "$APP_ROOT/app/common/includes/daloradius.conf.php.sample" ]] || \
        fail "Configuration sample not found in the checkout."
}

validate_git_inputs() {
    [[ "$REMOTE" =~ ^[A-Za-z0-9][A-Za-z0-9._-]*$ ]] || fail "Invalid Git remote name: $REMOTE"
    [[ "$TARGET_REF" =~ ^[A-Za-z0-9._/@:-]+$ ]] || fail "Invalid Git ref: $TARGET_REF"
    [[ "$TARGET_REF" != -* ]] || fail "Git ref must not start with '-'."
}

acquire_lock() {
    local owner mode
    owner=$(stat -c '%u' -- /run/lock)
    mode=$(stat -c '%a' -- /run/lock)
    [[ "$owner" == 0 ]] || fail "/run/lock must be owned by root."
    if (( (8#$mode & 022) && (8#$mode & 01000) == 0 )); then
        fail "/run/lock must be non-writable or sticky (mode $mode)."
    fi
    if [[ ! -e "$LOCK_FILE" && ! -L "$LOCK_FILE" ]]; then
        (set -o noclobber; : > "$LOCK_FILE") 2>/dev/null || true
    fi
    [[ ! -L "$LOCK_FILE" ]] || fail "Refusing to use a symlinked lock file."
    [[ -f "$LOCK_FILE" ]] || fail "Unable to create lock file: $LOCK_FILE"
    owner=$(stat -c '%u' -- "$LOCK_FILE")
    mode=$(stat -c '%a' -- "$LOCK_FILE")
    [[ "$owner" == 0 ]] || fail "Lock file must be owned by root."
    if (( 8#$mode & 077 )); then
        fail "Lock file must not be readable or writable by group or other users (mode $mode)."
    fi
    exec 9>"$LOCK_FILE"
    flock -n 9 || fail "Another daloRADIUS upgrade is already running."
}

validate_git_state() {
    local status
    status=$(git_app status --porcelain --untracked-files=all)
    [[ -z "$status" ]] || {
        printf '%s\n' "$status" >&2
        fail "Git checkout is not clean; refusing to overwrite local changes."
    }

    CURRENT_COMMIT=$(git_app rev-parse HEAD)
    git_app config remote."$REMOTE".url >/dev/null 2>&1 || \
        fail "Git remote does not exist: $REMOTE"
    if git_app ls-files --error-unmatch -- \
        app/common/includes/daloradius.conf.php >/dev/null 2>&1; then
        fail "Application configuration is tracked by Git; refusing to risk changing it."
    fi
}

fetch_target() {
    log "Fetching Git refs from $REMOTE."
    # Keep Git objects/refs readable using standard 022 umask (077 is only for temp files).
    local -a fetch_args=(--tags --prune)
    if [[ "$(git_app rev-parse --is-shallow-repository)" == "true" ]]; then
        log "Shallow Git checkout detected; unshallowing so fast-forward ancestry can be verified."
        fetch_args+=(--unshallow)
    fi
    (umask 022; git_app fetch "${fetch_args[@]}" -- "$REMOTE" \
        "+refs/heads/*:refs/remotes/$REMOTE/*")
    if git_app show-ref --verify --quiet "refs/remotes/$REMOTE/$TARGET_REF"; then
        TARGET_REF="$REMOTE/$TARGET_REF"
    fi
    TARGET_COMMIT=$(git_app rev-parse --verify "$TARGET_REF^{commit}") || \
        fail "Git ref does not resolve to a commit: $TARGET_REF"
    git_app cat-file -e \
        "$TARGET_COMMIT:app/common/includes/daloradius.conf.php.sample" || \
        fail "Target revision does not contain the daloRADIUS configuration sample."
    if git_app cat-file -e \
        "$TARGET_COMMIT:app/common/includes/daloradius.conf.php" 2>/dev/null; then
        fail "Target revision tracks the local application configuration; refusing to overwrite it."
    fi

    [[ "$CURRENT_COMMIT" == "$TARGET_COMMIT" ]] && {
        log "The checkout is already at $TARGET_COMMIT."
        return
    }

    git_app merge-base --is-ancestor "$CURRENT_COMMIT" "$TARGET_COMMIT" || \
        fail "Target is not a fast-forward from the current revision; refusing update."
    log "Upgrade plan: $CURRENT_COMMIT -> $TARGET_COMMIT."
}

migration_list() {
    if ! git_app cat-file -e "$TARGET_COMMIT:contrib/db/migrations" 2>/dev/null; then
        return 0
    fi
    git_app ls-tree --name-only "$TARGET_COMMIT" contrib/db/migrations/ | \
        while IFS= read -r path; do
            case "$path" in
                contrib/db/migrations/*.sql|contrib/db/migrations/*.php) printf '%s\n' "$path" ;;
            esac
        done | sort
}

stage_target() {
    STAGE_DIR=$(mktemp -d /run/daloradius-upgrade.XXXXXX)
    chgrp www-data "$STAGE_DIR"
    chmod 750 "$STAGE_DIR"

    # Require the migration runner and PDO helper from the selected target revision;
    # never silently mix files from another branch or the helper's checkout.
    local required_file
    for required_file in \
        contrib/scripts/maintenance/migrate-db.php \
        app/common/includes/pdo_connection.php; do
        git_app cat-file -e "$TARGET_COMMIT:$required_file" 2>/dev/null || \
            fail "Target revision $TARGET_COMMIT does not include $required_file; select a revision containing the tracked migration runner or use the legacy manual upgrade guide."
    done

    # Extract only migration assets and the config sample into /run (tmpfs)
    # instead of the entire repository.
    local -a archive_paths=(
        contrib/scripts/maintenance/migrate-db.php
        app/common/includes/pdo_connection.php
    )
    local optional_path
    for optional_path in \
        contrib/db/migrations \
        app/common/includes/daloradius.conf.php.sample; do
        if git_app cat-file -e "$TARGET_COMMIT:$optional_path" 2>/dev/null; then
            archive_paths+=("$optional_path")
        fi
    done

    (umask 022; git_app archive "$TARGET_COMMIT" -- "${archive_paths[@]}" | tar -x -C "$STAGE_DIR")

    RUNNER="$STAGE_DIR/contrib/scripts/maintenance/migrate-db.php"

    php -l "$RUNNER" >/dev/null
    php -l "$STAGE_DIR/app/common/includes/pdo_connection.php" >/dev/null
    php -r 'exit(class_exists("PDO") && in_array("mysql", PDO::getAvailableDrivers(), true) ? 0 : 1);' ||
        fail "PHP PDO MySQL driver (pdo_mysql) is required (Debian package php-mysql)."

    MIGRATIONS_PENDING=false
    if [[ -n "$(migration_list)" ]]; then
        local rc=0
        runuser -u www-data -- php "$RUNNER" \
            --check-pending \
            --config="$CONFIG_FILE" \
            --location="$LOCATION" \
            --migrations-dir="$STAGE_DIR/contrib/db/migrations" || rc=$?
        if ((rc == 10)); then
            MIGRATIONS_PENDING=true
        elif ((rc != 0)); then
            fail "Database migration preflight check failed (exit code $rc)."
        fi
    fi
}

report_missing_config_keys() {
    local target_sample="$STAGE_DIR/app/common/includes/daloradius.conf.php.sample"
    [[ -f "$target_sample" ]] || return 0

    local missing_keys
    missing_keys=$(runuser -u www-data -- php -r '
        $load = static function ($path) {
            $configValues = array();
            require $path;
            return is_array($configValues) ? $configValues : array();
        };
        $current = $load($argv[1]);
        $sample = $load($argv[2]);
        foreach (array_diff(array_keys($sample), array_keys($current)) as $key) {
            if (is_string($key)) {
                echo $key . "\n";
            }
        }
    ' "$CONFIG_FILE" "$target_sample") || {
        warn "Could not compare local configuration keys against the target sample."
        return 0
    }

    if [[ -n "$missing_keys" ]]; then
        log "New configuration keys in target daloradius.conf.php.sample (review and add manually if needed):"
        local key
        while IFS= read -r key; do
            [[ -n "$key" ]] && printf '  - %s\n' "$key"
        done <<< "$missing_keys"
    fi
}

show_plan() {
    local migration
    log "Planned application revision: $TARGET_COMMIT"
    log "Database migrations available in the target revision (the ledger will skip applied ones):"
    if ! migration_list | while IFS= read -r migration; do
        [[ -n "$migration" ]] && printf '  - %s\n' "$migration"
    done; then
        fail "Could not enumerate database migrations."
    fi
    report_missing_config_keys
    log "daloRADIUS, Apache and FreeRADIUS configuration files will not be rewritten."
    if [[ "$APPLY" == false ]]; then
        if [[ "$CURRENT_COMMIT" == "$TARGET_COMMIT" && "$MIGRATIONS_PENDING" == false ]]; then
            log "Check complete; already at $TARGET_COMMIT with no pending migrations."
        else
            local apply_cmd="sudo bash $(printf '%q' "$0") --apply --ref $(printf '%q' "$TARGET_COMMIT")"
            [[ "$APP_ROOT" == "/var/www/daloradius" ]] || apply_cmd+=" --root-dir $(printf '%q' "$APP_ROOT")"
            [[ "$REMOTE" == "origin" ]] || apply_cmd+=" --remote $(printf '%q' "$REMOTE")"
            [[ "$LOCATION" == "default" ]] || apply_cmd+=" --location $(printf '%q' "$LOCATION")"
            log "Check complete; no upgrade was applied. To apply this exact revision, run:"
            printf '  %s\n' "$apply_cmd"
        fi
    fi
}

run_migrations() {
    if [[ -z "$(migration_list)" ]]; then
        log "No database migration files in this target."
        return
    fi
    runuser -u www-data -- php "$RUNNER" \
        --apply \
        --config="$CONFIG_FILE" \
        --location="$LOCATION" \
        --migrations-dir="$STAGE_DIR/contrib/db/migrations"
}

validate_configuration_access() {
    runuser -u www-data -- php -d display_errors=stderr -r '
        require $argv[1];
        exit(isset($configValues) && is_array($configValues) ? 0 : 1);
    ' "$1" || fail "www-data cannot load the application configuration."
}

validate_services() {
    local apache_was_active=$1
    validate_configuration_access "$CONFIG_FILE"
    if [[ "$apache_was_active" == true ]]; then
        systemctl is-active --quiet apache2 || fail "Apache is not active after the upgrade."
    fi
}

apply_upgrade() {
    if [[ "$CURRENT_COMMIT" == "$TARGET_COMMIT" && "$MIGRATIONS_PENDING" == false ]]; then
        log "Already at $TARGET_COMMIT with no pending migrations; nothing to apply."
        return 0
    fi

    local apache_was_active=false
    local crontab_file="$APP_ROOT/contrib/scripts/dalo-crontab"
    local crontab_owner=""
    if [[ -f "$crontab_file" ]]; then
        crontab_owner=$(stat -c '%U:%G' -- "$crontab_file")
    fi

    warn "No backup will be created. Verify your own database, configuration and local-data backups before upgrading."
    log "Maintenance: stopping Apache for the upgrade if active."
    if systemctl is-active --quiet apache2; then
        apache_was_active=true
        APACHE_RESTORE_REQUIRED=true
        systemctl stop apache2
    fi
    UPGRADE_STARTED=true
    run_migrations
    # Repository files are public source; private umask 077 is for temporary files.
    # Git creates replaced/new files using umask, so 077 would hide PHP from Apache.
    if [[ "$CURRENT_COMMIT" != "$TARGET_COMMIT" ]]; then
        (umask 022; git_app merge --ff-only "$TARGET_COMMIT" >/dev/null)
    fi
    if [[ "$crontab_owner" == "www-data:www-data" && -f "$crontab_file" ]]; then
        chown www-data:www-data -- "$crontab_file"
    fi
    validate_configuration_access "$CONFIG_FILE"
    apachectl configtest >/dev/null
    [[ "$apache_was_active" == false ]] || systemctl start apache2
    validate_services "$apache_was_active"
    APACHE_RESTORE_REQUIRED=false
    UPGRADE_STARTED=false
    log "Upgrade completed successfully."
    log "Application revision: $TARGET_COMMIT"
}

main() {
    parse_args "$@"
    validate_host
    require_commands git realpath stat flock sort php runuser tar mktemp chown chmod chgrp
    if [[ "$APPLY" == true ]]; then
        require_commands systemctl apachectl
    fi
    validate_paths
    validate_git_inputs
    acquire_lock
    validate_git_state
    id www-data >/dev/null 2>&1 || fail "The www-data account is required."
    validate_configuration_access "$CONFIG_FILE"
    fetch_target
    stage_target
    show_plan

    [[ "$APPLY" == true ]] || return 0
    apply_upgrade
}

main "$@"
