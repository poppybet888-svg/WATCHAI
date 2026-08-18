<?php

session_start();

require __DIR__ . '/../config/database.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login/');
    exit;
}

$customerId = (int) $_SESSION['customer_id'];

$stmt = $pdo->prepare("
    SELECT id, phone, full_name, spin_chance, status
    FROM customers
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$customerId]);

$customer = $stmt->fetch();

if (!$customer || $customer['status'] !== 'active') {
    session_unset();
    session_destroy();

    header('Location: ../login/');
    exit;
}

$_SESSION['customer_name'] = $customer['full_name'];
$_SESSION['spin_chance'] = (int) $customer['spin_chance'];

?>

<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>สมาชิก - WATCH AI</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    min-height: 100vh;

    font-family:
        Arial,
        Tahoma,
        sans-serif;

    color: #fff;

    background:
        radial-gradient(
            circle at top,
            #202020,
            #0b0b0b 55%,
            #050505
        );

    padding: 20px;
}

.wrap {

    width: 100%;

    max-width: 900px;

    margin: 0 auto;
}

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 30px;
}

.logo {

    font-size: 28px;

    font-weight: 800;

    letter-spacing: 2px;
}

.logout {

    color: #fff;

    text-decoration: none;

    padding: 10px 16px;

    border: 1px solid #444;

    border-radius: 10px;

    background: #171717;
}

.welcome {

    margin-bottom: 25px;
}

.welcome h1 {

    margin: 0 0 8px;

    font-size: 32px;
}

.welcome p {

    margin: 0;

    color: #999;
}

.grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;
}

.card {

    background: rgba(23,23,23,.96);

    border: 1px solid #333;

    border-radius: 18px;

    padding: 25px;
}

.label {

    color: #999;

    font-size: 14px;

    margin-bottom: 10px;
}

.value {

    font-size: 25px;

    font-weight: 700;

    word-break: break-word;
}

.spin {

    grid-column: 1 / -1;

    text-align: center;

    padding: 35px;
}

.spin h2 {

    margin-top: 0;
}

.chance {

    font-size: 52px;

    font-weight: 800;

    margin: 10px 0 20px;
}

.spin-button {

    display: inline-block;

    padding: 14px 35px;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #7c4dff,
            #5e35b1
        );

    color: #fff;

    text-decoration: none;

    font-weight: 700;
}

@media (max-width: 700px) {

    .grid {

        grid-template-columns: 1fr;
    }

    .spin {

        grid-column: auto;
    }

}

</style>

</head>

<body>

<div class="wrap">

    <div class="topbar">

        <div class="logo">
            WATCH AI
        </div>

        <a
            class="logout"
            href="logout.php"
        >
            ออกจากระบบ
        </a>

    </div>

    <div class="welcome">

        <h1>
            สวัสดี,
            <?= htmlspecialchars($customer['full_name']) ?>
            👋
        </h1>

        <p>
            ยินดีต้อนรับเข้าสู่ระบบสมาชิก WATCH AI
        </p>

    </div>

    <div class="grid">

        <div class="card">

            <div class="label">
                ชื่อสมาชิก
            </div>

            <div class="value">
                <?= htmlspecialchars($customer['full_name']) ?>
            </div>

        </div>

        <div class="card">

            <div class="label">
                เบอร์โทรศัพท์
            </div>

            <div class="value">
                <?= htmlspecialchars($customer['phone']) ?>
            </div>

        </div>

        <div class="card">

            <div class="label">
                สถานะ
            </div>

            <div class="value">
                กำลังใช้งาน
            </div>

        </div>

        <div class="card spin">

            <h2>
                สิทธิ์หมุนวงล้อ
            </h2>

            <div class="chance">
                <?= (int) $customer['spin_chance'] ?>
            </div>

            <p>
                จำนวนครั้งที่สามารถหมุนได้
            </p>

            <a
                class="spin-button"
                href="#"
            >
                หมุนวงล้อ
            </a>

        </div>

    </div>

</div>

</body>

</html>
