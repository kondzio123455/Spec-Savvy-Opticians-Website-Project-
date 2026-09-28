<?php
// Screen:  finalDelete.php
// Purpose: Receives the confirmed stock number and description from
//      	confirmDelete.php and performs a soft delete by setting the
//      	Deleted flag to 1 in the Stock table. On success, redirects to
//      	deleteStockItem.php with the item description in the URL so a
//      	JavaScript success alert can be shown.
// Name:	Konrad Skoczylas
// ID:  	C00309030
// Date:	March 2026

include 'db.inc.php'; // Include database connection

// Redirect back if no stock id was posted
if (!isset($_POST['stock_id'])) {
    header("Location: deleteStockItem.php");
    exit();
}

// Get posted values from confirmDelete.php
$stock_id    = $_POST['stock_id'];
$description = $_POST['description'];

// Soft delete — sets Deleted flag to 1 rather than removing the record
$stmt = mysqli_prepare($con, "UPDATE Stock SET Deleted = 1 WHERE Stock_id = ?");
if (!$stmt) {
    die("Prepare failed: " . mysqli_error($con)); // Stop if prepare fails
}

// Bind stock id as integer
mysqli_stmt_bind_param($stmt, "i", $stock_id);

// Execute — stop and show error if it fails
if (!mysqli_stmt_execute($stmt)) {
    die("Error: " . mysqli_stmt_error($stmt));
}

mysqli_stmt_close($stmt); // Close statement
mysqli_close($con); // Close database connection

// Redirect back with description in URL to trigger the success popup
header("Location: deleteStockItem.php?deleted=" . urlencode($description));
exit();
?>