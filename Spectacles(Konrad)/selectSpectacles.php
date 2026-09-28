<?php
// Name: Konrad Skoczylas
// ID: C00309030

include 'db.inc.php'; // Include database connection

// Fetch all non-deleted customers ordered alphabetically by last name
$customers = mysqli_query($con, "
    SELECT customer_ID, first_name, last_name
    FROM Customer
    WHERE deleted = 0
    ORDER BY last_name
");

// Check if redirected back after a successful sale — used to trigger success popup
$added = isset($_GET['added']) ? htmlspecialchars($_GET['added']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spec Savvy | Select Spectacles</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="main-header">
    <div class="logo-area">
        <img src="spec savvy logo.png" alt="Spec Savvy Logo">
    </div>
</header>
<?php require_once 'Includes/nav.php'; // Include navigation bar ?>

<?php if ($added): ?>
<!-- Success popup — shown after sale is saved -->
<script>
    window.onload = function() {
        alert('Spectacle sale saved successfully!\n\nSale reference: <?php echo $added; ?>');
    };
</script>
<?php endif; ?>

<div class="page-container">
    <div class="card">
        <h2>Select Spectacles</h2>
        <h4>Please select a customer</h4>

        <!-- Selecting a customer auto-submits the form to spectaclesDetails.php -->
        <form method="POST" action="spectaclesDetails.php">
            <label for="customer_ID">Select Customer</label>
            <select name="customer_ID" id="customer_ID" required onchange="this.form.submit()">
                <option value="">-- Select a Customer --</option>
                <?php while ($row = mysqli_fetch_assoc($customers)): ?>
                    <!-- Each option value is customer_ID, display text is full name -->
                    <option value="<?php echo $row['customer_ID']; ?>">
                        <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>
</div>

</body>
</html>

<?php mysqli_close($con); // Close database connection ?>