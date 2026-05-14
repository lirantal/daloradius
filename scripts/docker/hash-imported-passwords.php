<?php
declare(strict_types=1);

function required_env(string $name): string
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        fwrite(STDERR, "Missing required environment variable: {$name}\n");
        exit(1);
    }

    return $value;
}

function stored_value_is_hash(string $storedValue): bool
{
    $info = password_get_info($storedValue);
    return !empty($info['algo']);
}

$host = required_env('MYSQL_HOST');
$port = required_env('MYSQL_PORT');
$database = required_env('MYSQL_DATABASE');
$user = required_env('MYSQL_USER');
$databaseSecret = required_env('MYSQL_PASSWORD');
$administratorSecret = required_env('DALORADIUS_ADMIN_PASSWORD');

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);
$pdo = new PDO($dsn, $user, $databaseSecret, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$select = $pdo->query('SELECT `id`, `username`, `password` FROM `operators` ORDER BY `id`');
$update = $pdo->prepare('UPDATE `operators` SET `password` = :hash WHERE `id` = :id');

$converted = 0;
$administratorUpdated = 0;

foreach ($select as $row) {
    $id = (int) $row['id'];
    $username = (string) $row['username'];
    $storedValue = (string) $row['password'];
    $newSecret = null;

    if ($username === 'administrator') {
        if (!stored_value_is_hash($storedValue) || !password_verify($administratorSecret, $storedValue)) {
            $newSecret = $administratorSecret;
            $administratorUpdated++;
        }
    } elseif (!stored_value_is_hash($storedValue)) {
        $newSecret = $storedValue;
    }

    if ($newSecret === null) {
        continue;
    }

    $update->execute([
        ':hash' => password_hash($newSecret, PASSWORD_DEFAULT),
        ':id' => $id,
    ]);
    $converted++;
}

printf("operator_passwords_converted=%d\n", $converted);
printf("administrator_password_updated=%d\n", $administratorUpdated);
