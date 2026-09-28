<link rel="stylesheet" href="style.css">
<?php
include 'db.inc.php';

$message = "";
$error = "";
	//This basically checks if the button Delivered? was clicked
if (isset($_POST['mark_delivered'])) {
    $order_id = $_POST['order_id'];
	
	// So now this is a query to get all items belonging to this order
    // And then we further need a Stock_ID and Qty_ordered so stock levels can be updated in the process
    $itemRes = mysqli_query($con, "SELECT Stock_ID, Qty_ordered
                                   FROM `OrderItem`
                                   WHERE Order_ID = '$order_id'");
	

    if (!$itemRes) {
        $error = "Could not load order items: " . mysqli_error($con);
    } 
	else {
		// Loop through each item in the order
        while ($item = mysqli_fetch_assoc($itemRes)) {
            $stock_id = $item['Stock_ID'];
            $qty = $item['Qty_ordered'];

            mysqli_query($con, "UPDATE `Stock`
                                SET Qty_in_stock = Qty_in_stock + '$qty'
                                WHERE Stock_id = '$stock_id'");
        }
		 // After updating stock, we suddenly mark the order as Delivered in the db
        if (mysqli_query($con, "UPDATE `Orders`
                                SET order_status = 'Delivered'
                                WHERE order_id = '$order_id'")) {
            $message = "Order ID $order_id has been marked as delivered.";
        } 
		else {
            $error = "Could not update order: " . mysqli_error($con);
        }
    }
}
// Most definitely needed AI assistance on this prompt which i will declare, but this searches ordered that are srill pending
$orderRes = mysqli_query($con, "SELECT o.order_id, o.order_date, s.Supplier_Name
                                FROM `Orders` o
                                LEFT JOIN `Supplier` s ON o.SupplierID = s.SupplierID
                                WHERE o.order_status = 'Pending'
                                ORDER BY o.order_date ASC, o.order_id ASC");

if (!$orderRes) {
    die("Pending orders query failed: " . mysqli_error($con));
}
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deliveries</title>
</head>
<body>

<header class="main-header">
    <div class="logo-area">
        <img src="SpecSavvyLogo.png" alt="Spec Savvy Logo">
    </div>
</header>

<?php require_once 'Includes/nav.php'; ?>

<div class="page-container">
    <div class="card">

        <h2>Deliveries</h2>

        <?php if ($message) echo "<p style='color:green;'><b>$message</b></p>"; ?>
        <?php if ($error) echo "<p style='color:red;'><b>$error</b></p>"; ?>

        <h3>Pending Orders</h3>

        <?php if (mysqli_num_rows($orderRes) == 0): ?>
            <p>No pending orders found.</p>
        <?php else: ?>
			<!-- Loop through each pending order -->
            <?php while ($order = mysqli_fetch_assoc($orderRes)): ?>
                <div style="border:1px solid black; padding:10px; margin-bottom:20px;">

                    <b>Order Number:</b> <?php echo $order['order_id']; ?><br>
                    <b>Order Date:</b> <?php echo date("d/m/Y", strtotime($order['order_date'])); ?><br>
                    <b>Supplier Name:</b> <?php echo $order['Supplier_Name'] ? $order['Supplier_Name'] : 'Unknown Supplier'; ?><br><br>

                    <b>Items on this Order:</b><br><br>

                    <table border="1" cellpadding="8" cellspacing="0">
                        <tr>
                            <th>Stock Number</th>
                            <th>Description</th>
                            <th>Quantity Ordered</th>
                        </tr>

                        <?php
						// Yet again, had to use ai for this but these are table aliases that shorten the table name and what variable youre 							getting from that specific table
                        $lineRes = mysqli_query($con, "SELECT st.Stock_no, st.Description, oi.Qty_ordered
                                                       FROM `OrderItem` oi
                                                       INNER JOIN `Stock` st ON oi.Stock_ID = st.Stock_id
                                                       WHERE oi.Order_ID = '" . $order['order_id'] . "'");

						// And finally to print out to the screen, if the item exists in db, display each one in a table row
                        if ($lineRes && mysqli_num_rows($lineRes) > 0) {
                            while ($line = mysqli_fetch_assoc($lineRes)) {
                                echo "<tr>";
                                echo "<td>{$line['Stock_no']}</td>";
                                echo "<td>{$line['Description']}</td>";
                                echo "<td>{$line['Qty_ordered']}</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No stock items found for this order.</td></tr>";
                        }
                        ?>
                    </table>

                    <br>

                    <form method="POST" action="deliveries.php" class="deliveredForm">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <button type="submit" name="mark_delivered">Delivered?</button>
                    </form>

                </div>
            <?php endwhile; ?>

        <?php endif; ?>

    </div>
</div>

<script src="deliveries.js"></script>
</body>
</html>

<?php mysqli_close($con); ?>