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

try {
    // รับค่า account_id จาก request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['account_id'])) {
        echo json_encode(["success" => false, "message" => "Invalid request"]);
        exit;
    }

    $account_id = $data['account_id'];

    // ตรวจสอบว่าไอดีเกมยังว่างอยู่หรือไม่
    $stmt = $conn->prepare("SELECT status FROM Accounts WHERE account_id = ?");
    $stmt->bind_param("s", $account_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && $result['status'] === 'available') {
        // อัปเดต status เป็น 'sold'
        $updateStmt = $conn->prepare("UPDATE Accounts SET status = 'sold' WHERE account_id = ?");
        $updateStmt->bind_param("s", $account_id);
        $updateStmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Purchase completed successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Game ID is no longer available'
        ]);
    }
} catch (Exception $e) {
    if (DEBUG) {
        error_log("Purchase Error: " . $e->getMessage());
    }
    echo json_encode([
        'success' => false,
        'message' => 'Purchase failed: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
