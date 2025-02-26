<?php
session_start();
include 'db_connection.php';

header("Content-Type: application/json");

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

try {
    // ใช้ MySQLi Query
    $sql = "SELECT account_id, game_id, user_id, username, details, price, status FROM Accounts WHERE status = 'available'";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $gameIds = [];
    while ($row = $result->fetch_assoc()) {
        $gameIds[] = $row;
    }

    // แสดง JSON Response
    echo json_encode(["success" => true, "gameIds" => $gameIds], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database query failed: " . $e->getMessage()]);
}

?>
