<?php
require 'config.php';

// The Checkout Function
function processSale($conn, $product_name, $qty_to_sell, $selling_price) {
    $qty_needed = $qty_to_sell;
    $total_order_revenue = 0;

    echo "<h3>Processing Sale: $qty_to_sell x $product_name at ₱$selling_price</h3>";

    // 1. Get batches with available stock, ordered by oldest first (FIFO)
    $sql = "SELECT batch_id, qty_remaining, total_batch_cost, total_recovered 
            FROM inventory_batches 
            WHERE product_name = ? AND qty_remaining > 0 
            ORDER BY created_at ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $product_name);
    $stmt->execute();
    $result = $stmt->get_result();

    // 2. Loop through the oldest batches until the order is fulfilled
    while ($batch = $result->fetch_assoc()) {
        if ($qty_needed <= 0) break; // Order is fully processed

        $batch_id = $batch['batch_id'];
        $qty_in_batch = $batch['qty_remaining'];

        // Determine how much to take from THIS specific batch
        if ($qty_in_batch >= $qty_needed) {
            $qty_deducted = $qty_needed;
            $qty_needed = 0; // Order complete
        } else {
            $qty_deducted = $qty_in_batch; // Take whatever is left
            $qty_needed -= $qty_in_batch;  // Still need more from the next batch
        }

        // Calculate the money made from this specific deduction
        $revenue_from_this_batch = $qty_deducted * $selling_price;
        $total_order_revenue += $revenue_from_this_batch;

        // 3. Update the Batch (Reduce Stock & Add to Capital Recovery)
        $update_sql = "UPDATE inventory_batches 
                       SET qty_remaining = qty_remaining - ?, 
                           total_recovered = total_recovered + ? 
                       WHERE batch_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("idi", $qty_deducted, $revenue_from_this_batch, $batch_id);
        $update_stmt->execute();

        // 4. Log the Sale for auditing
        $insert_sql = "INSERT INTO sales (batch_id, qty_sold, selling_price, total_sale_amount) 
                       VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iidd", $batch_id, $qty_deducted, $selling_price, $revenue_from_this_batch);
        $insert_stmt->execute();

        echo "<p>✔️ Took $qty_deducted items from Batch #$batch_id. Allocated ₱$revenue_from_this_batch to recovery.</p>";
    }

    if ($qty_needed > 0) {
        echo "<p style='color:red;'>Warning: Not enough stock! Missing $qty_needed items.</p>";
    } else {
        echo "<p style='color:green;'>Sale Complete! Total Revenue: ₱$total_order_revenue</p>";
    }
}

// ==========================================
// TEST THE LOGIC HERE
// ==========================================
// Let's pretend a customer is buying 15 bags of Coffee Beans at ₱250 each
processSale($conn, "Coffee Beans", 15, 250.00);

?>