<?php
// Name: Konrad Skoczylas
// ID: C00309030

include 'db.inc.php'; // Include database connection

// Redirect back if form was not submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {  
    header("Location: selectSpectacles.php"); // redirect to selection page
    exit(); // stop execution
}

// Get all posted values from spectaclesDetails.php
$customer_ID   = $_POST['customer_ID'];       // hidden field
$Stock_id      = $_POST['Stock_id'];          // selected frame
$lens_material = $_POST['lens_material'];     // lens type
$scratch       = $_POST['scratch_resistant']; // scratch coating option
$uv            = $_POST['uv_filter'];         // UV filter option

// Remove euro symbol from cost fields before storing
$total_cost = str_replace('€', '', $_POST['total_cost']);  
$deposit    = str_replace('€', '', $_POST['deposit']);  
$balance    = str_replace('€', '', $_POST['balance']);  

$sale_date = date('Y-m-d'); // today's date in YYYY-MM-DD format

// Fetch most recent eye test for this customer
$stmt0 = mysqli_prepare($con, "
    SELECT left_lens, right_lens 
    FROM `Eye Test` 
    WHERE customer_ID = ? AND Deleted = 0 
    ORDER BY date_of_test DESC 
    LIMIT 1
");
mysqli_stmt_bind_param($stmt0, "i", $customer_ID); // bind customer ID
mysqli_stmt_execute($stmt0); // execute query
$eyetest = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt0)); // get latest eye test
mysqli_stmt_close($stmt0); // close statement

$left_lens  = $eyetest['left_lens'];  // store left lens
$right_lens = $eyetest['right_lens']; // store right lens

// Insert new sale record into SpectacleSale table
$stmt = mysqli_prepare($con, "
    INSERT INTO SpectacleSale
    (customer_ID, Stock_id, lens_material, scratch_resistant, uv_filter, total_cost, deposit_paid, balance, sale_date)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {  
    die("Prepare failed: " . mysqli_error($con)); // stop if query preparation fails
}

// Bind all values to the prepared statement
mysqli_stmt_bind_param($stmt, "iisisddds",
    $customer_ID, $Stock_id, $lens_material, $scratch, $uv, $total_cost, $deposit, $balance, $sale_date
);

// Execute insert
if (mysqli_stmt_execute($stmt)) {
    $sale_id = mysqli_insert_id($con); // get auto-incremented sale ID
    mysqli_stmt_close($stmt);          // close statement
    mysqli_close($con);                // close DB connection

    // Redirect back to selection page with all necessary details for printing
    header("Location: selectSpectacles.php?added=" . $sale_id
        . "&sale_id="      . $sale_id
        . "&left="         . urlencode($left_lens)
        . "&right="        . urlencode($right_lens)
        . "&lens="         . urlencode($lens_material)
        . "&uv="           . $uv
        . "&scratch="      . $scratch
    );
    exit();
} else {
    // Display error if insert failed
    echo "Error: " . mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt); // close statement
}

mysqli_close($con); // close DB connection if not already closed
?>