<?php

/**
 * WATCH AI
 * SQLite Database
 */

declare(strict_types=1);

date_default_timezone_set('Asia/Bangkok');

// =====================================
// Database location
// =====================================

$storageDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage';

if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

$dbFile = $storageDir . DIRECTORY_SEPARATOR . 'watchai.sqlite';

// =====================================
// Connect SQLite
// =====================================

try {

    $pdo = new PDO('sqlite:' . $dbFile);

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    $pdo->setAttribute(
        PDO::ATTR_EMULATE_PREPARES,
        false
    );

    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');

} catch (PDOException $e) {

    http_response_code(500);

    die(
        'Database connection failed. ' .
        'Please enable PDO_SQLite on the hosting server.'
    );
}

// =====================================
// Create tables
// =====================================

$tables = [

    // Settings
    "
    CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        site_name TEXT NOT NULL DEFAULT 'WATCH AI',
        daily_code TEXT NOT NULL DEFAULT '123456',
        created_at TEXT NOT NULL
            DEFAULT (datetime('now','+7 hours')),
        updated_at TEXT NOT NULL
            DEFAULT (datetime('now','+7 hours'))
    )
    ",

    // Customers
    "
    CREATE TABLE IF NOT EXISTS customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        phone TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        full_name TEXT NOT NULL,
        spin_chance INTEGER NOT NULL DEFAULT 0,
        status TEXT NOT NULL DEFAULT 'active',
        created_at TEXT NOT NULL
            DEFAULT (datetime('now','+7 hours'))
    )
    ",

    // Prizes
    "
    CREATE TABLE IF NOT EXISTS prizes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        prize_name TEXT NOT NULL,
        probability REAL NOT NULL DEFAULT 0,
        color TEXT NOT NULL DEFAULT '#FFD700',
        image TEXT NOT NULL DEFAULT '',
        status TEXT NOT NULL DEFAULT 'active',
        created_at TEXT NOT NULL
            DEFAULT (datetime('now','+7 hours'))
    )
    ",

    // Spin history
    "
    CREATE TABLE IF NOT EXISTS spin_history (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL,
        prize_id INTEGER NOT NULL,
        spin_time TEXT NOT NULL
            DEFAULT (datetime('now','+7 hours')),

        FOREIGN KEY(customer_id)
            REFERENCES customers(id)
            ON DELETE CASCADE,

        FOREIGN KEY(prize_id)
            REFERENCES prizes(id)
            ON DELETE RESTRICT
    )
    ",

    // Admin
    "
    CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        full_name TEXT NOT NULL DEFAULT 'Administrator',
        status TEXT NOT NULL DEFAULT 'active',
        created_at TEXT NOT NULL
            DEFAULT (datetime('now','+7 hours'))
    )
    "
];

foreach ($tables as $sql) {
    $pdo->exec($sql);
}

// =====================================
// Default settings
// =====================================

$stmt = $pdo->prepare("
    INSERT OR IGNORE INTO settings
    (
        id,
        site_name,
        daily_code
    )
    VALUES
    (
        1,
        ?,
        ?
    )
");

$stmt->execute([
    'WATCH AI',
    '123456'
]);

// =====================================
// Default admin
// =====================================

$adminPassword = password_hash(
    'ChangeMe!2026',
    PASSWORD_DEFAULT
);

$stmt = $pdo->prepare("
    INSERT OR IGNORE INTO admins
    (
        id,
        username,
        password,
        full_name,
        status
    )
    VALUES
    (
        1,
        ?,
        ?,
        ?,
        'active'
    )
");

$stmt->execute([
    'admin',
    $adminPassword,
    'WATCH AI Administrator'
]);

?>
