<?php
// Name: Konrad Skoczylas
// ID: C00309030
// Screen: insertStock.php
// Purpose: Receives POST data from addStockitem.php and inserts a new stock item into the Stock table.
// Redirects back to the add form with a success message on completion.
// Date: March 2026

include 'db.inc.php'; // Include database connection

// Only process if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve form values from POST
    // Stock_no is NOT collected here — it is auto-allocated by the database as AUTO_INCREMENT
    $Description         = $_POST['Description'];
    $Cost_price          = $_POST['Cost_price'];
    $Retail_price        = $_POST['Retail_price'];
    $Reorder_qty         = $_POST['Reorder_qty'];
    $Supplier_Stock_Code = $_POST['Supplier_Stock_Code'];
    $SupplierID          = $_POST['SupplierID'];

    // Prepare INSERT statement — Stock_no excluded, DB assigns it automatically
    $stmt = mysqli_prepare($con,
        "INSERT INTO Stock
        (Description, Cost_price, Retail_price, Reorder_qty, Supplier_Stock_Code, SupplierID)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($con)); // Stop if prepare fails
    }

    // Bind parameters to statement
    // s = string, d = double (decimal), i = integer
    mysqli_stmt_bind_param($stmt, "sddisi",
        $Description, $Cost_price, $Retail_price, $Reorder_qty, $Supplier_Stock_Code, $SupplierID
    );

    // Execute the statement — insert the new stock record into the database
    if (mysqli_stmt_execute($stmt)) {

        // Retrieve the auto-allocated stock number assigned by the database
        $new_stock_no = mysqli_insert_id($con);

        mysqli_stmt_close($stmt);
        mysqli_close($con);

        // Redirect back to the add form with description and stock number for success alert
        header("Location: addStockitem.php?added=" . urlencode($Description) . "&stock_no=" . $new_stock_no);
        exit();

    } else {
        // Display error if execution fails
        echo "Error: " . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($con); // Close database connection
?>