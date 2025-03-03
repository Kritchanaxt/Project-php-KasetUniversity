<?php
require 'db_connection.php';

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $account_id = mysqli_real_escape_string($conn, $_POST['account_id']);
    $game_id = mysqli_real_escape_string($conn, $_POST['game_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update_query = "UPDATE Accounts SET 
                     game_id = '$game_id', 
                     username = '$username', 
                     details = '$details', 
                     price = '$price', 
                     status = '$status'
                     WHERE account_id = '$account_id'";

    if (mysqli_query($conn, $update_query)) {
        header("Location: edit_product.php");
        exit();
    } else {
        echo "Error updating product: " . mysqli_error($conn);
    }
}

// Search functionality
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM Accounts WHERE 
              account_id LIKE '%$search_query%' OR 
              game_id LIKE '%$search_query%' OR 
              username LIKE '%$search_query%' OR 
              details LIKE '%$search_query%' OR 
              price LIKE '%$search_query%' OR 
              status LIKE '%$search_query%'
              ORDER BY account_id ASC";
} else {
    $query = "SELECT * FROM Accounts ORDER BY account_id ASC";
}

$result = mysqli_query($conn, $query);

// Handle delete
if (isset($_GET['delete'])) {
    $account_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = "DELETE FROM Accounts WHERE account_id = '$account_id'";
    if (mysqli_query($conn, $delete_query)) {
        header("Location: edit_product.php");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Orbitron', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #ffffff;
            text-align: center;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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
            opacity: 0.5;
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
            width: 100%;
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

        /* 🌟 ปรับระยะห่างและเลเอาท์ของเนื้อหา */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-top: 80px; /* ระยะห่างจาก Navbar */
            padding-bottom: 20px;
        }

        .neon-card {
            background: transparent;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
            margin: 0 auto 20px auto;
            max-width: 95%; /* เพิ่มความกว้างของ neon-card */
        }

        .neon-btn {
            background: linear-gradient(45deg, #00c6ff, #0072ff);
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0px 5px 15px rgba(0, 255, 255, 0.5);
        }

        .neon-btn:hover {
            transform: scale(1.1);
            box-shadow: 0px 5px 25px rgba(0, 255, 255, 0.8);
        }

        .table-container {
            max-width: 100%; /* ตารางกว้างเต็มหน้าจอ */
            margin: 20px auto;
            overflow-x: auto;
        }

        table {
            width: 100%; /* ตารางกว้างเต็มหน้าจอ */
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
            background: rgba(0, 0, 0, 0.8); /* พื้นหลังตารางเข้มขึ้นเพื่อให้เข้ากับภาพ */
        }

        th, td {
            padding: 15px; /* เพิ่ม padding เพื่อให้ดูกว้างและสวยงาม */
            border: 2px solid rgba(70, 243, 255, 0.1); /* ขอบบางและโปร่งใส */
            text-align: center;
            font-size: 1.1rem; /* ขนาดตัวอักษรใหญ่ขึ้น */
        }

        th {
            background: rgba(0, 191, 255, 0.3); /* สีหัวตารางเข้ากับภาพ */
            color: cyan;
            text-shadow: 0px 0px 10px cyan;
            font-weight: bold;
        }

        tr {
            background: rgba(255, 255, 255, 0.05); /* พื้นหลังแถวโปร่งใสเข้มขึ้น */
            transition: all 0.3s;
        }

        tr:hover {
            background: rgba(0, 255, 255, 0.2); /* เอฟเฟกต์ hover เข้ากับภาพ */
        }

        /* ปรับสไตล์ฟอร์มในตาราง */
        input[type="text"],
        input[type="number"],
        select {
            width: 90%; /* ลดความกว้างของ input เพื่อให้ดูไม่แน่น */
            padding: 10px;
            border: 1px solid rgba(0, 255, 255, 0.3);
            border-radius: 8px;
            background: rgb(255, 255, 255);
            color: black;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: #00c6ff;
            box-shadow: 0 0 10px rgba(0, 198, 255, 0.5);
        }

        /* ปรับปุ่มในตาราง */
        .neon-btn.bg-green-500, 
        .neon-btn.bg-red-500 {
            background: linear-gradient(45deg, #00ff00, #00cc00) !important; /* ปรับสี Update เป็นเขียว */
            margin-right: 10px; /* ระยะห่างระหว่างปุ่ม */
        }

        .neon-btn.bg-red-500 {
            background: linear-gradient(45deg, #ff0000, #cc0000) !important; /* ปรับสี Delete เป็นแดง */
        }

        .neon-btn.bg-green-500:hover, 
        .neon-btn.bg-red-500:hover {
            transform: scale(1.1);
            box-shadow: 0px 5px 25px rgba(0, 255, 255, 0.8);
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
    font-size: 35px; 
    color: white;
}

    </style>
</head>
<body>
<div class="background-effect"></div>
<nav class="navbar">
<a href="../../HomePage.php" class="nav-logo">:package: INVENTORY</a>
    <ul class="nav-links">
        <li><a href="inventory.php">Store</a></li>
        <li><a href="edit_product.php">EditProduct</a></li>
        <li><a href="Stockgame.php">ShowProduct</a></li>
        <li><a href="swapper.php">API-DB</a></li>
        <li><a href="add_product.php" class="add-product-btn">➕ Add Product</a></li>
    </ul>
</nav>

<div class="main-content">
    <div class="neon-card">
        <h2 class="text-3xl font-bold text-cyan-400 mb-5">Edit Game Products</h2>
        <form method="GET" action="edit_product.php" class="mb-6">
            <input type="text" name="search" placeholder="🔍 Search Product..." 
                value="<?php echo htmlspecialchars($search_query); ?>"
                class="p-3 w-80 rounded-lg border-none shadow-lg text-black">
            <button type="submit" class="neon-btn">🔍 Search</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Account ID</th>
                        <th>Game ID</th>
                        <th>Username</th>
                        <th>Details</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <form method="POST" action="edit_product.php">
                                <td><?php echo htmlspecialchars($row['account_id']); ?></td>
                                <td><input type="text" name="game_id" value="<?php echo htmlspecialchars($row['game_id']); ?>" class="p-2 text-black rounded"></td>
                                <td><input type="text" name="username" value="<?php echo htmlspecialchars($row['username']); ?>" class="p-2 text-black rounded"></td>
                                <td><input type="text" name="details" value="<?php echo htmlspecialchars($row['details']); ?>" class="p-2 text-black rounded"></td>
                                <td><input type="number" name="price" value="<?php echo htmlspecialchars($row['price']); ?>" class="p-2 text-black rounded"></td>
                                <td>
                                    <select name="status" class="p-2 rounded text-black">
                                        <option value="available" <?php echo ($row['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="sold" <?php echo ($row['status'] === 'sold') ? 'selected' : ''; ?>>Sold</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="account_id" value="<?php echo $row['account_id']; ?>">
                                    <button type="submit" name="update" class="neon-btn bg-green-500 hover:bg-green-600">Update</button>
                                    <a href="edit_product.php?delete=<?php echo $row['account_id']; ?>" class="neon-btn bg-red-500 hover:bg-red-600">Delete</a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>