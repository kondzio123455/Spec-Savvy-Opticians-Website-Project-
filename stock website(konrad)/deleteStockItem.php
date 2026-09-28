<?php
// Screen:  deleteStockItem.php
// Purpose: Displays a dropdown of all active (non-deleted) stock items.
//      	Selecting an item auto-submits the form to confirmDelete.php.
//      	If redirected back after a successful deletion, a JavaScript alert
//      	confirms the item that was removed.
// Name:	Konrad Skoczylas
// ID:  	C00309030
// Date:	March 2026

include 'db.inc.php'; // Include database connection

// Fetch all non-deleted stock items ordered alphabetically for the dropdown
$result = mysqli_query($con, "
    SELECT Stock_id, Description
    FROM Stock
    WHERE Deleted = 0
    ORDER BY Description
");

// Check if redirected back after a successful delete — used to trigger the success popup
$deleted = isset($_GET['deleted']) ? htmlspecialchars($_GET['deleted']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spec Savvy | Delete Stock</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="main-header">
    <div class="logo-area">
        <img src="spec savvy logo.png" alt="Spec Savvy Logo">
    </div>
</header>
<?php require_once 'Includes/nav.php'; // Include navigation bar ?>
<?php if ($deleted): ?>
<!-- Success popup — shown after a successful deletion -->
<script>
    window.onload = function() {
        alert('Stock item deleted successfully!\n\nItem deleted: <?php echo $deleted; ?>');
    };
</script>
<?php endif; ?>
<div class="page-container">
    <div class="card">
        <h2>Delete Stock Item</h2>
        <h4>Please select a stock item to delete</h4>
        <!-- Selecting an item from the dropdown auto-submits the form to confirmDelete.php -->
        <form method="POST" action="confirmDelete.php">
            <label for="stock_id">Select Stock Item</label>
            <!-- Dropdown populated from the Stock table — onchange auto-submits the form -->
            <select name="stock_id" id="stock_id" required onchange="this.form.submit()">
                <option value="">-- Select a Stock Item --</option>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <!-- Each option value is Stock_id, display text is the Description -->
                    <option value="<?php echo $row['Stock_id']; ?>">
                        <?php echo htmlspecialchars($row['Description']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>
</div>
</body>
</html>
<?php mysqli_close($con); // Close database connection ?>