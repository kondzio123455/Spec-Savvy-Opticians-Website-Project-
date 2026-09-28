<!--Student Name: Scott Cardiff-->
<!--Purpose of Screen: Delete an Eye Test for a customer (HTML+PHP in one file)-->
<!--Student ID: C00311728-->
<!--Name of Screen: Delete Eye Test-->
<!--Date: 24/02/2026-->

<link rel="stylesheet" href="style.css">
<?php
include 'db.inc.php';
date_default_timezone_set("UTC");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// if any Spectacles row exists for test_ID, do NOT delete
$spectaclesTable = "Spectacles";
$spectaclesTestIdCol = "test_ID";


function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$message = "";
$error = "";

// This is the customer dropdown menu just like from the add eye test menu where it showcases EACH customer in Customer SQL Table
$customers = [];
$custSql = "SELECT customer_ID, first_name, last_name
            FROM `Customer`        
            ORDER BY last_name, first_name AND deleted = 0";

$custRes = mysqli_query($con, $custSql);
if (!$custRes) {
    die("Customer list query failed: " . mysqli_error($con));
}
while ($row = mysqli_fetch_assoc($custRes)) {
    $customers[] = $row;
}


// When the custoemr is selected then the results show up for what record they have in the database for eye test
$selected_customer_ID = $_POST['customer_ID'] ?? '';
$customer = null;
$eye_tests = [];

// This is the delete section where a custoemr will be flagged for deletion marked as 1 instead of 0
if (isset($_POST['delete_eye_test'])) {

    $customer_ID                   = mysqli_real_escape_string($con, $_POST['customer_ID'] ?? '');
    $test_ID                       = mysqli_real_escape_string($con, $_POST['test_ID'] ?? '');

    if ($customer_ID == '' || $test_ID == '') {
        $error = "Missing customer or eye test id."; // This is if theres no report of an acrtual customer or an eye test id then the error variable will fill  and echo to the screen
    } 
	
	else {

        // So now this time we jump  to the spectacles table  for the note, if spectacles are on order then the
        // eye test cannot be deleted becasue the spectacles are on the way and too late to cancel
        $spectaclesCount = 0;

        $checkSql = "SELECT COUNT(*) AS cnt
                     FROM `$spectaclesTable`
                     WHERE `$spectaclesTestIdCol` = '$test_ID'";

        $checkRes = mysqli_query($con, $checkSql);

        if (!$checkRes) {
            $error = "Could not check Spectacles table. (" . mysqli_error($con) . ")";
        } 
		else {
            $row = mysqli_fetch_assoc($checkRes);
            $spectaclesCount = (int)($row['cnt'] ?? 0);
        }

        // If the spectacles exist in the database from the spectacles table then the eye test record cannot be deleted
        if ($error == "" && $spectaclesCount > 0) {
            $error = "Spectacles are on order for these eye test details. Eye test was NOT deleted.";
        }

        // This is flagging for deletion
        if ($error == "") {
            $delSql = "UPDATE `Eye Test`
                       SET deleted = 1
                       WHERE test_ID = '$test_ID' AND customer_ID = '$customer_ID'";

            if (mysqli_query($con, $delSql)) {
                if (mysqli_affected_rows($con) > 0) {
                    $message = "Deletion complete: eye test ID $test_ID has been flagged for deletion.";
                }
				else {
                    $error = "No matching eye test found (or already deleted).";
                }
            } 
			else {
                $error = "Delete failed: " . mysqli_error($con);
            }
        }

      
    }
}

// ------ SEARCH / LOAD CUSTOMER -------
if (isset($_POST['search_customer']) || isset($_POST['delete_eye_test'])) {

    if ($selected_customer_ID === '') {
        $error = $error ?: "Please select a customer.";
    } else {
        $customer_ID = mysqli_real_escape_string($con, $selected_customer_ID);

        // Customer details
        $customerQuery = "SELECT first_name, last_name, address, dob
                          FROM `Customer`
                          WHERE customer_ID = '$customer_ID'";
		
        $customerResult = mysqli_query($con, $customerQuery);

        if (!$customerResult) {
            $error = "Customer query failed: " . mysqli_error($con);
        } 
		else {
            $customer = mysqli_fetch_assoc($customerResult);

            if (!$customer) {
                $error = $error ?: "Customer not found.";
            } 
			else {
                // Eye test history (reverse chronological), not deleted
                $eyeSql = "SELECT test_ID, left_lens, right_lens, date_of_test
                           FROM `Eye Test`
                           WHERE customer_ID = '$customer_ID' AND deleted = 0
                           ORDER BY date_of_test DESC, test_ID DESC";
                $eyeRes = mysqli_query($con, $eyeSql);

                if (!$eyeRes) {
                    $error = "Eye test query failed: " . mysqli_error($con);
                } 
				else {
                    while ($row = mysqli_fetch_assoc($eyeRes)) {
                        $eye_tests[] = $row;
                    }
                }
            }
        }
    }
}

mysqli_close($con);
?>




<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delete Eye Test</title>
   
</head>
<body>
        <!-- header section that shows the company logo -->
<header class="main-header">
    <div class="logo-area">
                         <!-- logo image for the website -->
        <img src="SpecSavvyLogo.png" alt="Spec Savvy Logo">
    </div>
</header>

<?php require_once 'Includes/nav.php'; ?>

<div class="page-container">
    <div class="card">  <!-- card style box for the form -->
<h2>Delete Eye Test</h2>

<?php if ($message): ?>
    <p style="color:green;"><b><?php echo h($message); ?></b></p>
<?php endif; ?>

<?php if ($error): ?>
    <p style="color:red;"><b><?php echo h($error); ?></b></p>
<?php endif; ?>

<!-- SEARCH FORM ) -->
<form action="delete_eye_test.php" method="POST" id="searchForm">
    <label><b>Select Customer (Full Name):</b></label><br>

    <select name="customer_ID" id="customer_ID" required>
        <option value="">-- choose customer --</option>
        <?php foreach ($customers as $c): ?>
            <?php
                $id = $c['customer_ID'];
                $fullName = $c['first_name'] . " " . $c['last_name'];
            ?>
		
            <option value="<?php echo h($id); ?>"
                <?php echo ((string)$selected_customer_ID === (string)$id) ? 'selected' : ''; ?>>
                <?php echo h($fullName); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>
    <input type="submit" value="Search" name="search_customer">
</form>

<hr>

<?php if ($customer): ?>
    <h3>Customer Confirmation</h3>
    Name: <?php echo h($customer['first_name'] . " " . $customer['last_name']); ?><br>
		
    Address: <?php echo h($customer['address']); ?><br>
		
    Date of Birth: <?php echo h(date("d/m/Y", strtotime($customer['dob']))); ?><br><br>

		
    <h3>Eye Test History (Newest First)</h3>

    <?php if (count($eye_tests) === 0): ?>
        No eye tests found for this customer.<br><br>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>Eye Test ID</th>
                <th>Left Eye</th>
                <th>Right Eye</th>
                <th>Date of Eye Test</th>
                <th>Delete</th>
            </tr>

            <?php foreach ($eye_tests as $t): ?>
                <tr>
                    <td><?php echo h($t['test_ID']); ?></td>
                    <td><?php echo h($t['left_lens']); ?></td>
                    <td><?php echo h($t['right_lens']); ?></td>
                    <td><?php echo h(date("d/m/Y", strtotime($t['date_of_test']))); ?></td>
                    <td>
                        <!-- DELETE FORM (separate, no nesting) -->
                        <form method="POST" action="delete_eye_test.php" class="deleteForm" style="margin:0;">
                            <input type="hidden" name="customer_ID" value="<?php echo h($selected_customer_ID); ?>">
                            <input type="hidden" name="test_ID" value="<?php echo h($t['test_ID']); ?>">
                            <button type="submit" name="delete_eye_test">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php endif; ?>
     </div>
</div>

<script src="delete_eye_test.js"></script>
</body>
</html>