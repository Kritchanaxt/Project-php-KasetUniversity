<?php
include '/Users/kritchanaxt_./Desktop/InventoryProject/database/db_connection.php';

// สร้างคำสั่ง SQL เพื่อดึงข้อมูลจากตาราง Users
$sql = "SELECT user_id, username, password, email, phone, role, created_at FROM Users";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <!-- Bootstrap CSS -->
     
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                while($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row["username"]) ?></h5>
                                <p class="card-text"><strong>Email:</strong> <?= htmlspecialchars($row["email"]) ?></p>
                                <p class="card-text"><strong>Phone:</strong> <?= htmlspecialchars($row["phone"]) ?></p>
                                <p class="card-text"><strong>Role:</strong> <?= htmlspecialchars($row["role"]) ?></p>
                                <p class="card-text"><strong>Joined:</strong> <?= htmlspecialchars($row["created_at"]) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='text-center'>ไม่พบข้อมูลผู้ใช้</p>";
            }
            // ปิดการเชื่อมต่อฐานข้อมูล
            mysqli_close($conn);
            ?>
        </div>
    </div>
</body>
</html>
