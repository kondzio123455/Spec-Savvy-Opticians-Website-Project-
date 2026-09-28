<!-- Kamile Kacinskaite -->
<!-- C00312390 -->
<!-- Project Add New Supplier Screen -->  
<!-- The user supplies details about a new supplier and when they confirm all details are correct a new record is added to the Supplier Table -->
<!-- 12/02/26 -->


<?php
session_start();
include 'success.php'; 
date_default_timezone_set("UTC"); 

if (!$con) // db connection error handling
{
    die("Connection failed: " . mysqli_connect_error()); // connection error message
}

// direct insertion using the post values
$sql = "INSERT INTO Supplier 
        (Supplier_Name, Supplier_stock_code, Supplier_Address, Supplier_Phone, Supplier_Eircode, Supplier_Email, Supplier_Website)
        VALUES (
            '{$_POST['suppliername']}', 
            '{$_POST['supplierstockcode']}', 
            '{$_POST['supplieraddress']}', 
            '{$_POST['phonenumber']}', 
            '{$_POST['eircode']}', 
            '{$_POST['supplieremail']}', 
            '{$_POST['websiteaddress']}'
        )";

if ($con->query($sql)) 
{
    // set the message that will show up in the green box
    $_SESSION['success_message'] = "Supplier '" . $_POST['suppliername'] . "' has been added to the system.";
    header("Location: AddSupplier.html.php");
    exit();
} 
else 
{
    echo "Error: " . $sql . "<br>" . $con->error;
}