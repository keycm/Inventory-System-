<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $sku = cleanInput($_POST['sku']);
    $price = cleanInput($_POST['price']);
    $category_id = cleanInput($_POST['category_id']);
    if (empty($category_id)) {
        $category_id = null;
    }

    if (empty($name) || empty($sku) || empty($price)) {
        $error = "All fields are required.";
    } else {
        // Check if SKU exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->execute([$sku]);
        if ($stmt->fetch()) {
            $error = "SKU already exists.";
        } else {
            // Check if name exists
            $stmt = $pdo->prepare("SELECT id FROM products WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                $error = "Product name already exists.";
            } else {
                // Insert Product
                $stmt = $pdo->prepare("INSERT INTO products (name, sku, price, category_id) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $sku, $price, $category_id])) {
                    $success = "Product added successfully.";
                } else {
                    $error = "Failed to add product.";
                }
            }
        }
    }
}

// Fetch Categories for Dropdown
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>

<div class="main-content">
    <h2>Add New Product</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="sku">SKU</label>
            <input type="text" id="sku" name="sku" required>
        </div>
        <div class="form-group">
            <label for="price">Selling Price</label>
            <input type="number" id="price" name="price" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <small>If category not listed, add it in Category Management (Coming Soon).</small>
        </div>
        <button type="submit" class="btn btn-success">Save Product</button>
        <a href="inventory.php" class="btn btn-danger">Cancel</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>