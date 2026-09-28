<?php
// Screen:  finalAmend.php
// Purpose: Receives the updated stock item values from confirmAmend.php and
//      	updates the matching record in the Stock table using a prepared
//      	statement. On success, redirects to amendStockItem.php with a URL
//      	parameter that triggers a JavaScript success alert.
// Name:	Konrad Skoczylas
// ID:  	C00309030
// Date:	March 2026

include 'db.inc.php'; // Include database connection

// Get the posted form values from confirmAmend.php
$stock_id     = $_POST['stock_id'];
$description  = $_POST['description'];
$cost_price   = $_POST['cost_price'];
$retail_price = $_POST['retail_price'];
$reorder_qty  = $_POST['reorder_qty'];

// Prepare the UPDATE statement to amend the stock item
$stmt = mysqli_prepare($con, "UPDATE Stock SET Description = ?, Cost_price = ?, Retail_price = ?, Reorder_qty = ? WHERE Stock_id = ?");
if (!$stmt)
{
    die("Prepare failed: " . mysqli_error($con)); // Stop if prepare fails
}

// Bind all values as strings — MySQL will handle type conversion
mysqli_stmt_bind_param($stmt, "sssss", $description, $cost_price, $retail_price, $reorder_qty, $stock_id);

// Execute the statement — show error and stop if it fails
if (!mysqli_stmt_execute($stmt))
{
    echo "Error " . mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    exit();
}

mysqli_stmt_close($stmt); // Close the statement
mysqli_close($con); // Close the database connection

// Redirect back to amendStockItem.php with the description in the URL to trigger the success popup
header("Location: amendStockItem.php?amended=" . urlencode($description));
exit();
?>