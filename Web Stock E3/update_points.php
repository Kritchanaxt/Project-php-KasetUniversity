<?php
session_start();
include 'db_connection.php';

// ตรวจสอบว่ามีค่าถูกต้องไหม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? '';
    $points = isset($_POST["points"]) ? (int)$_POST["points"] : 0;
    $amount = isset($_POST["payment_amount"]) ? (float)$_POST["payment_amount"] : 0;
    $method = $_POST["payment_method"] ?? '';

    // Debug ค่า Input
    error_log("DEBUG: Username = $username, Points = $points, Amount = $amount, Method = $method");

    if (empty($username) || $points <= 0 || $amount <= 0 || empty($method)) {
        echo json_encode(["success" => false, "message" => "Invalid input data"]);
        exit;
    }

    // ดึง user_id จาก username
    $query = "SELECT user_id FROM Users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user["user_id"];

        // อัปเดต point ใน Users
        $update_query = "UPDATE Users SET point = point + ? WHERE user_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "is", $points, $user_id);
        mysqli_stmt_execute($update_stmt);

        if (mysqli_stmt_affected_rows($update_stmt) > 0) {
            // ✅ ถ้าอัปเดตสำเร็จ ให้เพิ่มประวัติลงใน `TopUpHistory`
            $transaction_id = "T" . str_pad(mt_rand(1, 9999), 4, "0", STR_PAD_LEFT);
            $insert_query = "INSERT INTO TopUpHistory (user_id, transaction_id, points, amount, payment_method, status) VALUES (?, ?, ?, ?, ?, 'Completed')";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "ssids", $user_id, $transaction_id, $points, $amount, $method);
            mysqli_stmt_execute($insert_stmt);
            mysqli_stmt_close($insert_stmt);

            echo json_encode(["success" => true, "message" => "เครดิตถูกอัปเดตสำเร็จ"]);
        } else {
            error_log("DEBUG: UPDATE FAILED for user_id = $user_id");
            echo json_encode(["success" => false, "message" => "การอัปเดตเครดิตล้มเหลว"]);
        }
        mysqli_stmt_close($update_stmt);
    } else {
        error_log("DEBUG: USER NOT FOUND - $username");
        echo json_encode(["success" => false, "message" => "ไม่พบผู้ใช้ในระบบ"]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

?>
