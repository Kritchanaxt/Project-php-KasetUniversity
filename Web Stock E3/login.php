<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // ใช้ Prepared Statement เพื่อป้องกัน SQL Injection
    $query = "SELECT * FROM Users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // เช็คว่า password ตรงกันหรือไม่
        if (password_verify($password, $user["password"])) {
            $_SESSION["username"] = $user["username"];
            $_SESSION["email"] = $user["email"];

            echo "<script>
                localStorage.setItem('username', '{$user['username']}');
                localStorage.setItem('email', '{$user['email']}');
                window.location.href = 'HomePage.html';
            </script>";
        } else {
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง'); window.location.href = 'Login.html';</script>";
        }
    } else {
        echo "<script>alert('ชื่อผู้ใช้ไม่ถูกต้อง'); window.location.href = 'Login.html';</script>";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
