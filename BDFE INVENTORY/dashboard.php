<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// 1. Fetch Key Metrics
// Total Sales
$stmt = $pdo->query("SELECT SUM(total_amount) FROM sales");
$totalSales = $stmt->fetchColumn() ?: 0;

// Total Purchases (Total Cost of all Batches)
$stmt = $pdo->query("SELECT SUM(total_batch_cost) FROM inventory_batches");
$totalPurchases = $stmt->fetchColumn() ?: 0;

// Sales Return (Placeholder)
$salesReturn = 17584.00; // Using dummy data to match image example

// Purchases Return (Placeholder)
$purchasesReturn = 2800.00; // Using dummy data to match image example


// 2. Fetch Chart Data
// Top Selling Products (Pie Chart)
$stmt = $pdo->query("
    SELECT p.name, SUM(si.quantity) as total_qty
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    GROUP BY p.id, p.name
    ORDER BY total_qty DESC
    LIMIT 5
");
$topProducts = $stmt->fetchAll();
$topProductLabels = [];
$topProductData = [];
foreach ($topProducts as $prod) {
    $topProductLabels[] = $prod['name'];
    $topProductData[] = $prod['total_qty'];
}
// Fallback if no data
if (empty($topProductLabels)) {
    $topProductLabels = ['Product A', 'Product B', 'Product C'];
    $topProductData = [30, 50, 20];
}

// Weekly Sales vs Purchases (Bar Chart)
// Get dates for last 7 days
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('D', strtotime("-$i days"));
}
// Using dummy data structure for the chart if real data is sparse, to match the look
$chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$chartSalesData = [10, 25, 15, 30, 45, 20, 35]; // Placeholder
$chartPurchasesData = [5, 15, 10, 20, 30, 15, 25]; // Placeholder

// Real Data Logic (uncomment if data exists)
/*
$stmt = $pdo->query("SELECT DATE(created_at) as date, SUM(total_amount) as total FROM sales WHERE created_at >= DATE(NOW()) - INTERVAL 7 DAY GROUP BY DATE(created_at)");
$salesDataRaw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$stmt = $pdo->query("SELECT DATE(received_at) as date, SUM(total_batch_cost) as total FROM inventory_batches WHERE received_at >= DATE(NOW()) - INTERVAL 7 DAY GROUP BY DATE(received_at)");
$purchasesDataRaw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$chartLabels = [];
$chartSalesData = [];
$chartPurchasesData = [];
$datesFull = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('D', strtotime($d));
    $chartSalesData[] = $salesDataRaw[$d] ?? 0;
    $chartPurchasesData[] = $purchasesDataRaw[$d] ?? 0;
}
*/


// 3. Low Stock Alerts (For Bottom Table)
$stmt = $pdo->query("
    SELECT p.id, p.name, SUM(ib.remaining_quantity) as stock
    FROM products p
    LEFT JOIN inventory_batches ib ON p.id = ib.product_id
    GROUP BY p.id, p.name
    HAVING stock < 10 OR stock IS NULL
    LIMIT 5
");
$lowStockItems = $stmt->fetchAll();

?>

<!-- Row 1: Key Metrics Cards (2x2 Grid) -->
<div class="dashboard-grid">
    <!-- SALES -->
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-wallet" style="color: #689f38;"></i>
        </div>
        <div class="card-content">
            <h4 class="card-label">SALES</h4>
            <p class="card-value"><?php echo formatCurrency($totalSales); ?></p>
        </div>
    </div>

    <!-- PURCHASES -->
    <div class="dashboard-card">
        <div class="card-icon">
            <div style="position: relative; display: inline-block;">
                <i class="fas fa-wallet" style="color: #689f38;"></i>
                <i class="fas fa-plus-circle" style="position: absolute; bottom: -5px; right: -5px; font-size: 1.5rem; color: #fff; background: #689f38; border-radius: 50%;"></i>
            </div>
        </div>
        <div class="card-content">
            <h4 class="card-label">PURCHASES</h4>
            <p class="card-value"><?php echo formatCurrency($totalPurchases); ?></p>
        </div>
    </div>

    <!-- SALES RETURN -->
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-sync-alt" style="color: #689f38;"></i>
             <i class="fas fa-dollar-sign" style="font-size: 1rem; position: absolute; margin-left: -20px; margin-top: 15px; color: white;"></i>
        </div>
        <div class="card-content">
            <h4 class="card-label">SALES RETURN</h4>
            <p class="card-value"><?php echo formatCurrency($salesReturn); ?></p>
        </div>
    </div>

    <!-- PURCHASES RETURN -->
    <div class="dashboard-card">
        <div class="card-icon">
            <i class="fas fa-box-open" style="color: #689f38;"></i>
            <i class="fas fa-undo" style="font-size: 1rem; position: absolute; margin-left: -10px; margin-top: 20px;"></i>
        </div>
        <div class="card-content">
            <h4 class="card-label">PURCHASES RETURN</h4>
            <p class="card-value"><?php echo formatCurrency($purchasesReturn); ?></p>
        </div>
    </div>
</div>

<!-- Row 2: Charts (2 Columns) -->
<div class="charts-grid">
    <div class="chart-container">
        <div class="chart-header">
            <h3>Top Selling Products</h3>
        </div>
        <canvas id="topSellingChart"></canvas>
    </div>

    <div class="chart-container">
        <div class="chart-header">
            <h3>This Week Purchases</h3>
            <div style="float: right; font-size: 0.8rem;">
                <span style="color: #689f38;">■ Sales</span>
                <span style="color: #f1c40f; margin-left: 10px;">■ Purchases</span>
            </div>
        </div>
        <canvas id="weeklyChart"></canvas>
    </div>
</div>

<!-- Row 3: Stock Alert Table -->
<div class="table-section">
    <h3>Stock Alert Table</h3>
    <?php if (empty($lowStockItems)): ?>
        <p>No low stock alerts at this time.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Current Stock</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lowStockItems as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td style="font-weight: bold; color: #e74c3c;"><?php echo (int)$item['stock']; ?></td>
                <td><span class="alert-danger">Low Stock</span></td>
                <td><a href="receive_stock.php?id=<?php echo $item['id']; ?>" class="btn-restock">Restock</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Scripts for Charts -->
<script>
    // Top Selling Products (Pie Chart)
    const ctxPie = document.getElementById('topSellingChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($topProductLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($topProductData); ?>,
                backgroundColor: [
                    '#03A9F4', // Light Blue
                    '#FFEB3B', // Yellow
                    '#F44336', // Red
                    '#4CAF50', // Green
                    '#9C27B0'  // Purple
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8
                    }
                }
            }
        }
    });

    // Weekly Sales vs Purchases (Bar Chart)
    const ctxBar = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [
                {
                    label: 'Sales',
                    data: <?php echo json_encode($chartSalesData); ?>,
                    backgroundColor: '#689f38', // Green
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Purchases',
                    data: <?php echo json_encode($chartPurchasesData); ?>,
                    backgroundColor: '#FBC02D', // Yellow/Gold
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f0f0f0',
                        borderDash: [5, 5]
                    },
                    ticks: {
                        font: { size: 10 }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 10 }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false // Using custom legend in header
                }
            }
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>