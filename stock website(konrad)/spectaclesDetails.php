<?php
// Name: Konrad Skoczylas
// ID: C00309030

include 'db.inc.php'; // Include database connection

// Redirect back if no customer_ID was posted
if (!isset($_POST['customer_ID'])) {  
    header("Location: selectSpectacles.php"); // redirect to selection page
    exit(); // stop script execution
}

$customer_ID = $_POST['customer_ID']; // get customer ID from POST

// Fetch customer details
$stmt = mysqli_prepare($con, "SELECT first_name, last_name, address, town, dob FROM Customer WHERE customer_ID = ?"); // prepare query
mysqli_stmt_bind_param($stmt, "i", $customer_ID); // bind customer ID
mysqli_stmt_execute($stmt); // execute query
$customer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); // fetch associative array
mysqli_stmt_close($stmt); // close statement

// Fetch most recent eye test for this customer
$stmt2 = mysqli_prepare($con, "SELECT left_lens, right_lens, date_of_test FROM `Eye Test` WHERE customer_ID = ? AND Deleted = 0 ORDER BY date_of_test DESC LIMIT 1"); // prepare query
mysqli_stmt_bind_param($stmt2, "i", $customer_ID); // bind customer ID
mysqli_stmt_execute($stmt2); // execute query
$eyetest = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2)); // fetch latest eye test
mysqli_stmt_close($stmt2); // close statement

// Fetch all non-deleted stock items for frames dropdown
$frames = mysqli_query($con, "SELECT Stock_id,Description, Retail_price FROM Stock WHERE Deleted = 0 ORDER BY Description"); // stock list

mysqli_close($con); // close DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spec Savvy | Spectacles Details</title>
    <link rel="stylesheet" href="style.css"> <!-- main stylesheet -->
</head>
<body>

<header class="main-header">
    <div class="logo-area">
        <img src="spec savvy logo.png" alt="Spec Savvy Logo"> <!-- logo -->
    </div>
</header>
<?php require_once 'Includes/nav.php'; ?> <!-- navigation menu -->

<div class="page-container">
    <div class="card">
        <h2>Select Spectacles</h2>

        <!-- Customer details — readonly fields -->
        <label>Name</label>
        <input type="text" value="<?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>" readonly> <!-- full name -->

        <label>Address</label>
        <input type="text" value="<?php echo htmlspecialchars($customer['address'] . ', ' . $customer['town']); ?>" readonly> <!-- address -->

        <label>Date of Birth</label>
        <input type="text" value="<?php echo $customer['dob']; ?>" readonly> <!-- DOB -->

        <!-- Eye test details — readonly fields -->
        <label>Left Eye</label>
        <input type="text" value="<?php echo $eyetest ? $eyetest['left_lens'] : 'No eye test found'; ?>" readonly> <!-- left lens -->

        <label>Right Eye</label>
        <input type="text" value="<?php echo $eyetest ? $eyetest['right_lens'] : 'No eye test found'; ?>" readonly> <!-- right lens -->

        <label>Date of Test</label>
        <input type="text" value="<?php echo $eyetest ? $eyetest['date_of_test'] : 'N/A'; ?>" readonly> <!-- test date -->

        <!-- Order form -->
        <form method="POST" action="finalSpectacles.php" onsubmit="return confirm('Please confirm the order details are correct. Save sale?');">
            <input type="hidden" name="customer_ID" value="<?php echo $customer_ID; ?>"> <!-- hidden customer ID -->

            <!-- Frames dropdown — retail price in data-price for JS -->
            <label for="Stock_id">Frames</label>
            <select name="Stock_id" id="Stock_id" required onchange="calculateCost()"> <!-- calculate cost when changed -->
                <option value="" data-price="0">-- Select Frames --</option>
                <?php while ($row = mysqli_fetch_assoc($frames)): ?>
                    <option value="<?php echo $row['Stock_id']; ?>" data-price="<?php echo $row['Retail_price']; ?>">
                        <?php echo htmlspecialchars($row['Description']); ?> (No: <?php echo $row['Stock_id']; ?>)
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

            <!-- Cost fields — auto calculated by JS -->
            <label>Total Cost</label>
            <input type="text" id="total_cost" name="total_cost" readonly> <!-- total cost -->

            <label>Deposit Required (20%)</label>
            <input type="text" id="deposit" name="deposit" readonly> <!-- deposit -->

            <label>Remaining Balance</label>
            <input type="text" id="balance" name="balance" readonly> <!-- balance -->

            <div class="button-group">
                <input type="submit" value="Save Sale"> <!-- submit button -->
                <a href="selectSpectacles.php"><input type="button" value="Cancel"></a> <!-- cancel button -->
            </div>
        </form>
    </div>
</div>

<script>
// Calculate cost, deposit and balance when frames are selected
function calculateCost() {
    var select = document.getElementById('Stock_id'); // get frames dropdown
    var price  = parseFloat(select.options[select.selectedIndex].getAttribute('data-price')) || 0; // get price
    document.getElementById('total_cost').value = '€' + price.toFixed(2); // set total
    document.getElementById('deposit').value    = '€' + (price * 0.20).toFixed(2); // set 20% deposit
    document.getElementById('balance').value    = '€' + (price * 0.80).toFixed(2); // set remaining balance
}
</script>

</body>
</html>