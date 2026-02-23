<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = cleanInput($_POST['product_id']);
    $supplier_id = cleanInput($_POST['supplier_id']);
    if (empty($supplier_id)) {
        $supplier_id = null;
    }
    $cost_price = cleanInput($_POST['cost_price']);
    $quantity = cleanInput($_POST['quantity']);

    if (empty($product_id) || empty($cost_price) || empty($quantity)) {
        $error = "Product, Quantity, and Cost Price are required.";
    } else {
        // Calculate Total Batch Cost
        $total_batch_cost = $cost_price * $quantity;

        // Insert Inventory Batch
        $stmt = $pdo->prepare("INSERT INTO inventory_batches (product_id, supplier_id, cost_price, initial_quantity, remaining_quantity, total_batch_cost) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$product_id, $supplier_id, $cost_price, $quantity, $quantity, $total_batch_cost])) {
            $success = "Stock received successfully.";
        } else {
            $error = "Failed to receive stock.";
        }
    }
}

// Fetch Suppliers
$stmt = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC");
$suppliers = $stmt->fetchAll();

// Fetch Products
$stmt = $pdo->query("SELECT id, name FROM products ORDER BY name ASC");
$products = $stmt->fetchAll();

$selectedProductId = isset($_GET['id']) ? (int)$_GET['id'] : null;
?>

<div class="main-content">
    <h2>Receive Stock (Create Batch)</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="product_id">Product</label>
            <select id="product_id" name="product_id" required>
                <option value="">Select Product</option>
                <?php foreach ($products as $prod): ?>
                    <option value="<?php echo $prod['id']; ?>" <?php echo ($selectedProductId == $prod['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($prod['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="supplier_id">Supplier</label>
            <select id="supplier_id" name="supplier_id">
                <option value="">Select Supplier (Optional)</option>
                <?php foreach ($suppliers as $sup): ?>
                    <option value="<?php echo $sup['id']; ?>"><?php echo htmlspecialchars($sup['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="cost_price">Cost Price (Per Unit)</label>
            <input type="number" id="cost_price" name="cost_price" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" required>
        </div>
        <button type="submit" class="btn btn-success">Receive Stock</button>
        <a href="inventory.php" class="btn btn-danger">Cancel</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>