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
$salesReturn = 0;

// Purchases Return (Placeholder)
$purchasesReturn = 0;


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

// Weekly Sales vs Purchases (Bar Chart)
// Get dates for last 7 days
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}

// Fetch Daily Sales
$stmt = $pdo->query("
    SELECT DATE(created_at) as date, SUM(total_amount) as total
    FROM sales
    WHERE created_at >= DATE(NOW()) - INTERVAL 7 DAY
    GROUP BY DATE(created_at)
");
$salesDataRaw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Date => Total

// Fetch Daily Purchases
$stmt = $pdo->query("
    SELECT DATE(received_at) as date, SUM(total_batch_cost) as total
    FROM inventory_batches
    WHERE received_at >= DATE(NOW()) - INTERVAL 7 DAY
    GROUP BY DATE(received_at)
");
$purchasesDataRaw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Date => Total

$chartLabels = []; // Formatted dates (Mon, Tue...)
$chartSalesData = [];
$chartPurchasesData = [];

foreach ($dates as $date) {
    $chartLabels[] = date('D', strtotime($date));
    $chartSalesData[] = $salesDataRaw[$date] ?? 0;
    $chartPurchasesData[] = $purchasesDataRaw[$date] ?? 0;
}


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

<!-- Row 1: Key Metrics Cards -->
<div class="widget-row">
    <div class="widget-card">
        <div class="widget-icon">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="widget-info">
            <h4 class="widget-title">SALES</h4>
            <p class="widget-value"><?php echo formatCurrency($totalSales); ?></p>
        </div>
    </div>

    <div class="widget-card">
        <div class="widget-icon">
            <i class="fas fa-wallet"></i>
            <i class="fas fa-plus" style="font-size: 1rem; vertical-align: top;"></i>
        </div>
        <div class="widget-info">
            <h4 class="widget-title">PURCHASES</h4>
            <p class="widget-value"><?php echo formatCurrency($totalPurchases); ?></p>
        </div>
    </div>

    <div class="widget-card">
        <div class="widget-icon">
            <i class="fas fa-sync-alt"></i>
        </div>
        <div class="widget-info">
            <h4 class="widget-title">SALES RETURN</h4>
            <p class="widget-value"><?php echo formatCurrency($salesReturn); ?></p>
        </div>
    </div>

    <div class="widget-card">
        <div class="widget-icon">
            <i class="fas fa-box-open"></i>
        </div>
        <div class="widget-info">
            <h4 class="widget-title">PURCHASES RETURN</h4>
            <p class="widget-value"><?php echo formatCurrency($purchasesReturn); ?></p>
        </div>
    </div>
</div>

<!-- Row 2: Charts -->
<div class="charts-row">
    <div class="chart-card">
        <h3>Top Selling Products</h3>
        <canvas id="topSellingChart"></canvas>
    </div>

    <div class="chart-card">
        <h3>This Week Sales vs Purchases</h3>
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
                <td><span class="alert-danger" style="font-size: 0.8rem;">Low Stock</span></td>
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
                    '#3498db', // Blue
                    '#f1c40f', // Yellow
                    '#e74c3c', // Red
                    '#2ecc71', // Green
                    '#9b59b6'  // Purple
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
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
                    borderRadius: 4
                },
                {
                    label: 'Purchases',
                    data: <?php echo json_encode($chartPurchasesData); ?>,
                    backgroundColor: '#f1c40f', // Yellow
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f0f0f0'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8
                    }
                }
            }
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>