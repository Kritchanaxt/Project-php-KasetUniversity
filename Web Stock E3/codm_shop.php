<?php
// เริ่มเซสชัน
session_start();

// ตรวจสอบการล็อกอิน
$logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
if (!$logged_in && (isset($_POST['add_to_cart']) || isset($_POST['purchase']))) {
    // ถ้าไม่ได้ล็อกอินและพยายามซื้อ ให้ redirect ไปหน้าล็อกอิน
    header("Location: login.php");
    exit();
}

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

$user_point = 0;
if ($logged_in) {
    $user_id = $_SESSION['user_id'];
    
    try {
        // ดึงข้อมูลล่าสุดจากฐานข้อมูล รวมทั้ง point
        $stmt = $conn->prepare("SELECT username, email, phone, point FROM Users WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // อัปเดตเซสชันด้วยข้อมูลล่าสุด
            $_SESSION["username"] = $row["username"];
            $_SESSION["email"] = $row["email"];
            $_SESSION["phone"] = $row["phone"];
            $_SESSION["points"] = $row["point"]; // เก็บพอยต์ในเซสชัน
            
            // กำหนดค่าพอยต์สำหรับใช้ในไฟล์นี้
            $user_point = $row["point"];
        } else {
            // กรณีไม่พบข้อมูลผู้ใช้
            $user_point = 0;
            $_SESSION["points"] = 0;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        // กรณีเกิดข้อผิดพลาด
        error_log("Error fetching user data: " . $e->getMessage());
        $user_point = 0;
        $_SESSION["points"] = 0;
    }
}

// ดึงข้อมูลสินค้า CODM เท่านั้น
$query = "SELECT * FROM Accounts WHERE status = 'available' AND game_id = '#CODM' ORDER BY account_id ASC";
$result = mysqli_query($conn, $query);

// ตรวจสอบตะกร้า
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// เพิ่มสินค้าลงตะกร้า
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!$logged_in) {
        header("Location: login.php");
        exit();
    }
    
    $account_id = $_POST['account_id'];
    $game_id = $_POST['game_id'];
    $price = $_POST['price'];

    // ตรวจสอบว่ามีในตะกร้าแล้วหรือไม่
    if (!in_array($account_id, array_column($_SESSION['cart'], 'account_id'))) {
        $_SESSION['cart'][] = [
            'account_id' => $account_id,
            'game_id' => $game_id,
            'price' => $price
        ];
    }
    
    // ไม่ต้อง redirect เพื่อให้อยู่หน้าเดิม
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ลบสินค้าออกจากตะกร้า
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_from_cart'])) {
    $account_id = $_POST['account_id'];
    
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) use ($account_id) {
        return $item['account_id'] != $account_id;
    });
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ทำคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['purchase'])) {
    if (!$logged_in) {
        header("Location: login.php");
        exit();
    }
    
    // ตรวจสอบว่ามีสินค้าในตะกร้าหรือไม่
    if (empty($_SESSION['cart'])) {
        $error_message = "ตะกร้าของคุณว่างเปล่า";
    } else {
        // คำนวณราคารวม
        $total_price = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_price += $item['price'];
        }
        
        // ตรวจสอบว่ามี point เพียงพอไหม
        if ($user_point < $total_price) {
            $error_message = "คุณมี point ไม่เพียงพอ (มี $user_point point, ต้องการ $total_price point)";
        } else {
            // เริ่ม transaction
            mysqli_begin_transaction($conn);
            try {
                // หัก point จากผู้ใช้
                $new_point = $user_point - $total_price;
                
                // ใช้ prepared statement เพื่อความปลอดภัยและตรวจสอบข้อผิดพลาดได้ดีขึ้น
                $update_stmt = $conn->prepare("UPDATE Users SET point = ? WHERE user_id = ?");
                if (!$update_stmt) {
                    throw new Exception("Prepare update statement failed: " . $conn->error);
                }
                
                $update_stmt->bind_param("ds", $new_point, $user_id);
                $update_result = $update_stmt->execute();
                
                if (!$update_result) {
                    throw new Exception("Failed to update user points: " . $update_stmt->error);
                }
                
                $update_stmt->close();
                
                // บันทึกประวัติการซื้อและอัปเดตสถานะสินค้า
foreach ($_SESSION['cart'] as $item) {
    $account_id = $item['account_id'];
    $price = $item['price'];
    $original_game_id = $item['game_id']; // ใช้ game_id ที่มากับไอเทมในตะกร้า
    
    // อัปเดตสถานะบัญชี
    $update_account_stmt = $conn->prepare("UPDATE Accounts SET status = 'sold' WHERE account_id = ?");
    if (!$update_account_stmt) {
        throw new Exception("Prepare account update failed: " . $conn->error);
    }
    
    $update_account_stmt->bind_param("s", $account_id);
    if (!$update_account_stmt->execute()) {
        throw new Exception("Failed to update account status: " . $update_account_stmt->error);
    }
    
    // ตรวจสอบว่ามีการอัปเดตจริงหรือไม่
    if ($update_account_stmt->affected_rows == 0) {
        throw new Exception("No rows updated when changing account status. Account ID: $account_id");
    }
    
    $update_account_stmt->close();
    
    // ดึงรหัสผ่านจากตาราง Accounts
    $get_password_stmt = $conn->prepare("SELECT password FROM Accounts WHERE account_id = ?");
    if (!$get_password_stmt) {
        throw new Exception("Prepare get password statement failed: " . $conn->error);
    }
    
    $get_password_stmt->bind_param("s", $account_id);
    if (!$get_password_stmt->execute()) {
        throw new Exception("Failed to get password: " . $get_password_stmt->error);
    }
    
    $password_result = $get_password_stmt->get_result();
    if ($password_row = $password_result->fetch_assoc()) {
        $account_password = $password_row['password'];
    } else {
        $account_password = ''; // กรณีไม่พบรหัสผ่าน
    }
    
    $get_password_stmt->close();
    
    // บันทึกประวัติการซื้อ พร้อมรหัสผ่าน
    $purchase_date = date('Y-m-d H:i:s');
    $insert_stmt = $conn->prepare("INSERT INTO purchase_history (user_id, account_id, game_id, price, password, purchase_date) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$insert_stmt) {
        throw new Exception("Prepare insert statement failed: " . $conn->error);
    }
    
    $insert_stmt->bind_param("sssdss", $user_id, $account_id, $original_game_id, $price, $account_password, $purchase_date);
    if (!$insert_stmt->execute()) {
        throw new Exception("Failed to insert purchase history: " . $insert_stmt->error);
    }
    
    $insert_stmt->close();
}
                
                // Commit transaction
                mysqli_commit($conn);
                
                // อัปเดตข้อมูลใน session
                $_SESSION['points'] = $new_point;
                $user_point = $new_point; // อัปเดตตัวแปรในหน้านี้ด้วย
                
                // ล้างตะกร้า
                $_SESSION['cart'] = [];
                
                // แสดงข้อความสำเร็จ
                $success_message = "ซื้อสินค้าสำเร็จ! point คงเหลือ: $new_point";
                
                // ดึงข้อมูลสินค้าใหม่
                $result = mysqli_query($conn, $query);
                
            } catch (Exception $e) {
                // Rollback transaction เมื่อเกิดข้อผิดพลาด
                mysqli_rollback($conn);
                $error_message = "เกิดข้อผิดพลาด: " . $e->getMessage();
                error_log("Purchase error: " . $e->getMessage());
            }
        }
    }
}

// คำนวณราคารวมในตะกร้า
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call of Duty Mobile Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #212121, #000000);
            color: white;
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #000000; /* CODM black */
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            border-bottom: 2px solid #FF6C00; /* CODM orange */
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s, transform 0.3s;
        }

        .navbar a:hover {
            background: rgba(255, 108, 0, 0.2);
            transform: scale(1.05);
        }

        .brand {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
            color: #FF6C00; /* CODM orange */
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(40, 40, 40, 0.8);
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #FF6C00; /* CODM orange border */
        }

        .cart {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 8px 12px;
            background: rgba(40, 40, 40, 0.8);
            border-radius: 5px;
            transition: background 0.3s;
            border: 1px solid #FF6C00; /* CODM orange border */
        }

        .cart:hover {
            background: rgba(255, 108, 0, 0.2);
        }

        .cart span {
            margin-left: 8px;
            background: #FF6C00;
            color: #000000;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }

        /* Header */
        .header {
            text-align: center;
            padding: 40px 20px;
            background: rgba(0, 0, 0, 0.5);
            border-bottom: 1px solid #FF6C00;
        }

        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
            color: #FF6C00; /* CODM orange */
            text-shadow: 0 0 10px rgba(255, 108, 0, 0.5);
        }

        .header p {
            font-size: 18px;
            max-width: 800px;
            margin: 0 auto;
            color: #FFFFFF;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 5px;
            text-align: center;
        }

        .alert-error {
            background: rgba(255, 0, 0, 0.1);
            color: #ff6b6b;
            border: 1px solid #ff0000;
        }

        .alert-success {
            background: rgba(0, 255, 0, 0.1);
            color: #8affb6;
            border: 1px solid #00ff00;
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: #212121;
            width: 90%;
            max-width: 600px;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            position: relative;
            border: 2px solid #FF6C00;
        }

        .modal h2 {
            color: #FF6C00;
            margin-top: 0;
            border-bottom: 2px solid rgba(255, 108, 0, 0.3);
            padding-bottom: 15px;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.7);
            transition: color 0.3s;
        }

        .close-btn:hover {
            color: #FF6C00;
        }

        .modal table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .modal table th, .modal table td {
            border: 1px solid #444;
            padding: 12px;
            text-align: left;
        }

        .modal table th {
            background: rgba(255, 108, 0, 0.2);
            color: #FFFFFF;
        }

        .modal table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #444;
        }

        .total-price {
            font-size: 18px;
            font-weight: bold;
            color: #FF6C00;
        }

        .purchase-btn {
            background: #333;
            color: #FF6C00;
            border: 1px solid #FF6C00;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s, transform 0.3s;
        }

        .purchase-btn:hover {
            background: #FF6C00;
            color: #000;
            transform: scale(1.05);
        }

        .purchase-btn:disabled {
            background: #6c757d;
            color: white;
            border-color: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .remove-btn {
            background: transparent;
            color: #ff0000;
            border: 1px solid #ff0000;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s, color 0.3s;
        }

        .remove-btn:hover {
            background: rgba(255, 0, 0, 0.2);
        }

        /* Products */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .card {
            background: rgba(40, 40, 40, 0.8);
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid #444;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
            border-color: #FF6C00;
        }

        .card-header {
            background: rgba(255, 108, 0, 0.2);
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #444;
        }

        .card-header h2 {
            margin: 0;
            font-size: 22px;
            color: #FF6C00;
        }

        .card-body {
            padding: 20px;
        }

        .card-info {
            margin-bottom: 15px;
        }

        .card-info p {
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
        }

        .card-info .label {
            color: rgba(255, 255, 255, 0.7);
        }

        .card-info .value {
            font-weight: 500;
            color: #FFFFFF;
        }

        .price {
            display: block;
            text-align: center;
            color: #FF6C00;
            font-size: 24px;
            font-weight: bold;
            margin: 15px 0;
        }

        .card button {
            width: 100%;
            background: #333;
            color: #FF6C00;
            border: 1px solid #FF6C00;
            padding: 12px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }

        .card button:hover {
            background: #FF6C00;
            color: #000;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px 10px;
            }
            
            .nav-right {
                width: 100%;
                justify-content: space-between;
            }
            
            .products {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .header h1 {
                font-size: 28px;
            }
            
            .header p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="brand">🎮 PLAYER HAVEN</div>
        <div class="nav-right">
        <a href="HomePage.php">หน้าแรก</a>
        <a href="RandomWheel.php">สุ่มไอดีเกม</a>
        <a href="vlr_shop.php">VALORANT</a>
        <a href="rov_shop.php">ROV</a>
        <a href="tft_shop.php">TFT</a>
        <a href="codm_shop.php">CODM</a>
        <a href="lol_shop.php">LOL</a>
            <?php if ($logged_in): ?>
                <div class="user-info">
                    👤 <?php echo $_SESSION['username'] ?? 'User'; ?> 
                    <span style="color: #FF6C00; font-weight: bold;"><?php echo number_format($user_point, 2); ?> Point</span>
                </div>
                <a href="logout.php">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php">เข้าสู่ระบบ</a>
                <a href="register.php">สมัครสมาชิก</a>
            <?php endif; ?>
            <div class="cart" onclick="toggleModal()">
                🛒 ตะกร้า <span id="cart-count"><?php echo count($_SESSION['cart']); ?></span>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="header">
        <h1>CALL OF DUTY MOBILE ACCOUNTS SHOP</h1>
        <p>เลือกซื้อไอดีเกม Call of Duty Mobile คุณภาพดี การันตีทุกบัญชี</p>
    </div>

    <!-- Alerts -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <!-- Modal ตะกร้า -->
    <div class="modal" id="cart-modal">
        <div class="modal-content">
            <span class="close-btn" onclick="toggleModal()">&times;</span>
            <h2>🛒 ตะกร้าสินค้าของคุณ</h2>
            
            <?php if (!empty($_SESSION['cart'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>เกม</th>
                            <th>Account ID</th>
                            <th>ราคา</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['game_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['account_id']); ?></td>
                                <td><?php echo number_format($item['price'], 2); ?> บาท</td>
                                <td>
                                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                                        <input type="hidden" name="account_id" value="<?php echo htmlspecialchars($item['account_id']); ?>">
                                        <button type="submit" name="remove_from_cart" class="remove-btn">ลบ</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="modal-footer">
                    <div class="total-price">รวม: <?php echo number_format($cart_total, 2); ?> บาท</div>
                    
                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <button type="submit" name="purchase" class="purchase-btn" <?php echo (!$logged_in || $user_point < $cart_total) ? 'disabled' : ''; ?>>
                            ชำระเงิน
                        </button>
                    </form>
                </div>
                
                <?php if (!$logged_in): ?>
                    <p style="color: #ff6b6b; text-align: center; margin-top: 15px;">กรุณาเข้าสู่ระบบเพื่อทำการชำระเงิน</p>
                <?php elseif ($user_point < $cart_total): ?>
                    <p style="color: #ff6b6b; text-align: center; margin-top: 15px;">point ไม่เพียงพอ (ต้องการ <?php echo number_format($cart_total, 2); ?> แต่มี <?php echo number_format($user_point, 2); ?>)</p>
                <?php endif; ?>
                
            <?php else: ?>
                <p style="text-align: center; padding: 20px 0; color: #FFFFFF;">ไม่มีสินค้าในตะกร้า</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Container -->
    <div class="container">
        <div class="products">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2><?php echo htmlspecialchars($row['game_id']); ?> ACCOUNT</h2>
                        </div>
                        <div class="card-body">
                            <div class="card-info">
                                <p>
                                    <span class="label">Account ID:</span>
                                    <span class="value"><?php echo htmlspecialchars($row['account_id']); ?></span>
                                </p>
                                <p>
                                    <span class="label">Username:</span>
                                    <span class="value"><?php echo htmlspecialchars($row['username']); ?></span>
                                </p>
                                <?php if (!empty($row['details'])): ?>
                                    <p>
                                        <span class="label">รายละเอียด:</span>
                                        <span class="value"><?php echo htmlspecialchars($row['details']); ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="price"><?php echo number_format($row['price'], 2); ?> บาท</div>
                            
                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                <input type="hidden" name="account_id" value="<?php echo htmlspecialchars($row['account_id']); ?>">
                                <input type="hidden" name="game_id" value="<?php echo htmlspecialchars($row['game_id']); ?>">
                                <input type="hidden" name="price" value="<?php echo htmlspecialchars($row['price']); ?>">
                                <button type="submit" name="add_to_cart">เพิ่มในตะกร้า</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <h2 style="color: #FF6C00;">ไม่มีสินค้า Call of Duty Mobile ในขณะนี้</h2>
                    <p style="color: #FFFFFF;">กรุณากลับมาใหม่ในภายหลัง</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('cart-modal');
            if (modal.style.display === 'flex') {
                modal.style.display = 'none';
            } else {
                modal.style.display = 'flex';
            }
        }
        
        // ปิด modal เมื่อคลิกนอกพื้นที่ modal
        window.onclick = function(event) {
            const modal = document.getElementById('cart-modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>