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

// Validate input data
if (!isset($data['points']) || !isset($data['user_id'])) {
    $response['message'] = 'Missing required parameters';
    echo json_encode($response);
    exit;
}

// Sanitize inputs
$points = filter_var($data['points'], FILTER_VALIDATE_FLOAT);
$userId = filter_var($data['user_id'], FILTER_VALIDATE_INT);

if ($points === false || $userId === false) {
    $response['message'] = 'Invalid data format';
    echo json_encode($response);
    exit;
}

try {
    // Check if user exists in database
    $checkUserStmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $checkUserStmt->bind_param("i", $userId);
    $checkUserStmt->execute();
    $checkUserResult = $checkUserStmt->get_result();
    
    if ($checkUserResult->num_rows === 0) {
        $response['message'] = 'User not found';
        echo json_encode($response);
        exit;
    }
    
    // Update points in database
    $updateStmt = $conn->prepare("UPDATE users SET point = ? WHERE user_id = ?");
    $updateStmt->bind_param("di", $points, $userId);
    
    if ($updateStmt->execute()) {
        // Update session data
        $_SESSION['user_point'] = $points;
        
        // Set response
        $response['success'] = true;
        $response['message'] = 'Points synchronized successfully';
    } else {
        $response['message'] = 'Failed to update points in database';
    }
    
    $updateStmt->close();
    
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} finally {
    $conn->close();
}

// Return JSON response
echo json_encode($response);
?>