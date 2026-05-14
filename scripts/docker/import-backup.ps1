[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$DumpPath,

    [string]$ProjectDirectory = (Resolve-Path (Join-Path $PSScriptRoot "..\..")).Path,

    [switch]$SkipBuild
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"
if ($PSVersionTable.PSVersion.Major -ge 7) {
    $PSNativeCommandUseErrorActionPreference = $true
}

$InitialDirectory = (Get-Location).ProviderPath
$MariaDbCommand = @'
set -eu
defaults_file="$(mktemp)"
cleanup() {
    rm -f "$defaults_file"
}
trap cleanup EXIT
{
    printf "[client]\n"
    printf "user=radius\n"
    printf "password=%s\n" "$MYSQL_PASSWORD"
} > "$defaults_file"
chmod 600 "$defaults_file"
mariadb --defaults-extra-file="$defaults_file" --batch --skip-column-names radius
'@

function Assert-LastExitCode {
    param(
        [Parameter(Mandatory = $true)][string]$Action
    )

    if ($LASTEXITCODE -ne 0) {
        throw "$Action failed with exit code $LASTEXITCODE."
    }
}

function Resolve-InputPath {
    param([Parameter(Mandatory = $true)][string]$Path)

    if ([System.IO.Path]::IsPathRooted($Path)) {
        return (Resolve-Path -LiteralPath $Path).Path
    }

    return (Resolve-Path -LiteralPath (Join-Path $InitialDirectory $Path)).Path
}

function Invoke-Compose {
    param([Parameter(ValueFromRemainingArguments = $true)][string[]]$Arguments)

    & docker compose @Arguments
}

function Wait-ComposeServiceHealthy {
    param(
        [Parameter(Mandatory = $true)][string]$ServiceName,
        [int]$TimeoutSeconds = 180
    )

    $containerId = (Invoke-Compose ps -q $ServiceName).Trim()
    if (-not $containerId) {
        throw "No container found for Compose service '$ServiceName'."
    }

    $deadline = (Get-Date).AddSeconds($TimeoutSeconds)
    $status = ""
    do {
        $status = (& docker inspect --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' $containerId).Trim()
        Assert-LastExitCode "Inspecting Compose service '$ServiceName'"
        if ($status -eq "healthy" -or $status -eq "running") {
            return
        }

        Start-Sleep -Seconds 3
    } while ((Get-Date) -lt $deadline)

    throw "Service '$ServiceName' did not become healthy within $TimeoutSeconds seconds. Last status: $status"
}

function Invoke-MariaDbScalar {
    param([Parameter(Mandatory = $true)][string]$Sql)

    $result = $Sql | docker compose exec -T radius-mysql sh -lc $MariaDbCommand
    Assert-LastExitCode "Executing MariaDB scalar query"

    return ($result | Select-Object -First 1).Trim()
}

function Validate-ImportedSchema {
    $passwordWidth = Invoke-MariaDbScalar "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='operators' AND COLUMN_NAME='password';"
    if ([int]$passwordWidth -lt 95) {
        throw "Expected operators.password width >= 95, found $passwordWidth."
    }

    $passwordNullable = Invoke-MariaDbScalar "SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='operators' AND COLUMN_NAME='password';"
    if ($passwordNullable -ne "NO") {
        throw "Expected operators.password to be NOT NULL, found IS_NULLABLE=$passwordNullable."
    }

    foreach ($table in @("operators", "operators_acl", "radcheck", "radreply", "radusergroup", "radacct", "nas")) {
        $count = Invoke-MariaDbScalar "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$table';"
        if ([int]$count -ne 1) {
            throw "Expected imported table '$table' to exist."
        }
    }
}

function Invoke-PostImportPasswordConversion {
    docker compose build radius-web
    Assert-LastExitCode "Building radius-web for post-import password conversion"

    docker compose run --rm --no-deps --entrypoint php radius-web /usr/local/bin/daloradius-hash-imported-passwords.php
    Assert-LastExitCode "Converting imported operator passwords"
}

Push-Location $ProjectDirectory
try {
    $resolvedDump = Resolve-InputPath $DumpPath
    if (-not (Test-Path -LiteralPath ".env")) {
        throw "Missing .env in $ProjectDirectory. Copy .env.example to .env and set required variables first."
    }

    docker compose config --quiet
    Assert-LastExitCode "Validating Docker Compose configuration"

    gzip -t $resolvedDump
    Assert-LastExitCode "Validating dump integrity"

    docker compose stop radius radius-web
    Assert-LastExitCode "Stopping application services"

    docker compose up -d radius-mysql
    Assert-LastExitCode "Starting radius-mysql"
    Wait-ComposeServiceHealthy -ServiceName "radius-mysql"

    gzip -cd $resolvedDump | docker compose exec -T radius-mysql sh -lc $MariaDbCommand
    Assert-LastExitCode "Importing database dump"

    Get-ChildItem -LiteralPath (Join-Path $ProjectDirectory "docker\post-import-migrations") -Filter "*.sql" | Sort-Object Name | ForEach-Object {
        Write-Host "Applying post-import migration $($_.Name)"
        Get-Content -Raw -LiteralPath $_.FullName | docker compose exec -T radius-mysql sh -lc $MariaDbCommand
        Assert-LastExitCode "Applying post-import migration $($_.Name)"
    }

    Invoke-PostImportPasswordConversion

    Validate-ImportedSchema

    if ($SkipBuild) {
        docker compose up -d
        Assert-LastExitCode "Starting Docker Compose stack"
    } else {
        docker compose up -d --build
        Assert-LastExitCode "Building and starting Docker Compose stack"
    }

    docker compose ps
    Assert-LastExitCode "Listing Docker Compose services"
}
finally {
    Pop-Location
}
