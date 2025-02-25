<?php
session_start();
require 'db_connection.php'; // เชื่อมต่อฐานข้อมูล

header("Content-Type: application/json");
$response = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // รับค่าเก่า (จาก session) และค่าสำหรับอัปเดตใหม่ (จาก POST)
    $oldUsername = $_SESSION['username'] ?? null;
    $newUsername = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;

    if (!$oldUsername) {
        $response["success"] = false;
        $response["message"] = "คุณยังไม่ได้ล็อกอิน";
        echo json_encode($response);
        exit;
    }

    // เตรียมคำสั่ง SQL สำหรับอัปเดตข้อมูล
    $stmt = $conn->prepare("UPDATE Users SET username = ?, email = ?, phone = ? WHERE username = ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssss", $newUsername, $email, $phone, $oldUsername);

    if ($stmt->execute()) {
        // อัปเดต session ด้วยข้อมูลใหม่
        $_SESSION["username"] = $newUsername;
        $_SESSION["email"] = $email;
        $_SESSION["phone"] = $phone;

        $response["success"] = true;
        $response["message"] = "บันทึกสำเร็จ!";
    } else {
        $response["success"] = false;
        $response["message"] = "ไม่สามารถบันทึกข้อมูลได้: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $response["success"] = false;
    $response["message"] = "ไม่รองรับเมธอดนี้";
}

echo json_encode($response);
?>
