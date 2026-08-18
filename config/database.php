<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Bangkok');

$host = 'sql312.infinityfree.com';
$port = 3306;

$dbname = 'if0_42681514_watchai';
$username = 'if0_42681514';

/*
 * ใส่ MySQL Password ของ InfinityFree ตรงนี้
 */
$password = 'PB12B245ss';

try {

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    die(
        'Database connection failed. ' .
        'Please check the MySQL settings.'
    );
}
