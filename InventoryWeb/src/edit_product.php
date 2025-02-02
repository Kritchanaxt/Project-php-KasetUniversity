<?php
// Database connection
$servername = getenv('DB_SERVER') ?: '158.108.101.153';
$username = getenv('DB_USERNAME') ?: 'std6630202015';
$password = getenv('DB_PASSWORD') ?: 'g3#Vjp8L';
$dbname = getenv('DB_NAME') ?: 'it_std6630202015';

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

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

    if (!mysqli_query($conn, $update_query)) {
        echo "Error updating product: " . mysqli_error($conn);
    } else {
        header("Location: edit_product.php");
        exit();
    }
}

// Search functionality
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);

    // Search query for all fields in the Accounts table
    $query = "SELECT * FROM Accounts WHERE 
              account_id LIKE '%$search_query%' OR 
              game_id LIKE '%$search_query%' OR 
              user_id LIKE '%$search_query%' OR
              username LIKE '%$search_query%' OR 
              password LIKE '%$search_query%' OR 
              details LIKE '%$search_query%' OR 
              price LIKE '%$search_query%' OR 
              status LIKE '%$search_query%' OR 
              created_at LIKE '%$search_query%'
              ORDER BY account_id ASC";
} else {
    // Default query if no search term is provided
    $query = "SELECT * FROM Accounts ORDER BY account_id ASC";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error executing query: " . mysqli_error($conn));
}

// Handle delete
if (isset($_GET['delete'])) {
    $account_id = mysqli_real_escape_string($conn, $_GET['delete']);

    $delete_query = "DELETE FROM Accounts WHERE account_id = '$account_id'";
    if (mysqli_query($conn, $delete_query)) {
        header("Location: edit_product.php"); // Reload the page after deletion
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Kanit', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f0f4ff, #d3e8ff);
            color: #333;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #4a90e2;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        /* Search Bar */
        .search-container {
            text-align: center;
            margin: 30px 0;
        }

        .search-container input {
            width: 350px;
            padding: 12px;
            border: none;
            border-radius: 25px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            outline: none;
            transition: box-shadow 0.3s;
        }

        .search-container input:focus {
            box-shadow: 0 0 15px rgba(74, 144, 226, 0.7);
        }

        .search-container button {
            padding: 12px 25px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            margin-left: 10px;
            transition: background 0.3s, transform 0.3s;
        }

        .search-container button:hover {
            background: #357ab8;
            transform: scale(1.05);
        }

        /* Table */
        .product-table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .product-table th, .product-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #f2f2f2;
        }

        .product-table th {
            background: #4a90e2;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .product-table tr:hover {
            background: #f9f9f9;
            transition: background 0.3s;
        }

        .product-table td input, .product-table td select {
            width: 90%;
            padding: 8px;
            margin: 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .product-table td input:focus, .product-table td select:focus {
            border: 1px solid #4a90e2;
            box-shadow: 0 0 10px rgba(74, 144, 226, 0.5);
        }

        /* Buttons */
        .submit-btn {
            padding: 8px 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }

        .submit-btn:hover {
            background: #218838;
            transform: scale(1.1);
        }
        <style>
 /* ปุ่ม Delete */
 .delete-btn {
        padding: 8px 15px;
        background: #e74c3c; /* สีแดงสำหรับ Delete */
        color: white;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.3s, transform 0.3s;
    }

    .delete-btn:hover {
        background: #c0392b; /* สีแดงเข้มขึ้นเมื่อ Hover */
        transform: scale(1.1); /* ขยายเล็กน้อยเมื่อ Hover */
    }

</style>

    </style>
</head>
<body>
    <div class="navbar">
        <div>Edit Product</div>
        <div>
            <a href="inventory.php">Home</a>
            <a href="add_product.php">Add Game</a>
            <a href="edit_product.php">Edit Games</a>
        </div>
    </div>

    <h1 style="text-align: center; margin-top: 20px;">Edit Game Products</h1>

    <div class="search-container">
        <form method="GET" action="edit_product.php">
            <input type="text" name="search" placeholder="Search by Account ID, Game ID, or Username" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <table class="product-table">
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
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <form method="POST" action="edit_product.php">
                    <tr>
                        <td><?php echo htmlspecialchars($row['account_id']); ?></td>
                        <td>
                            <input type="text" name="game_id" value="<?php echo htmlspecialchars($row['game_id']); ?>" required>
                        </td>
                        <td>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($row['username']); ?>" required>
                        </td>
                        <td>
                            <input type="text" name="details" value="<?php echo htmlspecialchars($row['details']); ?>" required>
                        </td>
                        <td>
                            <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($row['price']); ?>" required>
                        </td>
                        <td>
                            <select name="status">
                                <option value="available" <?php echo ($row['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                                <option value="sold" <?php echo ($row['status'] === 'sold') ? 'selected' : ''; ?>>Sold</option>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="account_id" value="<?php echo htmlspecialchars($row['account_id']); ?>">
                            <button type="submit" name="update" class="submit-btn">Update</button>
                            <a href="edit_product.php?delete=<?php echo $row['account_id']; ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this record?');">
                               Delete
                            </a>
                        </td>
                    </tr>
                </form>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No products found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>



<?php
mysqli_close($conn);
?>
