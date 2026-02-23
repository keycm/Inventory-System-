<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$dateFilter = $_GET['date'] ?? date('Y-m-d');

// Fetch Sales for the selected date
$sql = "SELECT s.id, u.username, s.total_amount, s.created_at
        FROM sales s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE DATE(s.created_at) = ?
        ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$dateFilter]);
$sales = $stmt->fetchAll();

// Calculate Daily Total
$dailyTotal = 0;
foreach ($sales as $sale) {
    $dailyTotal += $sale['total_amount'];
}
?>

<div class="top-bar">
    <h2>Sales Report</h2>
    <form method="GET" action="" style="display: flex; align-items: center; gap: 10px;">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>">
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<div class="card-container">
    <div class="card">
        <h3>Total Sales for <?php echo htmlspecialchars($dateFilter); ?></h3>
        <p><?php echo formatCurrency($dailyTotal); ?></p>
    </div>
    <div class="card">
        <h3>Transactions</h3>
        <p><?php echo count($sales); ?></p>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Cashier</th>
                <th>Amount</th>
                <th>Time</th>
                <th>Items</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $sale): ?>
            <?php
                // Get items for this sale
                $itemStmt = $pdo->prepare("SELECT p.name, si.quantity, si.subtotal FROM sale_items si JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
                $itemStmt->execute([$sale['id']]);
                $items = $itemStmt->fetchAll();
                $itemDetails = [];
                foreach ($items as $item) {
                    $itemDetails[] = $item['quantity'] . 'x ' . htmlspecialchars($item['name']);
                }
            ?>
            <tr>
                <td>#<?php echo $sale['id']; ?></td>
                <td><?php echo htmlspecialchars($sale['username']); ?></td>
                <td><?php echo formatCurrency($sale['total_amount']); ?></td>
                <td><?php echo date('H:i:s', strtotime($sale['created_at'])); ?></td>
                <td><?php echo implode(', ', $itemDetails); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>