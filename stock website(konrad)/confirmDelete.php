<?php
// Screen:  confirmDelete.php
// Purpose: Receives the selected stock number from deleteStockItem.php.
//      	Checks whether the item can be deleted (quantity in stock must be
//      	zero and item must not be on any active order). If checks pass,
//      	displays the item details in read-only fields and asks the user to
//      	confirm. If checks fail, displays an appropriate error message.
// Name:	Konrad Skoczylas
// ID:  	C00309030
// Date:	March 2026

include 'db.inc.php'; // Include database connection

// Redirect back if no stock id was posted
if (!isset($_POST['stock_id'])) {
    header("Location: deleteStockItem.php");
    exit();
}

$stock_id = $_POST['stock_id']; // Get stock id from the form
$error = null; // Initialise error variable

// Check if quantity in stock is greater than zero — cannot delete if so
$stmt = mysqli_prepare($con, "SELECT Qty_in_stock FROM Stock WHERE Stock_id = ?");
mysqli_stmt_bind_param($stmt, "i", $stock_id);
mysqli_stmt_execute($stmt);
$r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$r) {
    $error = "Stock item not found.";
} elseif ($r['Qty_in_stock'] > 0) {
    $error = "Cannot delete: Quantity in stock is greater than zero.";
} else {
    // Check if the item is on any order — cannot delete if it is
    $stmt2 = mysqli_prepare($con, "SELECT COUNT(*) as order_count FROM OrderItem WHERE Stock_ID = ?");
    mysqli_stmt_bind_param($stmt2, "i", $stock_id);
    mysqli_stmt_execute($stmt2);
    $r2 = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
    mysqli_stmt_close($stmt2);

    if ($r2['order_count'] > 0) {
        $error = "Cannot delete: Item is currently on order.";
    }
}

// If no errors, fetch full item details to display on the confirmation page
$row = null;
if (!$error) {
    $stmt3 = mysqli_prepare($con,
        "SELECT s.Stock_id, s.Description, s.Cost_price, sup.Supplier_Name, s.Qty_in_stock
         FROM Stock s
         JOIN Supplier sup ON s.SupplierID = sup.SupplierID
         WHERE s.Stock_id = ? AND s.Deleted = 0");
    mysqli_stmt_bind_param($stmt3, "i", $stock_id);
    mysqli_stmt_execute($stmt3);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt3));
    mysqli_stmt_close($stmt3);
}

mysqli_close($con); // Close database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spec Savvy | Confirm Delete</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="main-header">
    <div class="logo-area">
        <img src="spec savvy logo.png" alt="Spec Savvy Logo">
    </div>
</header>
<?php require_once 'Includes/nav.php'; // Include navigation bar ?>

<div class="page-container">
    <div class="card">
        <h2>Delete Stock Item</h2>

        <?php if ($error): ?>
            <!-- Show error if item cannot be deleted -->
            <p style="color: red;"><?php echo $error; ?></p>
            <div class="button-group">
                <a href="deleteStockItem.php"><input type="button" value="Go Back"></a>
            </div>

        <?php elseif ($row): ?>
            <!-- Display item details and ask user to confirm deletion -->
            <p>Are you sure you want to delete this stock item? This cannot be undone.</p>

            <!-- Readonly fields show the item details -->
            <label>Stock Number</label>
            <input type="text" value="<?php echo $row['Stock_id']; ?>" readonly>

            <label>Description</label>
            <input type="text" value="<?php echo htmlspecialchars($row['Description']); ?>" readonly>

            <label>Cost Price</label>
            <input type="text" value="€<?php echo number_format($row['Cost_price'], 2); ?>" readonly>

            <label>Supplier</label>
            <input type="text" value="<?php echo htmlspecialchars($row['Supplier_Name']); ?>" readonly>

            <label>Quantity In Stock</label>
            <input type="text" value="<?php echo $row['Qty_in_stock']; ?>" readonly>

            <div class="button-group">
                <!-- Yes — posts stock id and description to finalDelete.php -->
                <form method="POST" action="finalDelete.php">
                    <input type="hidden" name="stock_id" value="<?php echo $row['Stock_id']; ?>">
                    <input type="hidden" name="description" value="<?php echo htmlspecialchars($row['Description']); ?>">
                    <input type="submit" value="Yes, Delete">
                </form>
                <!-- No — returns user to the selection page -->
                <a href="deleteStockItem.php"><input type="button" value="No, Cancel"></a>
            </div>

        <?php endif; ?>
    </div>
</div>

</body>
</html>