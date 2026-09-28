<?php
// Name: Konrad Skoczylas
// ID: C00309030

include 'db.inc.php'; // Include database connection

// Redirect back if form was not posted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: selectSpectacles.php");
    exit();
}

// Get all posted values from spectaclesDetails.php
$customer_ID     = $_POST['customer_ID'];
$Stock_id        = $_POST['Stock_id'];
$lens_material   = $_POST['lens_material'];
$scratch         = $_POST['scratch_resistant'];
$uv              = $_POST['uv_filter'];
$total_cost      = str_replace('€', '', $_POST['total_cost']);  // Strip euro sign before storing
$deposit         = str_replace('€', '', $_POST['deposit']);
$balance         = str_replace('€', '', $_POST['balance']);
$sale_date       = date('Y-m-d'); // Use today's date as the sale date

// Insert the new sale record into the SpectacleSale table
$stmt = mysqli_prepare($con, "
    INSERT INTO SpectacleSale
    (customer_ID, Stock_id, lens_material, scratch_resistant, uv_filter, total_cost, deposit_paid, balance, sale_date)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($con)); // Stop if prepare fails
}

// Bind all values — i = integer, s = string, d = decimal
mysqli_stmt_bind_param($stmt, "iisisddds",
    $customer_ID, $Stock_id, $lens_material, $scratch, $uv, $total_cost, $deposit, $balance, $sale_date
);

if (mysqli_stmt_execute($stmt)) {
    $sale_id = mysqli_insert_id($con); // Get the auto-assigned sale reference number
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    // Redirect back with sale ID to trigger success popup
    header("Location: selectSpectacles.php?added=" . $sale_id);
    exit();
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
}

mysqli_close($con); // Close database connection
?>