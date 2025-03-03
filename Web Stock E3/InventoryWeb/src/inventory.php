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

// ดึงข้อมูลยอดขาย
$query = "SELECT game_id, COUNT(*) as sales_count, SUM(price) as total_sales FROM Accounts WHERE status = 'sold' GROUP BY game_id ORDER BY total_sales DESC";
$result = mysqli_query($conn, $query);

$game_sales = [];
while ($row = mysqli_fetch_assoc($result)) {
    $game_sales[] = [
        "game_id" => $row['game_id'],
        "sales_count" => (int)$row['sales_count'],
        "total_sales" => (float)$row['total_sales'],
    ];
}

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
    <title>Inventory</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <style>
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            color: #fff;
            padding: 2rem;
            
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            animation: fadeIn 0.5s ease-out;
            margin-top: 90px; 
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
            animation: slideIn 0.5s ease-out;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .market-share {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .market-share h2 {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
            background: linear-gradient(to right, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .game-share {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            transition: transform 0.2s ease;
        }

        .game-share:hover {
            transform: scale(1.02);
            background: rgba(255, 255, 255, 0.08);
        }

        .game-icon {
            width: 50px;
            height: 50px;
            margin-right: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .share-bar {
            flex-grow: 1;
            margin: 0 1.5rem;
            height: 24px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }

        .share-value {
            height: 100%;
            background: linear-gradient(90deg, #a78bfa, #7c3aed);
            border-radius: 12px;
            transition: width 1s ease-in-out;
        }

        .share-percent {
            min-width: 70px;
            text-align: right;
            font-weight: 500;
            font-size: 1.1rem;
            color: #a78bfa;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .data-table th, .data-table td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .data-table th {
            background: rgba(124, 58, 237, 0.3);
            font-weight: 500;
            font-size: 1.1rem;
        }

        .data-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .data-table td:last-child {
            color: #a78bfa;
            font-weight: 500;
        }

       /* เอฟเฟคพื้นหลัง */
       .background-effect {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            background: radial-gradient(circle, rgba(167, 139, 250, 0.1) 10%, transparent 10.01%);
            background-size: 20px 20px;
            animation: moveBackground 10s linear infinite;
        }

        @keyframes moveBackground {
            from {
                transform: translateY(0) translateX(0);
            }
            to {
                transform: translateY(-100%) translateX(-100%);
            }
        }

        /* หน้าล็อคอิน */
        .login-container {
            position: relative;
            margin: 0 auto;
            bottom: -250px;
            z-index: 2;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 400px;
            text-align: center;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-container input {
            width: 100%;
            padding: 1rem;
            margin-bottom: 1rem;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 1rem;
            outline: none;
            transition: background 0.3s ease;
        }

        .login-container input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .login-container input:focus {
            background: rgba(255, 255, 255, 0.2);
        }

        .login-container button {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg, #a78bfa, #7c3aed);
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-container button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(167, 139, 250, 0.3);
        }

        .login-container p {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .login-container a {
            color: #a78bfa;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-container a:hover {
            color: #7c3aed;
        }

        /* ซ่อน Dashboard จนกว่าจะล็อคอิน */
        .dashboard {
            display: none;
        }
        
        .admin-profile {
    position: absolute; /* ตรึงตำแหน่ง */
    top: 10px; /* ระยะห่างจากขอบบน */
    right: 10px; /* ระยะห่างจากขอบขวา */
    display: flex;
    align-items: center;
    gap: 1rem; /* ระยะห่างระหว่างไอคอนและปุ่ม */
    z-index: 1000; /* ให้แน่ใจว่าอยู่ด้านบนสุด */
}


.admin-profile span {
    font-size: 1.2rem;
    font-weight: 500;
    color: #fff;
}

.admin-profile button {
    background: #ff4d4d;
    color: white;
    border: none;
    padding: 0.7rem 1.5rem;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.3s ease;
}

.admin-profile button:hover {
    background: #ff1a1a;
}
.btn-class-name {
  --primary: 255, 90, 120;  /* สีหลัก */
  --secondary: 150, 50, 60; /* สีรอง */
  width: 60px;
  height: 50px;
  border: none;
  outline: none;
  cursor: pointer;
  user-select: none;
  touch-action: manipulation;
  outline: 5px solid rgba(var(--primary), .5);
  border-radius: 50%;
  position: relative;
  transition: .3s;
}

.btn-class-name .back {
  background: rgb(var(--secondary));
  border-radius: 50%;
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
}

.btn-class-name .front {
  background: linear-gradient(0deg, rgba(var(--primary), .6) 20%, rgba(var(--primary)) 50%);
  box-shadow: 0 .5em 1em -0.2em rgba(var(--secondary), .5);
  border-radius: 50%;
  position: absolute;
  border: 1px solid rgb(var(--secondary));
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1.2rem;
  font-weight: 600;
  font-family: inherit;
  transform: translateY(-15%);
  transition: .15s;
  color: rgb(var(--secondary));
}

.btn-class-name:active .front {
  transform: translateY(0%);
  box-shadow: 0 0;
}

/* ปรับปุ่มให้แสดงเอฟเฟกต์ hover */
.btn-class-name:hover .front {
  transform: translateY(-10%);
}

/* --------------------- */
/* Navigation Bar (เมนูด้านบน) */
/* --------------------- */
/* --------------------- */
/* Navigation Bar (เมนูด้านบน) */
/* --------------------- */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    background: rgba(16, 7, 32, 0.8); /* สีพื้นหลังโปร่งใส */
    backdrop-filter: blur(12px); /* เอฟเฟกต์เบลอ */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    transition: background 0.3s ease-in-out;
}

/* เอฟเฟกต์ Glow Blur */
.navbar::after {
    content: '';
    width: 100%;
    height: 100%;
    background-image: radial-gradient(circle farthest-corner at 10% 20%, rgba(255, 94, 247, 0.4) 17.8%, rgba(2, 245, 255, 0.4) 100.2%);
    filter: blur(20px);
    z-index: -1;
    position: absolute;
    left: 0;
    top: 0;
}

/* โลโก้ */
.nav-logo {
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: transform 0.2s ease-in-out;
}

/* เอฟเฟกต์เมื่อโฮเวอร์ที่โลโก้ */
.nav-logo:hover {
    transform: scale(1.1);
    text-shadow: 0px 0px 10px rgba(255, 94, 247, 0.6);
}

/* รายการเมนู */
.nav-links {
    list-style: none;
    display: flex;
    gap: 1.5rem;
}

/* ลิงก์เมนู */
.nav-links a {
    text-decoration: none;
    font-size: 1.2rem;
    font-weight: 600;
    color: #fff;
    padding: 0.8rem 1.5rem;
    border-radius: 10px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease-in-out;
}

/* เอฟเฟกต์ Hover */
.nav-links a:hover {
    background: linear-gradient(90deg, #ff5ef7, #02f5ff);
    box-shadow: 0px 4px 15px rgba(255, 94, 247, 0.5);
    color: #fff;
}

/* เอฟเฟกต์เมื่อกด */
.nav-links a:active {
    transform: scale(0.95);
}


/* ปุ่ม Add Product (ดีไซน์ใหม่) */
.add-product-btn {
  width: 165px;
  height: 62px;
  cursor: pointer;
  color: #fff;
  font-size: 17px;
  border-radius: 1rem;
  border: none;
  position: relative;
  background: #100720;
  transition: 0.1s;
  font-weight: bold;
}

/* เอฟเฟกต์ Glow Blur */
.add-product-btn::after {
  content: '';
  width: 100%;
  height: 100%;
  background-image: radial-gradient(circle farthest-corner at 10% 20%, rgba(255,94,247,1) 17.8%, rgba(2,245,255,1) 100.2%);
  filter: blur(15px);
  z-index: -1;
  position: absolute;
  left: 0;
  top: 0;
}

/* เอฟเฟกต์เมื่อกดปุ่ม */
.add-product-btn:active {
  transform: scale(0.9) rotate(3deg);
  background: radial-gradient(circle farthest-corner at 10% 20%, rgba(255,94,247,1) 17.8%, rgba(2,245,255,1) 100.2%);
  transition: 0.5s;
}
.game-icon {
    width: 50px;
    height: 50px;
    margin-right: 1rem;
    border-radius: 10px;
    object-fit: cover; /* ให้รูปภาพถูกปรับขนาดพอดี */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.nav-logo {
    text-decoration: none; /* เอาเส้นใต้ลิงก์ออก */
    font-weight: bold; /* ทำให้ตัวหนังสือหนา */
    font-size: 35px; /* ปรับขนาดตัวอักษร */
    color: white; /* ตั้งค่าสีตัวอักษร */
}

    </style>
</head>
<body>
     <!-- เมนูด้านบน (Navbar) -->
 <nav class="navbar">
 <a href="../../HomePage.php" class="nav-logo">📦 INVENTORY</a>
    <ul class="nav-links">
        <li><a href="inventory.php">Store</a></li>
        <li><a href="edit_product.php">EditProduct</a></li>
        <li><a href="Stockgame.php">ShowProduct</a></li>
        <li><a href="swapper.php">API-DB</a></li>
        <li><a href="add_product.php" class="add-product-btn">➕ Add Product</a></li>
    </ul>
</nav>

    <!-- หน้าล็อคอิน -->
    <div class="background-effect"></div>
    <div class="login-container" id="loginContainer">
        <h1>เข้าสู่ระบบ</h1>
        <input type="text" id="username" placeholder="ชื่อผู้ใช้">
        <input type="password" id="password" placeholder="รหัสผ่าน">
        <button onclick="login()">เข้าสู่ระบบ</button>
    </div>

    <div class="container dashboard" id="dashboard">
        <div class="dashboard-header">
            <h1>ข้อมูลสถิติยอดขาย</h1>
            <div class="admin-profile">
                <span>👤 Admin</span>
                <button class="btn-class-name" onclick="logout()">
                    <div class="back"></div>
                    <div class="front"></div>
                </button>
            </div>
            
            <div class="stats-grid">
    <div class="stat-card">
        <h3>จำนวนเกมทั้งหมด</h3>
        <div class="value" id="totalGames">6</div>
    </div>
    <div class="stat-card">
        <h3>ยอดขายรวม</h3>
        <div class="value" id="totalRevenue">0 บาท</div>
    </div>
    <div class="stat-card">
        <h3>เกมที่ขายดีที่สุด</h3>
        <div class="value" id="topGame">-</div>
        <p>ยอดขาย: <span id="topGameSales">-</span> บาท</p>
        <p>จำนวนที่ขายได้: <span id="topGameCount">-</span> ชิ้น</p>
    </div>
</div>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>


    <script>
      // ฟังก์ชันล็อคอิน
function login() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    if (username === "admin" && password === "1234") {
        localStorage.setItem('isLoggedIn', 'true');
        document.getElementById('loginContainer').style.display = 'none';
        document.getElementById('dashboard').style.display = 'block';
        document.querySelector('.navbar').style.display = 'flex';
    } else {
        alert("ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง!");
    }
}

// ตรวจสอบสถานะการล็อคอินเมื่อโหลดหน้าเว็บ
function checkLoginStatus() {
    const isLoggedIn = localStorage.getItem('isLoggedIn');
    if (isLoggedIn === 'true') {
        document.getElementById('loginContainer').style.display = 'none';
        document.getElementById('dashboard').style.display = 'block';
        document.querySelector('.navbar').style.display = 'flex';
    } else {
        document.getElementById('loginContainer').style.display = 'block';
        document.getElementById('dashboard').style.display = 'none';
        document.querySelector('.navbar').style.display = 'none';
    }
}

// ฟังก์ชันล็อคเอาท์
function logout() {
    localStorage.removeItem('isLoggedIn');
    location.reload();
}

// ตัวแปร global สำหรับ Chart.js
let salesChart;

// อัปเดตข้อมูล Dashboard
function updateDashboard() {
    fetch("fetch_dashboard.php")
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) {
                console.error("Error: No data received.");
                return;
            }

            console.log("Fetched Data:", data);

            // อัปเดตยอดขายรวม
            document.querySelector('.stat-card:nth-child(2) .value').innerText = `${data.total_revenue.toLocaleString()} บาท`;

            const topGameCard = document.querySelector('.stat-card:nth-child(3)');
            topGameCard.innerHTML = `
                <h3>เกมที่ขายดีที่สุด</h3>
                <div class="value">Game ID: ${data.top_game.game_id}</div>
                <p>ยอดขาย: ${data.top_game.total_sales.toLocaleString()} บาท</p>
                <p>จำนวนที่ขายได้: ${data.top_game.sales_count} ชิ้น</p>
            `;

            updateChart(data.game_sales);
            updateMarketShare(data.game_sales);
            updateTable(data.game_sales);
        })
        .catch(error => console.error("Error fetching data:", error));
}


// อัปเดต Chart.js
function updateChart(data) {
    const chartLabels = data.map(game => `Game ID: ${game.game_id}`);
    const chartData = data.map(game => game.total_sales);

    if (salesChart) {
        salesChart.destroy(); // ทำลายกราฟเก่า ก่อนสร้างใหม่
    }

    const ctx = document.getElementById('salesChart').getContext('2d');
    salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'ยอดขายรวม (บาท)',
                data: chartData,
                backgroundColor: '#a78bfa',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#fff' } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    ticks: { color: '#fff' }
                },
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    ticks: { color: '#fff' }
                }
            }
        }
    });
}

// อัปเดตตารางข้อมูล
function updateTable(data) {
    const tableBody = document.querySelector(".data-table tbody");
    tableBody.innerHTML = ""; // ล้างค่าก่อนอัปเดตใหม่

    data.forEach(game => {
        tableBody.innerHTML += `
            <tr>
                <td>Game ID: ${game.game_id}</td>
                <td>${game.total_sales.toLocaleString()} บาท</td>
                <td>${game.sales_count}</td>
            </tr>
        `;
    });
}

// โหลดข้อมูลทุก ๆ 15 วินาที
setInterval(updateDashboard, 15000);

// โหลดข้อมูลครั้งแรก
updateDashboard();      

    checkLoginStatus();


    </script>
</body>
</html>