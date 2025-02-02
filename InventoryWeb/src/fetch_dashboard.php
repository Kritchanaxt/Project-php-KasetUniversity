<?php
// เชื่อมต่อฐานข้อมูล
$servername = getenv('DB_SERVER') ?: '158.108.101.153';
$username = getenv('DB_USERNAME') ?: 'std6630202015';
$password = getenv('DB_PASSWORD') ?: 'g3#Vjp8L';
$dbname = getenv('DB_NAME') ?: 'it_std6630202015';

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
}
mysqli_set_charset($conn, "utf8");

// ดึงข้อมูลยอดขายจากฐานข้อมูล
$query = "SELECT game_id, COUNT(*) as sales_count, SUM(price) as total_sales FROM Accounts WHERE status = 'sold' GROUP BY game_id ORDER BY total_sales DESC";
$result = mysqli_query($conn, $query);

$game_sales = [];
while ($row = mysqli_fetch_assoc($result)) {
    $game_sales[] = [
        "game_id" => $row['game_id'],
        "sales_count" => $row['sales_count'],
        "total_sales" => $row['total_sales'],
    ];
}

mysqli_close($conn);

// ส่งข้อมูลกลับเป็น JSON
echo json_encode($game_sales);
?>
