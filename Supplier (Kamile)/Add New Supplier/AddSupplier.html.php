<!-- Kamile Kacinskaite -->
<!-- C00312390 -->
<!-- Project Add New Supplier Screen -->  
<!-- 12/02/26 -->

<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spec Savvy | Add A Supplier</title>
    <!-- link to the css file that styles the page -->
    <link rel="stylesheet" href="supplier.css">
</head>

<body>

<!-- header section that shows the company logo -->
<header class="main-header">
    <div class="logo-area">
        <!-- logo image for the website -->
        <img src="spec savvy logo.png" alt="Spec Savvy Logo">
    </div>
</header>

<!-- navigation bar that lets users move around the website -->
<nav class="navbar">
    <ul>
         <!-- dropdown menu for adding suppliers -->
        <li class="dropdown">
             <!-- main menu link -->
            <a href="AddSupplier.html.php" class="active">Add Supplier</a>
            <!-- submenu options -->
            <ul class="dropdown-menu">
                <li><a href="AddSupplier.html.php">Add A New Supplier</a></li>
            </ul>
        </li>

        <!-- dropdown menu to view supplier records -->
        <li class="dropdown">
            <a href="#">View Supplier</a>
            <ul class="dropdown-menu">
                <!-- view all suppliers -->
                <li><a href="viewStock.php">All Suppliers</a></li>
                <!-- view suppliers and delete them -->
                <li><a href="viewDeleteStock.php">View / Delete Supplier</a></li>
                <!-- view and amend supplier details -->
                <li><a href="viewAmendStock.php">Amended Supplier</a></li>
            </ul>
        </li>

        <!-- dropdown menu for managing suppliers -->
        <li class="dropdown">
            <a href="#">Manage</a>
            <ul class="dropdown-menu">
                 <!-- page to delete suppliers -->
                <li><a href="deleteStockItem.php">Delete Supplier</a></li>
                <!-- page to amend supplier details -->
                <li><a href="viewAmendStock.php">Amended Supplier</a></li>
            </ul>
        </li>
    </ul>
</nav>


<!-- container that holds the supplier form -->
<body>
    <div class="page-container"> <div class="card">
            <?php
            session_start();
            if (isset($_SESSION['success_message'])) {
                echo "<div class='success-banner'>";
                echo "<strong>Success!</strong> " . $_SESSION['success_message'];
                echo "</div>";
                unset($_SESSION['success_message']);
            }
            ?>
            <h2>Add A New Supplier</h2>
        <!-- form that sends data to insert_supplier.php using post method -->
        <form id="supplierForm" method="post" action="insert_supplier.php" onsubmit="return confirm('Are you sure you want to insert this supplier record?');">

          <label for="suppliername">Supplier Name</label> <!-- name validation -->
          <input type="text" name="suppliername" id="suppliername" placeholder="Full Name" 
          pattern="[A-Za-z ]{2,30}" title="Name must be 2–30 letters and spaces only"required> <!-- full name no less than 2 no more than 30 characters -->

            <label for="supplierstockcode">Supplier Stock Code</label> <!-- stock code validation -->
            <input type="text" name="supplierstockcode" id="supplierstockcode" placeholder="Stock Code" 
            pattern="\d{2,6}" title="Please enter between 2 and 6 digits only"required> 

            <!-- address validation -->
            <label for="supplieraddress">Address</label>
            <input type="text" name="supplieraddress" id="supplieraddress" 
            placeholder="e.g. Apt 5, 12 Main Street, Carlow, Ireland"
            maxlength="150" 
            pattern="^[A-Za-z0-9\s.,/-]{5,150}$"
            title="Enter a valid address including street, city, and optionally apartment or country."
            required>

            <label for="eircode">Eircode</label> <!-- eircode validation -->
            <input type="text" name="eircode" id="eircode" placeholder="Eircode e.g. Y12 A345"
            pattern="[A-Za-z][0-9]{2}\s?[A-Za-z][0-9]{3}"title="Eircode must be in the format: Letter + 2 numbers, space optional, Letter + 3 numbers (e.g. Y12 A345)"
            required>   <!-- must be like what the placeholder asks for -->

            <!-- email validation, javascript file holds the validation -->
            <label for="supplieremail">Email Address</label>
            <input type="supplieremail" name="supplieremail" id="supplieremail" placeholder="example@email.com" required 
            pattern="^[A-Za-z]{1,40}(@([A-Za-z]+\.)*[A-Za-z]+)\.(com|ie|org|net|co\.uk|edu)$"
            title="Enter a valid email address like example@email.com, example@outlook.ie">

            <!-- website validation, javascript file holds the validation -->
            <label for="websiteaddress">Website Address</label>
            <input type="url" name="websiteaddress" id="websiteaddress" placeholder="http:// or https://website.com" required
            pattern="https?://.+"
            title="Enter a valid website URL starting with http:// or https://">

            <!-- supplier phone number with format validation -->
            <label for="phonenumber">Telephone Number</label>
            <input type="text" name="phonenumber" id="phonenumber" placeholder="08X XXX XXXX"
            pattern="^08\d \d{3} \d{4}$"
            title="Phone number must be in the format 08X XXX XXXX"
            required>

            <!-- buttons for submitting and clearing the form -->
            <div class="button-group">
                <input type="submit" value="Add Supplier">
                <input type="reset" value="Clear">
                </div>
            </form>
        </div>
    </div>
</body>

<!-- page footer-->
<footer>
    <p>&copy; 2026 Spec Savvy. All rights reserved.</p> <!-- text displayed at the bottom/footer -->
</footer>

<script src="script.js"></script> <!-- java script file link to the form -->

</body>
</html>