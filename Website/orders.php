<?php
require_once 'Includes/db.inc.php';
require_once 'Includes/header.php';
require_once 'Includes/nav.php';

$suppliers = [];
$res = mysqli_query($con, "SELECT SupplierID, Supplier_Name FROM Supplier ORDER BY Supplier_Name");
while($row = mysqli_fetch_assoc($res)){
    $suppliers[] = $row;
}

$supplier_id = isset($_GET["supplier_id"]) ? (int)$_GET["supplier_id"] : 0;
$stockItems = [];

// --- Stock viewer 
$allStock = [];
$stockRes = mysqli_query($con, "SELECT Stock_id, Stock_no, Description FROM Stock ORDER BY Description");
while($r = mysqli_fetch_assoc($stockRes)){
    $allStock[] = $r;
}

$view_stock_id = isset($_GET["view_stock_id"]) ? (int)$_GET["view_stock_id"] : 0;
$viewStockRow = null;

if($view_stock_id > 0){
    $sqlView = "SELECT 
                    s.Stock_no AS stock_number,
                    s.Description,
                    s.Qty_in_stock,
                    s.Reorder_qty,
                    s.Supplier_stock_code,
                    sup.Supplier_Name AS supplier_name
                FROM Stock s
                JOIN Supplier sup ON sup.SupplierID = s.SupplierID
                WHERE s.Stock_id = ?
                LIMIT 1";
    $stmtView = mysqli_prepare($con, $sqlView);
    mysqli_stmt_bind_param($stmtView, "i", $view_stock_id);
    mysqli_stmt_execute($stmtView);
    $resView = mysqli_stmt_get_result($stmtView);
    $viewStockRow = mysqli_fetch_assoc($resView);
    mysqli_stmt_close($stmtView);
}


if($supplier_id){
    $sql = "SELECT
                s.Stock_id AS Stock_id,
                s.Stock_no AS stock_number,
                s.Description AS Description,
                s.Qty_in_stock AS qty_in_stock,
                s.Reorder_qty AS reorder_qty,
                s.Supplier_stock_code AS supplier_stock_code,
                sup.Supplier_Name as supplier_name
            FROM Stock s
            JOIN Supplier sup ON sup.SupplierID = s.SupplierID
            WHERE s.SupplierID = ?
            ORDER BY s.Description";

    $stmt = mysqli_prepare($con, $sql);
    if(!$stmt){
        die('Error preparing statement: ' . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, "i", $supplier_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while($row = mysqli_fetch_assoc($result)){
        $stockItems[] = $row;
    }

    mysqli_stmt_close($stmt);
}
?>

<!-- ================= HERO SECTION ================= -->
<section class="hero">
    <h1>Orders</h1>
    <p>Select a supplier, choose quantities, and print the order</p>
    <div class="cta-buttons">
        <a href="Home_Page.php" class="button">Back to Home</a>
    </div>
</section>

<!-- ================= CONTENT ================= -->
<div class="page-container">
    <div style="width:100%; max-width:980px; display:flex; flex-direction:column; gap:22px;">

        <!-- CARD: View Stock Item -->
        <div class="card" style="max-width:580px;">
            <h2>View Stock Item</h2>

            <form class="card-form" method="get">
                <label>Stock Item</label>
                <select name="view_stock_id">
                    <option value="">-- Choose stock item --</option>
                    <?php foreach($allStock as $s): ?>
                        <option value="<?php echo (int)$s["Stock_id"]; ?>"
                            <?php if((int)$s["Stock_id"] === $view_stock_id) echo "selected"; ?>>
                            <?php echo htmlspecialchars($s["Description"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="button-group">
                    <input type="submit" value="View Details">
                    <a class="btn-cancel" href="orders.php"
                       style="text-align:center; padding:12px; border-radius:8px; display:block; text-decoration:none;">
                        Clear
                    </a>
                </div>
            </form>

            <?php if($viewStockRow): ?>
                <p class="muted" style="margin-top:10px;">
                    <strong>Stock No:</strong> <?php echo htmlspecialchars($viewStockRow["stock_number"]); ?><br>
                    <strong>Description:</strong> <?php echo htmlspecialchars($viewStockRow["Description"]); ?><br>
                    <strong>Qty in Stock:</strong> <?php echo htmlspecialchars($viewStockRow["Qty_in_stock"]); ?><br>
                    <strong>Reorder Qty:</strong> <?php echo htmlspecialchars($viewStockRow["Reorder_qty"]); ?><br>
                    <strong>Supplier Stock Code:</strong> <?php echo htmlspecialchars($viewStockRow["Supplier_stock_code"]); ?><br>
                    <strong>Supplier:</strong> <?php echo htmlspecialchars($viewStockRow["supplier_name"]); ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- CARD 1: Select Supplier -->
        <div class="card" style="max-width:580px;">
            <h2>Select Supplier</h2>

            <form class="card-form" method="get">
                <label>Supplier</label>
                <select name="supplier_id" required>
                    <option value="">-- Choose supplier --</option>
                    <?php foreach($suppliers as $supplier): ?>
                        <option value="<?php echo (int)$supplier['SupplierID']; ?>"
                            <?php if((int)$supplier['SupplierID'] === $supplier_id) echo "selected"; ?>>
                            <?php echo htmlspecialchars($supplier['Supplier_Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="button-group">
                    <input type="submit" value="View Stock">
                    <a class="btn-cancel" href="orders.php"
                       style="text-align:center; padding:12px; border-radius:8px; display:block; text-decoration:none;">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- CARD 2: Stock Table -->
        <?php if($supplier_id > 0): ?>
            <div class="card" style="max-width:980px;">
                <h2>Stock Items</h2>

                <?php if(!$stockItems): ?>
                    <p class="muted">No stock items found for this supplier.</p>
                <?php else: ?>
                    <form method="post" action="orders_print.php" class="card-form"
                          onsubmit="return confirm('Print order with the quantities you entered?');">
                        <input type="hidden" name="supplier_id" value="<?php echo (int)$supplier_id; ?>">

                        <div class="table-wrap">
                            <table class="simple-table">
                                <tr>
                                    <th>Stock No</th>
                                    <th>Description</th>
                                    <th>Qty in Stock</th>
                                    <th>Reorder Qty</th>
                                    <th>Supplier Stock Code</th>
                                    <th>Supplier Name</th>
                                    <th>Qty Ordered</th>
                                </tr>

                                <?php foreach($stockItems as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['stock_number']); ?></td>
                                        <td><?php echo htmlspecialchars($item['Description']); ?></td>
                                        <td><?php echo htmlspecialchars($item['qty_in_stock']); ?></td>
                                        <td><?php echo htmlspecialchars($item['reorder_qty']); ?></td>
                                        <td><?php echo htmlspecialchars($item['supplier_stock_code']); ?></td>
                                        <td><?php echo htmlspecialchars($item['supplier_name']); ?></td>
                                        <td>
                                            <input
                                                type="number"
                                                name="qty_ordered[<?php echo (int)$item['Stock_id']; ?>]"
                                                min="0"
                                                value="0"
                                                style="width: 90px;"
                                            >
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>

                        <div class="button-group">
                            <input type="submit" value="Print Order">
                            <a class="btn-cancel" href="orders.php?supplier_id=<?php echo (int)$supplier_id; ?>"
                               style="text-align:center; padding:12px; border-radius:8px; display:block; text-decoration:none;"
                               onclick="return confirm('Clear all quantities?');">
                                Reset Quantities
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'Includes/footer.php'; ?>