<?php
require 'db_connection.php';

$query = "SELECT game_id, COUNT(*) AS total_accounts, 
                 SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) AS sold_accounts, 
                 SUM(CASE WHEN status != 'sold' OR status IS NULL THEN 1 ELSE 0 END) AS available_accounts
          FROM Accounts
          GROUP BY game_id
          ORDER BY available_accounts ASC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShowProduct</title>
    <link rel="stylesheet" href="styles.css">
    <style>
body {
    font-family: 'Orbitron', sans-serif;
    background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
    color: #ffffff;
    text-align: center;
    margin: 0;
    padding: 0;
    min-height: 100vh;
   
}
.background-effect {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    background: radial-gradient(circle, rgba(167, 139, 250, 0.2) 10%, transparent 10.01%);
    background-size: 25px 25px;
    animation: moveBackground 10s linear infinite;
    opacity: 0.5; /* ปรับให้จางลง */
}
@keyframes moveBackground {
    from {
        transform: translateY(0) translateX(0);
    }
    to {
        transform: translateY(-50px) translateX(-50px);
    }
}


/* 🌟 Navbar (เมนูด้านบน) */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 98%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    padding: 1rem 2rem;
    background: rgba(16, 7, 32, 0.9);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    z-index: 1000;
    transition: background 0.3s ease-in-out;
}

/* 🌟 เอฟเฟกต์ Glow */
.navbar::after {
    content: '';
    width: 100%;
    height: 100%;
    background-image: radial-gradient(circle, rgba(255, 94, 247, 0.4) 17.8%, rgba(2, 245, 255, 0.4) 100.2%);
    filter: blur(15px);
    z-index: -1;
    position: absolute;
    left: 0;
    top: 0;
}

/* 🌟 โลโก้ */
.nav-logo {
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: transform 0.2s ease-in-out;
}

.nav-logo:hover {
    transform: scale(1.1);
    text-shadow: 0px 0px 10px rgba(255, 94, 247, 0.6);
}

/* 🌟 รายการเมนู */
.nav-links {
    list-style: none;
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

/* 🌟 ลิงก์เมนู */
.nav-links a {
    text-decoration: none;
    font-size: 1.2rem;
    font-weight: 600;
    color: #fff;
    padding: 0.8rem 1.5rem;
    border-radius: 10px;
    transition: all 0.3s ease-in-out;
}

/* 🌟 เอฟเฟกต์ Hover */
.nav-links a:hover {
    background: linear-gradient(90deg, #ff5ef7, #02f5ff);
    box-shadow: 0px 4px 15px rgba(255, 94, 247, 0.5);
    color: #fff;
}

/* 🌟 ปรับระยะห่างตาราง */
.container {
    max-width: 80%;
    margin: 100px auto 40px auto;
    background: rgba(0, 0, 0, 0.9);
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0px 10px 30px rgba(0, 255, 255, 0.5);
}

/* 🌟 ตารางแบบ Cyber 3D */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 15px rgba(0, 255, 255, 0.4);
    animation: fadeIn 1s ease-in-out;
}

/* 🌟 หัวข้อ */
th, td {
    padding: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
}

th {
    background: rgba(0, 255, 255, 0.2);
    color: cyan;
    text-shadow: 0px 0px 10px cyan;
    animation: glow 2s infinite alternate;
}

/* 🌟 แถวปกติ */
tr {
    background: rgba(255, 255, 255, 0.1);
    transition: all 0.3s;
}

tr:hover {
    background: rgba(0, 255, 255, 0.3);
}

/* 🌟 สต็อกต่ำ */
.low-stock {
    background-color: rgba(255, 69, 69, 0.8) !important;
    color: white !important;
    font-weight: bold;
    animation: blink 1s infinite alternate;
}

/* 🌟 ปุ่มแบบ Cyber 3D */
button {
    background: linear-gradient(45deg, #00c6ff, #0072ff);
    border: none;
    padding: 14px 22px;
    border-radius: 10px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0px 5px 15px rgba(0, 255, 255, 0.5);
}

button:hover {
    transform: scale(1.1);
    box-shadow: 0px 5px 25px rgba(0, 255, 255, 0.8);
}

/* 🌟 ป้องกันปุ่มล้นเมื่อจอเล็ก */
@media screen and (max-width: 1024px) {
    .navbar {
        flex-direction: column;
        align-items: center;
    }
    .nav-links {
        flex-wrap: wrap;
        justify-content: center;
    }
    .add-product-btn {
        margin-top: 10px;
        width: 45px;
        height: 45px;
        font-size: 5px;
    }
}

/* 🌟 Animation Effects */
@keyframes glow {
    from {
        text-shadow: 0px 0px 10px cyan;
    }
    to {
        text-shadow: 0px 0px 20px cyan;
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes blink {
    from {
        background-color: rgba(255, 69, 69, 0.5);
    }
    to {
        background-color: rgba(255, 69, 69, 0.9);
    }
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
.nav-logo {
    text-decoration: none; 
    font-weight: bold; 
    font-size: 30px; 
    color: white;
}

    </style>
</head>
<body>

<h2>Stock Game</h2>

<?php
require 'db_connection.php';

$query = "SELECT game_id, COUNT(*) AS total_accounts, 
                 SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) AS sold_accounts, 
                 SUM(CASE WHEN status != 'sold' OR status IS NULL THEN 1 ELSE 0 END) AS available_accounts
          FROM Accounts
          GROUP BY game_id
          ORDER BY available_accounts ASC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShowProduct</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="background-effect"></div>

 <!-- เมนูด้านบน (Navbar) -->
 <nav class="navbar">
 <a href="../../HomePage.php" class="nav-logo">📦 INVENTORY</a>
    <ul class="nav-links">
        <li><a href="inventory.php">Store</a></li>
        <li><a href="edit_product.php">EditProduct</a></li>
        <li><a href="#Stockgame.php">ShowProduct</a></li>
        <li><a href="swapper.php">API-DB</a></li>
        <li><a href="add_product.php" class="add-product-btn">➕ Add Product</a></li>
    </ul>
</nav>


<!-- 🌟 เนื้อหาหลัก -->
<div class="main-content">
    <div class="container">
        <h2>🎮 STOCK GAME DASHBOARD</h2>

        <?php
        if ($result->num_rows > 0) {
            echo "<table>
                    <thead>
                        <tr>
                            <th>Game ID</th>
                            <th>ALL ID</th>
                            <th>SOLD ID</th>
                            <th>ID IN STOCKS</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>";

            while ($row = $result->fetch_assoc()) {
                $lowStock = ($row['available_accounts'] < 5) ? "⚠️ Restock now!" : "✅ NORMAL";
                $rowClass = ($row['available_accounts'] < 5) ? "low-stock" : "";

                echo "<tr class='{$rowClass}'>
                        <td>{$row['game_id']}</td>
                        <td>{$row['total_accounts']}</td>
                        <td>{$row['sold_accounts']}</td>
                        <td>{$row['available_accounts']}</td>
                        <td>{$lowStock}</td>
                    </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>❌ ไม่มีข้อมูลบัญชีเกม</p>";
        }
        ?>

        <div style="margin-top: 20px;">
            <!-- ปุ่มดาวน์โหลด PDF -->
            <form action="generate_report.php" method="post">
                <button type="submit" class="btn btn-danger">📄 ดาวน์โหลด PDF</button>
            </form>

        </div>
    </div>
</div>
</body>
</html>
