<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection file
require_once 'db_connect.php';

// Set header to JSON
header('Content-Type: application/json');

// Get JSON input
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

// Response array
$response = array('success' => false, 'message' => '');

// Check if action is set
if (!isset($data['action'])) {
    $response['message'] = 'Action not specified';
    echo json_encode($response);
    exit;
}

// Different actions: add_to_cart, remove_from_cart, buy_now, purchase_cart
switch ($data['action']) {
    case 'add_to_cart':
        handleAddToCart($conn, $data, $response);
        break;
        
    case 'remove_from_cart':
        handleRemoveFromCart($conn, $data, $response);
        break;
        
    case 'buy_now':
        handleBuyNow($conn, $data, $response);
        break;
        
    case 'purchase_cart':
        handlePurchaseCart($conn, $data, $response);
        break;
        
    default:
        $response['message'] = 'Invalid action';
        break;
}

// Return JSON response
echo json_encode($response);
$conn->close();

// Function to handle adding item to cart
function handleAddToCart($conn, $data, &$response) {
    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
        $response['message'] = 'User not logged in';
        return;
    }
    
    // Validate required params
    if (!isset($data['account_id'])) {
        $response['message'] = 'Missing account ID';
        return;
    }
    
    $userId = $_SESSION['user_id'];
    $accountId = filter_var($data['account_id'], FILTER_VALIDATE_INT);
    
    if ($accountId === false) {
        $response['message'] = 'Invalid account ID';
        return;
    }
    
    try {
        // Check if account exists and is available
        $checkAccountStmt = $conn->prepare("SELECT account_id, status FROM accounts WHERE account_id = ?");
        $checkAccountStmt->bind_param("i", $accountId);
        $checkAccountStmt->execute();
        $accountResult = $checkAccountStmt->get_result();
        
        if ($accountResult->num_rows === 0) {
            $response['message'] = 'Account not found';
            return;
        }
        
        $account = $accountResult->fetch_assoc();
        if ($account['status'] !== 'available') {
            $response['message'] = 'Account is not available for purchase';
            return;
        }
        
        // Check if account is already in cart
        $checkCartStmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND account_id = ?");
        $checkCartStmt->bind_param("ii", $userId, $accountId);
        $checkCartStmt->execute();
        $cartResult = $checkCartStmt->get_result();
        
        if ($cartResult->num_rows > 0) {
            $response['message'] = 'Account already in cart';
            $response['success'] = true;
            
            // Get cart count
            $cartCountStmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
            $cartCountStmt->bind_param("i", $userId);
            $cartCountStmt->execute();
            $countResult = $cartCountStmt->get_result();
            $countRow = $countResult->fetch_assoc();
            $response['cart_count'] = $countRow['count'];
            
            return;
        }
        
        // Add to cart
        $addCartStmt = $conn->prepare("INSERT INTO cart (user_id, account_id, date_added) VALUES (?, ?, NOW())");
        $addCartStmt->bind_param("ii", $userId, $accountId);
        
        if ($addCartStmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Added to cart successfully';
            
            // Get cart count
            $cartCountStmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
            $cartCountStmt->bind_param("i", $userId);
            $cartCountStmt->execute();
            $countResult = $cartCountStmt->get_result();
            $countRow = $countResult->fetch_assoc();
            $response['cart_count'] = $countRow['count'];
        } else {
            $response['message'] = 'Failed to add to cart';
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

// Function to handle removing item from cart
function handleRemoveFromCart($conn, $data, &$response) {
    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
        $response['message'] = 'User not logged in';
        return;
    }
    
    // Validate required params
    if (!isset($data['account_id'])) {
        $response['message'] = 'Missing account ID';
        return;
    }
    
    $userId = $_SESSION['user_id'];
    $accountId = filter_var($data['account_id'], FILTER_VALIDATE_INT);
    
    if ($accountId === false) {
        $response['message'] = 'Invalid account ID';
        return;
    }
    
    try {
        // Remove from cart
        $removeStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND account_id = ?");
        $removeStmt->bind_param("ii", $userId, $accountId);
        
        if ($removeStmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Removed from cart successfully';
            
            // Get cart count
            $cartCountStmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
            $cartCountStmt->bind_param("i", $userId);
            $cartCountStmt->execute();
            $countResult = $cartCountStmt->get_result();
            $countRow = $countResult->fetch_assoc();
            $response['cart_count'] = $countRow['count'];
        } else {
            $response['message'] = 'Failed to remove from cart';
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

// Function to handle direct purchase (buy now)
function handleBuyNow($conn, $data, &$response) {
    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
        $response['message'] = 'User not logged in';
        return;
    }
    
    // Validate required params
    if (!isset($data['account_id']) || !isset($data['price'])) {
        $response['message'] = 'Missing required parameters';
        return;
    }
    
    $userId = $_SESSION['user_id'];
    $accountId = filter_var($data['account_id'], FILTER_VALIDATE_INT);
    $price = filter_var($data['price'], FILTER_VALIDATE_FLOAT);
    
    if ($accountId === false || $price === false) {
        $response['message'] = 'Invalid parameters';
        return;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Check if account exists and is available
        $checkAccountStmt = $conn->prepare("SELECT account_id, status, price, game_id FROM accounts WHERE account_id = ?");
        $checkAccountStmt->bind_param("i", $accountId);
        $checkAccountStmt->execute();
        $accountResult = $checkAccountStmt->get_result();
        
        if ($accountResult->num_rows === 0) {
            $response['message'] = 'Account not found';
            $conn->rollback();
            return;
        }
        
        $account = $accountResult->fetch_assoc();
        if ($account['status'] !== 'available') {
            $response['message'] = 'Account is not available for purchase';
            $conn->rollback();
            return;
        }
        
        // Verify price
        if ($account['price'] != $price) {
            $response['message'] = 'Price mismatch';
            $conn->rollback();
            return;
        }
        
        // Check if user has enough points
        $checkUserStmt = $conn->prepare("SELECT point FROM users WHERE user_id = ?");
        $checkUserStmt->bind_param("i", $userId);
        $checkUserStmt->execute();
        $userResult = $checkUserStmt->get_result();
        
        if ($userResult->num_rows === 0) {
            $response['message'] = 'User not found';
            $conn->rollback();
            return;
        }
        
        $user = $userResult->fetch_assoc();
        if ($user['point'] < $price) {
            $response['message'] = 'Insufficient points';
            $conn->rollback();
            return;
        }
        
        // Update user points
        $newPoints = $user['point'] - $price;
        $updateUserStmt = $conn->prepare("UPDATE users SET point = ? WHERE user_id = ?");
        $updateUserStmt->bind_param("di", $newPoints, $userId);
        
        if (!$updateUserStmt->execute()) {
            $response['message'] = 'Failed to update user points';
            $conn->rollback();
            return;
        }
        
        // Update account status
        $updateAccountStmt = $conn->prepare("UPDATE accounts SET status = 'sold', buyer_id = ? WHERE account_id = ?");
        $updateAccountStmt->bind_param("ii", $userId, $accountId);
        
        if (!$updateAccountStmt->execute()) {
            $response['message'] = 'Failed to update account status';
            $conn->rollback();
            return;
        }
        
        // Record purchase in purchase history
        $insertHistoryStmt = $conn->prepare("
            INSERT INTO purchase_history (user_id, account_id, game_id, price, purchase_date)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $gameId = $account['game_id'];
        $insertHistoryStmt->bind_param("iisd", $userId, $accountId, $gameId, $price);
        
        if (!$insertHistoryStmt->execute()) {
            $response['message'] = 'Failed to record purchase history';
            $conn->rollback();
            return;
        }
        
        // Commit transaction
        $conn->commit();
        
        // Update session
        $_SESSION['user_point'] = $newPoints;
        
        $response['success'] = true;
        $response['message'] = 'Purchase successful';
        $response['remaining_points'] = $newPoints;
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

// Function to handle purchasing all items in cart
function handlePurchaseCart($conn, $data, &$response) {
    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
        $response['message'] = 'User not logged in';
        return;
    }
    
    // Validate required params
    if (!isset($data['total_price'])) {
        $response['message'] = 'Missing total price';
        return;
    }
    
    $userId = $_SESSION['user_id'];
    $totalPrice = filter_var($data['total_price'], FILTER_VALIDATE_FLOAT);
    
    if ($totalPrice === false) {
        $response['message'] = 'Invalid total price';
        return;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get cart items
        $cartStmt = $conn->prepare("
            SELECT c.cart_id, c.account_id, a.price, a.game_id, a.status
            FROM cart c
            JOIN accounts a ON c.account_id = a.account_id
            WHERE c.user_id = ?
        ");
        $cartStmt->bind_param("i", $userId);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();
        
        if ($cartResult->num_rows === 0) {
            $response['message'] = 'Cart is empty';
            $conn->rollback();
            return;
        }
        
        // Calculate total price from database items and verify with client total
        $cartItems = array();
        $dbTotalPrice = 0;
        
        while ($item = $cartResult->fetch_assoc()) {
            if ($item['status'] !== 'available') {
                $response['message'] = 'One or more items are no longer available';
                $conn->rollback();
                return;
            }
            
            $cartItems[] = $item;
            $dbTotalPrice += $item['price'];
        }
        
        // Verify total price (with small tolerance for floating point)
        if (abs($dbTotalPrice - $totalPrice) > 0.01) {
            $response['message'] = 'Price mismatch';
            $conn->rollback();
            return;
        }
        
        // Check if user has enough points
        $checkUserStmt = $conn->prepare("SELECT point FROM users WHERE user_id = ?");
        $checkUserStmt->bind_param("i", $userId);
        $checkUserStmt->execute();
        $userResult = $checkUserStmt->get_result();
        
        if ($userResult->num_rows === 0) {
            $response['message'] = 'User not found';
            $conn->rollback();
            return;
        }
        
        $user = $userResult->fetch_assoc();
        if ($user['point'] < $dbTotalPrice) {
            $response['message'] = 'Insufficient points';
            $conn->rollback();
            return;
        }
        
        // Update user points
        $newPoints = $user['point'] - $dbTotalPrice;
        $updateUserStmt = $conn->prepare("UPDATE users SET point = ? WHERE user_id = ?");
        $updateUserStmt->bind_param("di", $newPoints, $userId);
        
        if (!$updateUserStmt->execute()) {
            $response['message'] = 'Failed to update user points';
            $conn->rollback();
            return;
        }
        
        // Process each cart item
        foreach ($cartItems as $item) {
            // Update account status
            $updateAccountStmt = $conn->prepare("UPDATE accounts SET status = 'sold', buyer_id = ? WHERE account_id = ?");
            $updateAccountStmt->bind_param("ii", $userId, $item['account_id']);
            
            if (!$updateAccountStmt->execute()) {
                $response['message'] = 'Failed to update account status';
                $conn->rollback();
                return;
            }
            
            // Record purchase in purchase history
            $insertHistoryStmt = $conn->prepare("
                INSERT INTO purchase_history (user_id, account_id, game_id, price, purchase_date)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $insertHistoryStmt->bind_param("iisd", $userId, $item['account_id'], $item['game_id'], $item['price']);
            
            if (!$insertHistoryStmt->execute()) {
                $response['message'] = 'Failed to record purchase history';
                $conn->rollback();
                return;
            }
        }
        
        // Clear user's cart
        $clearCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearCartStmt->bind_param("i", $userId);
        
        if (!$clearCartStmt->execute()) {
            $response['message'] = 'Failed to clear cart';
            $conn->rollback();
            return;
        }
        
        // Commit transaction
        $conn->commit();
        
        // Update session
        $_SESSION['user_point'] = $newPoints;
        
        $response['success'] = true;
        $response['message'] = 'Cart purchase successful';
        $response['remaining_points'] = $newPoints;
        $response['redirect_to_history'] = true;
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}
?>