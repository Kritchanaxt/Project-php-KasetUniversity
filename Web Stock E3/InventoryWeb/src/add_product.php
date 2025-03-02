<?php
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account_id = mysqli_real_escape_string($conn, $_POST['account_id']);
    $game_id = mysqli_real_escape_string($conn, $_POST['game_id']);
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "INSERT INTO Accounts (account_id, game_id, user_id, username, password, details, price, status, created_at)
            VALUES ('$account_id', '$game_id', '$user_id', '$username', '$password', '$details', '$price', '$status', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo "<p class='success-msg'>Product added successfully!</p>";
    } else {
        echo "<p class='error-msg'>Error: " . mysqli_error($conn) . "</p>";
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
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

        /* Enhanced Form Design */
        .form-container {
            max-width: 900px; /* Increased width for better spacing */
            margin: 120px auto 40px; /* Increased top margin to prevent navbar overlap */
            padding: 3rem; /* Increased padding for better spacing */
            background: rgba(0, 0, 0, 0.8);
            border-radius: 20px;
            box-shadow: 0 0 40px rgba(0, 255, 255, 0.4);
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            inset: -15px; /* Adjusted for better glow visibility */
            background: linear-gradient(45deg, #00c6ff, #ff5ef7);
            filter: blur(30px);
            z-index: -1;
            border-radius: 25px;
            opacity: 0.6;
        }

        .form-container h1 {
            text-align: center;
            color: #00c6ff;
            font-size: 2.5rem; /* Slightly larger for emphasis */
            margin-bottom: 2.5rem; /* Increased margin for better spacing */
            text-shadow: 0 0 15px #00c6ff;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Increased minimum width for fields */
            gap: 3rem; /* Increased gap for better spacing */
            padding: 1rem;
        }

        .form-group {
            position: relative;
            margin-bottom: 0rem; /* Added margin for vertical spacing */
        }

        .form-group label {
            position: absolute;
            top: -12px; /* Adjusted for better alignment with larger inputs */
            left: 15px;
            background: #000;
            padding: 0 8px; /* Increased padding for larger labels */
            font-size: 1rem; /* Slightly larger font for readability */
            color: cyan;
            text-shadow: 0 0 5px cyan;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 1.5rem 1rem 0.7rem; /* Adjusted padding for larger inputs */
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(0, 255, 255, 0.3);
            border-radius: 15px; /* Slightly larger radius for a softer look */
            color: white;
            font-size: 1.1rem; /* Slightly larger font for better readability */
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #00c6ff;
            box-shadow: 0 0 25px rgba(0, 198, 255, 0.7);
        }

        .form-group select {
            appearance: none;
            background: url('data:image/svg+xml;utf8,<svg fill="%2300c6ff" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 1rem center;
            background-size: 1.2rem; /* Slightly larger arrow for clarity */
        }

        .form-group select option {
            color: black; /* Black text for options in the dropdown */
            background: white;
        }

        .submit-btn {
            grid-column: 1 / -1;
            padding: 1.5rem; /* Increased padding for larger button */
            background: linear-gradient(45deg, #00c6ff, #0072ff);
            border: none;
            border-radius: 15px; /* Larger radius to match inputs */
            color: white;
            font-size: 1.3rem; /* Larger font for emphasis */
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 25px rgba(0, 255, 255, 0.6);
            margin-top: 2rem; /* Increased margin for better spacing */
        }

        .submit-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 35px rgba(0, 255, 255, 0.8);
        }

        .success-msg {
            text-align: center;
            color: #00ff00;
            text-shadow: 0 0 15px #00ff00;
            margin: 1.5rem 0; /* Increased margin for better spacing */
        }

        .error-msg {
            text-align: center;
            color: #ff0000;
            text-shadow: 0 0 15px #ff0000;
            margin: 1.5rem 0; /* Increased margin for better spacing */
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

    </style>
</head>
<body>
<div class="background-effect"></div>
    <nav class="navbar">
        <div class="nav-logo">📦 Inventory</div>
        <ul class="nav-links">
        <li><a href="inventory.php">Store</a></li>
        <li><a href="edit_product.php">EditProduct</a></li>
        <li><a href="Stockgame.php">ShowProduct</a></li>
        <li><a href="add_product.php" class="add-product-btn">➕ Add Product</a></li>
        </ul>
    </nav>

    <div class="form-container">
        <h1>Add New Product</h1>
        <form method="POST" action="" class="form-grid">
            <div class="form-group">
                <label for="account_id">Account ID</label>
                <input type="text" id="account_id" name="account_id" required>
            </div>

            <div class="form-group">
                <label for="game_id">Game ID</label>
                <input type="text" id="game_id" name="game_id" required>
            </div>

            <div class="form-group">
                <label for="user_id">User ID</label>
                <input type="text" id="user_id" name="user_id" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="details">Details</label>
                <input type="text" id="details" name="details" required>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="available">Available</option>
                    <option value="sold">Sold</option>
                </select>
            </div>

            <button type="submit" class="submit-btn">Add Product</button>
        </form>
    </div>
</body>
</html>