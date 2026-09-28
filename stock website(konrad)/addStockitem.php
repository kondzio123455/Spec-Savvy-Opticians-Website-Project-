<?php
// Screen:  addStockitem.php
// Purpose: Displays a form allowing the user to add a new stock item.
//      	Retrieves the list of suppliers from the database to populate
//      	the supplier dropdown. After a successful insert, redirects here
//      	with URL parameters that trigger a JavaScript success popup.
// Name:	Konrad Skoczylas
// ID:  	C00309030
// Date:	March 2026


include 'db.inc.php'; // Include database connection

// Fetch all suppliers ordered alphabetically for the dropdown
$result = mysqli_query($con, "SELECT SupplierID, Supplier_Name FROM Supplier ORDER BY Supplier_Name");

if(!$result){
    die("Error retrieving suppliers: " . mysqli_error($con)); // Stop if query fails
}

// Grab success message and allocated stock number from URL if redirected back after insert
$added_description = isset($_GET['added']) ? htmlspecialchars($_GET['added']) : '';
$stock_no = isset($_GET['stock_no']) ? (int)$_GET['stock_no'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Spec Savvy | Add Stock</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<header class="main-header">
    <div class="logo-area">
        <img src="spec savvy logo.png" alt="Spec Savvy Logo">
    </div>
</header>
<?php require_once 'Includes/nav.php'; // Include navigation bar ?>

<?php if ($added_description): ?>
<!-- Success popup — triggered when redirected back with ?added=Description&stock_no=X -->
<script>
    window.onload = function() {
        alert('Stock item added successfully!\n\nItem: <?php echo $added_description; ?>\nStock No: <?php echo $stock_no; ?>');
    };
</script>
<?php endif; ?>

<div class="page-container">
    <div class="card">
        <h2>Add Stock Item</h2>

        <!-- Stock item form — submits to insertStock.php via POST -->
        <form action="insertStock.php" method="POST" autocomplete="off">

            <!-- Description: letters, numbers, basic punctuation only -->
            <label for="Description">Description</label>
            <input type="text"
                id="Description"
                name="Description"
                placeholder="e.g. Ray-Ban Black Frame Glasses"
                minlength="5"
                maxlength="50"
                pattern="[A-Za-z0-9 .,\-]+"
                title="5–50 characters. Letters, numbers, spaces, commas, dots and hyphens only."
                required>

            <!-- Cost Price: decimal value between 1 and 5000 -->
            <label for="Cost_price">Cost Price</label>
            <input type="number"
                id="Cost_price"
                name="Cost_price"
                placeholder="e.g. 55.50"
                min="1"
                max="5000"
                step="0.01"
                title="Enter a valid price between 1 and 5000"
                required>

            <!-- Retail Price: decimal value between 1 and 10000 -->
            <label for="Retail_price">Retail Price</label>
            <input type="number"
                id="Retail_price"
                name="Retail_price"
                placeholder="e.g. 89.99"
                min="1"
                max="10000"
                step="0.01"
                title="Enter a valid price between 1 and 10000"
                required>

            <!-- Reorder Quantity: whole number between 1 and 500 -->
            <label for="Reorder_qty">Reorder Quantity</label>
            <input type="number"
                id="Reorder_qty"
                name="Reorder_qty"
                placeholder="e.g. 10"
                min="1"
                max="500"
                step="1"
                title="Must be a whole number between 1 and 500"
                required>

            <!-- Supplier Stock Code: alphanumeric and hyphens only -->
            <label for="Supplier_Stock_Code">Supplier Stock Code</label>
            <input type="text"
                id="Supplier_Stock_Code"
                name="Supplier_Stock_Code"
                placeholder="e.g. DB12345"
                maxlength="15"
                pattern="[A-Za-z0-9\-]+"
                title="Letters, numbers and hyphens only (max 15 characters)"
                required>

            <!-- Supplier Dropdown: populated from database, user selects rather than types -->
            <label for="SupplierID">Supplier</label>
            <select name="SupplierID" id="SupplierID" required>
                <option value="">-- Select Supplier --</option>
                <?php
                // Loop through suppliers and output each as a dropdown option
                while($row = mysqli_fetch_assoc($result)){
                    echo "<option value='".$row['SupplierID']."'>".$row['Supplier_Name']."</option>";
                }
                ?>
            </select>

            <!-- Submit and reset buttons -->
            <div class="button-group">
                <input type="submit" value="Add Stock">
                <input type="reset" value="Clear Form">
            </div>
        </form>
    </div>
</div>
</body>
</html>

<?php mysqli_close($con); // Close database connection ?>