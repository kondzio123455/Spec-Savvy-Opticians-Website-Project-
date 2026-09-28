<?php
require_once 'Includes/db.inc.php';
require_once 'Includes/nav.php';
require_once 'Includes/header.php';

$errors = [];
$order_id=null;

function fetch_last_order_id($con, $supplier_id, &$errors){
    $columns = ["order_id", "Order_ID", "OrderID"];
    foreach($columns as $col){
        $sql = "SELECT $col AS order_id FROM Orders WHERE SupplierID=? ORDER BY $col DESC LIMIT 1";
        $stmt = mysqli_prepare($con, $sql);
        if(!$stmt){
            $errors[]="Prepare failed (header lookup $col): " . mysqli_error($con);
            continue;
        }
        mysqli_stmt_bind_param($stmt, "i", $supplier_id);
        if(!mysqli_stmt_execute($stmt)){
            $errors[]="Header lookup failed ($col): " . mysqli_stmt_error($stmt);
            continue;
        }
        $res = mysqli_stmt_get_result($stmt);
        if($res){
            $row = mysqli_fetch_assoc($res);
            if($row && isset($row["order_id"])){
                return (int)$row["order_id"];
            }
        }
    }
    return 0;
}
?>

<?php 
$supplier_id=isset($_POST["supplier_id"])?(int)$_POST["supplier_id"]:0;
$qty_ordered=isset($_POST["qty_ordered"])?$_POST["qty_ordered"]:[];

if($supplier_id<=0){
    $errors[]="Invalid supplier selected";
}

if(!is_array($qty_ordered)){
    $errors[]="Invalid quantity data";
}


$items=[]; 

if(!$errors){
    foreach($qty_ordered as $stock_id=>$qty){
        $stock_id=(int)$stock_id;
        $qty=(int)$qty;

        if($stock_id>0 && $qty>0){
            $items[]=[
                "stock_id"=>$stock_id,
                "qty"=>$qty
            ];  
        }
    }

    if(count($items)===0){
        $errors[]="No valid items selected";
    }

}
?>

<?php

if(empty($errors)){

    mysqli_begin_transaction($con);

    //Insert Header
    $sql="INSERT INTO Orders (SupplierID, Order_date, Order_Status, Notes)
    VALUES (?, CURDATE(), 'Pending', '')";

    $stmt=mysqli_prepare($con, $sql);
    if(!$stmt){
        $errors[]="Prepare failed (header):" . mysqli_error($con);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $supplier_id);

        if(!mysqli_stmt_execute($stmt)){
            $errors[]="Header insert failed: " . mysqli_stmt_error($stmt);
        }

        if(mysqli_stmt_affected_rows($stmt)<= 0){
            $errors[]="Could not create order (no rows inserted).";
        } else {
            $order_id=mysqli_insert_id($con);
            if(!$order_id){
                $order_id = fetch_last_order_id($con, $supplier_id, $errors);
                if(!$order_id){
                    $errors[]="Order header inserted but order_id is still 0. Check Orders primary key and auto-increment.";
                }
            }
        }
    }

    //Insert Lines
    if(empty($errors)){
        if(!$order_id){
            $errors[]="Order header was not created.";
        }
    }

    if(empty($errors)){
        $sqlLine="INSERT INTO OrderItem (Order_ID, Stock_id, Qty_ordered)
        VALUES (?, ?, ?)";
        $stmtLine=mysqli_prepare($con, $sqlLine);

        if(!$stmtLine){
            $errors[]="Prepare failed (lines):" . mysqli_error($con);
        } else {
            foreach($items as $item){
                mysqli_stmt_bind_param($stmtLine, "iii", $order_id, $item["stock_id"], $item["qty"]);

                if(!mysqli_stmt_execute($stmtLine)){
                    $errors[]="Insert failed for stock ID: " . $item["stock_id"] . " (" . mysqli_stmt_error($stmtLine) . ")";
                    continue;
                }

                if(mysqli_stmt_affected_rows($stmtLine) <= 0){
                    $errors[]="No row inserted for stock ID: " . $item["stock_id"];
                }
            }
        }
    }

    if(!empty($errors)){
        mysqli_rollback($con);
    } else {
        mysqli_commit($con);
    }

}
?>

<?php

$supplier=null;
$orderRows=[];

if(empty($errors)){
    //Supplier details to build letterhead
    $sql="SELECT Supplier_Name, Supplier_Street, Supplier_Town, Supplier_County FROM Supplier WHERE SupplierID=?";
    $stmt=mysqli_prepare($con, $sql);

    mysqli_stmt_bind_param($stmt, "i", $supplier_id);
    mysqli_stmt_execute($stmt);
    $res=mysqli_stmt_get_result($stmt);
    $supplier=mysqli_fetch_assoc($res);

    if(!$supplier){
        $errors[]="Supplier not found for printing.";
    }

    $sql="SELECT oi.Qty_ordered, s.Stock_no, s.Description AS StockItemDescription, s.Supplier_stock_code AS YourStockCode
    FROM OrderItem oi
    JOIN Stock s ON s.Stock_id = oi.Stock_id
    WHERE oi.Order_ID=?";

    $stmt=mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $res=mysqli_stmt_get_result($stmt);
    while($row=mysqli_fetch_assoc($res)){
        $orderRows[]=$row;
    }
}
?>

<main class="main">
    <header class="main-header">
        <H1>Order Letter to Supplier</H1>
        <span class="Subtittle">Order Number: <?php echo htmlspecialchars($order_id); ?></span>
    </header>

    <section class="content">
        <section class="content">
            <?php if($errors): ?>
                <p class="muted"><?php echo htmlspecialchars(implode(" ",$errors)); ?></p>
            <?php else: ?>

                <div class="card-form" style="max-width:700px;">
                    <div style= "display:flex; justify-content:space-between; gap:20px;">
                        <div>
                            <strong><?php echo htmlspecialchars($supplier["Supplier_Name"]); ?></strong><br>
                            <?php echo htmlspecialchars($supplier["Supplier_Street"]); ?><br>
                            <?php echo htmlspecialchars($supplier["Supplier_Town"]); ?><br>
                            <?php echo htmlspecialchars($supplier["Supplier_County"]); ?><br>
                        </div>

                        <div style="text-align:right;">
                            <strong>Spec Savvy Opticians</strong><br>
                            Main Street,<br>
                            Carlow<br>
                            <?php echo date("Y-m-d"); ?>
                        </div>
                    </div>

                    <hr style="margin:14px 0;">
                    <p><Strong>Order Number: </strong> <?php echo htmlspecialchars($order_id); ?></p>
                    <p> Please Supply the following stock:</p>

                    <div class="table-wrap">
                        <table class="simple-table" style="width:100%; margin-top:10px">
                            <tr>
                                <th>Quantity</th>
                                <th>Stock Item</th>
                                <th>Description</th>
                                <th>Your Stock Code</th>
                            </tr>

                            <?php foreach($orderRows as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["Qty_ordered"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["Stock_no"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["StockItemDescription"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["YourStockCode"]); ?></td>
                                </tr>

                            <?php endforeach; ?>
                        </table>
                    </div>

                    <div style="margin-top:20px;">
                        <p> Yours Sincerely,</p>
                        <P> Optician.</P>
                        </div>

                        <div class="form-actions" style="margin-top:14px;">
                            <button type="button" onclick="window.print()">Print</button>
                            <a class="menu-item" href="orders.php?supplier_id=<?php echo (int)$supplier_id; ?>">Back</a>
                        </div>
                </div>
            <?php endif; ?>
        </section>
    </section>

    <footer class="main-footer">
        <p>&copy; 2024 Spec Savvy Opticians</p>
    </footer>
</main>
