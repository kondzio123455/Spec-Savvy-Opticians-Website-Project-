<!-- Kamile Kacinskaite -->
<!-- C00312390 -->
<!-- Project Contact Lens Sales Screen -->
<!-- This form is used when a customer wishes to purchase a supply of disposable contact lenses -->
<!-- 19/02/26 -->
 
<?php
include 'success.php'; // this file contains the $conn variable for database connection
 
// selecting the customers using query and storing in array for dropdown
$customers = [];
$custRes = mysqli_query($conn, "SELECT customer_ID, first_name, last_name, dob, address FROM Customer WHERE deleted = 0 ORDER BY first_name");
if ($custRes) {
    while ($r = mysqli_fetch_assoc($custRes)) {
        $customers[] = $r;
    }
}
 
// eye test selection for each customer with array 
$eyeTestData = [];
$etRes = mysqli_query($conn, "SELECT test_ID, left_lens, right_lens, date_of_test, customer_ID, Suitable FROM `Eye Test` WHERE Deleted = 0 ORDER BY date_of_test DESC");
if ($etRes) {
    while ($er = mysqli_fetch_assoc($etRes)) {
        $cid = $er['customer_ID'];
        if (!isset($eyeTestData[$cid])) {
            $eyeTestData[$cid] = $er;
        }
    }
}
 
// contact lens selection for dropdowns with sql query and storing in array
$lenses = [];
$lensRes = mysqli_query($conn, "SELECT Lens_ID, Lens_Strength, Retail_Price FROM `Contact Lens` ORDER BY Lens_Strength = 0");
if ($lensRes) {
    while ($lr = mysqli_fetch_assoc($lensRes)) {
        $lenses[] = $lr;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Lens Sales - Spec Savvy</title> <!-- page title -->
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
 
    <header class="main-header">
        <div class="logo-area">
            <img src="spec savvy logo.png" alt="Spec Savvy Logo">
        </div>
    </header>
	<?php require_once 'Includes/nav.php'; // Include navigation bar ?>
 
    <!-- eye  test defined before the form so that it can be used in the form -->
    <script>
        var eyeTests = <?php echo json_encode($eyeTestData); ?>;
    </script>
 
    <div class="page-container">
        <div class="card">
            <h2>New Sale</h2>
            <form action="ProcessLens.php" method="POST" id="saleForm">
 
                <!-- selected customer -->
                <fieldset class="form-section">
                    <!-- &amp; is used to display the & symbol in HTML -->
                    <legend class="section-label">Step 1: Select &amp; Confirm Customer</legend>
 
                    <label for="customerSelect">Customer</label>
                    <select id="customerSelect" name="customer_ID" onchange="loadCustomerDetails()" required>
                        <option value="">-- Select Customer --</option>
                        <!-- loop through customers to create dropdown option -->
                        <?php foreach ($customers as $c): ?>
                            <!-- using data attributes to store address and dob for display when customer is selected -->
                            <option value="<?php echo (int)$c['customer_ID']; ?>" 
                                    data-address="<?php echo htmlspecialchars($c['address'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-dob="<?php echo htmlspecialchars($c['dob'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name'], ENT_QUOTES, 'UTF-8'); ?>
                                <!-- showing customer ID in brackets for clarity cast to int for safety -->
                                (ID: <?php echo (int)$c['customer_ID']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
 
                    <!-- display customer details after selection -->
                    <div class="info-box">
                        <div class="info-row">
                            <span class="info-label">Address</span>
                            <span class="info-value" id="dispAddress">—</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date of Birth</span>
                            <span class="info-value" id="dispDOB">—</span>
                        </div>
                    </div>
                </fieldset>
 
                <!-- eye test information -->
                <fieldset class="form-section">
                    <legend class="section-label">Step 2: Most Recent Eye Test</legend>
 
                    <div class="info-box">
                        <div class="info-row">
                            <span class="info-label">Left Eye</span>
                            <span class="info-value" id="dispLeft">—</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Right Eye</span>
                            <span class="info-value" id="dispRight">—</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Test Date</span>
                            <span class="info-value" id="dispTestDate">—</span>
                        </div>
                        <div class="info-row" id="suitableRow" style="display:none;">
                            <span class="info-label">Suitable for Lenses</span>
                            <span class="info-value" id="dispSuitable">—</span>
                        </div>
                    </div>
                </fieldset>
 
                <!-- lens strength selection -->
                <fieldset class="form-section">
                    <legend class="section-label">Step 3: Select Lens Strengths</legend>
 
                    <!-- if no lenses found in database show message instead of dropdowns -->
                    <?php if (empty($lenses)): ?>
                        <p style="color:#8B000F;font-size:13px;margin-top:10px;">
                             No lenses found in Contact Lens table.
                        </p>
                    <?php else: ?>
                    <div class="two-col">
                        <div>
                            <!-- loop through lenses to create dropdown options with data-price attribute for use in total calculation -->
                            <label for="left_eye_id">Left Eye Strength</label>
                            <select id="left_eye_id" name="left_eye_id" onchange="calculateTotal()" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($lenses as $l): ?>
                                    <option value="<?php echo (int)$l['Lens_ID']; ?>"
                                            data-price="<?php echo number_format((float)$l['Retail_Price'], 2, '.', ''); ?>">
                                        <?php echo htmlspecialchars($l['Lens_Strength']); ?>
                                        (€<?php echo number_format((float)$l['Retail_Price'], 2); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <!-- loop through lenses to create dropdown options with data-price attribute for use in total calculation -->
                            <label for="right_eye_id">Right Eye Strength</label>
                            <select id="right_eye_id" name="right_eye_id" onchange="calculateTotal()" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($lenses as $l): ?>
                                    <option value="<?php echo (int)$l['Lens_ID']; ?>"
                                            data-price="<?php echo number_format((float)$l['Retail_Price'], 2, '.', ''); ?>">
                                        <?php echo htmlspecialchars($l['Lens_Strength']); ?>
                                        (€<?php echo number_format((float)$l['Retail_Price'], 2); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>
                </fieldset>
 
                <!-- months supply selection -->
                <fieldset class="form-section">
                    <legend class="section-label">Step 4: Months Supply (max 12)</legend>
 
                    <!-- custom month picker with plus and minus buttons -->
                    <div class="months-picker">
                        <button type="button" class="month-btn" onclick="changeMonths(-1)">−</button>
                        <span id="monthsDisplay">1</span>
                        <button type="button" class="month-btn" onclick="changeMonths(1)">+</button>
                        <span class="months-label">month(s)</span>
                    </div>
                    <input type="hidden" name="months" id="monthsInput" value="1">
                    <p class="note">Maximum 12 months — a new eye test is required annually.</p>
                </fieldset>
 
                <!-- total calculation -->
                <div class="total-box">
                    <span class="total-label">Total Cost</span>
                    <span class="total-amount">€<span id="totalDisplay">0.00</span></span>
                </div>
 
                <p class="note" style="margin-bottom:20px;">Payment is required immediately. No credit given.</p>
 
                <!-- hidden div for prescription block message -->
                <div id="prescriptionWarning" style="
                    display:none;
                    background:#fff0f1;
                    border:1.5px solid #8B000F;
                    border-radius:10px;
                    padding:14px 18px;
                    color:#8B000F;
                    font-size:13px;
                    font-weight:600;
                    margin-bottom:18px;
                    text-align:center;
                "></div>
 
                <!-- submit and reset buttons -->
                <div class="button-group">
                    <input type="submit" value="Complete Sale">
                    <input type="button" value="Clear Form" onclick="document.getElementById('saleForm').reset(); resetDisplay();">
                </div>
 
            </form>
        </div>
    </div>
 
    <script src="script.js"></script>
</body>
</html>