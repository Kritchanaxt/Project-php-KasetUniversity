<?php
include '/Users/kritchanaxt_./Desktop/InventoryProject/database/db_connection.php';

// สร้างคำสั่ง SQL เพื่อดึงข้อมูลจากตาราง Games
$sql = "SELECT account_id, game_id, username, password, details, price, status, created_at FROM Accounts";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Error in query: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <link href="../../resources/css/kuy.css" rel="stylesheet"> -->

  
    <style>

        body {
            /* background: linear-gradient(135deg, #ff7e5f, #feb47b); */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .card {
            background-color: #0056b3;
            border: none;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.9s;

        }

        .container {
            margin-top: 20px;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card-body {
            padding: 20px;
            color: #fff;
            
         }

        .btn-custom {
            background-color: #007bff;
            color: #fff;
            border-radius: 20px;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .card-title {
            font-size: 30px;
            color: #feb47b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <?php
            // ตรวจสอบว่ามีผลลัพธ์หรือไม่
            if (mysqli_num_rows($result) > 0) {
                // วนลูปเพื่อแสดงข้อมูลแต่ละแถวในรูปแบบการ์ด
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row["username"]) ?></h5>
                                <p class="card-text"><strong>Account ID:</strong> <?= htmlspecialchars($row["account_id"]) ?></p>
                                <p class="card-text"><strong>Game ID:</strong> <?= htmlspecialchars($row["game_id"]) ?></p>
                                <p class="card-text"><strong>Password:</strong> <?= htmlspecialchars($row["password"]) ?></p>
                                <p class="card-text"><strong>Details:</strong> <?= htmlspecialchars($row["details"]) ?></p>
                                <p class="card-text"><strong>Price:</strong> <?= htmlspecialchars($row["price"]) ?></p>
                                <p class="card-text"><strong>Status:</strong> <?= htmlspecialchars($row["status"]) ?></p>
                                <p class="card-text"><strong>Created At:</strong> <?= htmlspecialchars($row["created_at"]) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='text-center'>ไม่พบข้อมูลเกม</p>";
            }
            // ปิดการเชื่อมต่อฐานข้อมูล
            mysqli_close($conn);
            ?>
        </div>
    </div>
</body>
</html>
