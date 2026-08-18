<?php

session_start();

require __DIR__ . '/../config/database.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $message = 'กรุณากรอก Username และ Password';

    } else {

        $stmt = $pdo->prepare("
            SELECT
                id,
                username,
                password,
                full_name,
                status
            FROM admins
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);

        $admin = $stmt->fetch();

        if (
            $admin &&
            $admin['status'] === 'active' &&
            password_verify($password, $admin['password'])
        ) {

            session_regenerate_id(true);

            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];

            header('Location: dashboard.php');
            exit;

        } else {

            $message = 'Username หรือ Password ไม่ถูกต้อง';
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

<title>Admin Login - WATCH AI</title>

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
            #292929,
            #0c0c0c 55%,
            #050505
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

    max-width: 420px;

    padding: 35px;

    background: #151515;

    border: 1px solid #333;

    border-radius: 20px;

    box-shadow:
        0 20px 60px rgba(0,0,0,.6);
}

.logo {

    text-align: center;

    font-size: 32px;

    font-weight: 900;

    letter-spacing: 3px;

    color: #e5bd55;

    margin-bottom: 8px;
}

.title {

    text-align: center;

    color: #aaa;

    margin-bottom: 30px;
}

.message {

    background: #351717;

    border: 1px solid #6b2a2a;

    color: #ff9b9b;

    padding: 12px;

    border-radius: 10px;

    margin-bottom: 20px;

    font-size: 14px;
}

label {

    display: block;

    margin-bottom: 8px;

    color: #ddd;

    font-size: 14px;
}

input {

    width: 100%;

    padding: 14px;

    margin-bottom: 18px;

    background: #0d0d0d;

    color: #fff;

    border: 1px solid #444;

    border-radius: 10px;

    outline: none;

    font-size: 16px;
}

input:focus {

    border-color: #d4af37;

    box-shadow:
        0 0 0 3px
        rgba(212,175,55,.12);
}

button {

    width: 100%;

    padding: 14px;

    border: 0;

    border-radius: 10px;

    background:
        linear-gradient(
            135deg,
            #f2d477,
            #c49527,
            #f2d477
        );

    color: #111;

    font-size: 17px;

    font-weight: 800;

    cursor: pointer;
}

button:hover {

    filter: brightness(1.08);
}

</style>

</head>

<body>

<div class="box">

    <div class="logo">
        WATCH AI
    </div>

    <div class="title">
        ADMINISTRATOR
    </div>

    <?php if ($message !== ''): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <form method="post">

        <label>
            Username
        </label>

        <input
            type="text"
            name="username"
            placeholder="กรอก Username"
            autocomplete="username"
            required
        >

        <label>
            Password
        </label>

        <input
            type="password"
            name="password"
            placeholder="กรอก Password"
            autocomplete="current-password"
            required
        >

        <button type="submit">
            เข้าสู่ระบบ
        </button>

    </form>

</div>

</body>

</html>
