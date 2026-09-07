<?php

define('DALO_RADIUS_STATUS_FUNCTIONS_ONLY', true);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require __DIR__ . '/../app/operators/library/extensions/radius_server_info.php';

$checks = 0;
function status_assert($condition, $message) {
    global $checks;
    $checks++;
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

status_assert(dalo_radius_status_mode(array(), false) === 'local', 'auto uses local mode outside a container');
status_assert(dalo_radius_status_mode(array(), true) === 'network', 'auto uses network mode in a container');
status_assert(dalo_radius_status_mode(array('CONFIG_STATUS_MODE' => 'network'), false) === 'network', 'network mode works outside a container');
status_assert(dalo_radius_status_mode(array('CONFIG_STATUS_MODE' => 'disabled'), true) === 'disabled', 'disabled mode is explicit');
status_assert(dalo_radius_status_mode(array('CONFIG_STATUS_MODE' => 'invalid'), false) === 'invalid', 'invalid mode fails closed');

$_SESSION = array('location_name' => 'secondary');
$config = array(
    'CONFIG_DB_ENGINE' => 'mysqli',
    'CONFIG_DB_USER' => 'default-user',
    'CONFIG_DB_PASS' => 'default-pass',
    'CONFIG_DB_HOST' => 'default-db',
    'CONFIG_DB_PORT' => '3306',
    'CONFIG_DB_NAME' => 'default-name',
    'CONFIG_LOCATIONS' => array(
        'secondary' => array(
            'Engine' => 'pgsql',
            'Username' => 'location-user',
            'Password' => "p@ss:/word",
            'Hostname' => 'location-db',
            'Port' => '5432',
            'Database' => 'location-name',
        ),
    ),
);
$database = dalo_radius_effective_database_config($config);
status_assert($database['engine'] === 'pgsql', 'selected location engine is used');
status_assert($database['host'] === 'location-db', 'selected location host is used');
status_assert($database['password'] === "p@ss:/word", 'location password is preserved without URL parsing');

$_SESSION = array('location_name' => 'default');
$database = dalo_radius_effective_database_config($config);
status_assert($database['host'] === 'default-db', 'default location uses top-level DB settings');

status_assert(strpos(dalo_radius_render_status(dalo_radius_status_result('unknown')), 'status unavailable') !== false, 'unknown is not rendered as stopped');
status_assert(strpos(dalo_radius_render_status(dalo_radius_status_result('disabled')), 'disabled') !== false, 'disabled state is visible');
status_assert(strpos(dalo_radius_render_status(dalo_radius_status_result('na')), 'N/A (container)') !== false, 'container SSH state remains explicit');

$tmp = sys_get_temp_dir() . '/daloradius-status-test-' . bin2hex(random_bytes(6));
mkdir($tmp, 0700);
$fake = $tmp . '/radclient';
$args_file = $tmp . '/args';
$input_file = $tmp . '/input';
$secret_seen_file = $tmp . '/secret-seen';
$mode_file = $tmp . '/secret-mode';
$script = <<<'SH'
#!/bin/sh
printf '%s\n' "$@" > "$DALO_TEST_ARGS"
previous=''
for argument in "$@"; do
    if [ "$previous" = '-S' ]; then
        test -r "$argument" || exit 7
        IFS= read -r secret < "$argument"
        printf '%s' "$secret" > "$DALO_TEST_SECRET_SEEN"
        /usr/bin/stat -c '%a' "$argument" > "$DALO_TEST_SECRET_MODE"
    fi
    previous="$argument"
done
: > "$DALO_TEST_INPUT"
while IFS= read -r line; do
    printf '%s\n' "$line" >> "$DALO_TEST_INPUT"
done
printf '%s\n' 'Received Access-Accept Id 1 from 127.0.0.1:18122'
exit 0
SH;
file_put_contents($fake, $script);
chmod($fake, 0700);
define('DALO_RADIUS_TEST_BIN_DIR', $tmp);
putenv('DALO_TEST_ARGS=' . $args_file);
putenv('DALO_TEST_INPUT=' . $input_file);
putenv('DALO_TEST_SECRET_SEEN=' . $secret_seen_file);
putenv('DALO_TEST_SECRET_MODE=' . $mode_file);
$secret = '0123456789abcdef0123456789abcdef';
$status = dalo_radius_check_network_radius(array(
    'CONFIG_STATUS_SERVER' => '127.0.0.1',
    'CONFIG_STATUS_PORT' => '18122',
    'CONFIG_STATUS_SECRET' => $secret,
));
$args = file_get_contents($args_file);
$input = file_get_contents($input_file);
$arguments = explode("\n", rtrim($args, "\n"));
$secret_option = array_search('-S', $arguments, true);
$temporary_secret = $secret_option === false ? '' : ($arguments[$secret_option + 1] ?? '');
status_assert($status['state'] === 'up', 'authenticated radclient response is healthy');
status_assert(strpos($args, $secret) === false, 'secret is absent from process arguments');
status_assert(strpos($args, "-S\n") !== false, 'radclient receives the secret-file option');
status_assert(file_get_contents($secret_seen_file) === $secret, 'radclient reads the expected secret from -S');
status_assert(trim(file_get_contents($mode_file)) === '600', 'temporary secret is mode 0600');
status_assert($temporary_secret !== '' && !file_exists($temporary_secret), 'temporary secret is removed after the probe');
status_assert(strpos($args, "-b\n") !== false, 'BlastRADIUS response checks are mandatory');
status_assert(strpos($input, 'Message-Authenticator = 0x00') !== false, 'probe explicitly includes Message-Authenticator');

$insecure_secret_file = $tmp . '/insecure-secret';
file_put_contents($insecure_secret_file, $secret);
chmod($insecure_secret_file, 0644);
$temporary_file = null;
status_assert(
    dalo_radius_status_secret_file(array('CONFIG_STATUS_SECRET_FILE' => $insecure_secret_file), $temporary_file) === null,
    'world-readable secret files are rejected'
);
unlink($insecure_secret_file);

$hang_script = "#!/bin/sh\nwhile :; do :; done\n";
file_put_contents($fake, $hang_script);
chmod($fake, 0700);
$started_at = microtime(true);
$hung = dalo_radius_run_process(array($fake), '', 0.2);
status_assert($hung !== null && $hung['timed_out'] && $hung['exit_code'] === 124, 'hung probe is terminated at its deadline');
status_assert(microtime(true) - $started_at < 2.0, 'hung probe cannot hold the status request indefinitely');

putenv('DALO_TEST_ARGS');
putenv('DALO_TEST_INPUT');
putenv('DALO_TEST_SECRET_SEEN');
putenv('DALO_TEST_SECRET_MODE');
foreach (array($args_file, $input_file, $secret_seen_file, $mode_file, $fake) as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
rmdir($tmp);

$config_read = file_get_contents(__DIR__ . '/../app/common/includes/config_read.php');
$init = file_get_contents(__DIR__ . '/../init.sh');
status_assert(strpos($config_read, 'daloradius.status.conf.php') !== false, 'generated status override is loaded separately');
status_assert(strpos($init, 'ensure_status_configuration') === false, 'legacy main config is not appended for status migration');
status_assert(strpos($init, 'php -l "$status_tmp"') !== false, 'generated override is linted before activation');
status_assert(strpos(file_get_contents(__DIR__ . '/../init-freeradius.sh'), 'mktemp "${secret_file}.tmp.XXXXXX"') !== false, 'persistent secret rotation is atomic');
status_assert(strpos(file_get_contents(__DIR__ . '/../app/operators/library/extensions/radius_server_info.php'), "DB::connect(\$dsn, array('timeout' => 2))") === false, 'unsupported PEAR timeout option is absent');

printf("service status regression checks: %d passed\n", $checks);
