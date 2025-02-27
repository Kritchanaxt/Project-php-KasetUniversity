<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection file
require_once 'db_connect.php';

// Set header to JSON
header('Content-Type: application/json');

// Response array
$response = array('success' => false, 'cart_items' => array());

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Get cart items for the user
    $stmt = $conn->prepare("
        SELECT c.cart_id, c.user_id, c.account_id, c.date_added, 
               a.username as account_username, a.price, a.status, a.game_id, a.details
        FROM cart c
        JOIN accounts a ON c.account_id = a.account_id
        WHERE c.user_id = ? AND a.status = 'available'
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response['cart_items'][] = array(
                'cart_id' => $row['cart_id'],
                'account_id' => $row['account_id'],
                'username' => $row['account_username'],
                'price' => $row['price'],
                'game_id' => $row['game_id'],
                'details' => $row['details'],
                'date_added' => $row['date_added']
            );
        }
        $response['success'] = true;
    } else {
        $response['success'] = true;
        $response['message'] = 'Cart is empty';
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} finally {
    $conn->close();
}

// Return JSON response
echo json_encode($response);
?>