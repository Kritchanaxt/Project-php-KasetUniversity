<?php
require 'db_connection.php';

// คำสั่ง SQL ดึงข้อมูลสินค้า
$query = "SELECT game_id, 
                 COUNT(*) AS total_accounts, 
                 SUM(CASE WHEN status != 'sold' OR status IS NULL THEN 1 ELSE 0 END) AS available_accounts
          FROM Accounts
          GROUP BY game_id
          ORDER BY available_accounts DESC";

$result = $conn->query($query);

if (!$result) {
    die("❌ SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📦 Show Products</title>
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
            padding: 20px;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
            max-width: 90%;
        }

        .card {
            position: relative;
            width: 280px;
            height: 380px;
            background: linear-gradient(135deg, #1a1a40, #2c2c54);
            border-radius: 20px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.6s ease, box-shadow 0.6s ease;
            text-align: center;
            padding: 20px;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 255, 255, 0.5);
        }

        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        .card h3 {
            font-size: 1.5rem;
            color: white;
            margin: 10px 0;
        }

        .card span {
            font-size: 1rem;
            color: #bbb;
            font-weight: bold;
        }

        .stock-value {
            font-size: 1.5rem;
            color: cyan;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // ตั้งค่าพาธรูปของเกมโดยใช้ game_id เช่น "img/LOL.png"
                $imagePath = "img/" . strtolower($row['game_id']) . "TFT.png";
                
                // ตรวจสอบว่ารูปภาพมีอยู่จริง ถ้าไม่มีให้ใช้ default image
                if (!file_exists($imagePath)) {
                    $imagePath = "img/LOL.png"; // ตั้งค่ารูปภาพเริ่มต้นถ้าไม่มีรูปของเกม
                }

                echo '<div class="card">
                        <img src="' . $imagePath . '" alt="' . $row['game_id'] . '">
                        <h3>' . $row['game_id'] . '</h3>
                        <span>🎮 In Stock</span>
                        <div class="stock-value">' . $row['available_accounts'] . '</div>
                      </div>';
            }
        } else {
            echo "<p>❌ ไม่มีสินค้าในระบบ</p>";
        }
        ?>
    </div>
</body>
</html>
