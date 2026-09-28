<?php
// Screen:  confirmAmend.php
// Purpose: Receives the stock number selected on amendStockItem.php, fetches
//      	the full item details from the database, and displays an editable
//      	form pre-filled with the current values. Description, cost price,
//      	retail price and reorder quantity can be changed. Stock number and
//      	supplier are displayed as read-only. A confirm() dialog prompts the
//      	user before the form posts to finalAmend.php.
// Name:	Konrad Skoczylas
// ID:  	C00309030
// Date:	March 2026

include 'db.inc.php'; // Include database connection

// Redirect back if no stock id was posted
if (!isset($_POST['stock_id'])) {
    header("Location: amendStockItem.php");
    exit();
}

$stock_id = $_POST['stock_id']; // Get the stock id from the form

// Fetch the selected stock item details along with the supplier name
$stmt = mysqli_prepare($con,
    "SELECT s.Stock_id, s.Description, s.Cost_price, s.Retail_price, s.Reorder_qty, sup.Supplier_Name
     FROM Stock s
     JOIN Supplier sup ON s.SupplierID = sup.SupplierID
     WHERE s.Stock_id = ? AND s.Deleted = 0");
mysqli_stmt_bind_param($stmt, "i", $stock_id); // Bind stock id as integer
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result); // Fetch the row as an associative array
mysqli_stmt_close($stmt);

// If no item was found, redirect back to the selection page
if (!$row) {
    header("Location: amendStockItem.php");
    exit();
}

mysqli_close($con); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spec Savvy | Confirm Amend</title>
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
        <h2>Amend Stock Item</h2>
        <p>Update the fields below and click Save Changes when done.</p>

        <!-- Form posts updated values to finalAmend.php -->
        <!-- onsubmit confirm() dialog double checks before saving — satisfies spec requirement -->
        <form method="POST" action="finalAmend.php" onsubmit="return confirm('Please confirm that the details are correct. Save changes?');">

            <!-- Hidden field to carry the stock id through to finalAmend.php -->
            <input type="hidden" name="stock_id" value="<?php echo $row['Stock_id']; ?>">

            <!-- Stock id is displayed but not editable -->
            <label>Stock Number (not editable)</label>
            <p><?php echo $row['Stock_id']; ?></p>

            <!-- Description — must be 5 to 50 characters, letters/numbers/spaces/commas/dots/hyphens only -->
            <label for="description">Description</label>
            <input type="text"
                   id="description"
                   name="description"
                   value="<?php echo htmlspecialchars($row['Description']); ?>"
                   minlength="5"
                   maxlength="50"
                   pattern="[A-Za-z0-9 .,\-]+"
                   title="5 to 50 characters. Letters, numbers, spaces, commas, dots and hyphens only."
                   required>

            <!-- Cost price — must be between 1 and 5000 -->
            <label for="cost_price">Cost Price</label>
            <input type="number"
                   id="cost_price"
                   name="cost_price"
                   value="<?php echo $row['Cost_price']; ?>"
                   min="1"
                   max="5000"
                   step="0.01"
                   title="Enter a price between 1 and 5000"
                   required>

            <!-- Retail price — must be between 1 and 10000 -->
            <label for="retail_price">Retail Price</label>
            <input type="number"
                   id="retail_price"
                   name="retail_price"
                   value="<?php echo $row['Retail_price']; ?>"
                   min="1"
                   max="10000"
                   step="0.01"
                   title="Enter a price between 1 and 10000"
                   required>

            <!-- Reorder quantity — must be a whole number between 1 and 500 -->
            <label for="reorder_qty">Reorder Quantity</label>
            <input type="number"
                   id="reorder_qty"
                   name="reorder_qty"
                   value="<?php echo $row['Reorder_qty']; ?>"
                   min="1"
                   max="500"
                   step="1"
                   title="Enter a whole number between 1 and 500"
                   required>

            <!-- Supplier is displayed but not editable -->
            <label>Supplier (not editable)</label>
            <p><?php echo htmlspecialchars($row['Supplier_Name']); ?></p>

            <div class="button-group">
                <input type="submit" value="Save Changes">
                <a href="amendStockItem.php"><input type="button" value="Cancel"></a>
            </div>
        </form>
    </div>
</div>
</body>
</html>