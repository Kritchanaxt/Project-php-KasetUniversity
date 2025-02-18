<?php
require 'db_connection.php'; // ใช้ไฟล์เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash รหัสผ่าน
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role = 'buyer';
    $created_at = date("Y-m-d H:i:s");

    // ตรวจสอบว่ามี username หรือ email นี้อยู่แล้วหรือไม่
    $check_sql = "SELECT * FROM Users WHERE username='$username' OR email='$email'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว!'); window.location.href='Register.html';</script>";
        exit();
    }

    // บันทึกข้อมูลลงฐานข้อมูล
    $sql = "INSERT INTO Users (username, password, email, phone, role, created_at) 
            VALUES ('$username', '$password', '$email', '$phone', '$role', '$created_at')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('สมัครสมาชิกสำเร็จ!'); window.location.href='Login.html';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
} else {
    echo "<h2>Method Not Allowed (405)</h2><p>โปรดใช้ฟอร์มสำหรับสมัครสมาชิก.</p>";
}
?>
