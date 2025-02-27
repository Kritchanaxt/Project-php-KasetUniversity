<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Login.html");
    exit;
}

// Database connection file
require_once 'db_connect.php';

// Get user ID from session
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$userPoint = $_SESSION['user_point'] ?? 0;

// Fetch purchase history
$history = array();
try {
    $stmt = $conn->prepare("
        SELECT ph.purchase_id, ph.account_id, ph.game_id, ph.price, ph.purchase_date,
               a.username as account_username, a.details
        FROM purchase_history ph
        JOIN accounts a ON ph.account_id = a.account_id
        WHERE ph.user_id = ?
        ORDER BY ph.purchase_date DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    $stmt->close();
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History - PLAYERHAVEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --bg-color: #f3f4f6; --text-color: #1f2937; --navbar-bg: #6b46c1;
            --navbar-text: #ffffff; --card-bg: #ffffff; --card-shadow: rgba(0, 0, 0, 0.1);
            --card-highlight: rgba(107, 70, 193, 0.2); --badge-available: #10b981;
            --badge-sold: #ef4444; --btn-primary: #6b46c1; --btn-hover: #5a32a3;
        }
        .dark-mode {
            --bg-color: #10142b; --text-color: #e5e7eb; --navbar-bg: #1a1f36;
            --navbar-text: #00ffff; --card-bg: #232c58; --card-shadow: rgba(0, 0, 0, 0.4);
            --card-highlight: rgba(0, 255, 255, 0.1); --badge-available: #059669;
            --badge-sold: #dc2626; --btn-primary: #4f46e5; --btn-hover: #4338ca;
        }
        body { font-family: 'Kanit', sans-serif; background-color: var(--bg-color); color: var(--text-color); transition: all 0.3s; line-height: 1.6; }
        header { background: var(--navbar-bg); color: var(--navbar-text); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); padding: 1rem; position: sticky; top: 0; z-index: 100; }
        .dark-mode header { text-shadow: 0 0 5px #00ffff, 0 0 10px #00ffff; background: linear-gradient(to right, #070f1f, #0c1326); }
    </style>
</head>
<body>
    <header>
        <div class="container mx-auto flex flex-col md:flex-row items-center justify-between gap-4 py-4 px-6">
            <div class="text-3xl font-bold flex items-center gap-3">
                <img id="logo" src="image/logo_player/light_logo.png" alt="PLAYERHAVEN Logo" class="w-16 h-16 object-contain transition-transform duration-300 hover:scale-110" />
                PLAYERHAVEN
            </div>
            <nav class="nav-menu flex flex-wrap justify-center md:justify-start gap-6 text-lg">
                <a href="HomePage.html" class="hover:text-indigo-300 transition-colors">หน้าแรก</a>
                <a href="game_shop.php" class="hover:text-indigo-300 transition-colors">ไอดีเกมออนไลน์</a>
                <a href="#" class="hover:text-indigo-300 transition-colors">สุ่มไอดีเกม</a>
                <a href="#" class="hover:text-indigo-300 transition-colors">บัตรเติมเกม</a>
                <a href="#" class="hover:text-indigo-300 transition-colors">เติมเกมออนไลน์</a>
                <a href="#" class="hover:text-indigo-300 transition-colors">ติดต่อเรา</a>
            </nav>
            <div class="header-actions flex items-center gap-4">
                <span class="text-sm text-white">คงเหลือ <?php echo number_format($userPoint, 2); ?> เครดิต</span>
                <button class="bg-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-800 transition-colors" onclick="window.location.href='game_shop.php'">กลับไปหน้าร้านค้า</button>
                <button class="bg-red-500 px-4 py-2 rounded-lg hover:bg-red-600 transition-colors" onclick="logout()">ออกจากระบบ</button>
                <button onclick="toggleTheme()" class="p-2 rounded-lg bg-gray-300 dark:bg-gray-600 transition-colors text-lg">🌙 / ☀️</button>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg p-8 mb-12 text-white shadow-lg">
            <h1 class="text-4xl font-bold mb-4 text-center">ประวัติการซื้อ</h1>
            <p class="text-xl text-center">ประวัติการซื้อไอดีเกมทั้งหมดของคุณ <?php echo htmlspecialchars($username); ?></p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($history)): ?>
            <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-8 text-center">
                <p class="text-xl">คุณยังไม่มีประวัติการซื้อ</p>
                <a href="game_shop.php" class="inline-block mt-4 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">ซื้อไอดีเกมเลย</a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                    <thead class="bg-purple-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">รหัสการซื้อ</th>
                            <th class="py-3 px-4 text-left">เกม</th>
                            <th class="py-3 px-4 text-left">ไอดีเกม</th>
                            <th class="py-3 px-4 text-left">ชื่อผู้ใช้</th>
                            <th class="py-3 px-4 text-left">รายละเอียด</th>
                            <th class="py-3 px-4 text-right">ราคา</th>
                            <th class="py-3 px-4 text-left">วันที่ซื้อ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($history as $item): ?>
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <td class="py-3 px-4">#<?php echo $item['purchase_id']; ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($item['game_id']); ?></td>
                                <td class="py-3 px-4">#<?php echo $item['account_id']; ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($item['account_username']); ?></td>
                                <td class="py-3 px-4 max-w-md truncate"><?php echo htmlspecialchars($item['details']); ?></td>
                                <td class="py-3 px-4 text-right"><?php echo number_format($item['price'], 2); ?> บาท</td>
                                <td class="py-3 px-4"><?php echo date('d/m/Y H:i', strtotime($item['purchase_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Section -->
            <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                <h2 class="text-2xl font-bold mb-4">สรุปการซื้อ</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-lg text-center">
                        <p class="text-lg font-semibold">จำนวนไอดีที่ซื้อทั้งหมด</p>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-300"><?php echo count($history); ?></p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg text-center">
                        <p class="text-lg font-semibold">มูลค่าการซื้อทั้งหมด</p>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-300">
                            <?php 
                                $totalSpent = array_sum(array_column($history, 'price'));
                                echo number_format($totalSpent, 2); 
                            ?> บาท
                        </p>
                    </div>
                    <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg text-center">
                        <p class="text-lg font-semibold">การซื้อล่าสุด</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-300">
                            <?php 
                                if (!empty($history)) {
                                    echo date('d/m/Y', strtotime($history[0]['purchase_date']));
                                } else {
                                    echo "-";
                                }
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex justify-center gap-4">
                <a href="game_shop.php" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">กลับไปหน้าร้านค้า</a>
                <a href="user_profile.php" class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">ดูโปรไฟล์ของฉัน</a>
            </div>
        <?php endif; ?>
    </main>

    <footer class="mt-16 bg-gray-800 text-white">
        <div class="container mx-auto px-6 py-8 text-center">
            <p class="text-lg mb-4">© 2025 PLAYERHAVEN - ศูนย์กลางเกมออนไลน์ที่ครบวงจร</p>
            <div class="footer-links flex justify-center gap-6 text-lg">
                <a href="#" class="hover:text-purple-400 transition-colors">เงื่อนไขการใช้บริการ</a>
                <a href="#" class="hover:text-purple-400 transition-colors">นโยบายความเป็นส่วนตัว</a>
                <a href="#" class="hover:text-purple-400 transition-colors">คำถามที่พบบ่อย</a>
            </div>
        </div>
    </footer>

    <script>
        // Logout function
        function logout() {
            fetch('logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        localStorage.removeItem("username");
                        localStorage.removeItem("email");
                        localStorage.removeItem("points");
                        localStorage.removeItem("user_id");
                        alert("ออกจากระบบสำเร็จ");
                        window.location.href = 'Login.html';
                    } else {
                        alert("เกิดข้อผิดพลาดในการออกจากระบบ");
                    }
                })
                .catch(error => {
                    console.error("Logout error:", error);
                    localStorage.removeItem("username");
                    localStorage.removeItem("email");
                    localStorage.removeItem("points");
                    localStorage.removeItem("user_id");
                    window.location.href = 'Login.html';
                });
        }

        // Theme toggle function
        function toggleTheme() {
            const body = document.body;
            const navbar = document.querySelector("header");
            const isDarkMode = body.classList.toggle("dark-mode");
            const logo = document.getElementById("logo");

            if (isDarkMode) {
                logo.src = "image/logo_player/dark_logo.png";
                body.style.backgroundColor = "#10142b";
                navbar.style.backgroundColor = "#1a1f36";
            } else {
                logo.src = "image/logo_player/light_logo.png";
                body.style.backgroundColor = "";
                navbar.style.backgroundColor = "";
            }

            localStorage.setItem("theme", isDarkMode ? "dark" : "light");
        }

        // Apply saved theme
        document.addEventListener("DOMContentLoaded", function() {
            const savedTheme = localStorage.getItem("theme");
            if (savedTheme === "dark") {
                toggleTheme();
            }
        });
    </script>
</body>
</html>