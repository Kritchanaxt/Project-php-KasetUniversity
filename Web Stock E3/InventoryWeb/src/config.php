<?php
$host = "158.108.101.153";
$user = "std6630202015"; // เปลี่ยนเป็น user ของคุณ
$password = "g3#Vjp8L"; // เปลี่ยนเป็นรหัสผ่านของคุณ
$dbname = "it_std6630202015";

$conn = new mysqli($host, $user, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}
?>
