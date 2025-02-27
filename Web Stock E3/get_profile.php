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
    echo json_encode(["logged_in" => false, "message" => "Database connection failed"]);
    exit;
}

// ตรวจสอบว่าเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(["logged_in" => false, "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Debug: ตรวจสอบค่า user_id (เฉพาะในโหมด DEBUG)
if (DEBUG) {
    error_log("User ID: " . $user_id);
}

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
        $_SESSION["points"] = $row["point"]; // Ensure points are stored in session

        // Debug: ตรวจสอบข้อมูลที่ดึงจากฐานข้อมูล (เฉพาะในโหมด DEBUG)
        if (DEBUG) {
            error_log("DB Data: " . print_r($row, true));
            error_log("Session Points Set: " . $_SESSION["points"]);
        }

        // ส่งข้อมูลกลับไปในรูปแบบ JSON รวมทั้ง point
        $response = [
            "logged_in" => true,
            "user" => [
                "username" => $row["username"],
                "email" => $row["email"],
                "phone" => $row["phone"],
                "point" => $row["point"]
            ]
        ];
        echo json_encode($response);
    } else {
        // กรณีไม่พบข้อมูลผู้ใช้ แต่ยัง logged in
        echo json_encode(["logged_in" => false, "message" => "User data not found in database"]);
        $_SESSION["points"] = 0; // Default to 0 if user not found
    }

    $stmt->close();
} catch (Exception $e) {
    // กรณีเกิดข้อผิดพลาดกับฐานข้อมูล
    if (DEBUG) {
        error_log("Error: " . $e->getMessage() . " in " . $e->getFile() . " at line " . $e->getLine());
    }
    echo json_encode(["logged_in" => false, "message" => "An error occurred"]);
    $_SESSION["points"] = 0; // Default to 0 on error
}

$conn->close();
?>