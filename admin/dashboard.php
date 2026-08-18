<?php

session_start();

require __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$customerCount = (int) $pdo
    ->query("SELECT COUNT(*) FROM customers")
    ->fetchColumn();

$activeCustomerCount = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM customers
        WHERE status = 'active'
    ")
    ->fetchColumn();

$prizeCount = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM prizes
        WHERE status = 'active'
    ")
    ->fetchColumn();

$spinCount = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM spin_history
    ")
    ->fetchColumn();

?>

<!DOCTYPE html>

<html lang="th">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Admin Dashboard - WATCH AI</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    min-height: 100vh;

    background:
        #090909;

    color: #fff;

    font-family:
        Arial,
        Tahoma,
        sans-serif;
}

.header {

    height: 70px;

    padding: 0 25px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    background: #121212;

    border-bottom: 1px solid #292929;
}

.logo {

    font-size: 24px;

    font-weight: 900;

    letter-spacing: 2px;

    color: #e3bc50;
}

.admin {

    color: #aaa;

    font-size: 14px;
}

.logout {

    margin-left: 15px;

    color: #fff;

    text-decoration: none;

    padding: 8px 13px;

    border: 1px solid #444;

    border-radius: 8px;
}

.container {

    width: 100%;

    max-width: 1200px;

    margin: auto;

    padding: 30px 20px;
}

.title {

    font-size: 30px;

    font-weight: 800;

    margin-bottom: 8px;
}

.subtitle {

    color: #888;

    margin-bottom: 30px;
}

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 30px;
}

.card {

    padding: 25px;

    background: #151515;

    border: 1px solid #303030;

    border-radius: 16px;
}

.card-title {

    color: #999;

    font-size: 14px;

    margin-bottom: 12px;
}

.number {

    color: #e5bd55;

    font-size: 34px;

    font-weight: 900;
}

.menu {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;
}

.menu a {

    display: block;

    padding: 25px;

    background: #151515;

    border: 1px solid #303030;

    border-radius: 16px;

    color: #fff;

    text-decoration: none;

    transition: .2s;
}

.menu a:hover {

    border-color: #c9a33a;

    transform: translateY(-2px);
}

.menu-title {

    color: #e5bd55;

    font-size: 20px;

    font-weight: 800;

    margin-bottom: 8px;
}

.menu-text {

    color: #999;

    font-size: 14px;
}

@media (max-width: 800px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);
    }

    .menu {

        grid-template-columns: 1fr;
    }

}

@media (max-width: 500px) {

    .stats {

        grid-template-columns: 1fr;
    }

    .header {

        padding: 0 15px;
    }

    .admin {

        display: none;
    }

}

</style>

</head>

<body>

<div class="header">

    <div class="logo">
        WATCH AI
    </div>

    <div>

        <span class="admin">
            <?= htmlspecialchars($_SESSION['admin_name']) ?>
        </span>

        <a
            class="logout"
            href="logout.php"
        >
            ออกจากระบบ
        </a>

    </div>

</div>

<div class="container">

    <div class="title">
        Dashboard
    </div>

    <div class="subtitle">
        ภาพรวมระบบ WATCH AI
    </div>

    <div class="stats">

        <div class="card">

            <div class="card-title">
                สมาชิกทั้งหมด
            </div>

            <div class="number">
                <?= $customerCount ?>
            </div>

        </div>

        <div class="card">

            <div class="card-title">
                สมาชิกที่ใช้งาน
            </div>

            <div class="number">
                <?= $activeCustomerCount ?>
            </div>

        </div>

        <div class="card">

            <div class="card-title">
                ของรางวัล
            </div>

            <div class="number">
                <?= $prizeCount ?>
            </div>

        </div>

        <div class="card">

            <div class="card-title">
                ประวัติการหมุน
            </div>

            <div class="number">
                <?= $spinCount ?>
            </div>

        </div>

    </div>

    <div class="menu">

        <a href="customers.php">

            <div class="menu-title">
                สมาชิก
            </div>

            <div class="menu-text">
                ดูและจัดการสมาชิก
            </div>

        </a>

        <a href="prizes.php">

            <div class="menu-title">
                ของรางวัล
            </div>

            <div class="menu-text">
                เพิ่ม แก้ไข และจัดการของรางวัล
            </div>

        </a>

        <a href="history.php">

            <div class="menu-title">
                ประวัติการหมุน
            </div>

            <div class="menu-text">
                ตรวจสอบรายการหมุนทั้งหมด
            </div>

        </a>

    </div>

</div>

</body>

</html>
