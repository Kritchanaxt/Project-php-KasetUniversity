<?php
// เชื่อมต่อฐานข้อมูล
$servername = getenv('DB_SERVER') ?: '158.108.101.153';
$username = getenv('DB_USERNAME') ?: 'std6630202015';
$password = getenv('DB_PASSWORD') ?: 'g3#Vjp8L';
$dbname = getenv('DB_NAME') ?: 'it_std6630202015';

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// ดึงข้อมูลสินค้า
$query = "SELECT * FROM Accounts WHERE status = 'available' ORDER BY account_id ASC";
$result = mysqli_query($conn, $query);

// ทำคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['purchase'])) {
    $account_id = mysqli_real_escape_string($conn, $_POST['account_id']);
    $update_query = "UPDATE Accounts SET status = 'sold' WHERE account_id = '$account_id'";
    
    if (mysqli_query($conn, $update_query)) {
        header("Location: purchase.php");
        exit();
    } else {
        echo "Error updating product: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Store - Purchase</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* ตั้งค่า UI พื้นฐาน */
        body {
            font-family: 'Kanit', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #141E30, #243B55);
            color: white;
            text-align: center;
        }

        /* เมนูด้านบน */
        .navbar {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            background: #007bff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s, transform 0.3s;
        }

        .navbar a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        /* Container หลัก */
        .container {
            width: 90%;
            margin: 20px auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        /* Card UI */
        .card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            width: 280px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .card-content {
            padding: 15px;
        }

        .card h2 {
            margin: 10px 0;
            font-size: 20px;
            color: #FFD700;
        }

        .card p {
            font-size: 16px;
            color: #ddd;
        }

        /* ปุ่มซื้อ */
        .buy-btn {
            background: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 80%;
            font-size: 16px;
            margin-top: 10px;
            transition: background 0.3s, transform 0.3s;
        }

        .buy-btn:hover {
            background: #218838;
            transform: scale(1.1);
        }

        /* Dropdown เลือกการชำระเงิน */
        .payment-select {
            width: 80%;
            padding: 8px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            margin-top: 10px;
        }

    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div>🎮 Game Store</div>
        <div>
            <a href="home.php">Home</a>
            <a href="purchase.php">Buy Games</a>
            <a href="edit_product.php">Edit Products</a>
        </div>
    </div>

    <h1>🕹️ ซื้อเกมที่คุณต้องการ 🕹️</h1>

    <div class="container">
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <div class="card">
                <?php 
                    // ตรวจสอบว่ามีภาพเกมไหม ถ้าไม่มีให้ใช้ภาพ default
                    $image_path = !empty($row['game_image']) ? "game_images/" . $row['game_image'] : "img/valorant.png";
                ?>
                <img src="<?php echo $image_path; ?>" alt="Game Image">
                <div class="card-content">
                    <h2><?php echo htmlspecialchars($row['username']); ?></h2>
                    <p><?php echo htmlspecialchars($row['details']); ?></p>
                    <p><strong>💰 ราคา:</strong> <?php echo number_format($row['price'], 2); ?> บาท</p>

                    <!-- ฟอร์มทำรายการซื้อ -->
                    <form method="POST" action="purchase.php">
                        <input type="hidden" name="account_id" value="<?php echo htmlspecialchars($row['account_id']); ?>">
                        
                        <select name="payment_method" class="payment-select" required>
                            <option value="">เลือกวิธีชำระเงิน</option>
                            <option value="credit_card">💳 บัตรเครดิต</option>
                            <option value="paypal">💰 PayPal</option>
                            <option value="bank_transfer">🏦 โอนเงินธนาคาร</option>
                        </select>
                        
                        <button type="submit" name="purchase" class="buy-btn">ซื้อเลย</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</body>
</html>
