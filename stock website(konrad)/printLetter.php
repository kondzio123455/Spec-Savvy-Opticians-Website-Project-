<?php
// Name: Konrad Skoczylas
// ID: C00309030

$sale_id = isset($_GET['sale_id']) ? (int)$_GET['sale_id'] : ''; // get sale ID and cast to int

$left  = isset($_GET['left'])  ? htmlspecialchars($_GET['left'])  : ''; // left lens (escaped)
$right = isset($_GET['right']) ? htmlspecialchars($_GET['right']) : ''; // right lens (escaped)
$lens  = isset($_GET['lens'])  ? htmlspecialchars($_GET['lens'])  : ''; // lens material

$uv      = isset($_GET['uv'])      ? ((int)$_GET['uv'] ? 'Yes' : 'No') : 'No'; // convert 1/0 to Yes/No
$scratch = isset($_GET['scratch']) ? ((int)$_GET['scratch'] ? 'Yes' : 'No') : 'No'; // convert 1/0 to Yes/No

$date = date('d/m/Y'); // current date
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Letter</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 60px; } /* basic print styling */
        p { margin: 5px 0; } /* spacing between lines */
        .section { margin-bottom: 20px; } /* spacing between sections */
    </style>
</head>

<body onload="window.print()"> <!-- auto open print dialog -->

<div class="section">
    <p>Spec Savvy,</p> <!-- sender -->
    <p>Main Street,</p>
    <p>Carlow</p>
</div>

<div class="section">
    <p><?php echo $date; ?></p> <!-- print current date -->
</div>

<div class="section">
    <p>Clear View Lens Laboratory,</p> <!-- recipient -->
    <p>Tims house,</p>
    <p>Kilkenny.</p>
</div>

<div class="section">
    <p>Our Reference: <?php echo $sale_id; ?></p> <!-- sale reference -->
</div>

<div class="section">
    <p>Please supply and fit lenses in the enclosed frames, as per the details below:</p>
</div>

<div class="section">
    <p>Left Eye: <?php echo $left; ?></p> <!-- left lens value -->
    <p>Right Eye: <?php echo $right; ?></p> <!-- right lens value -->
    <p>Lens Material: <?php echo ucfirst($lens); ?></p> <!-- capitalise first letter -->
    <p>U-V Filter: <?php echo $uv; ?></p> <!-- UV option -->
    <p>Scratch Coating: <?php echo $scratch; ?></p> <!-- scratch option -->
</div>

<div class="section">
    <p>Yours sincerely,</p>
    <br><br> <!-- space for signature -->
    <p>________________________,</p>
    <p>Optician.</p>
</div>

</body>
</html>