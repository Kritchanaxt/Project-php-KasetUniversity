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
$user_points = $_SESSION['points'] ?? 0; // Get points from session, default to 0 if not set

if (DEBUG) {
    error_log("User ID: " . $user_id);
    error_log("User Points: " . $user_points);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['action'])) {
        echo json_encode(["success" => false, "message" => "Invalid request"]);
        exit;
    }

    $action = $data['action'];

    if ($action === 'add_to_cart') {
        if (!isset($data['account_id'])) {
            echo json_encode(["success" => false, "message" => "Missing account_id"]);
            exit;
        }

        $account_id = $data['account_id'];

        // Check if the game ID is available
        $stmt = $conn->prepare("SELECT status, price, game_id, username, details FROM Accounts WHERE account_id = ?");
        $stmt->bind_param("s", $account_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result || $result['status'] !== 'available') {
            echo json_encode(["success" => false, "message" => "Game ID is no longer available"]);
            exit;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$account_id])) {
            echo json_encode(["success" => false, "message" => "Item already in cart"]);
            exit;
        }

        $_SESSION['cart'][$account_id] = [
            'account_id' => $account_id,
            'game_id' => $result['game_id'],
            'username' => $result['username'],
            'details' => $result['details'],
            'price' => $result['price'],
            'added_at' => time()
        ];

        echo json_encode([
            "success" => true,
            "message" => "Added to cart successfully",
            "cart_count" => count($_SESSION['cart']),
            "cart_item" => $_SESSION['cart'][$account_id]
        ]);

    } elseif ($action === 'remove_from_cart') {
        if (!isset($data['account_id'])) {
            echo json_encode(["success" => false, "message" => "Missing account_id"]);
            exit;
        }

        $account_id = $data['account_id'];

        if (!isset($_SESSION['cart']) || !isset($_SESSION['cart'][$account_id])) {
            echo json_encode(["success" => false, "message" => "Item not found in cart"]);
            exit;
        }

        unset($_SESSION['cart'][$account_id]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array

        echo json_encode([
            "success" => true,
            "message" => "Removed from cart successfully",
            "cart_count" => count($_SESSION['cart'])
        ]);

    } elseif ($action === 'purchase_cart') {
        if (empty($_SESSION['cart'])) {
            echo json_encode(["success" => false, "message" => "Cart is empty"]);
            exit;
        }

        $totalPrice = 0;
        foreach ($_SESSION['cart'] as $item) {
            $totalPrice += $item['price'];
        }

        if ($user_points < $totalPrice) {
            echo json_encode([
                "success" => false,
                "message" => "Insufficient points. Need " . $totalPrice . " but have " . $user_points
            ]);
            exit;
        }

        $conn->begin_transaction();

        try {
            foreach ($_SESSION['cart'] as $item) {
                $account_id = $item['account_id'];

                // Update game ID status to 'sold'
                $updateStmt = $conn->prepare("UPDATE Accounts SET status = 'sold' WHERE account_id = ?");
                $updateStmt->bind_param("s", $account_id);
                $updateStmt->execute();

                // Record purchase history
                $historyStmt = $conn->prepare("INSERT INTO Purchase_History (user_id, account_id, price, purchase_date) VALUES (?, ?, ?, NOW())");
                $historyStmt->bind_param("ssd", $user_id, $account_id, $item['price']);
                $historyStmt->execute();
            }

            // Deduct total points
            $newPoints = $user_points - $totalPrice;
            $pointsStmt = $conn->prepare("UPDATE Users SET point = ? WHERE user_id = ?");
            $pointsStmt->bind_param("ds", $newPoints, $user_id);
            $pointsStmt->execute();

            // Update session points
            $_SESSION['points'] = $newPoints;

            // Clear cart
            $_SESSION['cart'] = [];

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

    } elseif ($action === 'buy_now') {
        if (!isset($data['account_id'])) {
            echo json_encode(["success" => false, "message" => "Missing account_id"]);
            exit;
        }

        $account_id = $data['account_id'];

        // Check if the game ID is available
        $stmt = $conn->prepare("SELECT status, price FROM Accounts WHERE account_id = ?");
        $stmt->bind_param("s", $account_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result || $result['status'] !== 'available') {
            echo json_encode(["success" => false, "message" => "Game ID is no longer available"]);
            exit;
        }

        $itemPrice = $result['price'];

        if ($user_points < $itemPrice) {
            echo json_encode([
                "success" => false,
                "message" => "Insufficient points. Need " . $itemPrice . " but have " . $user_points
            ]);
            exit;
        }

        $conn->begin_transaction();

        try {
            // Update game ID status to 'sold'
            $updateStmt = $conn->prepare("UPDATE Accounts SET status = 'sold' WHERE account_id = ?");
            $updateStmt->bind_param("s", $account_id);
            $updateStmt->execute();

            // Deduct points
            $newPoints = $user_points - $itemPrice;
            $pointsStmt = $conn->prepare("UPDATE Users SET point = ? WHERE user_id = ?");
            $pointsStmt->bind_param("ds", $newPoints, $user_id);
            $pointsStmt->execute();

            // Record purchase history
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
    if (DEBUG) error_log("Error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}

$conn->close();
?>