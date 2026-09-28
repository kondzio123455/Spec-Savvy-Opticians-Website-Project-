<!-- Kamile Kacinskaite -->
<!-- C00312390 -->
<!-- Project Contact Lens Sales Screen -->  
<!-- 19/02/26 -->
 
<?php
include 'success.php';
 
$customerID  = mysqli_real_escape_string($conn, $_POST['customer_ID']);
$leftLensID  = mysqli_real_escape_string($conn, $_POST['left_eye_id']);
$rightLensID = mysqli_real_escape_string($conn, $_POST['right_eye_id']);
$months      = (int) $_POST['months'];
 
// not letting months be less than 1 or more than 12 to prevent invalid orders and ensure prescription validity
if ($months < 1)  $months = 1;
if ($months > 12) $months = 12;
 
// perscription check 
// fetch the most recent active eye test for this customer
$etCheck = mysqli_query($conn, "SELECT left_lens, right_lens FROM `Eye Test` WHERE customer_ID = '$customerID' AND Deleted = 0 ORDER BY date_of_test DESC LIMIT 1");
$prescriptionBlocked = false;
if ($etCheck && mysqli_num_rows($etCheck) > 0) 
{
    $etRow      = mysqli_fetch_assoc($etCheck);
    $leftVal    = (float) $etRow['left_lens'];
    $rightVal   = (float) $etRow['right_lens'];
    if ($leftVal < -5 || $leftVal > 5 || $rightVal < -5 || $rightVal > 5) 
    {
        $prescriptionBlocked = true;
    }
}
 
// fetch customer name for the confirmation receipt
$custName = "";
$custRes = mysqli_query($conn, "SELECT first_name, last_name FROM Customer WHERE customer_ID = '$customerID'");
if ($custRes && $row = mysqli_fetch_assoc($custRes)) {
    $custName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
}
 
// fetch lens strengths for the receipt
$leftStrength  = "";
$rightStrength = "";
$leftRes = mysqli_query($conn, "SELECT Lens_Strength, Retail_Price FROM `Contact Lens` WHERE Lens_ID = '$leftLensID'");
if ($leftRes && $lr = mysqli_fetch_assoc($leftRes)) 
{
    $leftStrength  = $lr['Lens_Strength'];
    $leftPrice     = (float)$lr['Retail_Price'];
}
$rightRes = mysqli_query($conn, "SELECT Lens_Strength, Retail_Price FROM `Contact Lens` WHERE Lens_ID = '$rightLensID'");
if ($rightRes && $rr = mysqli_fetch_assoc($rightRes)) 
{
    $rightStrength = $rr['Lens_Strength'];
    $rightPrice    = (float)$rr['Retail_Price'];
}
$totalCost = ($leftPrice + $rightPrice) * $months;
 
if ($prescriptionBlocked) 
{
    $success = false;
    $blockedReason = "prescription";
} 
else 
{
 
    // insert sale record and get the new SaleID for the Sales Item records
    $sqlSale = "INSERT INTO `Sales` (customer_ID, Sale_Date) VALUES ('$customerID', CURDATE())";
    mysqli_query($conn, $sqlSale);
    $saleID = mysqli_insert_id($conn);
 
    $success = ($saleID > 0);
 
    if ($success) 
    {
        // each item is represented as an associative array with 'id' and 'qty' keys
        $items = [
            ['id' => $leftLensID,  'qty' => $months],
            ['id' => $rightLensID, 'qty' => $months],
        ];
 
        foreach ($items as $item) 
        {
            if (!empty($item['id'])) 
            {
                $id  = $item['id'];
                $qty = $item['qty'];
 
                // column names in Sales Item table are SaleID, StockID, Quantity
                mysqli_query($conn, "INSERT INTO `Sales Item` (SaleID, StockID, Quantity) VALUES ('$saleID', '$id', '$qty')");
 
                if (mysqli_error($conn)) { $success = false; }
            }
        }
    }
} // end prescription check
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sale Result - Spec Savvy</title> <!-- page title -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="main-header">
        <div class="logo-area">
            <img src="spec savvy logo.png" alt="Spec Savvy Logo">
        </div>
    </header>
    <!-- main content area -->
    <div class="page-container">
        <div class="card" style="text-align:center;">
            <?php if ($success): ?>
                <div style="font-size:48px; margin-bottom:8px;">✅</div>
                <h2 style="color:#2e7d32; margin-bottom:4px;">Order Confirmed!</h2>
                <p style="color:#888; font-size:13px; margin-bottom:24px;">Thank you — the sale has been recorded successfully.</p>
 
                <!-- receipt card -->
                <div style="
                    background:#f9fafb; border:1px solid #e0e8df;
                    border-radius:12px; padding:24px 28px;
                    text-align:left; margin-bottom:24px;
                ">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:12px; border-bottom:2px solid #e0e0e0;">
                        <span style="font-size:13px; font-weight:700; color:#8B000F; text-transform:uppercase; letter-spacing:0.5px;">Order Receipt</span>
                        <span style="font-size:13px; color:#888;">Sale ID: <strong style="color:#333;">#<?php echo $saleID; ?></strong></span>
                    </div>
 
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f0f0;">
                        <span style="font-size:13px; color:#888; font-weight:600; text-transform:uppercase;">Customer</span>
                        <span style="font-size:14px; color:#333; font-weight:600;"><?php echo $custName; ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f0f0;">
                        <span style="font-size:13px; color:#888; font-weight:600; text-transform:uppercase;">Sale Date</span>
                        <span style="font-size:14px; color:#333; font-weight:600;"><?php echo date('d M Y'); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f0f0;">
                        <span style="font-size:13px; color:#888; font-weight:600; text-transform:uppercase;">Left Eye (<?php echo htmlspecialchars($leftStrength); ?>)</span>
                        <span style="font-size:14px; color:#333; font-weight:600;"><?php echo $months; ?> box(es) — €<?php echo number_format($leftPrice * $months, 2); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f0f0;">
                        <span style="font-size:13px; color:#888; font-weight:600; text-transform:uppercase;">Right Eye (<?php echo htmlspecialchars($rightStrength); ?>)</span>
                        <span style="font-size:14px; color:#333; font-weight:600;"><?php echo $months; ?> box(es) — €<?php echo number_format($rightPrice * $months, 2); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:12px 0 0;">
                        <span style="font-size:14px; font-weight:700; color:#333; text-transform:uppercase;">Total Paid</span>
                        <span style="font-size:20px; font-weight:700; color:#8B000F;">€<?php echo number_format($totalCost, 2); ?></span>
                    </div>
                </div>
                <!-- back to sales button -->
            <?php elseif (isset($blockedReason) && $blockedReason === "prescription"): ?>
                <div style="font-size:48px; margin-bottom:16px;"></div>
                <h2 style="color:#8B000F;">Sale Blocked</h2>
                <p style="color:#555; margin-top:10px;">This customer's prescription is outside the <strong>±5.00</strong> range.<br>Contact lenses cannot be dispensed for prescriptions stronger than this.</p>
            <?php else: ?>
                <div style="font-size:48px; margin-bottom:16px;"></div>
                <h2 style="color:#8B000F;">Something went wrong</h2>
                <p style="color:#555;">Please contact your system administrator.</p>
            <?php endif; ?>
            <div style="margin-top:30px;">
                <a href="ContactLensSales.html.php" style="
                    display:inline-block; padding:12px 28px;
                    background:linear-gradient(135deg,#8B000F,#a80012);
                    color:#fff; border-radius:8px; text-decoration:none;
                    font-weight:600; font-size:14px;
                ">← Back to Sales</a>
            </div>
        </div>
    </div>
</body>
</html>