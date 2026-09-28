<!--Student Name: Scott Cardiff-->
<!--Purpose of Screen: Amend / View an Eye Test for a customer in the database-->
<!--Student ID: C00311728-->
<!--Name of Screen: Amend / View Eye Test-->
<!--Date: 24/03/2026-->

<link rel="stylesheet" href="style.css">

<?php

include 'db.inc.php';
// Includes the database connection

date_default_timezone_set("UTC");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
// Helper function used to safely display values and prevents special characters being treated as HTML.
$message = "";
// Stores success messages
$error = "";
// Stores error messages



//---LOAD CUSTOMERS FOR DROPDOWN--- 

$customers = [];
$custSql = "SELECT customer_ID, first_name, last_name
            FROM `Customer`
            WHERE deleted = 0
            ORDER BY last_name, first_name";
// sql query to get all active customers from the Customer table.

$custRes = mysqli_query($con, $custSql);
// Executes customer list query.

if (!$custRes) {
    die("Customer list query failed: " . mysqli_error($con)); // Error occurred when searching for customer
}

while ($row = mysqli_fetch_assoc($custRes)) {
    $customers[] = $row; // Gets each customer row
}


$selected_customer_ID = $_POST['customer_ID'] ?? '';
// These will store the customer + their eye tests after search
$customer = null;
$eye_tests = [];
$edit_test = null;
// Will hold one specific eye test record when the user clicks Amend and it is edited



// ---- SAVE CHANGES ----

if (isset($_POST['save_changes'])) {
    // Runs when Save Changes button is clicked

    $customer_ID			 = mysqli_real_escape_string($con, $_POST['customer_ID'] ?? '');
    $test_ID 				 = mysqli_real_escape_string($con, $_POST['test_ID'] ?? '');
    $left_lens				 = mysqli_real_escape_string($con, $_POST['left_lens'] ?? '');
    $right_lens 			 = mysqli_real_escape_string($con, $_POST['right_lens'] ?? '');
    $date_of_test 			 = mysqli_real_escape_string($con, $_POST['date_of_test'] ?? '');

    if ($customer_ID === '' || $test_ID === '' || $left_lens === '' || $right_lens === '' || $date_of_test === '') {
        $error = "All fields are required to save changes.";
        // Checks that none of the required fields are empty, if any are missing, show an error message.
    } 
	else {

        // if the eye test record exists
        $checkSql = "SELECT test_ID
                     FROM `Eye Test`
                     WHERE test_ID = '$test_ID'
                       AND customer_ID = '$customer_ID'
                       AND Deleted = 0";

        $checkRes = mysqli_query($con, $checkSql);
        // Executes the query

        if (!$checkRes) {
            $error = "Check query failed: " . mysqli_error($con);
            // If the check query fails, database error.
        } 
		elseif (mysqli_num_rows($checkRes) == 0) {
            $error = "Eye test not found.";
            // If it does not match, error.
        } 
		else {
            $updateSql = "UPDATE `Eye Test`
                          SET left_lens = '$left_lens',
                              right_lens = '$right_lens',
                              date_of_test = '$date_of_test'
                          WHERE test_ID = '$test_ID'
                            AND customer_ID = '$customer_ID'
                            AND Deleted = 0";

            	if (mysqli_query($con, $updateSql)) {
                $message = "Eye test ID $test_ID has been updated.";
                // Updating the message variable
            	}
				else {
                $error = "Update failed: " . mysqli_error($con);
            	}
       		 }
    	}
}



// -----LOAD SELECTED TEST FOR AMEND FORM ------

if (isset($_POST['amend_eye_test'])) {
    // Runs only when the Amend button is clicked
    $customer_ID 		= mysqli_real_escape_string($con, $_POST['customer_ID'] ?? '');
    $test_ID 			= mysqli_real_escape_string($con, $_POST['test_ID'] ?? '');

    if ($customer_ID != '' && $test_ID != '') {
        // Continues if both IDs are present
        $editSql = "SELECT test_ID, left_lens, right_lens, date_of_test
                    FROM `Eye Test`
                    WHERE customer_ID = '$customer_ID'
                      AND test_ID = '$test_ID'
                      AND deleted = 0
                    LIMIT 1";

        $editRes = mysqli_query($con, $editSql);

        if ($editRes) {
            $edit_test = mysqli_fetch_assoc($editRes);
        }
        // If successful, stores that eye test record in $edit_test, filled into amend form
    }
}



//------- LOAD CUSTOMER DETAILS AND EYE TEST HISTORY ------------

if (isset($_POST['search_customer']) || isset($_POST['amend_eye_test']) || isset($_POST['save_changes'])) {

    if ($selected_customer_ID === '') {
        $error = $error ?: "Please select a customer.";
        // If no customer was selected, show an error.
        // The ?: keeps any earlier error message if one exist
    } else {
        $customer_ID = mysqli_real_escape_string($con, $selected_customer_ID);
       

        // Customer confirmation details printed to the screen to ensure the customer is correct and the details are correct.
        $customerQuery = "SELECT first_name, last_name, address, dob
                          FROM `Customer`
                          WHERE customer_ID = '$customer_ID' AND deleted = 0";

        $customerResult = mysqli_query($con, $customerQuery);

        if (!$customerResult) {
            $error = "Customer query failed: " . mysqli_error($con);
        }
		else {
            $customer = mysqli_fetch_assoc($customerResult);
            // Fetches the customer row and stores it in $customer variable and all details are eventually shown

            if (!$customer) {
                $error = $error ?: "Customer not found.";
                // If no matching customer exists, error is stored
            } 
			else {

                // Load ALL eye tests for this customer (not deleted)
                $eyeSql = "SELECT test_ID, left_lens, right_lens, date_of_test
                           FROM `Eye Test`
                           WHERE customer_ID = '$customer_ID' AND deleted = 0
                           ORDER BY date_of_test DESC, test_ID DESC";

                $eyeRes = mysqli_query($con, $eyeSql);
           

                if (!$eyeRes) {
                    $error = "Eye test query failed: " . mysqli_error($con);
                } 
				else {
                    // This loop gets every eye test row returned from SQL
                    // and stores it in the $eye_tests array.
                    // Later we use foreach($eye_tests as $t) to display them in a table.
                    while ($row = mysqli_fetch_assoc($eyeRes)) {
                        $eye_tests[] = $row;
                    }
                }
            }
        }
    }
}

mysqli_close($con);
// Closes the db connection

?>


<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Amend / View Eye Test</title>
</head>
	
<body>
    <header class="main-header">
        <div class="logo-area">
            <!-- Container for the logo image. -->
            <img src="SpecSavvyLogo.png" alt="Spec Savvy Logo">
        </div>
    </header>

    <?php require_once 'Includes/nav.php'; ?>
	
    <div class="page-container">
        <div class="card">
            <!-- Card-style box that holds the form and table contents for amending -->
            <h2>Amend / View an Eye Test</h2>

            <?php if ($message): ?>
                <!-- If there is a success message, show it in green. -->
                <p style="color:green;">
                    <b><?php echo h($message); ?></b>
                </p>
            <?php endif; ?>

            <?php if ($error): ?>
                <!-- If there is an error message, show it in red. -->
                <p style="color:red;">
                    <b><?php echo h($error); ?></b>
                </p>
            <?php endif; ?>


			
<!-- -- ------- SEARCH FORM ------------ -->

            <form action="amend_eye_test.php" method="POST" id="searchForm">
                
                <label><b>Select Customer (Full Name):</b></label><br>      
                <select name="customer_ID" id="customer_ID" required>
                    <option value="">-- choose customer --</option>
                   
                    <?php foreach ($customers as $c): ?>
                        <!-- Loops through the customers array -->

                        <?php
                        $id = $c['customer_ID'];                
                        $fullName = $c['first_name'] . " " . $c['last_name'];
                        ?>

                        <option value="<?php echo h($id); ?>"
                            <?php echo ((string)$selected_customer_ID === (string)$id) ? 'selected' : ''; ?>>
                            <!-- Creates one dropdown option and keeps the same customer selected after the form reloads. -->

                            <?php echo h($fullName); ?>
                            <!-- Prints full name -->
                        </option>
                    <?php endforeach; ?>
                </select>
		
                <br><br>
                <input type="submit" value="Search" name="search_customer">
                <!-- Search button. -->
            </form>
            <hr>
			
			

 <!-- -------CUSTOMER DETAILS AND EYE TEST HISTORY -------- -->

            <?php if ($customer): ?>
                <!-- Only show the customer details and eye test section if a customer was actually found in the db -->

                <h3>Customer Confirmation</h3>
                Name: <?php echo h($customer['first_name'] . " " . $customer['last_name']); ?><br>            
                Address: <?php echo h($customer['address']); ?><br>
                Date of Birth: <?php echo h(date("d/m/Y", strtotime($customer['dob']))); ?><br><br>

                <h3>Eye Test History (Newest First)</h3>

                <?php if (count($eye_tests) === 0): ?>
                    No eye tests found for this customer.<br><br>
                <?php else: ?>
                    <table border="1" cellpadding="8" cellspacing="0">
                        <!-- Table displaying all eye test records for the selected customer. -->

                        <tr>
                            <th>Eye Test ID (not editable)</th>
                            <th>Left Eye</th>
                            <th>Right Eye</th>
                            <th>Date of Eye Test</th>
                            <th>Amend</th>
                        </tr>
                        <!-- Table headings. -->

                        <?php foreach ($eye_tests as $t): ?>
                            <!-- Loops through each eye test row in $eye_tests. -->

                            <tr>
                                <td><?php echo h($t['test_ID']); ?></td>
                                <td><?php echo h($t['left_lens']); ?></td>
                                <td><?php echo h($t['right_lens']); ?></td>
                                <td><?php echo h(date("d/m/Y", strtotime($t['date_of_test']))); ?></td>
                                <td>
                                   
                                    <form method="POST" action="amend_eye_test.php" style="margin:0;">
                                        <!-- Small form inside the row so the Amend button can actually send this test ID --->

                                        <input type="hidden" name="customer_ID" value="<?php echo h($selected_customer_ID); ?>">
                                        <input type="hidden" name="test_ID" value="<?php echo h($t['test_ID']); ?>">
                                        <button type="submit" name="amend_eye_test">Amend</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>


			
 <!-- -------- AMEND FORM --------- -->
                <?php if ($edit_test): ?>
                   
                    <hr>
                    <h3>Amend Selected Eye Test</h3>

                    <form method="POST" action="amend_eye_test.php" id="amendForm">               
                        <input type="hidden" name="customer_ID" value="<?php echo h($selected_customer_ID); ?>">                      
                        <input type="hidden" name="test_ID" value="<?php echo h($edit_test['test_ID']); ?>">

                        <p>
                            <b>Eye Test ID:</b> <?php echo h($edit_test['test_ID']); ?> (not editable)
                        </p>

                        <label>Left Lens Measurement:</label><br>
                        <input type="text" name="left_lens" id="left_lens"
                               value="<?php echo h($edit_test['left_lens']); ?>" required>
                        <br><br>

                        <label>Right Lens Measurement:</label><br>
                        <input type="text" name="right_lens" id="right_lens"
                               value="<?php echo h($edit_test['right_lens']); ?>" required>
                        <br><br>

                        <label>Date of Eye Test:</label><br>

                        <?php
                        $dateValue = date('Y-m-d', strtotime($edit_test['date_of_test']));
                        ?>

                        <input type="date" name="date_of_test" id="date_of_test"
                               value="<?php echo h($dateValue); ?>" required>
                        <br><br>

                        <button type="submit" name="save_changes" id="saveBtn">Save Changes</button>
                        <!-- Save button sends the edited values back to PHP -->
                    </form>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>

    <script src="amend_eye_test.js"></script>

</body>
</html>