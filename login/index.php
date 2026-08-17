<?php

session_start();

require __DIR__ . '/../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($phone === '' || $password === '') {

        $message = 'กรุณากรอกข้อมูลให้ครบ';

    } else {

        $stmt = $pdo->prepare("
            SELECT id, phone, password, full_name, spin_chance, status
            FROM customers
            WHERE phone = ?
            LIMIT 1
        ");

        $stmt->execute([$phone]);

        $customer = $stmt->fetch();

        if (
            $customer &&
            $customer['status'] === 'active' &&
            password_verify($password, $customer['password'])
        ) {

            $_SESSION['customer_id'] = (int)$customer['id'];
            $_SESSION['customer_name'] = $customer['full_name'];
            $_SESSION['spin_chance'] = (int)$customer['spin_chance'];

            header('Location: ../customer/');
            exit;

        } else {

            $message = 'เบอร์โทรศัพท์หรือรหัสผ่านไม่ถูกต้อง';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="th">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>เข้าสู่ระบบ - WATCH AI</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #0f0f0f;
            color: white;
            font-family: Arial, sans-serif;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .box {
            width: 90%;
            max-width: 420px;
            padding: 35px;
            background: #171717;
            border: 1px solid #333;
            border-radius: 18px;
        }

        h1 {
            text-align: center;
            margin-top: 0;
        }

        .sub {
            text-align: center;
            color: #999;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 14px;
            margin-bottom: 18px;
            border: 1px solid #444;
            border-radius: 10px;
            background: #101010;
            color: white;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 14px;
            border: 0;
            border-radius: 10px;
            background: #6c3df5;
            color: white;
            font-size: 17px;
            cursor: pointer;
        }

        .message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 8px;
            background: #351717;
            color: #ff8a8a;
        }

    </style>

</head>

<body>

<div class="box">

    <h1>WATCH AI</h1>

    <div class="sub">
        เข้าสู่ระบบ
    </div>

    <?php if ($message !== ''): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <form method="post">

        <label>
            เบอร์โทรศัพท์
        </label>

        <input
            type="text"
            name="phone"
            placeholder="กรอกเบอร์โทรศัพท์"
            autocomplete="tel"
        >

        <label>
            รหัสผ่าน
        </label>

        <input
            type="password"
            name="password"
            placeholder="กรอกรหัสผ่าน"
        >

        <button type="submit">
            เข้าสู่ระบบ
        </button>

    </form>

</div>

</body>
</html>
