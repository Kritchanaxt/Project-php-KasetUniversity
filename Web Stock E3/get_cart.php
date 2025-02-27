<?php
session_start();
header('Content-Type: application/json');

$conn = mysqli_connect('158.108.101.153', 'std6630202015', 'g3#Vjp8L', 'it_std6630202015');
mysqli_set_charset($conn, "utf8");

if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'add_to_cart') {
        $account_id = $data['account_id'];
        $game_id = $data['game_id'];

        if (!in_array($account_id, array_column($_SESSION['cart'], 'account_id'))) {
            $_SESSION['cart'][] = ['account_id' => $account_id, 'game_id' => $game_id];
        }
        echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
    } elseif ($action === 'remove_from_cart') {
        $account_id = $data['account_id'];
        $_SESSION['cart'] = array_filter($_SESSION['cart'], fn($item) => $item['account_id'] != $account_id);
        echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $cart_items = [];
    foreach ($_SESSION['cart'] as $item) {
        $cart_items[] = [
            'account_id' => $item['account_id'],
            'game_id' => $item['game_id']
        ];
    }
    echo json_encode(['success' => true, 'cart_items' => $cart_items]);
}

mysqli_close($conn);
?>