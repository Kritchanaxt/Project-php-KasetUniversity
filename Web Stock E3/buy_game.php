<?php
session_start();
header('Content-Type: application/json');

// เปิดการแสดง error เพื่อดีบัก
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// เชื่อมต่อฐานข้อมูล
$servername = getenv('DB_SERVER') ?: '158.108.101.153';
$username = getenv('DB_USERNAME') ?: 'std6630202015';
$password = getenv('DB_PASSWORD') ?: 'g3#Vjp8L';
$dbname = getenv('DB_NAME') ?: 'it_std6630202015';

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]));
}
mysqli_set_charset($conn, "utf8");

// เริ่มต้นตะกร้าถ้ายังไม่มี
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// อ่านข้อมูลจาก input JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    error_log('Invalid JSON input');
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$action = $data['action'] ?? '';
$username = $data['username'] ?? ''; // ใช้ username จากผู้ใช้ที่ล็อคอิน

// ตรวจสอบว่าผู้ใช้ล็อคอินหรือไม่
if (empty($username)) {
    error_log('User not logged in');
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// ดึง user_id จาก username (สมมติว่ามีตาราง users)
$user_query = "SELECT user_id, point FROM users WHERE username = '$username'";
$user_result = mysqli_query($conn, $user_query);
if (!$user_result || mysqli_num_rows($user_result) == 0) {
    error_log('User not found: ' . $username);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = mysqli_fetch_assoc($user_result);
$user_id = $user['user_id'];
$current_points = $user['point'] ?? 0;

if ($action === 'buy_now') {
    $account_id = mysqli_real_escape_string($conn, $data['account_id'] ?? '');

    if (empty($account_id)) {
        error_log('Missing account_id in buy_now action');
        echo json_encode(['success' => false, 'message' => 'Missing account_id']);
        exit;
    }

    // ดึงข้อมูลสินค้าเพื่อเช็คราคาและสถานะ
    $account_query = "SELECT game_id, price, status FROM Accounts WHERE account_id = '$account_id'";
    $account_result = mysqli_query($conn, $account_query);
    if (!$account_result || mysqli_num_rows($account_result) == 0) {
        error_log('Account not found: ' . $account_id);
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }

    $account = mysqli_fetch_assoc($account_result);
    if ($account['status'] !== 'available') {
        error_log('Item not available for account_id: ' . $account_id);
        echo json_encode(['success' => false, 'message' => 'Item already sold or not available']);
        exit;
    }

    $price = $account['price'];
    $game_id = $account['game_id'];

    // ตรวจสอบพอยต์ของผู้ใช้ว่าพอหรือไม่
    if ($current_points < $price) {
        error_log('Insufficient points for user ' . $username . ': ' . $current_points . ' < ' . $price);
        echo json_encode(['success' => false, 'message' => 'Insufficient points. Required: ' . $price . ', Available: ' . $current_points]);
        exit;
    }

    // อัปเดตสถานะใน Accounts
    $update_query = "UPDATE Accounts SET status = 'sold' WHERE account_id = '$account_id'";
    if (!mysqli_query($conn, $update_query)) {
        error_log('Update query failed for account_id ' . $account_id . ': ' . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . mysqli_error($conn)]);
        exit;
    }

    if (mysqli_affected_rows($conn) == 0) {
        error_log('No rows updated for account_id: ' . $account_id);
        echo json_encode(['success' => false, 'message' => 'No rows updated']);
        exit;
    }

    // บันทึกข้อมูลลง TempAccounts
    $insert_query = "INSERT INTO TempAccounts (account_id, game_id, user_id, username, password, details, price, status)
                    SELECT account_id, game_id, '$user_id', username, password, details, price, 'sold'
                    FROM Accounts WHERE account_id = '$account_id'";
    if (!mysqli_query($conn, $insert_query)) {
        error_log('Insert into TempAccounts failed for account_id ' . $account_id . ': ' . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . mysqli_error($conn)]);
        exit;
    }

    // หักพอยต์ของผู้ใช้
    $new_points = $current_points - $price;
    $update_points_query = "UPDATE users SET point = '$new_points' WHERE user_id = '$user_id'";
    if (!mysqli_query($conn, $update_points_query)) {
        error_log('Update points failed for user_id ' . $user_id . ': ' . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Failed to update points: ' . mysqli_error($conn)]);
        exit;
    }

    echo json_encode(['success' => true, 'remaining_points' => $new_points]);
} elseif ($action === 'purchase_cart') {
    if (empty($_SESSION['cart'])) {
        error_log('Cart is empty for user ' . $username);
        echo json_encode(['success' => false, 'message' => 'Cart is empty. Please add items to your cart.']);
        exit;
    }

    $success = true;
    $total_price = 0;
    $errors = [];

    // คำนวณราคารวมและตรวจสอบสินค้าในตะกร้า
    foreach ($_SESSION['cart'] as $item) {
        $account_id = mysqli_real_escape_string($conn, $item['account_id'] ?? '');
        if (empty($account_id)) {
            $success = false;
            $errors[] = "Invalid account_id in cart";
            continue;
        }

        $account_query = "SELECT price, status FROM Accounts WHERE account_id = '$account_id'";
        $account_result = mysqli_query($conn, $account_query);
        if (!$account_result || mysqli_num_rows($account_result) == 0) {
            $success = false;
            $errors[] = "Item not found for account_id: $account_id";
            continue;
        }

        $account = mysqli_fetch_assoc($account_result);
        if ($account['status'] !== 'available') {
            $success = false;
            $errors[] = "Item already sold or not available for account_id: $account_id";
            continue;
        }

        $total_price += $account['price'];
    }

    // ตรวจสอบพอยต์ว่าพอหรือไม่
    if ($current_points < $total_price) {
        error_log('Insufficient points for user ' . $username . ': ' . $current_points . ' < ' . $total_price);
        echo json_encode(['success' => false, 'message' => 'Insufficient points. Required: ' . $total_price . ', Available: ' . $current_points]);
        exit;
    }

    if ($success) {
        foreach ($_SESSION['cart'] as $item) {
            $account_id = mysqli_real_escape_string($conn, $item['account_id'] ?? '');
            $game_id = mysqli_real_escape_string($conn, $item['game_id'] ?? '');

            if (empty($account_id) || empty($game_id)) {
                $success = false;
                $errors[] = "Invalid account_id or game_id in cart";
                continue;
            }

            // อัปเดตสถานะใน Accounts
            $update_query = "UPDATE Accounts SET status = 'sold' WHERE account_id = '$account_id'";
            if (!mysqli_query($conn, $update_query)) {
                $success = false;
                $errors[] = "Update failed for account_id $account_id: " . mysqli_error($conn);
                continue;
            }

            // บันทึกข้อมูลลง TempAccounts
            $insert_query = "INSERT INTO TempAccounts (account_id, game_id, user_id, username, password, details, price, status)
                            SELECT account_id, game_id, '$user_id', username, password, details, price, 'sold'
                            FROM Accounts WHERE account_id = '$account_id'";
            if (!mysqli_query($conn, $insert_query)) {
                $success = false;
                $errors[] = "Insert failed for account_id $account_id: " . mysqli_error($conn);
                continue;
            }
        }

        if ($success) {
            // หักพอยต์ของผู้ใช้
            $new_points = $current_points - $total_price;
            $update_points_query = "UPDATE users SET point = '$new_points' WHERE user_id = '$user_id'";
            if (!mysqli_query($conn, $update_points_query)) {
                error_log('Update points failed for user_id ' . $user_id . ': ' . mysqli_error($conn));
                echo json_encode(['success' => false, 'message' => 'Failed to update points: ' . mysqli_error($conn)]);
                exit;
            }

            $_SESSION['cart'] = [];
            echo json_encode(['success' => true, 'remaining_points' => $new_points]);
        } else {
            error_log('Purchase cart errors: ' . implode('; ', $errors));
            echo json_encode(['success' => false, 'message' => 'Some items could not be purchased: ' . implode('; ', $errors)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Some items in cart are invalid or unavailable']);
    }
} else {
    error_log('Invalid action: ' . $action);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

mysqli_close($conn);