<?php
require_once 'Includes/header.php';
require_once 'Includes/db.inc.php';
require_once 'Includes/nav.php';

$locked = true;      // controls whether inputs are editable
$found  = false;     // controls whether amend form is shown

$errors  = [];
$success = "";

// Editable fields
$customer_id = "";
$first_name = "";
$last_name  = "";
$email      = "";
$phone      = "";
$eircode    = "";
$address    = "";
$town       = "";
$dob        = "";

// Search inputs + original lookup key (used for update)
$search_email = "";
$search_phone = "";
$search_field = "";
$search_value = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){

    // UPDATE
    if(isset($_POST["update"])){

        $search_field = $_POST["search_field"] ?? "";
        $search_value = trim($_POST["search_value"] ?? "");
        
        $customer_id= trim($_POST["customer_id"] ?? "");
        $first_name = trim($_POST["first_name"] ?? "");
        $last_name  = trim($_POST["last_name"] ?? "");
        $address    = trim($_POST["address"] ?? "");
        $town       = trim($_POST["town"] ?? "");
        $eircode    = trim($_POST["eircode"] ?? "");
        $phone      = trim($_POST["phone"] ?? "");
        $email      = trim($_POST["email"] ?? "");
        $dob        = trim($_POST["dob"] ?? "");

        // keep form visible after update, but lock again
        $found  = true;
        $locked = true;

        // Validate required fields
        if($first_name==="") $errors[]="First name is required";
        if($last_name==="")  $errors[]="Last name is required";
        if($dob==="")        $errors[]="Date of Birth is required";
        if($address==="")    $errors[]="Address is required";
        if($town==="")       $errors[]="Town is required";
        if($eircode==="")    $errors[]="Eircode is required";
        if($phone==="")      $errors[]="Phone is required";

        // Validate email if provided
        if($email!=="" && !filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors[]="Email is not valid";
        }

        // Validate phone format if provided
        if($phone!=="" && !preg_match('/^\d{7,15}$/', $phone)){
            $errors[]="Phone number is not valid (7 to 15 digits).";
        }

        // Validate search key
        if(!in_array($search_field, ["email", "phone"], true) || $search_value===""){
            $errors[]="Missing Email or Phone. Please search again.";
        }

        // Update
        if(!$errors){
            $where = ($search_field === "email") ? "email" : "phone";

            $sql = "UPDATE Customer
                    SET first_name=?, last_name=?, address=?, town=?, eircode=?, phone=?, email=?, dob=?
                    WHERE $where = ?
                    LIMIT 1";

            $stmt = mysqli_prepare($con, $sql);
            if(!$stmt){
                die("Error preparing statement: " . mysqli_error($con));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "sssssssss",
                $first_name, $last_name, $address, $town, $eircode, $phone, $email, $dob, $search_value
            );

            mysqli_stmt_execute($stmt);

            if(mysqli_stmt_affected_rows($stmt) > 0) {
                $success = "Customer updated successfully";
            } else {
                $errors[] = "No changes were made or customer not found";
            }

            mysqli_stmt_close($stmt);
        }

    } else {
        // SEARCH
        $search_email = trim($_POST["search_email"] ?? "");
        $search_phone = trim($_POST["search_phone"] ?? "");

        if($search_email==="" && $search_phone===""){
            $errors[] = "Email or Phone are required";
        }

        if($search_email!=="" && !filter_var($search_email, FILTER_VALIDATE_EMAIL)){
            $errors[] = "Email is not valid";
        }

        if($search_phone!=="" && !preg_match('/^\d{7,15}$/', $search_phone)){
            $errors[] = "Phone number is not valid (7 to 15 digits).";
        }

        if(!$errors){
            if($search_email!==""){
                $search_field = "email";
                $search_value = $search_email;
            } else {
                $search_field = "phone";
                $search_value = $search_phone;
            }

            $where = ($search_field === "email") ? "email" : "phone";

            $sql = "SELECT * FROM Customer WHERE $where = ? AND deleted = 0 LIMIT 1";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "s", $search_value);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if($customer = mysqli_fetch_assoc($result)){
                $customer_id = $customer["customer_ID"] ?? "";
                $first_name = $customer["first_name"];
                $last_name  = $customer["last_name"];
                $address    = $customer["address"];
                $town       = $customer["town"];
                $eircode    = $customer["eircode"];
                $phone      = $customer["phone"];
                $email      = $customer["email"];
                $dob        = $customer["dob"];

                $found  = true;   // SHOW amend form
                $locked = true;   // but keep fields locked until Amend
            } else {
                $errors[] = "No customer found with that " . ($search_field==="email" ? "email" : "phone");
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<section class="hero">
    <h1>Amend Customer</h1>
    <p>Search by email or phone, then update customer information</p>
    <div class="cta-buttons">
        <a href="Home_Page.php" class="button">Back to Home</a>
    </div>
</section>

<div class="page-container">
    <div style="width:100%; max-width:580px; display:flex; flex-direction:column; gap:22px;">

        <div class="card">
            <h2>Find Customer</h2>

            <?php if($errors): ?>
                <script> alert('<?php echo htmlspecialchars(implode(" ", $errors)); ?>');</script>
            <?php endif; ?>

            <?php if($success): ?>
                <script> alert('<?php echo htmlspecialchars($success); ?>');</script>
            <?php endif; ?>

            <form id="amendForm" method="post" class="card-form">
                <label>Customer Email</label>
                <input type="text" name="search_email" value="<?php echo htmlspecialchars($search_email); ?>">

                <label>Customer Phone</label>
                <input type="text" name="search_phone" value="<?php echo htmlspecialchars($search_phone); ?>">

                <div class="button-group">
                    <input type="submit" value="Search">
                    <input type="reset" value="Clear">
                </div>
            </form>
        </div>

        <?php if($found): ?>
        <div class="card">
            <h2>Amend Details</h2>

            <form method="post" class="card-form" id="amendDetailsForm" onsubmit="return confirm('Save changes to this customer?');">
                <input type="hidden" name="search_field" value="<?php echo htmlspecialchars($search_field); ?>">
                <input type="hidden" name="search_value" value="<?php echo htmlspecialchars($search_value); ?>">

                <label>Customer ID</label>
                <input type="text" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>" readonly>

                <label>First name</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" <?php if($locked) echo "disabled"; ?> required>

                <label>Last name</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" <?php if($locked) echo "disabled"; ?> required>

                <label>Date Of Birth</label>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>" <?php if($locked) echo "disabled"; ?> required>

                <label>Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" <?php if($locked) echo "disabled"; ?> required>

                <label>Town</label>
                <input type="text" name="town" value="<?php echo htmlspecialchars($town); ?>" <?php if($locked) echo "disabled"; ?> required>

                <label>Eircode</label>
                <input type="text" name="eircode" value="<?php echo htmlspecialchars($eircode); ?>" <?php if($locked) echo "disabled"; ?> required>

                <label>Email</label>
                <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>" <?php if($locked) echo "disabled"; ?>>

                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" <?php if($locked) echo "disabled"; ?> required>

                <div class="button-group">
                    <button id="btnAmend" type="button" onclick="enableEdit()" <?php if(!$locked) echo 'style="display:none"'; ?>>
                        Amend
                    </button>

                    <input id="btnUpdate" type="submit" name="update" value="Update"
                           <?php if($locked) echo 'style="display:none"'; ?>>

                    <input type="reset" onclick="disableEdit()" value="Reset">
                </div>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
    
function enableEdit(){
    const inputs = document.querySelectorAll("#amendDetailsForm input[type=text], #amendDetailsForm input[type=date]");
    inputs.forEach(input => {
        if(input.name !== "customer_ID"){
            input.disabled = false;
        }
    });

    document.getElementById("btnAmend").style.display = "none";
    document.getElementById("btnUpdate").style.display = "inline-block";
}

function disableEdit(){
    const inputs = document.querySelectorAll("#amendDetailsForm input[type=text], #amendDetailsForm input[type=date]");
    inputs.forEach(input => {
        if(input.name !== "customer_ID"){
            input.disabled = true;
        }
    });

    document.getElementById("btnUpdate").style.display = "none";
    document.getElementById("btnAmend").style.display = "inline-block";
}
</script>

<?php require_once 'Includes/footer.php'; ?>