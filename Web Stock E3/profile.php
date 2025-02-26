<?php
session_start();
include 'db_connection.php';

// ป้องกัน JSON Cache และกำหนด Header
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header("Content-Type: application/json");

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    echo json_encode(["logged_in" => false, "message" => "Database connection failed"]);
    exit;
}

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["logged_in" => false, "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// ตรวจสอบว่า user_id เป็นตัวเลข (ป้องกัน SQL Injection)
if (!filter_var($user_id, FILTER_VALIDATE_INT)) {
    echo json_encode(["logged_in" => false, "message" => "Invalid user ID"]);
    exit;
}

// ดึงข้อมูลจากฐานข้อมูล
$query = "SELECT username, email, phone FROM Users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id); // ใช้ "i" แทน "s" เพราะ user_id เป็น integer
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// ตรวจสอบว่าพบข้อมูลหรือไม่
if (!$user) {
    echo json_encode(["logged_in" => true, "message" => "User data not found"]);
    exit;
}

// ส่ง JSON Response
echo json_encode([
    "logged_in" => true,
    "username" => $user['username'],
    "email" => $user['email'],
    "phone" => $user['phone']
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

// ปิด statement และ connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
