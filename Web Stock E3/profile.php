<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["logged_in" => false]);
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, phone FROM Users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

echo json_encode([
    "logged_in" => true,
    "username" => $user['username'],
    "email" => $user['email'],
    "phone" => $user['phone']
]);
?>
