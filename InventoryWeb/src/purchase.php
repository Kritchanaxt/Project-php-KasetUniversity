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

// เริ่มเซสชันสำหรับตะกร้า
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// เพิ่มสินค้าลงตะกร้า
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $account_id = $_POST['account_id'];
    $game_id = $_POST['game_id'];

    if (!in_array($account_id, array_column($_SESSION['cart'], 'account_id'))) {
        $_SESSION['cart'][] = [
            'account_id' => $account_id,
            'game_id' => $game_id
        ];
    }
    header("Location: purchase.php");
    exit();
}

// ลบสินค้าออกจากตะกร้า
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_from_cart'])) {
    $account_id = $_POST['account_id'];
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) use ($account_id) {
        return $item['account_id'] != $account_id;
    });
    header("Location: purchase.php");
    exit();
}

// ทำคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['purchase'])) {
    // ตรวจสอบว่ามีสินค้าในตะกร้าหรือไม่
    if (empty($_SESSION['cart'])) {
        die("Error: Cart is empty. Please add items to your cart.");
    }

    // ตั้งค่า user_id (หากไม่มีให้ใช้ guest_user)
    $user_id = $_SESSION['user_id'] ?? 'guest_user';

    // อัปเดตสถานะใน Accounts และบันทึกลง TempAccounts
    foreach ($_SESSION['cart'] as $item) {
        $account_id = mysqli_real_escape_string($conn, $item['account_id']);
        $game_id = mysqli_real_escape_string($conn, $item['game_id']);

        // อัปเดตสถานะบัญชี
        $update_query = "UPDATE Accounts SET status = 'sold' WHERE account_id = '$account_id'";
        if (!mysqli_query($conn, $update_query)) {
            die("Error updating Accounts: " . mysqli_error($conn));
        }

        // บันทึกข้อมูลการซื้อไปยัง TempAccounts
        $insert_temp_query = "INSERT INTO TempAccounts (account_id, game_id, user_id, username, password, details, price, status)
                              SELECT account_id, game_id, '$user_id', username, password, details, price, status
                              FROM Accounts WHERE account_id = '$account_id'";
        if (!mysqli_query($conn, $insert_temp_query)) {
            die("Error inserting into TempAccounts: " . mysqli_error($conn));
        }
    }

    // ล้างตะกร้า
    $_SESSION['cart'] = [];
    header("Location: confirmation.php");
    exit();
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
        body {
            font-family: 'Kanit', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #141E30, #243B55);
            color: white;
            text-align: center;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .cart {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .cart span {
            margin-left: 10px;
            background: #dc3545;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 14px;
            font-weight: bold;
        }

        /* Modal ตะกร้า */
        .modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 600px;
            background: rgba(0, 0, 0, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            display: none;
        }

        .modal h2 {
            color: #FFD700;
        }

        .modal table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .modal table th, .modal table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .modal table th {
            background: #007bff;
            color: white;
        }

        .modal button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }

        .modal button:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .modal .close-btn {
            background: #6c757d;
        }

        .modal .close-btn:hover {
            background: #5a6268;
        }

        /* Card */
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            width: 250px;
            text-align: center;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .card h2 {
            font-size: 20px;
            color: #FFD700;
        }

        .card p {
            font-size: 16px;
        }

        .card button {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .card button:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div>🎮 Game Store</div>
        <div class="cart" onclick="toggleModal()">
            🛒 ตะกร้า
            <span id="cart-count"><?php echo count($_SESSION['cart']); ?></span>
        </div>
    </div>

    <!-- Modal ตะกร้า -->
    <div class="modal" id="cart-modal">
        <h2>🛒 ตะกร้าสินค้าของคุณ</h2>
        <?php if (!empty($_SESSION['cart'])): ?>
            <table>
                <thead>
                    <tr>
                        <th>Game ID</th>
                        <th>Account ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['game_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['account_id']); ?></td>
                            <td>
                                <form method="POST" action="purchase.php" style="display:inline;">
                                    <input type="hidden" name="account_id" value="<?php echo htmlspecialchars($item['account_id']); ?>">
                                    <button type="submit" name="remove_from_cart">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="POST" action="purchase.php">
                <button type="submit" name="purchase">ชำระเงิน</button>
            </form>
        <?php else: ?>
            <p>ไม่มีสินค้าในตะกร้า</p>
        <?php endif; ?>
        <button class="close-btn" onclick="toggleModal()">ปิด</button>
    </div>

    <!-- Container -->
    <div class="container">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="card">
                <h2><?php echo htmlspecialchars($row['game_id']); ?></h2>
                <p>ราคา: <?php echo number_format($row['price'], 2); ?> บาท</p>
                <form method="POST" action="purchase.php">
                    <input type="hidden" name="account_id" value="<?php echo htmlspecialchars($row['account_id']); ?>">
                    <input type="hidden" name="game_id" value="<?php echo htmlspecialchars($row['game_id']); ?>">
                    <button type="submit" name="add_to_cart">เพิ่มในตะกร้า</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('cart-modal');
            modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>
