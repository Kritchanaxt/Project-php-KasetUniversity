<?php
session_start();
include 'db_connection.php';

define('DEBUG', true);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header("Content-Type: application/json");

if (!$conn) {
    if (DEBUG) error_log("Database connection failed");
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Please login first"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? ''; // Optional: Use if username is needed
$user_points = $_SESSION['points'] ?? 0; // Get points from session

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['account_id']) || !isset($data['action'])) {
        echo json_encode(["success" => false, "message" => "Invalid request"]);
        exit;
    }

    $account_id = $data['account_id'];
    $action = $data['action']; // 'add_to_cart' or 'buy_now'

    // Check if the game ID is available
    $stmt = $conn->prepare("SELECT status, price, game_id, username, details FROM Accounts WHERE account_id = ?");
    $stmt->bind_param("s", $account_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result || $result['status'] !== 'available') {
        echo json_encode(["success" => false, "message" => "Game ID is no longer available"]);
        exit;
    }

    $itemPrice = $result['price'];

    if ($action === 'add_to_cart') {
        // Use session for cart (store more detailed item information)
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$account_id])) {
            echo json_encode(["success" => false, "message" => "Item already in cart"]);
            exit;
        }

        // Store detailed information about the item in the cart
        $_SESSION['cart'][$account_id] = [
            'account_id' => $account_id,
            'game_id' => $result['game_id'],
            'username' => $result['username'],
            'details' => $result['details'],
            'price' => $itemPrice,
            'added_at' => time()
        ];

        echo json_encode([
            "success" => true,
            "message" => "Added to cart successfully",
            "cart_count" => count($_SESSION['cart']),
            "cart_item" => $_SESSION['cart'][$account_id] // Return cart item details for frontend
        ]);

    } elseif ($action === 'buy_now') {
        // Check if user has sufficient points
        if ($user_points < $itemPrice) {
            echo json_encode([
                "success" => false,
                "message" => "Insufficient points. Need " . $itemPrice . " but have " . $user_points
            ]);
            exit;
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update game ID status to 'sold'
            $updateStmt = $conn->prepare("UPDATE Accounts SET status = 'sold' WHERE account_id = ?");
            $updateStmt->bind_param("s", $account_id);
            $updateStmt->execute();

            // Deduct points from user's balance
            $newPoints = $user_points - $itemPrice;
            $pointsStmt = $conn->prepare("UPDATE Users SET point = ? WHERE user_id = ?");
            $pointsStmt->bind_param("ds", $newPoints, $user_id);
            $pointsStmt->execute();

            // Record purchase history (optional)
            $historyStmt = $conn->prepare("INSERT INTO Purchase_History (user_id, account_id, price, purchase_date) VALUES (?, ?, ?, NOW())");
            $historyStmt->bind_param("ssd", $user_id, $account_id, $itemPrice);
            $historyStmt->execute();

            // Update session points
            $_SESSION['points'] = $newPoints;

            $conn->commit();

            echo json_encode([
                "success" => true,
                "message" => "Purchase completed successfully",
                "remaining_points" => $newPoints
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid action"]);
    }

} catch (Exception $e) {
    if (DEBUG) error_log("Purchase Error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Purchase failed: " . $e->getMessage()
    ]);
}

$conn->close();
?>