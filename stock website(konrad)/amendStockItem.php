<?php
// Screen:  amendStockItem.php
// Purpose: Displays a dropdown of all active stock items for the user to
//          select one to amend. Selecting an item auto-submits the form
//          to confirmAmend.php. If redirected back after a successful
//          update, triggers a JavaScript alert showing the item name.
// Name:    Konrad Skoczylas
// ID:      C00309030
// Date:    March 2026

include 'db.inc.php'; // Include database connection

// Fetch all non-deleted stock items from the database, ordered alphabetically
$result = mysqli_query($con, "
    SELECT s.Stock_id, s.Description
    FROM Stock s
    WHERE s.Deleted = 0
    ORDER BY s.Description
");

// Check if redirected back after a successful amend — used to trigger the success popup
$amended = isset($_GET['amended']) ? htmlspecialchars($_GET['amended']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spec Savvy | Amend Stock</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="main-header">
    <div class="logo-area">
        <img src="spec savvy logo.png" alt="Spec Savvy Logo">
    </div>
</header>
<?php require_once 'Includes/nav.php'; // Include navigation bar ?>

<?php if ($amended): ?>
    <!-- Success popup — shown after a successful amend with the updated item name -->
    <script>
        window.onload = function() {
            alert('Stock item amended successfully!\n\nItem updated: <?php echo $amended; ?>');
        };
    </script>
<?php endif; ?>

<div class="page-container">
    <div class="card">
        <h2>Amend Stock Item</h2>
        <h4>Please select a stock item to amend</h4>

        <!-- Form auto-submits to confirmAmend.php when the user picks an item -->
        <form method="POST" action="confirmAmend.php">
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
<?php mysqli_close($con); // Close the database connection ?>