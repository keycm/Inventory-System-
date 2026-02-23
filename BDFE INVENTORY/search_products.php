<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, sku, price, (SELECT SUM(remaining_quantity) FROM inventory_batches WHERE product_id = products.id) as stock FROM products WHERE name LIKE ? OR sku LIKE ? LIMIT 10");
$searchTerm = "%$query%";
$stmt->execute([$searchTerm, $searchTerm]);
$results = $stmt->fetchAll();

echo json_encode($results);
?>