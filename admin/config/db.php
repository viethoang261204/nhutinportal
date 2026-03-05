<?php
declare(strict_types=1);

function getDbConnection(): PDO
{
    $host = getenv('DB_HOST') ?: 'dpg-d6kru3ua2pns7396assg-a.singapore-postgres.render.com';
    $port = (int) (getenv('DB_PORT') ?: 5432);
    $dbName = getenv('DB_NAME') ?: 'nhutin_portal';
    $username = getenv('DB_USER') ?: 'nhutin_user';
    $password = getenv('DB_PASSWORD') ?: 'q4t1KUVWizmK3jjHCTy7xlkIRMjyDOJ8';
    $sslMode = getenv('DB_SSLMODE') ?: 'require';

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbName};sslmode={$sslMode}";

    return new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}

