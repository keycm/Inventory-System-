# Inventory Management & POS System

A professional Inventory Management and Point of Sale (POS) system built with PHP and MySQL.

## Features

-   **Dashboard**: Overview of sales, stock, and low stock alerts.
-   **Inventory Management**:
    -   Add Products.
    -   Receive Stock (FIFO Batch Tracking).
    -   View Current Stock Levels.
-   **Point of Sale (POS)**:
    -   Product Search.
    -   Cart Management.
    -   Checkout with FIFO stock deduction.
-   **Sales Reporting**: View sales history by date.
-   **Authentication**: Secure login system.

## Installation

1.  **Database Setup**:
    -   Create a MySQL database named `inventory_db`.
    -   Import `database/schema.sql` into the database.
    -   Default Admin User:
        -   Username: `admin`
        -   Password: `password`

2.  **Configuration**:
    -   Check `includes/db.php` if you need to change database credentials (default: localhost, root, empty password).

3.  **Run**:
    -   Host the `BDFE INVENTORY` folder on a PHP-enabled server (e.g., XAMPP, Apache).
    -   Access `index.php` in your browser.

## File Structure

-   `assets/`: CSS and JS files.
-   `database/`: SQL schema.
-   `includes/`: Helper functions, database connection, authentication.
-   `uploads/`: (Optional) Product images.
-   `index.php`: Login page.
-   `dashboard.php`: Main dashboard.
-   `inventory.php`: Product list.
-   `add_category.php`: Add new product categories.
-   `add_supplier.php`: Add new suppliers.
-   `pos.php`: Point of Sale interface.
-   `sales_report.php`: Sales history.
