<?php
// เริ่มเซสชัน
session_start();

// ตรวจสอบการล็อกอิน
$logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
if (!$logged_in && isset($_POST['randomize'])) {
    // ถ้าไม่ได้ล็อกอินและพยายามสุ่ม ให้ redirect ไปหน้าล็อกอิน
    header("Location: login.php");
    exit();
}

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

$user_point = 0;
if ($logged_in) {
    $user_id = $_SESSION['user_id'];
    
    try {
        // ดึงข้อมูลล่าสุดจากฐานข้อมูล รวมทั้ง point
        $stmt = $conn->prepare("SELECT username, email, phone, point FROM Users WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // อัปเดตเซสชันด้วยข้อมูลล่าสุด
            $_SESSION["username"] = $row["username"];
            $_SESSION["email"] = $row["email"];
            $_SESSION["phone"] = $row["phone"];
            $_SESSION["points"] = $row["point"]; // เก็บพอยต์ในเซสชัน
            
            // กำหนดค่าพอยต์สำหรับใช้ในไฟล์นี้
            $user_point = $row["point"];
        } else {
            // กรณีไม่พบข้อมูลผู้ใช้
            $user_point = 0;
            $_SESSION["points"] = 0;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        // กรณีเกิดข้อผิดพลาด
        error_log("Error fetching user data: " . $e->getMessage());
        $user_point = 0;
        $_SESSION["points"] = 0;
    }
}

// กำหนดราคาสำหรับการสุ่มแต่ละเกม
$game_prices = [
    'ROV' => 50,
    'TFT' => 80,
    'LOL' => 100,
    'FF' => 70,
    'VALORANT' => 120,
    'CODM' => 60
];

// รายการเกมที่สามารถสุ่มได้
$available_games = array_keys($game_prices);

// ตัวแปรสำหรับเก็บผลการสุ่ม
$randomize_result = null;
$error_message = null;
$success_message = null;

// ระดับความหายากและอัตราการออก
$rarities = [
    'common' => 60,      // เกลือ
    'uncommon' => 25,    // ธรรมดา
    'rare' => 10,        // หายาก
    'epic' => 4,         // สุดยอด
    'legendary' => 1     // ตำนาน
];

// ถ้ามีการส่งฟอร์มการตั้งค่าอัตราการออก
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_rates'])) {
    // ตรวจสอบว่าเป็นแอดมินหรือไม่
    if ($logged_in && $_SESSION['username'] == 'admin12345678') {
        if (isset($_POST['rarity_rates']) && is_array($_POST['rarity_rates'])) {
            $new_rates = $_POST['rarity_rates'];
            $total = array_sum($new_rates);
            
            // ตรวจสอบว่าผลรวมเท่ากับ 100%
            if (abs($total - 100) <= 0.5) {
                $rarities = [
                    'common' => $new_rates['common'],
                    'uncommon' => $new_rates['uncommon'],
                    'rare' => $new_rates['rare'],
                    'epic' => $new_rates['epic'],
                    'legendary' => $new_rates['legendary']
                ];
                
                // บันทึกการตั้งค่าใน session
                $_SESSION['rarity_rates'] = $rarities;
                $success_message = "บันทึกอัตราการออกสำเร็จ";
            } else {
                $error_message = "ผลรวมของอัตราการออกต้องเท่ากับ 100% (ปัจจุบัน: $total%)";
            }
        }
    } else {
        $error_message = "คุณไม่มีสิทธิ์ตั้งค่าอัตราการออก";
    }
}

// โหลดการตั้งค่าจาก session ถ้ามี
if (isset($_SESSION['rarity_rates'])) {
    $rarities = $_SESSION['rarity_rates'];
}

// ฟังก์ชันสร้าง Account ID ตามรูปแบบของแต่ละเกม
function generateGameId($game) {
    switch ($game) {
        case 'ROV':
            return mt_rand(10000000, 99999999); // 8 หลัก
        case 'TFT':
            $names = [
                'Tactician', 'Galaxy', 'Cosmic', 'Star', 'Astral', 'Lunar', 'Nebula', 'Nova', 
                'Void', 'Shadow', 'Light', 'Dawn', 'Dusk', 'Echo', 'Whisper', 'Thunder', 'Storm'
            ];
            $name = $names[array_rand($names)];
            $tag = mt_rand(1000, 9999);
            return "$name#$tag";
        case 'LOL':
            $names = [
                'Summoner', 'Champion', 'Void', 'Mystic', 'Legend', 'Eternal', 'Immortal',
                'Cosmic', 'Epic', 'Divine', 'Arcane', 'Astra', 'Nexus', 'Phoenix', 'Dragon'
            ];
            $name = $names[array_rand($names)];
            $tag = mt_rand(1000, 9999);
            return "$name#$tag";
        case 'FF':
            return mt_rand(100000000, 999999999); // 9 หลัก
        case 'VALORANT':
            $names = [
                'Phantom', 'Ghost', 'Specter', 'Operator', 'Vandal', 'Odin', 'Sheriff',
                'Guardian', 'Marshal', 'Stinger', 'Frenzy', 'Classic', 'Judge', 'Bucky'
            ];
            $name = $names[array_rand($names)];
            $tag = mt_rand(1000, 9999);
            return "$name#$tag";
        case 'CODM':
            return mt_rand(10000000, 99999999); // 8 หลัก
        default:
            return "ERROR-" . mt_rand(1000, 9999);
    }
}

// ฟังก์ชันสร้างรหัสผ่านสุ่ม
function generateRandomPassword() {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < 12; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

// ฟังก์ชันสุ่มระดับความหายาก
function getRandomRarity($rarities) {
    $rand = mt_rand(1, 100);
    $cumulative = 0;
    
    foreach ($rarities as $rarity => $chance) {
        $cumulative += $chance;
        if ($rand <= $cumulative) {
            return $rarity;
        }
    }
    
    return 'common'; // กรณีมีข้อผิดพลาด
}

// ฟังก์ชันดึงคำอธิบายตามระดับความหายาก
function getRarityDescription($rarity) {
    switch ($rarity) {
        case 'common':
            return 'เกลือ! แต่ก็เป็นแอคที่ใช้งานได้ปกติ';
        case 'uncommon':
            return 'แอคทั่วไป เล่นได้ของนิดหน่อย';
        case 'rare':
            return 'แอคหายาก พอมีของแรร์ๆ';
        case 'epic':
            return 'แอคหายาก ของแรร์เยอะ';
        case 'legendary':
            return 'แอคเทพทรู ของเกือบครบ!';
        default:
            return '';
    }
}

// ฟังก์ชันดึงชื่อภาษาไทยของระดับความหายาก
function getRarityNameThai($rarity) {
    switch ($rarity) {
        case 'common':
            return 'เกลือ (Common)';
        case 'uncommon':
            return 'ธรรมดา (Uncommon)';
        case 'rare':
            return 'หายาก (Rare)';
        case 'epic':
            return 'สุดยอด (Epic)';
        case 'legendary':
            return 'ตำนาน (Legendary)';
        default:
            return '';
    }
}

// ฟังก์ชันดึงสินค้าเพิ่มเติมตามระดับความหายาก
function getSpecialItems($rarity, $game) {
    $items = [];
    
    switch ($rarity) {
        case 'common':
            $items[] = 'ไอดีเริ่มเริ่มต้น';
            break;
        case 'uncommon':
            $items[] = 'สกินพื้นฐาน พร้อมเล่น';
            break;
        case 'rare':
            $items[] = 'สกินหายาก 3-5 ชิ้น';
            $items[] = 'ของแรร์พอมี';
            break;
        case 'epic':
            $items[] = 'สกินตามเทศกาล 2-3 ชิ้น';
            $items[] = 'ยอดเติมหลักหมื่น';
            $items[] = 'ของพร้อมเล่น';
            break;
        case 'legendary':
            $items[] = 'ของเกือบครบทุกอย่าง!';
            $items[] = 'ยอดเติมหลักล้าน';
            $items[] = 'ของลิมิเต็ด หาไม่ได้แล้ว';
            $items[] = 'เอาไปไม่ต้องเติมเพิ่มพร้อมเล่น';
            break;
    }
    
    // เพิ่มรายละเอียดเฉพาะเกม
    if ($game == 'TFT') {
        if ($rarity == 'epic' || $rarity == 'legendary') {
            $items[] = 'Little Legends,Board สวยๆเพี๊ยบ';
        }
    } elseif ($game == 'VALORANT') {
        if ($rarity == 'legendary') {
            $items[] = 'มีดแรร์ๆเยอะมาก';
        }
    }
    
    return $items;
}

// ถ้ามีการสุ่ม
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['randomize'])) {
    if (!$logged_in) {
        header("Location: login.php");
        exit();
    }
    
    $selected_game = $_POST['game'];
    
    // ตรวจสอบว่าเลือกเกมที่ถูกต้อง
    if (!in_array($selected_game, $available_games)) {
        $error_message = "เกมที่เลือกไม่ถูกต้อง";
    } else {
        $price = $game_prices[$selected_game];
        
        // ตรวจสอบว่ามี point พอหรือไม่
        if ($user_point < $price) {
            $error_message = "คุณมี point ไม่เพียงพอสำหรับการสุ่ม $selected_game (มี $user_point point, ต้องการ $price point)";
        } else {
            // เริ่ม transaction
            mysqli_begin_transaction($conn);
            try {
                // สุ่มระดับความหายาก
                $rarity = getRandomRarity($rarities);
                
                // สร้าง Account ID และ Username
                $account_id = generateGameId($selected_game);
                $username = "Player_" . substr(md5(uniqid()), 0, 8);
                
                // สร้างรหัสผ่าน
                $password = generateRandomPassword();
                
                // สร้างรายละเอียดตามระดับความหายาก
                $items = getSpecialItems($rarity, $selected_game);
                $details = implode(", ", $items);
                
                // คำนวณราคาเพิ่มตามระดับความหายาก (ราคาที่ใช้สุ่มคงที่ แต่มูลค่าของที่ได้อาจแตกต่างกัน)
                $account_price = $price;
                
                // เตรียมข้อมูลสำหรับเพิ่มในฐานข้อมูล Accounts
                $stmt = $conn->prepare("INSERT INTO Accounts (account_id, game_id, username, password, price, details, status, user_id) VALUES (?, ?, ?, ?, ?, ?, 'sold', ?)");
                if (!$stmt) {
                    throw new Exception("Prepare account insert failed: " . $conn->error);
                }
                
                $game_id = "#" . $selected_game; // ใช้รูปแบบเดียวกับที่มีในฐานข้อมูลเดิม
                $stmt->bind_param("ssssdss", $account_id, $game_id, $username, $password, $account_price, $details, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert account: " . $stmt->error);
                }
                $stmt->close();
                
                // หัก point จากผู้ใช้
                $new_point = $user_point - $price;
                $update_stmt = $conn->prepare("UPDATE Users SET point = ? WHERE user_id = ?");
                if (!$update_stmt) {
                    throw new Exception("Prepare update failed: " . $conn->error);
                }
                
                $update_stmt->bind_param("ds", $new_point, $user_id);
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to update user points: " . $update_stmt->error);
                }
                $update_stmt->close();
                
                // บันทึกประวัติการสุ่ม/ซื้อ
                $purchase_date = date('Y-m-d H:i:s');
                $insert_stmt = $conn->prepare("INSERT INTO purchase_history (user_id, account_id, game_id, price, password, purchase_date) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$insert_stmt) {
                    throw new Exception("Prepare insert history failed: " . $conn->error);
                }
                
                $insert_stmt->bind_param("sssdss", $user_id, $account_id, $game_id, $price, $password, $purchase_date);
                if (!$insert_stmt->execute()) {
                    throw new Exception("Failed to insert purchase history: " . $insert_stmt->error);
                }
                $insert_stmt->close();
                
                // Commit transaction
                mysqli_commit($conn);
                
                // อัปเดตข้อมูลใน session
                $_SESSION['points'] = $new_point;
                $user_point = $new_point;
                
                // เก็บผลการสุ่มเพื่อแสดงผล
                $randomize_result = [
                    'game' => $selected_game,
                    'account_id' => $account_id,
                    'username' => $username,
                    'password' => $password,
                    'rarity' => $rarity,
                    'details' => $details,
                    'price' => $price
                ];
                
                $success_message = "สุ่มสำเร็จ! คุณได้รับบัญชี $selected_game ระดับ " . getRarityNameThai($rarity);
                
            } catch (Exception $e) {
                // Rollback transaction เมื่อเกิดข้อผิดพลาด
                mysqli_rollback($conn);
                $error_message = "เกิดข้อผิดพลาด: " . $e->getMessage();
                error_log("Randomize error: " . $e->getMessage());
            }
        }
    }
}

// ดึงประวัติการสุ่ม/ซื้อของผู้ใช้
$history = [];
if ($logged_in) {
    try {
            // ดึงประวัติการสุ่ม/ซื้อของผู้ใช้
            $hist_stmt = $conn->prepare("
                SELECT ph.*, a.username, a.details 
                FROM purchase_history ph
                LEFT JOIN Accounts a ON ph.account_id = a.account_id
                WHERE ph.user_id = ?
                ORDER BY ph.purchase_date DESC
                LIMIT 10
            ");
            
            if (!$hist_stmt) {
                throw new Exception("Prepare history query failed: " . $conn->error);
            }
            
            $hist_stmt->bind_param("s", $user_id);
            $hist_stmt->execute();
            $hist_result = $hist_stmt->get_result();
            
            while ($row = $hist_result->fetch_assoc()) {
                $history[] = $row;
            }
            
            $hist_stmt->close();
    } catch (Exception $e) {
        error_log("Error fetching history: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สุ่มไอดีเกม</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: white;
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #0AC8B9;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s, transform 0.3s;
        }

        .navbar a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .brand {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 0, 0, 0.2);
            padding: 8px 12px;
            border-radius: 5px;
        }

        /* Header */
        .header {
            text-align: center;
            padding: 40px 20px;
            background: rgba(0, 0, 0, 0.3);
        }

        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
            color: #C89B3C;
        }

        .header p {
            font-size: 18px;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 5px;
            text-align: center;
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
            border: 1px solid #dc3545;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.3);
            color: #8affb6;
            border: 1px solid #28a745;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Randomizer Section */
        .randomizer-section {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
        }

        .randomizer {
            flex: 1;
            min-width: 300px;
            max-width: 600px;
            background: rgba(10, 20, 40, 0.6);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid #0AC8B9;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .randomizer h2 {
            text-align: center;
            color: #C89B3C;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .game-selector {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .game-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 10px 20px;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .game-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        .game-btn.active {
            background: rgba(255, 255, 255, 0.3);
            border-color: #0AC8B9;
            box-shadow: 0 0 15px rgba(10, 200, 185, 0.5);
        }

        .price-info {
            text-align: center;
            margin-bottom: 25px;
            font-size: 18px;
        }

        .price-highlight {
            font-size: 24px;
            font-weight: bold;
            color: #C89B3C;
        }

        .randomize-btn {
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            background: linear-gradient(135deg, #0AC8B9, #0a8a7f);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(10, 200, 185, 0.4);
            text-align: center;
        }

        .randomize-btn:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(10, 200, 185, 0.6);
        }

        .randomize-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        /* Result Section */
        .result-section {
            flex: 1;
            min-width: 300px;
            max-width: 600px;
        }

        .result-card {
            background: rgba(10, 20, 40, 0.6);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.5s ease;
            border: 1px solid;
            transition: transform 0.3s;
        }

        .result-card:hover {
            transform: translateY(-5px);
        }

        .result-card.common {
            border-color: #aaaaaa;
        }

        .result-card.uncommon {
            border-color: #00ff9d;
        }

        .result-card.rare {
            border-color: #0095ff;
        }

        .result-card.epic {
            border-color: #bb00ff;
        }

        .result-card.legendary {
            border-color: #ff9d00;
            animation: glow 2s infinite alternate;
        }

        @keyframes glow {
            from {
                box-shadow: 0 0 10px #ff9d00;
            }
            to {
                box-shadow: 0 0 20px #ff9d00, 0 0 30px #ff9d00;
            }
        }

        .result-header {
            padding: 15px;
            text-align: center;
            position: relative;
        }

        .result-header.common {
            background: linear-gradient(to right, rgba(170, 170, 170, 0.2), rgba(170, 170, 170, 0.3));
        }

        .result-header.uncommon {
            background: linear-gradient(to right, rgba(0, 255, 157, 0.2), rgba(0, 255, 157, 0.3));
        }

        .result-header.rare {
            background: linear-gradient(to right, rgba(0, 149, 255, 0.2), rgba(0, 149, 255, 0.3));
        }

        .result-header.epic {
            background: linear-gradient(to right, rgba(187, 0, 255, 0.2), rgba(187, 0, 255, 0.3));
        }

        .result-header.legendary {
            background: linear-gradient(to right, rgba(255, 157, 0, 0.2), rgba(255, 157, 0, 0.3));
        }

        .rarity-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .rarity-badge.common {
            background: rgba(170, 170, 170, 0.3);
            color: #aaaaaa;
        }

        .rarity-badge.uncommon {
            background: rgba(0, 255, 157, 0.3);
            color: #00ff9d;
        }

        .rarity-badge.rare {
            background: rgba(0, 149, 255, 0.3);
            color: #0095ff;
        }

        .rarity-badge.epic {
            background: rgba(187, 0, 255, 0.3);
            color: #bb00ff;
        }

        .rarity-badge.legendary {
            background: rgba(255, 157, 0, 0.3);
            color: #ff9d00;
        }

        .result-game {
            font-size: 24px;
            font-weight: bold;
            color: #C89B3C;
        }

        .result-body {
            padding: 20px;
        }

        .result-info {
            margin-bottom: 15px;
        }

        .result-info p {
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
        }

        .result-info .label {
            color: rgba(255, 255, 255, 0.7);
        }

        .result-info .value {
            font-weight: 500;
        }

        .result-info .value.common {
            color: #aaaaaa;
        }

        .result-info .value.uncommon {
            color: #00ff9d;
        }

        .result-info .value.rare {
            color: #0095ff;
        }

        .result-info .value.epic {
            color: #bb00ff;
        }

        .result-info .value.legendary {
            color: #ff9d00;
        }

        .result-details {
            background: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .result-details h4 {
            margin-top: 0;
            color: #0AC8B9;
            margin-bottom: 10px;
        }

        .result-details ul {
            margin: 0;
            padding-left: 20px;
        }

        .result-details li {
            margin-bottom: 5px;
        }

        .empty-result {
            background: rgba(10, 20, 40, 0.6);
            border-radius: 15px;
            padding: 40px 20px;
            text-align: center;
            border: 1px dashed rgba(255, 255, 255, 0.2);
        }

        .empty-result h3 {
            color: #0AC8B9;
            margin-top: 0;
        }

        /* Settings Section */
        .settings-section {
            background: rgba(10, 20, 40, 0.6);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
        }

        .settings-header h2 {
            margin: 0;
            color: #C89B3C;
        }

        .toggle-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .toggle-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .settings-content {
            display: none;
        }

        .settings-content.active {
            display: block;
        }

        .rate-slider {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .rate-slider label {
            width: 160px;
            flex-shrink: 0;
        }

        .rate-slider input {
            flex: 1;
        }

        .rate-slider .value {
            width: 60px;
            text-align: right;
        }

        .rate-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 10px;
        }

        .rate-actions button {
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }

        .update-btn {
            background: #0AC8B9;
            color: white;
        }

        .reset-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .update-btn:hover {
            background: #089a8e;
        }

        .reset-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* History Section */
        .history-section {
            background: rgba(10, 20, 40, 0.6);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
        }

        .history-header h2 {
            margin: 0;
            color: #C89B3C;
        }

        .history-content {
            display: none;
        }

        .history-content.active {
            display: block;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th, .history-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .history-table th {
            color: #0AC8B9;
            font-weight: 500;
        }

        .history-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .history-rarity {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .history-rarity.common {
            background: rgba(170, 170, 170, 0.2);
            color: #aaaaaa;
        }

        .history-rarity.uncommon {
            background: rgba(0, 255, 157, 0.2);
            color: #00ff9d;
        }

        .history-rarity.rare {
            background: rgba(0, 149, 255, 0.2);
            color: #0095ff;
        }

        .history-rarity.epic {
            background: rgba(187, 0, 255, 0.2);
            color: #bb00ff;
        }

        .history-rarity.legendary {
            background: rgba(255, 157, 0, 0.2);
            color: #ff9d00;
        }

        .empty-history {
            text-align: center;
            padding: 30px 0;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Confetti Animation */
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: #f00;
            position: absolute;
            top: 0;
            opacity: 0;
            animation: confetti 5s ease-in-out;
            z-index: 100;
        }
        
        @keyframes confetti {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(1000px) rotate(720deg); opacity: 0; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 15px 10px;
            }
            
            .nav-right {
                width: 100%;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .header h1 {
                font-size: 28px;
            }
            
            .randomizer, .result-section {
                min-width: 100%;
            }
            
            .game-selector {
                flex-wrap: wrap;
            }
            
            .game-btn {
                flex: 1 0 calc(33.333% - 10px);
                font-size: 14px;
                padding: 8px;
                text-align: center;
            }
            
            .rate-slider {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .rate-slider label {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .rate-slider .value {
                text-align: left;
                margin-top: 5px;
            }
            
            .history-table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="brand">🎮 PLAYER HAVEN</div>
        <div class="nav-right">
            <a href="HomePage.html">หน้าแรก</a>
            <a href="RandomWheel.php">สุ่มไอดีเกม</a>
            <a href="vlr_shop.php">VALORANT</a>
            <a href="rov_shop.php">ROV</a>
            <a href="tft_shop.php">TFT</a>
            <a href="codm_shop.php">CODM</a>
            <a href="lol_shop.php">LOL</a>
            <?php if ($logged_in): ?>
                <div class="user-info">
                    👤 <?php echo $_SESSION['username'] ?? 'User'; ?> 
                    <span style="color: #C89B3C; font-weight: bold;"><?php echo number_format($user_point, 2); ?> Point</span>
                </div>
                <a href="logout.php">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php">เข้าสู่ระบบ</a>
                <a href="register.php">สมัครสมาชิก</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Header -->
    <div class="header">
        <h1>RANDOM GAME ID</h1>
        <p>ลองสุ่มเลยแล้วจะรู้ว่าเกลือมีอยู่จริง!</p>
    </div>

    <!-- Alerts -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <!-- Container -->
    <div class="container">
        <div class="randomizer-section">
            <!-- Randomizer Form -->
            <div class="randomizer">
                <h2>สุ่มไอดีเกม</h2>
                
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="randomizer-form">
                    <div class="game-selector">
                        <?php foreach ($available_games as $game): ?>
                            <button type="button" class="game-btn <?php echo ($game == 'ROV') ? 'active' : ''; ?>" data-game="<?php echo $game; ?>">
                                <?php echo $game; ?>
                            </button>
                        <?php endforeach; ?>
                        <input type="hidden" name="game" id="selected-game" value="ROV">
                    </div>
                    
                    <div class="price-info">
                        ราคา: <span class="price-highlight" id="game-price"><?php echo number_format($game_prices['ROV'], 2); ?></span> Point
                    </div>
                    
                    <button type="submit" name="randomize" class="randomize-btn" <?php echo (!$logged_in || $user_point < min($game_prices)) ? 'disabled' : ''; ?>>
                        สุ่มเลย!
                    </button>
                    
                    <?php if (!$logged_in): ?>
                        <p style="text-align: center; margin-top: 15px; color: #ff6b6b;">กรุณาเข้าสู่ระบบเพื่อสุ่มไอดี</p>
                    <?php elseif ($user_point < min($game_prices)): ?>
                        <p style="text-align: center; margin-top: 15px; color: #ff6b6b;">Point ไม่เพียงพอ (มี <?php echo number_format($user_point, 2); ?> Point)</p>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Result Section -->
            <div class="result-section">
                <?php if ($randomize_result): ?>
                    <div class="result-card <?php echo $randomize_result['rarity']; ?>">
                        <div class="result-header <?php echo $randomize_result['rarity']; ?>">
                            <div class="rarity-badge <?php echo $randomize_result['rarity']; ?>">
                                <?php echo getRarityNameThai($randomize_result['rarity']); ?>
                            </div>
                            <div class="result-game">
                                <?php echo htmlspecialchars($randomize_result['game']); ?> ACCOUNT
                            </div>
                        </div>
                        
                        <div class="result-body">
                            <div class="result-info">
                                <p>
                                    <span class="label">Account ID:</span>
                                    <span class="value <?php echo $randomize_result['rarity']; ?>"><?php echo htmlspecialchars($randomize_result['account_id']); ?></span>
                                </p>
                                <p>
                                    <span class="label">Username:</span>
                                    <span class="value <?php echo $randomize_result['rarity']; ?>"><?php echo htmlspecialchars($randomize_result['username']); ?></span>
                                </p>
                                <p>
                                    <span class="label">Password:</span>
                                    <span class="value <?php echo $randomize_result['rarity']; ?>"><?php echo htmlspecialchars($randomize_result['password']); ?></span>
                                </p>
                                <p>
                                    <span class="label">ราคา:</span>
                                    <span class="value"><?php echo number_format($randomize_result['price'], 2); ?> Point</span>
                                </p>
                            </div>
                            
                            <div class="result-details">
                                <h4>รายละเอียดเพิ่มเติม:</h4>
                                <p><?php echo getRarityDescription($randomize_result['rarity']); ?></p>
                                <?php if (!empty($randomize_result['details'])): ?>
                                    <ul>
                                        <?php foreach (explode(", ", $randomize_result['details']) as $item): ?>
                                            <li><?php echo htmlspecialchars($item); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($randomize_result['rarity'] == 'epic' || $randomize_result['rarity'] == 'legendary'): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                createConfetti(<?php echo ($randomize_result['rarity'] == 'legendary') ? 150 : 50; ?>);
                            });
                        </script>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="empty-result">
                        <h3>สุ่มไอดีเกมที่คุณชอบเลย!</h3>
                        <p>เลือกเกมและกดปุ่มสุ่ม เพื่อลุ้นรับไอดีระดับตำนาน</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Settings Section -->
        <div class="settings-section">
            <div class="settings-header">
                <h2>ตั้งค่าอัตราการออก (Admin Only)</h2>
                <button class="toggle-btn" id="toggle-settings">แสดง</button>
            </div>
            
            <div class="settings-content" id="settings-content">
                <?php if ($logged_in && $_SESSION['username'] == 'admin12345678'): ?>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="rate-slider">
                        <label for="common-rate">เกลือ (Common):</label>
                        <input type="range" min="0" max="100" value="<?php echo $rarities['common']; ?>" id="common-rate" name="rarity_rates[common]" oninput="updateRateValue('common')">
                        <span class="value" id="common-rate-value"><?php echo $rarities['common']; ?>%</span>
                    </div>
                    
                    <div class="rate-slider">
                        <label for="uncommon-rate">ธรรมดา (Uncommon):</label>
                        <input type="range" min="0" max="100" value="<?php echo $rarities['uncommon']; ?>" id="uncommon-rate" name="rarity_rates[uncommon]" oninput="updateRateValue('uncommon')">
                        <span class="value" id="uncommon-rate-value"><?php echo $rarities['uncommon']; ?>%</span>
                    </div>
                    
                    <div class="rate-slider">
                        <label for="rare-rate">หายาก (Rare):</label>
                        <input type="range" min="0" max="100" value="<?php echo $rarities['rare']; ?>" id="rare-rate" name="rarity_rates[rare]" oninput="updateRateValue('rare')">
                        <span class="value" id="rare-rate-value"><?php echo $rarities['rare']; ?>%</span>
                    </div>
                    
                    <div class="rate-slider">
                        <label for="epic-rate">สุดยอด (Epic):</label>
                        <input type="range" min="0" max="100" value="<?php echo $rarities['epic']; ?>" id="epic-rate" name="rarity_rates[epic]" oninput="updateRateValue('epic')">
                        <span class="value" id="epic-rate-value"><?php echo $rarities['epic']; ?>%</span>
                    </div>
                    
                    <div class="rate-slider">
                        <label for="legendary-rate">ตำนาน (Legendary):</label>
                        <input type="range" min="0" max="100" value="<?php echo $rarities['legendary']; ?>" id="legendary-rate" name="rarity_rates[legendary]" oninput="updateRateValue('legendary')">
                        <span class="value" id="legendary-rate-value"><?php echo $rarities['legendary']; ?>%</span>
                    </div>
                    
                    <p id="total-rate" style="text-align: right; margin-top: 15px;">
                        รวมทั้งหมด: <span id="total-rate-value">100</span>%
                    </p>
                    
                    <p id="rate-warning" style="color: #ff6b6b; text-align: right; display: none;">
                        ผลรวมต้องเท่ากับ 100%
                    </p>
                    
                    <div class="rate-actions">
                        <button type="button" id="reset-rates-btn" class="reset-btn">รีเซ็ตค่าเริ่มต้น</button>
                        <button type="submit" name="update_rates" class="update-btn">บันทึกการตั้งค่า</button>
                    </div>
                </form>
                <?php else: ?>
                <div style="text-align: center; padding: 20px;">
                    <p>เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถตั้งค่าอัตราการออกได้</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- History Section -->
        <div class="history-section">
            <div class="history-header">
                <h2>ประวัติการสุ่ม</h2>
                <button class="toggle-btn" id="toggle-history">แสดง</button>
            </div>
            
            <div class="history-content" id="history-content">
                <?php if (!$logged_in): ?>
                    <p style="text-align: center; padding: 20px 0;">กรุณาเข้าสู่ระบบเพื่อดูประวัติการสุ่ม</p>
                <?php elseif (empty($history)): ?>
                    <div class="empty-history">
                        <p>ยังไม่มีประวัติการสุ่ม</p>
                    </div>
                <?php else: ?>
                    <table class="history-table">
                        <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>เกม</th>
                                    <th>Account ID</th>
                                    <th>Username</th>
                                    <th>ราคา</th>
                                </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $item): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['purchase_date'])); ?></td>
                                <td><?php echo htmlspecialchars(str_replace('#', '', $item['game_id'])); ?></td>
                                <td><?php echo htmlspecialchars($item['account_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['username'] ?? 'N/A'); ?></td>
                                <td><?php echo number_format($item['price'], 2); ?> Point</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Game Selection
            const gameButtons = document.querySelectorAll('.game-btn');
            const selectedGameInput = document.getElementById('selected-game');
            const gamePriceSpan = document.getElementById('game-price');
            const randomizeBtn = document.querySelector('.randomize-btn');
            
            // Game Prices
            const gamePrices = <?php echo json_encode($game_prices); ?>;
            const userPoint = <?php echo $user_point; ?>;
            
            // Update selected game and price
            gameButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    gameButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Update hidden input
                    const game = this.getAttribute('data-game');
                    selectedGameInput.value = game;
                    
                    // Update price display
                    gamePriceSpan.textContent = gamePrices[game].toLocaleString();
                    
                    // Update button disabled state based on user's points
                    if (userPoint < gamePrices[game]) {
                        randomizeBtn.disabled = true;
                    } else {
                        randomizeBtn.disabled = false;
                    }
                });
            });
            
            // Toggle settings
            const toggleSettingsBtn = document.getElementById('toggle-settings');
            const settingsContent = document.getElementById('settings-content');
            
            toggleSettingsBtn.addEventListener('click', function() {
                settingsContent.classList.toggle('active');
                this.textContent = settingsContent.classList.contains('active') ? 'ซ่อน' : 'แสดง';
            });
            
            // Toggle history
            const toggleHistoryBtn = document.getElementById('toggle-history');
            const historyContent = document.getElementById('history-content');
            
            toggleHistoryBtn.addEventListener('click', function() {
                historyContent.classList.toggle('active');
                this.textContent = historyContent.classList.contains('active') ? 'ซ่อน' : 'แสดง';
            });
            
            // Rate sliders
            const rateSliders = {
                common: document.getElementById('common-rate'),
                uncommon: document.getElementById('uncommon-rate'),
                rare: document.getElementById('rare-rate'),
                epic: document.getElementById('epic-rate'),
                legendary: document.getElementById('legendary-rate')
            };
            
            const rateValues = {
                common: document.getElementById('common-rate-value'),
                uncommon: document.getElementById('uncommon-rate-value'),
                rare: document.getElementById('rare-rate-value'),
                epic: document.getElementById('epic-rate-value'),
                legendary: document.getElementById('legendary-rate-value')
            };
            
            const totalRateValue = document.getElementById('total-rate-value');
            const rateWarning = document.getElementById('rate-warning');
            
            // Update rate value display
            function updateRateValue(rarity) {
                rateValues[rarity].textContent = rateSliders[rarity].value + '%';
                updateTotalRate();
            }
            
            // Update total rate
            function updateTotalRate() {
                const total = Object.keys(rateSliders).reduce((sum, rarity) => {
                    return sum + parseInt(rateSliders[rarity].value);
                }, 0);
                
                totalRateValue.textContent = total;
                
                if (Math.abs(total - 100) > 0.5) {
                    rateWarning.style.display = 'block';
                } else {
                    rateWarning.style.display = 'none';
                }
            }
            
            // Make updateRateValue available globally
            window.updateRateValue = updateRateValue;
            
            // Reset rates button
            const resetRatesBtn = document.getElementById('reset-rates-btn');
            
            resetRatesBtn.addEventListener('click', function() {
                const defaultRates = {
                    common: 60,
                    uncommon: 25,
                    rare: 10,
                    epic: 4,
                    legendary: 1
                };
                
                Object.keys(defaultRates).forEach(rarity => {
                    rateSliders[rarity].value = defaultRates[rarity];
                    rateValues[rarity].textContent = defaultRates[rarity] + '%';
                });
                
                updateTotalRate();
            });
            
            // Confetti effect
            function createConfetti(count) {
                const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ff9d00', '#bb00ff'];
                
                for (let i = 0; i < count; i++) {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * window.innerWidth + 'px';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.width = (5 + Math.random() * 10) + 'px';
                    confetti.style.height = (5 + Math.random() * 10) + 'px';
                    confetti.style.animationDuration = (3 + Math.random() * 4) + 's';
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => {
                        confetti.remove();
                    }, 5000);
                }
            }
            
            // Make createConfetti available globally
            window.createConfetti = createConfetti;
        });
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>