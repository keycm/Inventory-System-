<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Fetch products with their total stock
$sql = "SELECT p.id, p.name, p.sku, p.price, SUM(ib.remaining_quantity) as total_stock
        FROM products p
        LEFT JOIN inventory_batches ib ON p.id = ib.product_id
        GROUP BY p.id, p.name, p.sku, p.price
        ORDER BY p.name ASC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();
?>

<div class="top-bar">
    <h2>Inventory Management</h2>
    <div>
        <a href="add_product.php" class="btn btn-success"><i class="fas fa-plus"></i> Add Product</a>
        <a href="add_category.php" class="btn btn-primary"><i class="fas fa-tags"></i> Add Category</a>
        <a href="add_supplier.php" class="btn btn-warning"><i class="fas fa-truck"></i> Add Supplier</a>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Selling Price</th>
                <th>Current Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo $product['id']; ?></td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['sku']); ?></td>
                <td><?php echo formatCurrency($product['price']); ?></td>
                <td><?php echo number_format((float)$product['total_stock']); ?></td>
                <td>
                    <a href="receive_stock.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-cubes"></i> Add Stock</a>
                    <!-- <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a> -->
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>