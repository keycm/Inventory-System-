<?php
require 'config.php';

echo "<h2>Investment & Capital Recovery Status</h2>";

$sql = "SELECT 
            batch_id, 
            product_name, 
            total_batch_cost, 
            total_recovered,
            (total_batch_cost - total_recovered) AS remaining_debt
        FROM inventory_batches";

$result = $conn->query($sql);

echo "<table border='1' cellpadding='10'>";
echo "<tr>
        <th>Batch ID</th>
        <th>Product</th>
        <th>Investment (Cost)</th>
        <th>Money Recovered (Sales)</th>
        <th>Remaining Debt</th>
        <th>Status</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    $cost = $row['total_batch_cost'];
    $recovered = $row['total_recovered'];
    $debt = $row['remaining_debt'];
    
    // Logic to determine if we are in profit
    if ($recovered >= $cost) {
        $status = "<strong style='color:green;'>PROFIT MODE</strong>";
        $debt = 0; // Prevent showing negative debt when in profit
    } else {
        $status = "<strong style='color:orange;'>RECOVERING CAPITAL</strong>";
    }

    echo "<tr>
            <td>{$row['batch_id']}</td>
            <td>{$row['product_name']}</td>
            <td>₱" . number_format($cost, 2) . "</td>
            <td>₱" . number_format($recovered, 2) . "</td>
            <td>₱" . number_format($debt, 2) . "</td>
            <td>$status</td>
          </tr>";
}
echo "</table>";
?>