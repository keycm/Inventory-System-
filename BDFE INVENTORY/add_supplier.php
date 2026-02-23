<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $contact = cleanInput($_POST['contact_info']);

    if (empty($name)) {
        $error = "Supplier name is required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_info) VALUES (?, ?)");
        if ($stmt->execute([$name, $contact])) {
            $success = "Supplier added successfully.";
        } else {
            $error = "Failed to add supplier.";
        }
    }
}
?>

<div class="main-content">
    <h2>Add New Supplier</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Supplier Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="contact_info">Contact Info (Phone/Email)</label>
            <textarea id="contact_info" name="contact_info" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Save Supplier</button>
        <a href="inventory.php" class="btn btn-danger">Back to Inventory</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>