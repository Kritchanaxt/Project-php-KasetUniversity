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

// คำนวณสถิติยอดขายเกมตาม game_id
$query = "SELECT game_id, COUNT(*) as sales_count, SUM(price) as total_sales FROM Accounts WHERE status = 'sold' GROUP BY game_id ORDER BY total_sales DESC";
$result = mysqli_query($conn, $query);

$game_sales = [];
$top_game = null;
$total_revenue = 0;
$max_sales = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $game_sales[] = $row;
    $total_revenue += $row['total_sales'];

    if ($row['total_sales'] > $max_sales) {
        $max_sales = $row['total_sales'];
        $top_game = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Sales Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Cyberpunk UI */
        body {
            font-family: 'Kanit', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: white;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            background: #007bff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s, transform 0.3s;
        }

        .navbar a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .container {
            width: 90%;
            margin: 20px auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            width: 300px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: scale(1.05);
        }

        .stat-card h2 {
            color: #FFD700;
            font-size: 24px;
        }

        .chart-container {
            width: 80%;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div>📊 Game Sales Dashboard</div>
        <div>
            <a href="home.php">Home</a>
            <a href="purchase.php">Buy Games</a>
            <a href="edit_product.php">Edit Products</a>
        </div>
    </div>

    <h1 style="text-align: center;">📊 สถิติยอดขายเกม</h1>

    <div class="container">
        <div class="stat-card">
            <h2>💰 รายได้รวม</h2>
            <p><strong><?php echo number_format($total_revenue, 2); ?> บาท</strong></p>
        </div>

        <?php if ($top_game): ?>
        <div class="stat-card">
            <h2>🔥 เกมขายดีที่สุด</h2>
            <p><strong>Game ID: <?php echo htmlspecialchars($top_game['game_id']); ?></strong></p>
            <p>ยอดขายรวม: <strong><?php echo number_format($top_game['total_sales'], 2); ?> บาท</strong></p>
        </div>
        <?php endif; ?>
    </div>

    <div class="chart-container">
        <canvas id="salesChart"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const chartData = {
            labels: <?php echo json_encode(array_column($game_sales, 'game_id')); ?>,
            datasets: [{
                label: 'ยอดขายรวม (บาท)',
                data: <?php echo json_encode(array_column($game_sales, 'total_sales')); ?>,
                backgroundColor: '#FFD700',
                borderRadius: 8
            }]
        };

        new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: '#fff'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#fff'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#fff'
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>
