<?php
declare(strict_types=1);

function getDbConnection(): PDO
{
    $host     = getenv('DB_HOST') ?: 'localhost';
    $port     = (int) (getenv('DB_PORT') ?: 3306);
    $dbName   = getenv('DB_NAME') ?: 'your_db_name';
    $username = getenv('DB_USER') ?: 'your_db_user';
    $password = getenv('DB_PASSWORD') ?: 'your_db_password';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

    return new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
}
