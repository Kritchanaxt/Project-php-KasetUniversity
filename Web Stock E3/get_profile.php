<?php
session_start();
include 'db_connection.php';

header("Content-Type: application/json");

$response = ["logged_in" => false];

// 🔥 ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(["logged_in" => false, "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// 🔍 ถ้า session มีข้อมูลครบ ให้ดึงจาก session
if (isset($_SESSION["username"], $_SESSION["email"], $_SESSION["phone"])) {
    $response = [
        "logged_in" => true,
        "user" => [
            "username" => $_SESSION["username"],
            "email" => $_SESSION["email"],
            "phone" => $_SESSION["phone"]
        ]
    ];
} else {
    // 🔥 ถ้า session ไม่มี email และ phone ให้ดึงจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT username, email, phone FROM Users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION["username"] = $row["username"];
        $_SESSION["email"] = $row["email"];
        $_SESSION["phone"] = $row["phone"];

        $response = [
            "logged_in" => true,
            "user" => [
                "username" => $row["username"],
                "email" => $row["email"],
                "phone" => $row["phone"]
            ]
        ];
    } else {
        $response["message"] = "User not found";
    }

    $stmt->close();
}

echo json_encode($response);
?>
