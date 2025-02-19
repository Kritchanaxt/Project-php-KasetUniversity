<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    // ตรวจสอบว่ามีค่ามาก่อนอัปเดต
    if (!empty($email) && !empty($phone)) {
        $query = "UPDATE Users SET email = ?, phone = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $email, $phone, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Profile updated successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Update failed."]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["success" => false, "message" => "Please fill all fields."]);
    }
}

mysqli_close($conn);
?>
