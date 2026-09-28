<?php
// Name: Konrad Skoczylas
// ID: C00309030

include 'db.inc.php'; // Include database connection

// Fetch all non-deleted customers ordered alphabetically
$customers = mysqli_query($con, "
    SELECT customer_ID, first_name, last_name
    FROM Customer
    WHERE deleted = 0
    ORDER BY last_name
");

// Check if redirected back after a successful sale
$added   = isset($_GET['added'])   ? htmlspecialchars($_GET['added'])   : '';
$sale_id = isset($_GET['sale_id']) ? (int)$_GET['sale_id']             : 0;
$left    = isset($_GET['left'])    ? htmlspecialchars($_GET['left'])    : '';
$right   = isset($_GET['right'])   ? htmlspecialchars($_GET['right'])   : '';
$lens    = isset($_GET['lens'])    ? htmlspecialchars($_GET['lens'])    : '';
$uv      = isset($_GET['uv'])      ? (int)$_GET['uv']                  : 0;
$scratch = isset($_GET['scratch']) ? (int)$_GET['scratch']             : 0;
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
<?php require_once 'Includes/nav.php'; ?>

<?php if ($added): ?>
<!-- Success popup after sale saved -->
<script>
    window.onload = function() {
        alert('Spectacle sale saved successfully!\n\nSale reference: <?php echo $added; ?>');
    };
</script>

<!-- Print button shown after successful sale -->
<div class="page-container">
    <div class="card">
        <h2>Sale Saved</h2>
        <p>Sale reference: <strong><?php echo $sale_id; ?></strong></p>
        <!-- Print button opens the lab letter in a new tab -->
        <a href="printLetter.php?sale_id=<?php echo $sale_id; ?>&left=<?php echo urlencode($left); ?>&right=<?php echo urlencode($right); ?>&lens=<?php echo urlencode($lens); ?>&uv=<?php echo $uv; ?>&scratch=<?php echo $scratch; ?>" target="_blank">
            <input type="button" value="Print Lab Letter">
        </a>
        <br><br>
        <a href="selectSpectacles.php"><input type="button" value="New Sale"></a>
    </div>
</div>

<?php else: ?>

<div class="page-container">
    <div class="card">
        <h2>Select Spectacles</h2>
        <h4>Please select a customer</h4>

        <!-- Selecting a customer auto-submits to spectaclesDetails.php -->
        <form method="POST" action="spectaclesDetails.php">
            <label for="customer_ID">Select Customer</label>
            <select name="customer_ID" id="customer_ID" required onchange="this.form.submit()">
                <option value="">-- Select a Customer --</option>
                <?php while ($row = mysqli_fetch_assoc($customers)): ?>
                    <option value="<?php echo $row['customer_ID']; ?>">
                        <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>
</div>

<?php endif; ?>

</body>
</html>

<?php mysqli_close($con); // Close database connection ?>