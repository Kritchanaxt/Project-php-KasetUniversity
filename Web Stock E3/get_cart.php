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

try {
    $cart = $_SESSION['cart'] ?? [];
    echo json_encode([
        "success" => true,
        "cart" => array_values($cart) // Reindex for consistency
    ]);
} catch (Exception $e) {
    if (DEBUG) error_log("Error fetching cart: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Error fetching cart: " . $e->getMessage()
    ]);
}

$conn->close();
?>