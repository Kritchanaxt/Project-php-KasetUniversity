<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role = 'buyer';
    $created_at = date("Y-m-d H:i:s");

    // 🔹 ค้นหาค่า user_id ล่าสุด
    $result = mysqli_query($conn, "SELECT user_id FROM Users ORDER BY user_id DESC LIMIT 1");
    $row = mysqli_fetch_assoc($result);
    
    if ($row) {
        // ดึงเลขท้ายของ user_id แล้วเพิ่มค่า
        $last_id = (int)substr($row['user_id'], 2); // ตัด #U ออก
        $new_id = "#U" . str_pad($last_id + 1, 3, '0', STR_PAD_LEFT);
    } else {
        // ถ้ายังไม่มีข้อมูล กำหนดให้เป็น #U001
        $new_id = "#U001";
    }

    // ตรวจสอบว่า username หรือ email ซ้ำหรือไม่
    $check_sql = "SELECT * FROM Users WHERE username='$username' OR email='$email'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้งานแล้ว!'); window.location.href='Register.html';</script>";
        exit();
    }

    // บันทึกข้อมูลลงฐานข้อมูล
    $sql = "INSERT INTO Users (user_id, username, password, email, phone, role, created_at) 
            VALUES ('$new_id', '$username', '$password', '$email', '$phone', '$role', '$created_at')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('สมัครสมาชิกสำเร็จ!'); window.location.href='Login.html';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
