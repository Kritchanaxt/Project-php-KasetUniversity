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
        error_log("Database connection failed");
    }
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// ตรวจสอบว่าเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Debug: ตรวจสอบค่า user_id
if (DEBUG) {
    error_log("User ID: " . $user_id);
}

try {
    // ดึงประวัติการเติมเงินของผู้ใช้
    $stmt = $conn->prepare("SELECT transaction_id, date, points, amount, payment_method, status 
                            FROM TopUpHistory WHERE user_id = ? ORDER BY date DESC");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // ใช้ "s" เพราะ user_id เป็น VARCHAR
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    // Debug: ตรวจสอบข้อมูลที่ดึงจากฐานข้อมูล
    if (DEBUG) {
        error_log("TopUp History: " . print_r($history, true));
    }

    // ส่ง JSON กลับไป
    echo json_encode([
        "success" => true,
        "history" => $history
    ]);

    $stmt->close();
} catch (Exception $e) {
    if (DEBUG) {
        error_log("Error: " . $e->getMessage());
    }
    echo json_encode(["success" => false, "message" => "An error occurred"]);
}

$conn->close();
?>
