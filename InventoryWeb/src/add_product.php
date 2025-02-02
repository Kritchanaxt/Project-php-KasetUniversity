<?php
include('db_connection.php'); // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account_id = mysqli_real_escape_string($conn, $_POST['account_id']);
    $game_id = mysqli_real_escape_string($conn, $_POST['game_id']);
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // สร้างคำสั่ง SQL
    $sql = "INSERT INTO Accounts (account_id, game_id, user_id, username, password, details, price, status, created_at)
            VALUES ('$account_id', '$game_id', '$user_id', '$username', '$password', '$details', '$price', '$status', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>Product added successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
    }
}

mysqli_close($conn); // ปิดการเชื่อมต่อฐานข้อมูล
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg,rgb(255, 255, 255),rgb(255, 255, 255));
            margin: 0;
            padding: 0;
            color: white;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #4a90e2;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .navbar div {
            font-size: 24px;
            font-weight: 600;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            transition: background 0.3s, transform 0.3s;
        }

        .navbar a:hover {
            background: rgb(255, 255, 255);
            transform: scale(1.1);
        }

        /* Form Container */
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            background: rgb(112, 180, 243);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgb(102, 167, 241);
        }

        .form-container h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: white;
            font-size: 28px;
        }

        .form-container label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-container input, .form-container select {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(253, 253, 253, 0.3);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .form-container input:focus, .form-container select:focus {
            outline: none;
            border: 1px solid #4a90e2;
            box-shadow: 0 0 10px rgba(74, 144, 226, 0.7);
        }

        .form-container button {
            width: 100%;
            padding: 1rem;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }

        .form-container button:hover {
            background: #357ab8;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div>Add Product</div>
        <div>
            <a href="inventory.php">Home</a>
            <a href="add_product.php">Add Product</a>
            <a href="edit_product.php">Edit Products</a>
        </div>
    </div>

    <div class="form-container">
        <h1>Add Product</h1>
        <form method="POST" action="">
            <label for="account_id">Account ID:</label>
            <input type="text" id="account_id" name="account_id" required>

            <label for="game_id">Game ID:</label>
            <input type="text" id="game_id" name="game_id" required>

            <label for="user_id">User ID:</label>
            <input type="text" id="user_id" name="user_id" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="details">Details:</label>
            <input type="text" id="details" name="details" required>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="available">Available</option>
                <option value="sold">Sold</option>
            </select>

            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>
