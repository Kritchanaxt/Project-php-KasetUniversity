<?php
session_start();
session_destroy(); // เคลียร์ข้อมูลทั้งหมด
header("Location: Login.html"); // กลับไปหน้า Login
exit();
?>
