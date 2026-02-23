<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

$items = $input['items']; // Expecting array of {product_id, quantity}

$result = processSale($pdo, $_SESSION['user_id'], $items);

echo json_encode($result);
?>