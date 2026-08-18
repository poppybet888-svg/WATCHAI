<?php

session_start();

require __DIR__ . '/../config/database.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login/');
    exit;
}

$customerId = (int) $_SESSION['customer_id'];

/*
|--------------------------------------------------------------------------
| API สำหรับหมุนวงล้อ
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json; charset=utf-8');

    try {

        $pdo->beginTransaction();

        // ตรวจสอบสมาชิกและจำนวนสิทธิ์ล่าสุด
        $stmt = $pdo->prepare("
            SELECT id, spin_chance, status
            FROM customers
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$customerId]);

        $customer = $stmt->fetch();

        if (!$customer || $customer['status'] !== 'active') {

            $pdo->rollBack();

            echo json_encode([
                'success' => false,
                'message' => 'ไม่พบสมาชิกหรือบัญชีถูกระงับ'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $spinChance = (int) $customer['spin_chance'];

        if ($spinChance <= 0) {

            $pdo->rollBack();

            echo json_encode([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์หมุนวงล้อแล้ว'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        // ดึงของรางวัลที่เปิดใช้งาน
        $stmt = $pdo->query("
            SELECT
                id,
                prize_name,
                probability,
                color,
                image
            FROM prizes
            WHERE status = 'active'
            ORDER BY id ASC
        ");

        $prizes = $stmt->fetchAll();

        if (!$prizes) {

            $pdo->rollBack();

            echo json_encode([
                'success' => false,
                'message' => 'ยังไม่มีของรางวัลในระบบ'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | สุ่มรางวัลตาม probability
        |--------------------------------------------------------------------------
        */

        $totalProbability = 0;

        foreach ($prizes as $prize) {

            $probability = (float) $prize['probability'];

            if ($probability > 0) {
                $totalProbability += $probability;
            }
        }

        if ($totalProbability <= 0) {

            $pdo->rollBack();

            echo json_encode([
                'success' => false,
                'message' => 'ยังไม่ได้ตั้งค่า Probability ของรางวัล'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $random = random_int(1, 1000000) / 1000000;

        $target = $random * $totalProbability;

        $current = 0;
        $selectedPrize = null;

        foreach ($prizes as $prize) {

            $probability = (float) $prize['probability'];

            if ($probability <= 0) {
                continue;
            }

            $current += $probability;

            if ($target <= $current) {

                $selectedPrize = $prize;

                break;
            }
        }

        // ป้องกันกรณี rounding
        if (!$selectedPrize) {
            $selectedPrize = $prizes[count($prizes) - 1];
        }

        /*
        |--------------------------------------------------------------------------
        | หักสิทธิ์ 1 ครั้ง
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            UPDATE customers
            SET spin_chance = spin_chance - 1
            WHERE id = ?
              AND spin_chance > 0
        ");

        $stmt->execute([$customerId]);

        if ($stmt->rowCount() !== 1) {

            $pdo->rollBack();

            echo json_encode([
                'success' => false,
                'message' => 'ไม่สามารถหักสิทธิ์การหมุนได้'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | บันทึกประวัติ
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            INSERT INTO spin_history
            (
                customer_id,
                prize_id
            )
            VALUES (?, ?)
        ");

        $stmt->execute([
            $customerId,
            (int) $selectedPrize['id']
        ]);

        $remaining = $spinChance - 1;

        $pdo->commit();

        $_SESSION['spin_chance'] = $remaining;

        echo json_encode([
            'success' => true,
            'prize_id' => (int) $selectedPrize['id'],
            'prize_name' => $selectedPrize['prize_name'],
            'color' => $selectedPrize['color'],
            'image' => $selectedPrize['image'],
            'remaining' => $remaining
        ], JSON_UNESCAPED_UNICODE);

        exit;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในระบบ'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| โหลดข้อมูลสำหรับหน้าเว็บ
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        full_name,
        phone,
        spin_chance
    FROM customers
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$customerId]);

$customer = $stmt->fetch();

if (!$customer) {

    session_destroy();

    header('Location: ../login/');
    exit;
}

$stmt = $pdo->query("
    SELECT
        id,
        prize_name,
        probability,
        color,
        image
    FROM prizes
    WHERE status = 'active'
    ORDER BY id ASC
");

$prizes = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="th">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>วงล้อ - WATCH AI</title>

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
            #242424 0%,
            #0d0d0d 55%,
            #050505 100%
        );

    color: #fff;

    font-family:
        Arial,
        Tahoma,
        sans-serif;

    padding: 20px;
}

.container {

    width: 100%;

    max-width: 800px;

    margin: auto;

    text-align: center;
}

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;
}

.logo {

    font-size: 25px;

    font-weight: 800;

    letter-spacing: 2px;
}

.back {

    color: #fff;

    text-decoration: none;

    border: 1px solid #444;

    background: #171717;

    padding: 9px 15px;

    border-radius: 10px;
}

.title {

    margin-top: 10px;

    font-size: 32px;

    font-weight: 800;
}

.subtitle {

    color: #999;

    margin-bottom: 20px;
}

.wheel-area {

    position: relative;

    width: min(90vw, 560px);

    margin: 0 auto 25px;
}

#wheelCanvas {

    display: block;

    width: 100%;

    height: auto;

    border-radius: 50%;

    background: #111;

    box-shadow:
        0 0 0 8px #242424,
        0 15px 50px rgba(0,0,0,.6);
}

.pointer {

    position: absolute;

    top: -12px;

    left: 50%;

    transform: translateX(-50%);

    width: 0;

    height: 0;

    border-left: 22px solid transparent;

    border-right: 22px solid transparent;

    border-top: 0;

    border-bottom: 45px solid #fff;

    filter:
        drop-shadow(0 3px 5px rgba(0,0,0,.5));

    z-index: 5;
}

.pointer::after {

    content: '';

    position: absolute;

    left: -11px;

    top: 4px;

    width: 22px;

    height: 22px;

    background: #fff;

    border-radius: 50%;
}

.center {

    position: absolute;

    top: 50%;

    left: 50%;

    transform: translate(-50%, -50%);

    width: 70px;

    height: 70px;

    border-radius: 50%;

    background: #111;

    border: 5px solid #fff;

    display: flex;

    align-items: center;

    justify-content: center;

    font-weight: 800;

    font-size: 12px;

    z-index: 4;

    box-shadow:
        0 4px 15px rgba(0,0,0,.5);
}

.info {

    background: #171717;

    border: 1px solid #333;

    border-radius: 16px;

    padding: 20px;

    margin-bottom: 20px;
}

.chance-label {

    color: #999;

    font-size: 14px;
}

.chance {

    font-size: 42px;

    font-weight: 800;

    margin: 5px 0;
}

.spin-btn {

    width: 100%;

    max-width: 350px;

    padding: 15px 25px;

    border: 0;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #7c4dff,
            #5e35b1
        );

    color: #fff;

    font-size: 18px;

    font-weight: 800;

    cursor: pointer;
}

.spin-btn:disabled {

    opacity: .5;

    cursor: not-allowed;
}

.result {

    display: none;

    margin-top: 20px;

    padding: 25px;

    background: #171717;

    border: 1px solid #444;

    border-radius: 16px;
}

.result.show {

    display: block;
}

.result-title {

    color: #aaa;

    margin-bottom: 8px;
}

.result-prize {

    font-size: 32px;

    font-weight: 800;
}

.empty {

    padding: 30px;

    background: #171717;

    border: 1px solid #333;

    border-radius: 15px;

    color: #aaa;
}

@media (max-width: 500px) {

    body {
        padding: 12px;
    }

    .title {
        font-size: 26px;
    }

    .center {
        width: 58px;
        height: 58px;
        font-size: 10px;
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="topbar">

        <div class="logo">
            WATCH AI
        </div>

        <a
            class="back"
            href="./"
        >
            ← กลับ
        </a>

    </div>

    <div class="title">
        🎯 วงล้อนำโชค
    </div>

    <div class="subtitle">
        สวัสดี <?= htmlspecialchars($customer['full_name']) ?>
    </div>

    <?php if (!$prizes): ?>

        <div class="empty">
            ยังไม่มีของรางวัลในระบบ
        </div>

    <?php else: ?>

        <div class="wheel-area">

            <div class="pointer"></div>

            <canvas
                id="wheelCanvas"
                width="600"
                height="600"
            ></canvas>

            <div class="center">
                WATCH<br>AI
            </div>

        </div>

        <div class="info">

            <div class="chance-label">
                สิทธิ์หมุนคงเหลือ
            </div>

            <div
                class="chance"
                id="spinChance"
            >
                <?= (int) $customer['spin_chance'] ?>
            </div>

            <button
                class="spin-btn"
                id="spinButton"
                type="button"
            >
                🎯 หมุนวงล้อ
            </button>

        </div>

        <div
            class="result"
            id="result"
        >

            <div class="result-title">
                🎉 คุณได้รับ
            </div>

            <div
                class="result-prize"
                id="resultPrize"
            ></div>

        </div>

    <?php endif; ?>

</div>

<script>

const prizes = <?= json_encode(
    $prizes,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
) ?>;

const canvas = document.getElementById('wheelCanvas');

const ctx = canvas
    ? canvas.getContext('2d')
    : null;

const spinButton =
    document.getElementById('spinButton');

const spinChance =
    document.getElementById('spinChance');

const result =
    document.getElementById('result');

const resultPrize =
    document.getElementById('resultPrize');

let rotation = 0;

let spinning = false;


/*
|--------------------------------------------------------------------------
| วาดวงล้อ
|--------------------------------------------------------------------------
*/

function drawWheel() {

    if (!canvas || !ctx || !prizes.length) {
        return;
    }

    const width = canvas.width;

    const height = canvas.height;

    const centerX = width / 2;

    const centerY = height / 2;

    const radius = Math.min(
        centerX,
        centerY
    ) - 10;

    const slice =
        (Math.PI * 2) / prizes.length;

    ctx.clearRect(
        0,
        0,
        width,
        height
    );

    prizes.forEach((prize, index) => {

        const start =
            rotation +
            index * slice;

        const end =
            start + slice;

        ctx.beginPath();

        ctx.moveTo(
            centerX,
            centerY
        );

        ctx.arc(
            centerX,
            centerY,
            radius,
            start,
            end
        );

        ctx.closePath();

        ctx.fillStyle =
            prize.color ||
            '#7c4dff';

        ctx.fill();

        ctx.strokeStyle = '#ffffff';

        ctx.lineWidth = 3;

        ctx.stroke();


        // ข้อความ
        ctx.save();

        ctx.translate(
            centerX,
            centerY
        );

        ctx.rotate(
            start + slice / 2
        );

        ctx.textAlign = 'right';

        ctx.fillStyle = '#ffffff';

        ctx.font =
            prizes.length > 10
            ? 'bold 18px Arial'
            : 'bold 22px Arial';

        let text =
            prize.prize_name || '';

        if (text.length > 18) {
            text = text.substring(0, 18) + '...';
        }

        ctx.fillText(
            text,
            radius - 25,
            8
        );

        ctx.restore();

    });

    // วงกลมตรงกลาง
    ctx.beginPath();

    ctx.arc(
        centerX,
        centerY,
        45,
        0,
        Math.PI * 2
    );

    ctx.fillStyle = '#111';

    ctx.fill();

    ctx.strokeStyle = '#fff';

    ctx.lineWidth = 5;

    ctx.stroke();
}


/*
|--------------------------------------------------------------------------
| หมุนไปยังช่องรางวัล
|--------------------------------------------------------------------------
*/

function getPrizeAngle(prizeId) {

    const index =
        prizes.findIndex(
            prize =>
                Number(prize.id) ===
                Number(prizeId)
        );

    if (index < 0) {
        return 0;
    }

    const slice =
        (Math.PI * 2) / prizes.length;

    /*
    |--------------------------------------------------------------------------
    | pointer อยู่ด้านบน = -PI/2
    |--------------------------------------------------------------------------
    */

    const target =
        -Math.PI / 2 -
        (index * slice) -
        (slice / 2);

    return target;
}


function animateSpin(targetRotation) {

    const startRotation = rotation;

    const difference =
        targetRotation -
        startRotation;

    const duration = 5000;

    const startTime = performance.now();


    function animation(currentTime) {

        const elapsed =
            currentTime - startTime;

        const progress =
            Math.min(
                elapsed / duration,
                1
            );

        /*
        |--------------------------------------------------------------------------
        | easeOutCubic
        |--------------------------------------------------------------------------
        */

        const ease =
            1 -
            Math.pow(
                1 - progress,
                3
            );

        rotation =
            startRotation +
            difference * ease;

        drawWheel();

        if (progress < 1) {

            requestAnimationFrame(animation);

        } else {

            rotation =
                targetRotation;

            drawWheel();

            spinning = false;

            if (spinButton) {
                spinButton.disabled = false;
            }
        }
    }

    requestAnimationFrame(animation);
}


/*
|--------------------------------------------------------------------------
| กดหมุน
|--------------------------------------------------------------------------
*/

if (spinButton) {

    spinButton.addEventListener(
        'click',
        async function () {

            if (spinning) {
                return;
            }

            const chance =
                Number(spinChance.textContent);

            if (chance <= 0) {

                alert(
                    'คุณไม่มีสิทธิ์หมุนวงล้อแล้ว'
                );

                return;
            }

            spinning = true;

            spinButton.disabled = true;

            result.classList.remove('show');

            try {

                const response =
                    await fetch(
                        'wheel.php',
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type':
                                    'application/x-www-form-urlencoded'
                            },
                            body: 'spin=1'
                        }
                    );

                const data =
                    await response.json();

                if (!data.success) {

                    alert(
                        data.message ||
                        'ไม่สามารถหมุนวงล้อได้'
                    );

                    spinning = false;

                    spinButton.disabled = false;

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | หมุนหลายรอบก่อนหยุด
                |--------------------------------------------------------------------------
                */

                const target =
                    getPrizeAngle(
                        data.prize_id
                    );

                const current =
                    rotation;

                const currentNormalized =
                    current %
                    (Math.PI * 2);

                let difference =
                    target -
                    currentNormalized;

                if (difference < 0) {
                    difference +=
                        Math.PI * 2;
                }

                const extraRounds =
                    Math.PI * 2 * 6;

                const finalRotation =
                    current +
                    extraRounds +
                    difference;

                animateSpin(finalRotation);

                /*
                |--------------------------------------------------------------------------
                | แสดงผลหลังหมุนเสร็จ
                |--------------------------------------------------------------------------
                */

                setTimeout(
                    function () {

                        spinChance.textContent =
                            data.remaining;

                        resultPrize.textContent =
                            data.prize_name;

                        result.classList.add(
                            'show'
                        );

                    },
                    5100
                );

            } catch (error) {

                console.error(error);

                alert(
                    'ไม่สามารถเชื่อมต่อระบบได้'
                );

                spinning = false;

                spinButton.disabled = false;
            }

        }
    );
}

drawWheel();

</script>

</body>

</html>
