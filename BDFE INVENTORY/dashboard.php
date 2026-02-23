<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Get Total Sales Today
$stmt = $pdo->prepare("SELECT SUM(total_amount) as total_sales FROM sales WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$salesToday = $stmt->fetchColumn() ?: 0;

// Get Total Products in Stock (sum of all batches)
$stmt = $pdo->query("SELECT SUM(remaining_quantity) as total_stock FROM inventory_batches");
$totalStock = $stmt->fetchColumn() ?: 0;

// Get Low Stock Alerts (products with total stock < 10)
$stmt = $pdo->query("
    SELECT p.name, SUM(ib.remaining_quantity) as stock
    FROM products p
    LEFT JOIN inventory_batches ib ON p.id = ib.product_id
    GROUP BY p.id
    HAVING stock < 10 OR stock IS NULL
    LIMIT 5
");
$lowStockItems = $stmt->fetchAll();

// Get Recent Sales
$stmt = $pdo->query("
    SELECT s.id, u.username, s.total_amount, s.created_at
    FROM sales s
    JOIN users u ON s.user_id = u.id
    ORDER BY s.created_at DESC
    LIMIT 5
");
$recentSales = $stmt->fetchAll();
?>

<div class="card-container">
    <div class="card">
        <h3>Today's Sales</h3>
        <p><?php echo formatCurrency($salesToday); ?></p>
    </div>
    <div class="card">
        <h3>Total Items in Stock</h3>
        <p><?php echo number_format($totalStock); ?></p>
    </div>
    <div class="card">
        <h3>Low Stock Items</h3>
        <p><?php echo count($lowStockItems); ?></p>
    </div>
</div>

<div class="table-container">
    <h3>Recent Sales</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cashier</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentSales as $sale): ?>
            <tr>
                <td>#<?php echo $sale['id']; ?></td>
                <td><?php echo htmlspecialchars($sale['username']); ?></td>
                <td><?php echo formatCurrency($sale['total_amount']); ?></td>
                <td><?php echo date('H:i', strtotime($sale['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($lowStockItems)): ?>
<div class="table-container">
    <h3>Low Stock Alerts</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Current Stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lowStockItems as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td style="color: red; font-weight: bold;"><?php echo (int)$item['stock']; ?></td>
                <td><a href="add_stock.php?product=<?php echo urlencode($item['name']); ?>" class="btn btn-primary">Restock</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>