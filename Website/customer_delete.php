<?php
require_once 'Includes/header.php';
require_once 'Includes/db.inc.php';
require_once 'Includes/nav.php';

$errors = [];
$success = "";

// user inputs
$email = "";
$phone = "";

// searched customer
$customer = null;

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $email  = trim($_POST["email"] ?? "");
    $phone  = trim($_POST["phone"] ?? "");
    $action = $_POST["action"] ?? "search";

    // Validate: must provide at least one
    if($email === "" && $phone === ""){
        $errors[] = "Email or Phone are required";
    }

    // Validate email if provided
    if($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Email is not valid";
    }

    // Validate phone if provided (7-15 digits)
    if($phone !== "" && !preg_match('/^\d{7,15}$/', $phone)){
        $errors[] = "Phone number is not valid (7 to 15 digits).";
    }

    // Decide which field to use for deletion
    $field = "";
    $value = "";

    if(!$errors){
        if($email !== ""){
            $field = "email";
            $value = $email;
        } else {
            $field = "phone";
            $value = $phone;
        }
    }

    // SEARCH
    if(!$errors && $action === "search"){
        // Only allow safe columns
        $where = ($field === "email") ? "email" : "phone";

        $sql = "SELECT customer_id, first_name, last_name, address, dob, town, eircode, phone, email
                FROM Customer
                WHERE $where = ? AND deleted = 0
                LIMIT 1";

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $value);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $customer = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if(!$customer){
            $errors[] = "No customer found with that " . ($field === "email" ? "email" : "phone");
        }
    }

    // DELETE
    // DELETE (SOFT DELETE)
    if(!$errors && $action === "delete"){
        $where = ($field === "email") ? "email" : "phone";

        // 1) Re-fetch customer (and ensure not already deleted)
        $sqlFind = "SELECT customer_id FROM Customer WHERE $where = ? AND deleted = 0 LIMIT 1";
        $stmtFind = mysqli_prepare($con, $sqlFind);
        mysqli_stmt_bind_param($stmtFind, "s", $value);
        mysqli_stmt_execute($stmtFind);
        $resFind = mysqli_stmt_get_result($stmtFind);
        $rowFind = mysqli_fetch_assoc($resFind);
        mysqli_stmt_close($stmtFind);

        if(!$rowFind){
            $errors[] = "No active customer found with that " . ($field === "email" ? "email" : "phone");
        } else {
            $custId = (int)$rowFind["customer_id"];

            // 2) Block deletion if eye tests exist
            // CHANGE TABLE NAME if needed (Eye_Test / EyeTest / EyeTests)
            $sqlCheck = "SELECT COUNT(*) AS cnt FROM `Eye Test` WHERE customer_ID = ?"; 
            $stmtCheck = mysqli_prepare($con, $sqlCheck);
            if(!$stmtCheck){
                $errors[] = "Error checking eye tests: " . mysqli_error($con);
            } else {
                mysqli_stmt_bind_param($stmtCheck, "i", $custId);
                mysqli_stmt_execute($stmtCheck);
                $resCheck = mysqli_stmt_get_result($stmtCheck);
                $cntRow = mysqli_fetch_assoc($resCheck);
                mysqli_stmt_close($stmtCheck);

                $eyeTestCount = (int)($cntRow["cnt"] ?? 0);

                if($eyeTestCount > 0){
                    $errors[] = "Cannot delete this customer because they have eye test records. Delete the eye tests first.";
                } else {
                    // 3) Soft delete
                    $sqlDel = "UPDATE Customer SET deleted = 1 WHERE customer_id = ? LIMIT 1";
                    $stmtDel = mysqli_prepare($con, $sqlDel);
                    if(!$stmtDel){
                        $errors[] = "Error preparing delete statement: " . mysqli_error($con);
                    } else {
                        mysqli_stmt_bind_param($stmtDel, "i", $custId);
                        mysqli_stmt_execute($stmtDel);

                        if(mysqli_stmt_affected_rows($stmtDel) > 0){
                            $success = "Customer deleted successfully (flagged as deleted).";
                            $email = "";
                            $phone = "";
                            $customer = null;
                        } else {
                            $errors[] = "Customer could not be deleted. Please try again.";
                        }
                        mysqli_stmt_close($stmtDel);
                    }
                }
            }
        }
    }
}
?>

<!-- ================= HERO SECTION ================= -->
<section class="hero">
    <h1>Delete Customer</h1>
    <p>Search by email or phone, then confirm deletion</p>
    <div class="cta-buttons">
        <a href="Home_Page.php" class="button">Back to Home</a>
    </div>
</section>

<!-- ================= CONTENT ================= -->
<div class="page-container">
    <div style="width:100%; max-width:580px; display:flex; flex-direction:column; gap:22px;">

        <!-- SEARCH CARD -->
        <div class="card">
            <h2>Find Customer</h2>

            <?php if($success): ?>
               <script>alert('<?php echo htmlspecialchars($success); ?>');</script>
            <?php endif; ?>

            <?php if($errors): ?>
                <p class="muted"><?php echo htmlspecialchars(implode(" ", $errors)); ?></p>
            <?php endif; ?>

            <form class="card-form" method="post">
                <label>Customer Email</label>
                <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>">

                <label>Customer Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">

                <input type="hidden" name="action" value="search">

                <div class="button-group">
                    <input type="submit" value="Find Customer">
                    <input type="reset" value="Clear">
                </div>
            </form>
        </div>

        <!-- CONFIRM CARD -->
        <?php if($customer): ?>
            <div class="card">
                <h2>Confirm Deletion</h2>

                <p>
                    Are you sure you want to delete:
                    Customer ID: <?php echo htmlspecialchars($customer["customer_id"]); ?><br>
                    <strong><?php echo htmlspecialchars($customer["first_name"] . " " . $customer["last_name"]); ?></strong>
                    (<?php echo htmlspecialchars($customer["email"]); ?>)
                </p>

                <p class="muted">
                    Town: <?php echo htmlspecialchars($customer["town"]); ?><br>
                    Phone: <?php echo htmlspecialchars($customer["phone"]); ?><br>
                    Address: <?php echo htmlspecialchars($customer["address"]); ?><br>
                    Eircode: <?php echo htmlspecialchars($customer["eircode"]); ?><br>
                    DOB: <?php echo htmlspecialchars($customer["dob"]); ?>

                </p>

                <form method="post"
                      onsubmit="return confirm('Are you sure you want to delete this customer from the database?');">
                    <!-- Keep same search key for deletion -->
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                    <input type="hidden" name="action" value="delete">

                    <div class="button-group">
                        <input type="submit" value="Yes, delete">
                        <a class="btn-cancel" href="customer_delete.php"
                           style="text-align:center; padding:12px; border-radius:8px; display:block; text-decoration:none;">
                            No, cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'Includes/footer.php'; ?>