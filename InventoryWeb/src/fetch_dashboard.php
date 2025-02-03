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

// ดึงข้อมูลยอดขายรวม
$query_total = "SELECT SUM(price) as total_revenue FROM Accounts WHERE status = 'sold'";
$result_total = mysqli_query($conn, $query_total);
$total_revenue = mysqli_fetch_assoc($result_total)['total_revenue'] ?? 0;

// ดึงข้อมูลเกมที่ขายดีที่สุด
$query_top = "SELECT game_id, COUNT(*) as sales_count, SUM(price) as total_sales 
              FROM Accounts WHERE status = 'sold' 
              GROUP BY game_id 
              ORDER BY total_sales DESC 
              LIMIT 1";
$result_top = mysqli_query($conn, $query_top);
$top_game = mysqli_fetch_assoc($result_top) ?? ["game_id" => "ไม่มีข้อมูล", "sales_count" => 0, "total_sales" => 0];

// ดึงข้อมูลสถิติยอดขายเกมทั้งหมด
$query = "SELECT game_id, COUNT(*) as sales_count, SUM(price) as total_sales 
          FROM Accounts WHERE status = 'sold' 
          GROUP BY game_id 
          ORDER BY total_sales DESC";
$result = mysqli_query($conn, $query);

$game_sales = [];
while ($row = mysqli_fetch_assoc($result)) {
    $game_sales[] = [
        "game_id" => $row['game_id'],
        "sales_count" => (int)$row['sales_count'],
        "total_sales" => (float)$row['total_sales'],
    ];
}

mysqli_close($conn);

// ส่ง JSON กลับไปที่ client
echo json_encode([
    "total_revenue" => (float)$total_revenue,
    "top_game" => $top_game,
    "game_sales" => $game_sales
]);
?>
