<?php
require_once '../config.php';

// Set header to return JSON response
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$productId = filter_var($input['product_id'], FILTER_VALIDATE_INT);
$quantity = filter_var($input['quantity'], FILTER_VALIDATE_INT);

// Validate product_id and quantity
if (!$productId || $quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    // Check if product exists and has enough stock
    $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // If quantity is 0, remove from cart
    if ($quantity === 0) {
        unset($_SESSION['cart'][$productId]);
        echo json_encode([
            'success' => true, 
            'message' => 'Product removed from cart',
            'cartCount' => array_sum($_SESSION['cart'])
        ]);
        exit;
    }

    // Check if new quantity exceeds stock
    $currentQuantity = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0;
    $newQuantity = $quantity;

    if ($newQuantity > $product['stock_quantity']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Not enough stock available'
        ]);
        exit;
    }

    // Update cart
    $_SESSION['cart'][$productId] = $newQuantity;

    // Return success response with updated cart count
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully',
        'cartCount' => array_sum($_SESSION['cart'])
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} 