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

// Graph Data: Hourly sales for the selected date
$sql = "SELECT HOUR(created_at) as sale_hour, SUM(total_amount) as total
        FROM sales
        WHERE DATE(created_at) = ?
        GROUP BY HOUR(created_at)
        ORDER BY sale_hour ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$dateFilter]);
$hourlyData = $stmt->fetchAll();

$hours = [];
$hourlyTotals = [];
// Initialize all hours to 0
for ($i = 0; $i < 24; $i++) {
    $hours[] = sprintf("%02d:00", $i);
    $hourlyTotals[] = 0;
}

foreach ($hourlyData as $data) {
    $hourlyTotals[$data['sale_hour']] = $data['total'];
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

<div class="card-container" style="margin-top: 20px;">
    <div class="card" style="width: 100%;">
        <h3>Hourly Sales Trend (<?php echo htmlspecialchars($dateFilter); ?>)</h3>
        <canvas id="hourlySalesChart"></canvas>
    </div>
</div>

<script>
    const ctx = document.getElementById('hourlySalesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($hours); ?>,
            datasets: [{
                label: 'Hourly Sales (₱)',
                data: <?php echo json_encode($hourlyTotals); ?>,
                borderColor: 'rgba(46, 204, 113, 1)',
                backgroundColor: 'rgba(46, 204, 113, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

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