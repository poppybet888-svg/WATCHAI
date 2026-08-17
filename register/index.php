<?php

require __DIR__ . '/config/database.php';

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>WATCH AI</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #0f0f0f;
            color: #ffffff;
            font-family: Arial, sans-serif;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 90%;
            max-width: 600px;
            text-align: center;
        }

        h1 {
            font-size: 56px;
            margin: 0 0 15px;
            letter-spacing: 3px;
        }

        p {
            color: #bdbdbd;
            font-size: 18px;
        }

        .status {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 22px;
            border-radius: 10px;
            background: #151515;
            border: 1px solid #333;
            color: #00e676;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>WATCH AI</h1>

    <p>เว็บไซต์กำลังเริ่มต้นระบบ</p>

    <div class="status">
        ✓ เชื่อมต่อฐานข้อมูลสำเร็จ
    </div>

</div>

</body>
</html>
