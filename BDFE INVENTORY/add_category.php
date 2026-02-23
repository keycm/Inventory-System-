<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);

    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $error = "Category already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            if ($stmt->execute([$name])) {
                $success = "Category added successfully.";
            } else {
                $error = "Failed to add category.";
            }
        }
    }
}
?>

<div class="main-content">
    <h2>Add New Category</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-success">Save Category</button>
        <a href="inventory.php" class="btn btn-danger">Back to Inventory</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>