<?php
session_start();
include 'db_connection.php';

// กำหนดค่า DEBUG (เปลี่ยนเป็น false ใน production)
define('DEBUG', true);

// ป้องกันการแคชที่ฝั่ง client และ server
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header("Content-Type: application/json");

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    if (DEBUG) {
        error_log("Database connection failed: " . mysqli_connect_error());
    }
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// ตรวจสอบว่าเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

// ตรวจสอบและจัดการ user_id จาก session
$user_id = $_SESSION['user_id'];

// ตรวจสอบว่า user_id เป็น string ที่ถูกต้อง (ตามที่ user_id ใน purchase_history เป็น VARCHAR)
if (!is_string($user_id) || trim($user_id) === '') {
    echo json_encode(["success" => false, "message" => "Invalid user ID"]);
    exit;
}

// Debug: ตรวจสอบค่า user_id
if (DEBUG) {
    error_log("User ID: " . $user_id);
}

try {
    // ดึงประวัติการซื้อของผู้ใช้ตาม user_id ที่ล็อกอิน รวมถึง password
    $stmt = $conn->prepare("SELECT purchase_id, user_id, game_id, account_id, price, password, purchase_date 
                            FROM purchase_history 
                            WHERE user_id = ? 
                            ORDER BY purchase_date DESC");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // ใช้ "s" เพราะ user_id ใน purchase_history เป็น VARCHAR
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row; // รักษา password ไว้ในผลลัพธ์ โดยไม่ใช้ unset($row['password'])
    }

    // Debug: ตรวจสอบข้อมูลที่ดึงจากฐานข้อมูล
    if (DEBUG) {
        error_log("Purchase History: " . print_r($history, true));
    }

    // ส่ง JSON กลับไป
    echo json_encode([
        "success" => true,
        "history" => $history,
        "user_id" => $user_id // เพิ่ม user_id ใน response เพื่อตรวจสอบว่าใช้ user_id ไหน
    ]);

    $stmt->close();
} catch (Exception $e) {
    if (DEBUG) {
        error_log("Error: " . $e->getMessage());
    }
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}

$conn->close();
?>