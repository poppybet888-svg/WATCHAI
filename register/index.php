<?php

session_start();

require __DIR__ . '/../config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $phone === '' || $password === '' || $confirmPassword === '') {

        $message = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
        $messageType = 'error';

    } elseif (mb_strlen($fullName) < 2) {

        $message = 'กรุณากรอกชื่ออย่างน้อย 2 ตัวอักษร';
        $messageType = 'error';

    } elseif (!preg_match('/^[0-9+\- ]{8,20}$/', $phone)) {

        $message = 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง';
        $messageType = 'error';

    } elseif (strlen($password) < 6) {

        $message = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        $messageType = 'error';

    } elseif ($password !== $confirmPassword) {

        $message = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน';
        $messageType = 'error';

    } else {

        $stmt = $pdo->prepare(
            'SELECT id FROM customers WHERE phone = ? LIMIT 1'
        );

        $stmt->execute([$phone]);

        if ($stmt->fetch()) {

            $message = 'เบอร์โทรศัพท์นี้ถูกสมัครสมาชิกแล้ว';
            $messageType = 'error';

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO customers
                (phone, password, full_name, spin_chance, status)
                VALUES (?, ?, ?, 0, 'active')"
            );

            $stmt->execute([
                $phone,
                $hashedPassword,
                $fullName
            ]);

            $message = 'สมัครสมาชิกสำเร็จ! กำลังเข้าสู่ระบบ...';
            $messageType = 'success';

            header('Refresh: 1.5; url=../login/');
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

    <title>สมัครสมาชิก - WATCH AI</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;

            background:
                radial-gradient(
                    circle at top,
                    #202020 0%,
                    #0b0b0b 55%,
                    #050505 100%
                );

            color: #fff;

            font-family:
                Arial,
                Tahoma,
                sans-serif;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 20px;
        }

        .box {

            width: 100%;
            max-width: 430px;

            padding: 32px;

            background: rgba(23, 23, 23, .96);

            border: 1px solid #333;

            border-radius: 20px;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, .5);
        }

        .logo {

            text-align: center;

            font-size: 34px;

            font-weight: 800;

            letter-spacing: 3px;

            margin-bottom: 8px;
        }

        .sub {

            text-align: center;

            color: #999;

            margin-bottom: 28px;
        }

        .message {

            padding: 13px 15px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;

            line-height: 1.5;
        }

        .message.error {

            background: #351717;

            border: 1px solid #6b2a2a;

            color: #ff9b9b;
        }

        .message.success {

            background: #12351f;

            border: 1px solid #246b3b;

            color: #75e69a;
        }

        label {

            display: block;

            margin: 0 0 8px;

            font-size: 14px;

            color: #ddd;
        }

        input {

            width: 100%;

            padding: 14px 15px;

            margin-bottom: 18px;

            border: 1px solid #414141;

            border-radius: 11px;

            background: #101010;

            color: #fff;

            outline: none;

            font-size: 16px;
        }

        input:focus {

            border-color: #7c4dff;

            box-shadow:
                0 0 0 3px
                rgba(124, 77, 255, .12);
        }

        button {

            width: 100%;

            padding: 14px;

            border: 0;

            border-radius: 11px;

            background:
                linear-gradient(
                    135deg,
                    #7c4dff,
                    #5e35b1
                );

            color: #fff;

            font-size: 17px;

            font-weight: 700;

            cursor: pointer;
        }

        button:hover {

            filter: brightness(1.1);
        }

        .login-link {

            text-align: center;

            margin-top: 22px;

            color: #999;

            font-size: 14px;
        }

        .login-link a {

            color: #9b7bff;

            text-decoration: none;

            font-weight: 700;
        }

        @media (max-width: 480px) {

            .box {
                padding: 25px 20px;
            }

            .logo {
                font-size: 29px;
            }

        }

    </style>

</head>

<body>

<div class="box">

    <div class="logo">
        WATCH AI
    </div>

    <div class="sub">
        สร้างบัญชีสมาชิกใหม่
    </div>

    <?php if ($message !== ''): ?>

        <div class="message <?= htmlspecialchars($messageType) ?>">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>

    <form method="post" autocomplete="off">

        <label for="full_name">
            ชื่อ-นามสกุล
        </label>

        <input
            id="full_name"
            type="text"
            name="full_name"
            placeholder="กรอกชื่อ-นามสกุล"
            value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
            required
        >

        <label for="phone">
            เบอร์โทรศัพท์
        </label>

        <input
            id="phone"
            type="tel"
            name="phone"
            placeholder="กรอกเบอร์โทรศัพท์"
            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
            autocomplete="tel"
            required
        >

        <label for="password">
            รหัสผ่าน
        </label>

        <input
            id="password"
            type="password"
            name="password"
            placeholder="อย่างน้อย 6 ตัวอักษร"
            minlength="6"
            autocomplete="new-password"
            required
        >

        <label for="confirm_password">
            ยืนยันรหัสผ่าน
        </label>

        <input
            id="confirm_password"
            type="password"
            name="confirm_password"
            placeholder="กรอกรหัสผ่านอีกครั้ง"
            minlength="6"
            autocomplete="new-password"
            required
        >

        <button type="submit">
            สมัครสมาชิก
        </button>

    </form>

    <div class="login-link">

        มีบัญชีอยู่แล้ว?

        <a href="../login/">
            เข้าสู่ระบบ
        </a>

    </div>

</div>

</body>

</html>
