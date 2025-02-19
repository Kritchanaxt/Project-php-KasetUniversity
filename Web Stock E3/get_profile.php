<?php
session_start();
include 'db_connection.php';

// 🔥 ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["logged_in" => false, "message" => "Not logged in"]);
    exit;
}

// ดึงข้อมูลจาก session (ไม่ต้อง query ฐานข้อมูลใหม่)
$response = [
    "logged_in" => true,
    "user" => [
        "username" => $_SESSION["username"],
        "email" => $_SESSION["email"],
        "phone" => $_SESSION["phone"]
    ]
];

echo json_encode($response);
?>
