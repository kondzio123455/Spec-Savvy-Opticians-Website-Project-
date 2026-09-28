<?php
require_once 'Includes/header.php';
require_once 'Includes/db.inc.php';
require_once 'Includes/nav.php';

$errors = [];
$success = "";

$first_name = $last_name = $email = $phone = $eircode = $address = $town = $dob = "";

if($_SERVER["REQUEST_METHOD"]==="POST"){
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name  = trim($_POST["last_name"] ?? "");
    $address    = trim($_POST["address"] ?? "");
    $town       = trim($_POST["town"] ?? "");
    $eircode    = trim($_POST["eircode"] ?? "");
    $phone      = trim($_POST["phone"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $dob        = trim($_POST["dob"] ?? "");

    if($first_name==="") $errors[] = "First name is required";
    if($last_name==="")  $errors[] = "Last name is required";

    if($dob === ""){
        $errors[] = "Date of Birth is required";
    } else {
        $dob_date = DateTime::createFromFormat('Y-m-d', $dob);
        $dob_errors = DateTime::getLastErrors();

        if(!$dob_date || ($dob_errors['warning_count'] ?? 0) > 0 || ($dob_errors['error_count'] ?? 0) > 0){
            $errors[] = "Date of Birth is not a valid date.";
        } else {
            $today = new DateTime('today');
            if($dob_date > $today){
                $errors[] = "Date of birth cannot be in the future";
            } else {
                $age = $dob_date->diff($today)->y;
                if($age > 120){
                    $errors[] = "Date of birth must be a reasonable date";
                }
            }
        }
    }

    if($address==="") $errors[] = "Address is required";
    if($town==="")    $errors[] = "Town is required";

    if($eircode!=="" && !preg_match('/^[A-Za-z0-9 ]{7}$/', $eircode)){
        $errors[] = "Eircode must be 7 Characters (letters, digits or space)";
    }

    if($phone==="") $errors[] = "Phone is required";
    if($phone!=="" && !preg_match('/^[0-9]+$/', $phone)){
        $errors[] = "Phone number must contain only digits";
    }

    if($email!=="" && !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Email is not valid";
    }

    if(!$errors){
        $sql="INSERT INTO Customer(first_name, last_name, address, town, eircode, phone, email, date_added, dob)
              VALUES(?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)";

        $stmt = mysqli_prepare($con, $sql);
        if(!$stmt){
            die("Error preparing statement: " . mysqli_error($con));
        }

        mysqli_stmt_bind_param($stmt, "ssssssss",
            $first_name, $last_name, $address, $town, $eircode, $phone, $email, $dob
        );

        if(!mysqli_stmt_execute($stmt)){
            die("Error executing statement: " . mysqli_stmt_error($stmt));
        } else {
           $newId = mysqli_insert_id($con);
            $success = "Customer added successfully. New Customer ID: " . $newId;

            $first_name = $last_name = $email = $phone = $eircode = $address = $town = $dob = "";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!-- ================= HERO SECTION ================= -->
<section class="hero">
    <h1>Add New Customer</h1>
    <p>Create a new customer record</p>
    <div class="cta-buttons">
        <a href="/Opticians/Opticians/Eye Test(Scott)/add_eye_test.html" class="button">Book an eye test</a>
    </div>
</section>

<!-- ================= CONTENT ================= -->
<div class="page-container">
    <div class="card">
        <h2>Customer Details</h2>

        <?php if($success): ?>
            <script>alert('<?php echo htmlspecialchars($success); ?>');</script>
        <?php endif; ?>

        <?php if($errors): ?>
            <p class="muted"><?php echo htmlspecialchars(implode(" ", $errors)); ?></p>
        <?php endif; ?>

        <form method="post" class="card-form"
              onsubmit="return confirm('Are you sure you want to add this customer to the database?');">

            <label>First name <span class="req">*</span></label>
            <input type="text" name="first_name" required value="<?php echo htmlspecialchars($first_name); ?>">

            <label>Last name <span class="req">*</span></label>
            <input type="text" name="last_name" required value="<?php echo htmlspecialchars($last_name); ?>">

            <label>Date Of Birth <span class="req">*</span></label>
            <input type="date" name="dob" required value="<?php echo htmlspecialchars($dob); ?>">

            <label>Address <span class="req">*</span></label>
            <input type="text" name="address" required value="<?php echo htmlspecialchars($address); ?>">

            <label>Town <span class="req">*</span></label>
            <input type="text" name="town" required value="<?php echo htmlspecialchars($town); ?>">

            <label>Eircode <span class="req">*</span></label>
            <input type="text" name="eircode" required value="<?php echo htmlspecialchars($eircode); ?>">

            <label>Email</label>
            <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>">

            <label>Phone <span class="req">*</span></label>
            <input type="text" name="phone" required value="<?php echo htmlspecialchars($phone); ?>">
           
            <div class="button-group">
                <input type="submit" value="Save">
                <input type="reset" value="Clear" onclick="return confirm('Are you sure you want to clear the page? Any entered data will be lost.');">
            </div>

        </form>
    </div>
</div>

<?php require_once 'Includes/footer.php'; ?>