<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Fetch all batches
$stmt = $pdo->query("
    SELECT ib.id, p.name as product_name, ib.total_batch_cost, ib.total_recovered, ib.received_at
    FROM inventory_batches ib
    JOIN products p ON ib.product_id = p.id
    ORDER BY ib.received_at ASC
");
$batches = $stmt->fetchAll();

$totalCapital = 0;
$totalRecovered = 0;

foreach ($batches as $batch) {
    $totalCapital += $batch['total_batch_cost'];
    $totalRecovered += $batch['total_recovered'];
}

$outstandingCapital = max(0, $totalCapital - $totalRecovered);
$profit = max(0, $totalRecovered - $totalCapital);
$progress = ($totalCapital > 0) ? ($totalRecovered / $totalCapital) * 100 : 0;
?>

<div class="top-bar">
    <h2>Capital Recovery & Batch Tracking</h2>
</div>

<div class="card-container">
    <div class="card">
        <h3>Total Capital Invested</h3>
        <p><?php echo formatCurrency($totalCapital); ?></p>
    </div>
    <div class="card">
        <h3>Total Recovered (Sales)</h3>
        <p style="color: green;"><?php echo formatCurrency($totalRecovered); ?></p>
    </div>
    <div class="card">
        <h3>Outstanding Capital</h3>
        <p style="color: orange;"><?php echo formatCurrency($outstandingCapital); ?></p>
    </div>
    <?php if ($profit > 0): ?>
    <div class="card">
        <h3>Net Profit</h3>
        <p style="color: blue;"><?php echo formatCurrency($profit); ?></p>
    </div>
    <?php endif; ?>
</div>

<div class="card-container" style="margin-top: 20px;">
    <div class="card" style="width: 100%;">
        <h3>Overall Capital Recovery</h3>
        <div style="background-color: #eee; border-radius: 10px; height: 30px; margin-top: 10px;">
            <div style="background-color: #27ae60; height: 100%; border-radius: 10px; width: <?php echo min(100, $progress); ?>%; text-align: center; color: white; line-height: 30px; font-weight: bold;">
                <?php echo number_format($progress, 1); ?>%
            </div>
        </div>
    </div>
</div>

<div class="table-container">
    <h3>Batch Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Product</th>
                <th>Received Date</th>
                <th>Batch Cost</th>
                <th>Recovered</th>
                <th>Status</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($batches as $batch): ?>
            <?php
                $batchProgress = ($batch['total_batch_cost'] > 0) ? ($batch['total_recovered'] / $batch['total_batch_cost']) * 100 : 0;
                $isRecovered = $batch['total_recovered'] >= $batch['total_batch_cost'];
            ?>
            <tr>
                <td>#<?php echo $batch['id']; ?></td>
                <td><?php echo htmlspecialchars($batch['product_name']); ?></td>
                <td><?php echo date('M j, Y', strtotime($batch['received_at'])); ?></td>
                <td><?php echo formatCurrency($batch['total_batch_cost']); ?></td>
                <td><?php echo formatCurrency($batch['total_recovered']); ?></td>
                <td>
                    <?php if ($isRecovered): ?>
                        <span style="color: white; background-color: green; padding: 5px 10px; border-radius: 4px;">PROFIT</span>
                    <?php else: ?>
                        <span style="color: white; background-color: orange; padding: 5px 10px; border-radius: 4px;">RECOVERING</span>
                    <?php endif; ?>
                </td>
                <td style="width: 200px;">
                    <div style="background-color: #eee; border-radius: 5px; height: 20px;">
                        <div style="background-color: <?php echo $isRecovered ? '#27ae60' : '#f39c12'; ?>; height: 100%; border-radius: 5px; width: <?php echo min(100, $batchProgress); ?>%;"></div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>