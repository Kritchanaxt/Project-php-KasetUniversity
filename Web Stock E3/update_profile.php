<?php
session_start();
require 'db_connection.php'; // เชื่อมต่อฐานข้อมูล

header("Content-Type: application/json");
$response = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_SESSION['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;

    if (!$username) {
        $response["success"] = false;
        $response["message"] = "คุณยังไม่ได้ล็อกอิน";
        echo json_encode($response);
        exit;
    }

    // ตรวจสอบ SQL Statement
    $stmt = $conn->prepare("UPDATE Users SET email = ?, phone = ? WHERE username = ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("sss", $email, $phone, $username);

    if ($stmt->execute()) {
        $_SESSION["email"] = $email; // อัปเดต session
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
