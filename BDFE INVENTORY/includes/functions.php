<?php
// includes/functions.php

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    // Removed htmlspecialchars to store raw data. Output should be sanitized.
    return $data;
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

/**
 * Process a sale transaction using FIFO logic.
 *
 * @param PDO $pdo Database connection
 * @param int $userId ID of the user processing the sale
 * @param array $items Array of items: [['product_id' => int, 'quantity' => int]]
 * @return array Result ['success' => bool, 'message' => string, 'sale_id' => int|null]
 */
function processSale($pdo, $userId, $items) {
    try {
        $pdo->beginTransaction();

        $totalSaleAmount = 0;

        // 1. Create Sale Record (initially with 0 total, update later)
        $stmt = $pdo->prepare("INSERT INTO sales (user_id, total_amount) VALUES (?, 0)");
        $stmt->execute([$userId]);
        $saleId = $pdo->lastInsertId();

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $qtyNeeded = $item['quantity'];

            // Get product details (price)
            $stmt = $pdo->prepare("SELECT price, name FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Product ID $productId not found.");
            }

            $sellingPrice = $product['price'];
            $productName = $product['name'];
            $itemSubtotal = $sellingPrice * $qtyNeeded;
            $totalSaleAmount += $itemSubtotal;

            // Create Sale Item Record
            $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$saleId, $productId, $qtyNeeded, $sellingPrice, $itemSubtotal]);
            $saleItemId = $pdo->lastInsertId();

            // 2. FIFO Logic: Get batches with stock
            // Use FOR UPDATE to lock rows and prevent race conditions
            $stmt = $pdo->prepare("SELECT id, remaining_quantity, cost_price FROM inventory_batches WHERE product_id = ? AND remaining_quantity > 0 ORDER BY received_at ASC FOR UPDATE");
            $stmt->execute([$productId]);
            $batches = $stmt->fetchAll();

            $qtyToAllocate = $qtyNeeded;

            foreach ($batches as $batch) {
                if ($qtyToAllocate <= 0) break;

                $batchId = $batch['id'];
                $qtyInBatch = $batch['remaining_quantity'];

                $qtyTakenFromBatch = min($qtyInBatch, $qtyToAllocate);

                // Revenue attribution for this specific batch allocation
                // This logic mirrors the original code's "total_recovered" concept
                // We attribute a portion of the revenue to this batch
                $revenueFromBatch = $qtyTakenFromBatch * $sellingPrice;

                // Update Batch
                $updateStmt = $pdo->prepare("UPDATE inventory_batches SET remaining_quantity = remaining_quantity - ?, total_recovered = total_recovered + ? WHERE id = ?");
                $updateStmt->execute([$qtyTakenFromBatch, $revenueFromBatch, $batchId]);

                // Record Allocation
                $allocStmt = $pdo->prepare("INSERT INTO batch_allocations (sale_item_id, batch_id, quantity) VALUES (?, ?, ?)");
                $allocStmt->execute([$saleItemId, $batchId, $qtyTakenFromBatch]);

                $qtyToAllocate -= $qtyTakenFromBatch;
            }

            if ($qtyToAllocate > 0) {
                // Not enough stock
                throw new Exception("Insufficient stock for product: $productName. Missing $qtyToAllocate.");
            }
        }

        // Update Sale Total
        $stmt = $pdo->prepare("UPDATE sales SET total_amount = ? WHERE id = ?");
        $stmt->execute([$totalSaleAmount, $saleId]);

        $pdo->commit();
        return ['success' => true, 'message' => 'Sale processed successfully.', 'sale_id' => $saleId];

    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage(), 'sale_id' => null];
    }
}
?>