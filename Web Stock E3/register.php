<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role = 'buyer';
    $created_at = date("Y-m-d H:i:s");

    // เริ่ม transaction เพื่อป้องกันการซ้ำ
    mysqli_begin_transaction($conn);

    try {
        // ล็อกตาราง Users เพื่อป้องกันการแข่งขันข้อมูล
        mysqli_query($conn, "LOCK TABLES Users WRITE");

        // หาค่า user_id ล่าสุด
        $result = mysqli_query($conn, "SELECT user_id FROM Users ORDER BY user_id DESC LIMIT 1");
        $row = mysqli_fetch_assoc($result);
        
        $new_id = "#U001"; // ค่าเริ่มต้นหากยังไม่มีข้อมูล
        
        if ($row) {
            // ดึงเลขท้ายของ user_id และเพิ่มค่า
            $last_id = (int)substr($row['user_id'], 2);
            $new_id_base = $last_id + 1;
            
            // วนลูปเพื่อหา user_id ที่ไม่ซ้ำ
            do {
                $new_id = "#U" . str_pad($new_id_base, 3, '0', STR_PAD_LEFT);
                $check_duplicate = mysqli_query($conn, "SELECT user_id FROM Users WHERE user_id = '$new_id'");
                $new_id_base++;
            } while (mysqli_num_rows($check_duplicate) > 0 && $new_id_base < 999); // ป้องกันลูปไม่สิ้นสุด
            
            if ($new_id_base >= 999) {
                throw new Exception("Cannot generate unique User ID - ID limit reached");
            }
        }

        // ตรวจสอบว่า username หรือ email ซ้ำหรือไม่
        $check_sql = "SELECT * FROM Users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            echo "<script>alert('ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้งานแล้ว!'); window.location.href='Register.html';</script>";
            mysqli_rollback($conn);
            mysqli_query($conn, "UNLOCK TABLES");
            exit();
        }

        // บันทึกข้อมูลลงฐานข้อมูล
        $sql = "INSERT INTO Users (user_id, username, password, email, phone, role, created_at) 
                VALUES ('$new_id', '$username', '$password', '$email', '$phone', '$role', '$created_at')";

        if (mysqli_query($conn, $sql)) {
            mysqli_commit($conn);
            echo "<script>alert('สมัครสมาชิกสำเร็จ!'); window.location.href='Login.html';</script>";
        } else {
            throw new Exception("Registration failed: " . mysqli_error($conn));
        }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    } finally {
        mysqli_query($conn, "UNLOCK TABLES");
    }

    mysqli_close($conn);
}
?>