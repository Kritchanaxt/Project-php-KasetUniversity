<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require 'config.php'; // เชื่อมต่อฐานข้อมูล

$tables = ["Accounts", "Games", "Users", "TopUpHistory", "purchase_history"];

if (isset($_GET['table']) && in_array($_GET['table'], $tables)) {
    $table = $_GET['table'];
    
    // ดึงข้อมูลของตาราง
    $sql = "SELECT * FROM $table";
    $query = $conn->query($sql);
    $data = [];
    while ($row = $query->fetch_assoc()) {
        $data[] = $row;
    }

    // ดึง Schema (โครงสร้างตาราง)
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM $table");
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    // ส่ง JSON ให้ครบทุกตัวแปร
    echo json_encode([
        "status" => "success",
        "table" => $table,
        "schema" => $columns,
        "data" => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} else {
    echo json_encode(["status" => "error", "message" => "Invalid table name"]);
}

$conn->close();
?>
