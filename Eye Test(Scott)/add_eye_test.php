<!--Student Name: Scott Cardiff--> 
<!--Purpose of Screen: To add a new Eye Test for a customer into the database-->
<!--Student ID: C00311728-->
<!--Name of Screen: Add Eye Test-->
<!--Date: 23/03/2026-->

<link rel="stylesheet" href="style.css">
<?php

include 'db.inc.php';
// Includes the database connection file.
date_default_timezone_set("UTC");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
    // Stops the script if no connection
}

function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
// Creates a helper function called h().
// displays text in HTML by converting special characters.
// Selecting customer from listbox only woerks because of this funtion. If not included, customer cannot be selected

$message = "";
// to store a success or status message later
$customer = null;
$latest = null;
// Loading all of the customers in the Customer Table to be viewed in the dropdown menu 
// which will load their previous details before an eye test can be submitted
// it will show their most recent eye test history and the date produced

$customers = [];
// Creatign an array to store all customer records for dropdown list

$custListSql = "SELECT customer_ID, first_name, last_name
                FROM `Customer`
                WHERE deleted = 0
                ORDER BY last_name, first_name";
// SQL query 
// Only customers where deleted = 0 are shown
// Results are sorted alphabetically

$custListResult = mysqli_query($con, $custListSql);
// Sending the query to the database

if (!$custListResult) {
    die("Customer list query failed: " . mysqli_error($con));
    // If error present, program stops.
}

while ($row = mysqli_fetch_assoc($custListResult)) {
    $customers[] = $row;
}
// Loading the details of the customer if submittion is clicked 
// It will either show whether the details from their most recent eye test is in the database or no record found

$selected_customer_ID = $_POST['customer_ID'] ?? '';
// Gets the selected customer from the form
// If nothing was submitted, uses the empty string

if ((isset($_POST['search_customer']) || isset($_POST['add_eye_test'])) && $selected_customer_ID !== '') {
// Runs when the button is clicked and customer selected

    $customer_ID = mysqli_real_escape_string($con, $selected_customer_ID);
    // This is the customer sql query confrimation showing their full name and adderess and date of birth from the table in db
    $customerQuery = "SELECT first_name, last_name, address, dob
                      FROM `Customer`
                      WHERE customer_ID = '$customer_ID'  AND deleted = 0";
   

    $customerResult = mysqli_query($con, $customerQuery);
    // Executes the customer details query
    if (!$customerResult) {
        die("Customer query failed: " . mysqli_error($con));
    }
    $customer = mysqli_fetch_assoc($customerResult);
    // Fetches the selected customer's row as an associative array.

    if ($customer) {
    // If a valid customer was found

        // Most recent eye test if the customer has any recently and if their deleted = 0 and is still active cusotmer
		// Looks in the Eye Test table to see if a histor of an eye test is present
        $latestQuery = "SELECT left_lens, right_lens, date_of_test
                        FROM `Eye Test`
                        WHERE customer_ID = '$customer_ID' AND Deleted = 0
                        ORDER BY date_of_test DESC, test_ID DESC
                        LIMIT 1";

        $latestResult = mysqli_query($con, $latestQuery);
      	// Exxctuting the latest query

        if (!$latestResult) {
            die("Latest test query failed: " . mysqli_error($con));      
        }
		
        $latest = mysqli_fetch_assoc($latestResult);
        // Stores the latest eye test record in $latest
    }
}

    // When the submittion is clicked, the new eye test is aadded to the database
if (isset($_POST['add_eye_test'])) {
	
    $customer_ID               = mysqli_real_escape_string($con, $_POST['customer_ID']);
    $left_lens                 = mysqli_real_escape_string($con, $_POST['left_lens']);
    $right_lens                = mysqli_real_escape_string($con, $_POST['right_lens']);
    $date_of_test              = mysqli_real_escape_string($con, $_POST['date_of_test']);
 
    // confirmation printout of the details that were beofre submitting to the database, name, new eye measurements and date of the test etc
    echo "<h3>Confirm Details</h3>";
    echo "Customer ID: "    . h($customer_ID) . "<br>";
    // Displays the customer ID submitted
    echo "Left Lens: "      . h($left_lens) . "<br>";
    echo "Right Lens: "     . h($right_lens) . "<br>";
    echo "Date of Test: "   . h(date("d/m/Y", strtotime($date_of_test))) . "<br><br>";
 

    $sql = "INSERT INTO `Eye Test` (customer_ID, left_lens, right_lens, date_of_test, Deleted)
            VALUES ('$customer_ID', '$left_lens', '$right_lens', '$date_of_test', 0)";
    // sql INSERT query to add a new record to the Eye Test table.
   

    if (mysqli_query($con, $sql)) {
        $message = "Eye test has been added.";
        // Saves a success message.
    } 
	else {
    // If the INSERT fail
        die("Insert failed: " . mysqli_error($con));
    }

    // Refresh most recent test after inserting a new eye test, so basically replaces their most recent eye test submittion. LIMIT 1 only shows latest result
    $latestQuery = "SELECT left_lens, right_lens, date_of_test
                    FROM `Eye Test`
                    WHERE customer_ID = '$customer_ID' AND Deleted = 0
                    ORDER BY date_of_test DESC, test_ID DESC
                    LIMIT 1";
 
    $latestResult = mysqli_query($con, $latestQuery);
    $latest = mysqli_fetch_assoc($latestResult);
  
}
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Eye Test</title>    
</head>
	
<body>       
<header class="main-header">
    <div class="logo-area">
        <!-- Container for the company logo. -->        
        <img src="SpecSavvyLogo.png" alt="Spec Savvy Logo">      
    </div>
</header>

<?php require_once 'Includes/nav.php'; ?>
<!-- Includes the navigation menu file once, hence why its require_once. -->
<div class="page-container"> 
    <div class="card">  
        <!-- Card-style box used to hold content -->
        <h2>Add Eye Test</h2>
      
        <form action="add_eye_test.php" method="POST" id="eyeTestForm">
            <!-- Form sends data to add_eye_test.php using POST  -->
            <!-- Customer selection by their fulll name from the Custoemr table -->
            <label><b>Select Customer (Full Name):</b></label><br>
    
            <select name="customer_ID" id="customer_ID" required>            
                <option value="">-- choose customer --</option>             
                <?php foreach ($customers as $c): ?>
                    <!-- Loops through every customer loaded from the database. -->

                    <?php
                    $id = $c['customer_ID'];
                    // Stores the current customer's ID.
                    $fullName = $c['first_name'] . " " . $c['last_name'];
                    // Creates the customer's full name on the listbox dropdown na dthen further down sets the label
                    $label = $fullName;            
                    ?>

                    <option value="<?php echo h($id); ?>"							
                        <?php echo ((string)$selected_customer_ID === (string)$id) ? 'selected' : ''; ?>>
                        <!-- Creates one dropdown option. -->
                           
                        <?php echo h($label); ?>
                        <!--Display fukll name of customer -->
                    </option>
                <?php endforeach; ?>
            </select>

            <br><br>
            <input type="submit" value="Search" name="search_customer">    
            <br><br>

            <?php if ($customer): ?>           
                <h3>Customer Confirmation</h3>           

                Name: <?php echo h($customer['first_name'] . " " . $customer['last_name']); ?><br>              
                Address: <?php echo h($customer['address']); ?><br>            
                Date of Birth: <?php echo h(date("d/m/Y", strtotime($customer['dob']))); ?><br><br>         

                <h3>Most Recent Eye Test (if any)</h3>        

                <?php if ($latest): ?>
                    <!-- If a previous eye test exists... -->

                    Left Eye: <?php echo h($latest['left_lens']); ?><br>
                    Right Eye: <?php echo h($latest['right_lens']); ?><br>
                    Date: <?php echo h(date("d/m/Y", strtotime($latest['date_of_test']))); ?><br><br>
                
                <?php else: ?>
                    <!-- If no eye test record exists in db -->

                    No previous eye tests found.<br><br>
                    <!-- Message shown -->
                <?php endif; ?>

                <hr>
   
                <input type="text" name="left_lens" id="left_lens" placeholder="Left Lens" required>        
                <br>
                <input type="text" name="right_lens" id="right_lens" placeholder="Right Lens" required>             
                <br>
                <input type="date" name="date_of_test" id="date_of_test"
                    value="<?php echo date('Y-m-d'); ?>" required>              
                <br><br>
			
                <div class="button-group">                
                    <input type="submit" value="Submit" name="add_eye_test">             
                    <input type="reset" value="Clear">             
                </div>

            <?php else: ?>
                <!-- If no customer has been searched yet -->
                <p><i>Select a customer and click Search to view details before adding a new eye test.</i></p>               
            <?php endif; ?>

        </form>
     </div>
</div>
           
<script src="script.js"></script>

</body>
</html>

<?php mysqli_close($con); ?>
<!-- Closes the database connection -->