<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Hover Card with Animated Border</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #0c0c25, #1a1a40);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card-container {
            display: flex;
            gap: 2rem;
            position: relative;
            z-index: 1;
        }

        .card {
            position: relative;
            width: 300px;
            height: 400px;
            background: linear-gradient(135deg, #1a1a40, #2c2c54);
            border-radius: 20px;
            overflow: visible; /* อนุญาตให้ตัวละครทะลุออกมา */
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.6s ease, box-shadow 0.6s ease;
        }

        .card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            width: calc(100% + 4px);
            height: calc(100% + 4px);
            border-radius: 20px;
            background: linear-gradient(90deg, rgba(255, 0, 0, 0.5), rgba(255, 140, 0, 0.5), rgba(255, 0, 0, 0.5));
            z-index: -1;
            animation: borderRun 2s linear infinite; /* เปลี่ยนเป็นไฟวิ่งรอบ */
        }

        @keyframes borderRun {
            0% {
                background-position: 0% 0%;
            }
            100% {
                background-position: 200% 0%;
            }
        }

        .card .wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
        }

        .card .character {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            width: 150%;
            height: auto;
            transition: transform 0.6s ease-in-out;
            z-index: 2;
        }

        .card:hover .character {
            transform: translate(-50%, -70%) scale(1.2); /* ตัวละครทะลุออกจากการ์ด */
        }

        .card .info {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: #fff;
            z-index: 3;
        }

        .card .info h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .card .info span {
            font-size: 1rem;
            color: #bbb;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <!-- Card 1 -->
        <div class="card">
            <div class="wrapper">
                <img src="img/lol.jpg" alt="League of Legends">
                <img class="character" src="img/jinx.png" alt="LOL Character">
                <div class="info">
                    <h3>League of Legends</h3>
                    <span>198 in stock</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>