<?php
// Name: Konrad Skoczylas
// ID: C00309030

include 'db.inc.php'; // Include database connection

// Redirect back if no customer was posted
if (!isset($_POST['customer_ID'])) {
    header("Location: selectSpectacles.php");
    exit();
}

$customer_ID = $_POST['customer_ID'];

// Fetch customer details
$stmt = mysqli_prepare($con, "SELECT first_name, last_name, address, town, dob FROM Customer WHERE customer_ID = ?");
mysqli_stmt_bind_param($stmt, "i", $customer_ID);
mysqli_stmt_execute($stmt);
$customer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Fetch most recent eye test for this customer
$stmt2 = mysqli_prepare($con, "SELECT left_lens, right_lens, date_of_test FROM `Eye Test` WHERE customer_ID = ? AND Deleted = 0 ORDER BY date_of_test DESC LIMIT 1");
mysqli_stmt_bind_param($stmt2, "i", $customer_ID);
mysqli_stmt_execute($stmt2);
$eyetest = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
mysqli_stmt_close($stmt2);

// Fetch all non-deleted stock items for frames dropdown
$frames = mysqli_query($con, "SELECT Stock_id, Stock_no, Description, Retail_price FROM Stock WHERE Deleted = 0 ORDER BY Description");

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spec Savvy | Spectacles Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="main-header">
    <div class="logo-area">
        <img src="spec savvy logo.png" alt="Spec Savvy Logo">
    </div>
</header>
<?php require_once 'Includes/nav.php'; ?>

<div class="page-container">
    <div class="card">
        <h2>Select Spectacles</h2>

        <!-- Customer details — readonly -->
        <label>Name</label>
        <input type="text" value="<?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>" readonly>

        <label>Address</label>
        <input type="text" value="<?php echo htmlspecialchars($customer['address'] . ', ' . $customer['town']); ?>" readonly>

        <label>Date of Birth</label>
        <input type="text" value="<?php echo $customer['dob']; ?>" readonly>

        <!-- Eye test details — readonly -->
        <label>Left Eye</label>
        <input type="text" value="<?php echo $eyetest ? $eyetest['left_lens'] : 'No eye test found'; ?>" readonly>

        <label>Right Eye</label>
        <input type="text" value="<?php echo $eyetest ? $eyetest['right_lens'] : 'No eye test found'; ?>" readonly>

        <label>Date of Test</label>
        <input type="text" value="<?php echo $eyetest ? $eyetest['date_of_test'] : 'N/A'; ?>" readonly>

        <!-- Order form -->
        <form method="POST" action="finalSpectacles.php" onsubmit="return confirm('Please confirm the order details are correct. Save sale?');">

            <input type="hidden" name="customer_ID" value="<?php echo $customer_ID; ?>">

            <!-- Frames dropdown — retail price in data-price for JS cost calculation -->
            <label for="Stock_id">Frames</label>
            <select name="Stock_id" id="Stock_id" required onchange="calculateCost()">
                <option value="" data-price="0">-- Select Frames --</option>
                <?php while ($row = mysqli_fetch_assoc($frames)): ?>
                    <option value="<?php echo $row['Stock_id']; ?>" data-price="<?php echo $row['Retail_price']; ?>">
                        <?php echo htmlspecialchars($row['Description']); ?> (No: <?php echo $row['Stock_no']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- Lens options -->
            <label for="lens_material">Lens Material</label>
            <select name="lens_material" id="lens_material" required>
                <option value="">-- Select --</option>
                <option value="glass">Glass</option>
                <option value="plastic">Plastic</option>
            </select>

            <label for="scratch_resistant">Scratch Resistant Coating</label>
            <select name="scratch_resistant" id="scratch_resistant" required>
                <option value="">-- Select --</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>

            <label for="uv_filter">UV Filter</label>
            <select name="uv_filter" id="uv_filter" required>
                <option value="">-- Select --</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>

            <!-- Cost fields — auto calculated by JS, passed to finalSpectacles.php -->
            <label>Total Cost</label>
            <input type="text" id="total_cost" name="total_cost" readonly>

            <label>Deposit Required (20%)</label>
            <input type="text" id="deposit" name="deposit" readonly>

            <label>Remaining Balance</label>
            <input type="text" id="balance" name="balance" readonly>

            <div class="button-group">
                <input type="submit" value="Save Sale">
                <a href="selectSpectacles.php"><input type="button" value="Cancel"></a>
            </div>
        </form>
    </div>
</div>

<script>
// Calculate cost, deposit and balance when frames are selected
function calculateCost() {
    var select = document.getElementById('Stock_id');
    var price  = parseFloat(select.options[select.selectedIndex].getAttribute('data-price')) || 0;
    document.getElementById('total_cost').value = '€' + price.toFixed(2);
    document.getElementById('deposit').value    = '€' + (price * 0.20).toFixed(2);
    document.getElementById('balance').value    = '€' + (price * 0.80).toFixed(2);
}
</script>

</body>
</html>