<?php
// เชื่อมต่อฐานข้อมูล
$servername = getenv('DB_SERVER') ?: '158.108.101.153';
$username = getenv('DB_USERNAME') ?: 'std6630202015';
$password = getenv('DB_PASSWORD') ?: 'g3#Vjp8L';
$dbname = getenv('DB_NAME') ?: 'it_std6630202015';

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// ดึงข้อมูลทั้งหมดจาก TempAccounts
$query = "SELECT * FROM TempAccounts ORDER BY account_id ASC";
$result = mysqli_query($conn, $query);

$temp_accounts = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $temp_accounts[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #141E30, #243B55);
            color: white;
            text-align: center;
        }
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            color: #FFD700;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            margin: 20px auto;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        table th, table td {
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 15px;
            text-align: left;
        }
        table th {
            background: #007bff;
            color: white;
            font-weight: 500;
        }
        table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ข้อมูลการซื้อทั้งหมด</h1>
        <?php if (empty($temp_accounts)): ?>
            <p>ไม่พบข้อมูลในตาราง TempAccounts</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>รหัสบัญชี</th>
                        <th>ชื่อเกม</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>รหัสผ่าน</th>
                        <th>รายละเอียด</th>
                        <th>ราคา</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($temp_accounts as $account): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($account['account_id']); ?></td>
                            <td><?php echo htmlspecialchars($account['game_id']); ?></td>
                            <td><?php echo htmlspecialchars($account['username']); ?></td>
                            <td><?php echo htmlspecialchars($account['password']); ?></td>
                            <td><?php echo htmlspecialchars($account['details']); ?></td>
                            <td><?php echo number_format($account['price'], 2); ?> บาท</td>
                            <td><?php echo htmlspecialchars($account['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="purchase.php" class="btn">กลับไปยังหน้าร้านค้า</a>
    </div>
</body>
</html>
