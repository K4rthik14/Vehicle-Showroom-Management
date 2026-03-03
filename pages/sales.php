<?php
session_start();
define('BASE_URL', '/vsms/');
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Sales Management';
$msg = '';

if (isset($_GET['delete']) && isAdmin()) {
    $id = (int)$_GET['delete'];
    // Restore vehicle status before deleting
    $sale = $conn->query("SELECT VIN FROM SALE WHERE Sale_ID=$id")->fetch_assoc();
    if ($sale) {
        $conn->query("UPDATE VEHICLE SET Status='Available', Stock_Quantity=Stock_Quantity+1 WHERE VIN='".$conn->real_escape_string($sale['VIN'])."'");
    }
    $conn->query("DELETE FROM SALE WHERE Sale_ID=$id");
    $msg = '<div class="alert alert-success alert-dismissible">Sale deleted and vehicle restored.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sale_id   = (int)($_POST['sale_id'] ?? 0);
    $vin       = trim($_POST['vin'] ?? '');
    $emp_id    = (int)($_POST['emp_id'] ?? 0);
    $cust_id   = (int)($_POST['customer_id'] ?? 0);
    $date      = $_POST['sale_date'] ?? date('Y-m-d');
    $amount    = (float)($_POST['amount'] ?? 0);
    $payment   = $_POST['payment_method'] ?? 'Cash';

    if ($vin && $emp_id && $cust_id && $amount) {
        if ($sale_id) {
            $stmt = $conn->prepare("UPDATE SALE SET VIN=?,Emp_ID=?,Customer_ID=?,Sale_Date=?,Amount=?,Payment_Method=? WHERE Sale_ID=?");
            $stmt->bind_param("siisssi",$vin,$emp_id,$cust_id,$date,$amount,$payment,$sale_id);
            $stmt->execute();
            $msg = '<div class="alert alert-success alert-dismissible">Sale updated.</div>';
        } else {
            // Check vehicle availability
            $vcheck = $conn->query("SELECT Status, Stock_Quantity FROM VEHICLE WHERE VIN='".$conn->real_escape_string($vin)."'")->fetch_assoc();
            if (!$vcheck || $vcheck['Status'] === 'Sold' || $vcheck['Stock_Quantity'] < 1) {
                $msg = '<div class="alert alert-danger alert-dismissible">Vehicle not available or out of stock.</div>';
            } else {
                $stmt = $conn->prepare("INSERT INTO SALE(VIN,Emp_ID,Customer_ID,Sale_Date,Amount,Payment_Method) VALUES(?,?,?,?,?,?)");
                $stmt->bind_param("siisds",$vin,$emp_id,$cust_id,$date,$amount,$payment);
                $stmt->execute();
                // Update vehicle
                $newQty = $vcheck['Stock_Quantity'] - 1;
                $newStatus = $newQty <= 0 ? 'Sold' : 'Available';
                $conn->query("UPDATE VEHICLE SET Stock_Quantity=$newQty, Status='$newStatus' WHERE VIN='".$conn->real_escape_string($vin)."'");
                $msg = '<div class="alert alert-success alert-dismissible">Sale recorded! Vehicle inventory updated.</div>';
            }
        }
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $editData = $conn->query("SELECT * FROM SALE WHERE Sale_ID=".(int)$_GET['edit'])->fetch_assoc();
}

$sales = $conn->query("
    SELECT s.*,
           CONCAT(c.Fname,' ',c.Lname) AS customer_name,
           st.Name AS staff_name,
           v.Model_Name
    FROM SALE s
    JOIN CUSTOMER c ON s.Customer_ID=c.Customer_ID
    JOIN STAFF st ON s.Emp_ID=st.Emp_ID
    JOIN VEHICLE v ON s.VIN=v.VIN
    ORDER BY s.Sale_Date DESC, s.Sale_ID DESC
");

$vehicles  = $conn->query("SELECT VIN, Model_Name, Status, Stock_Quantity FROM VEHICLE WHERE Status='Available' ORDER BY Model_Name");
$allVehicles = $conn->query("SELECT VIN, Model_Name FROM VEHICLE ORDER BY Model_Name");
$customers = $conn->query("SELECT Customer_ID, CONCAT(Fname,' ',Lname) AS name FROM CUSTOMER ORDER BY Fname");
$staff     = $conn->query("SELECT Emp_ID, Name FROM STAFF ORDER BY Name");

require_once '../includes/header.php';
?>
<?= $msg ?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-plus-circle me-2 text-primary"></i><?= $editData?'Edit':'Record' ?> Sale</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="sale_id" value="<?= $editData['Sale_ID']??0 ?>">
                    <div class="mb-2">
                        <label class="form-label">Vehicle *</label>
                        <select name="vin" class="form-select" required>
                            <option value="">-- Select Vehicle --</option>
                            <?php
                            $vList = $editData ? $allVehicles : $vehicles;
                            $vList->data_seek(0);
                            while ($v = $vList->fetch_assoc()):
                            ?>
                                <option value="<?= htmlspecialchars($v['VIN']) ?>"
                                    <?= ($editData['VIN']??'')===$v['VIN']?'selected':'' ?>>
                                    <?= htmlspecialchars($v['Model_Name']) ?> (<?= substr($v['VIN'],-6) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Customer *</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Select Customer --</option>
                            <?php $customers->data_seek(0); while ($c = $customers->fetch_assoc()): ?>
                                <option value="<?= $c['Customer_ID'] ?>" <?= ($editData['Customer_ID']??'')==$c['Customer_ID']?'selected':'' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Staff Member *</label>
                        <select name="emp_id" class="form-select" required>
                            <option value="">-- Select Staff --</option>
                            <?php $staff->data_seek(0); while ($s = $staff->fetch_assoc()): ?>
                                <option value="<?= $s['Emp_ID'] ?>" <?= ($editData['Emp_ID']??'')==$s['Emp_ID']?'selected':'' ?>>
                                    <?= htmlspecialchars($s['Name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Sale Date *</label>
                        <input type="date" name="sale_date" class="form-control" required value="<?= $editData['Sale_Date']??date('Y-m-d') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Amount (₹) *</label>
                        <input type="number" name="amount" class="form-control" min="0" step="0.01" required value="<?= $editData['Amount']??'' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <?php foreach (['Cash','Card','UPI'] as $pm): ?>
                                <option <?= ($editData['Payment_Method']??'Cash')===$pm?'selected':'' ?>><?= $pm ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-vsms"><?= $editData?'Update':'Record Sale' ?></button>
                        <?php if($editData): ?><a href="sales.php" class="btn btn-outline-vsms">Cancel</a><?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card-vsms">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi-receipt me-2 text-primary"></i>All Sales (<?= $sales->num_rows ?>)</span>
                <?php
                $totalRev = $conn->query("SELECT COALESCE(SUM(Amount),0) FROM SALE")->fetch_row()[0];
                ?>
                <span class="badge bg-success" style="font-size:.8rem;">Total: ₹<?= number_format($totalRev) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if ($sales->num_rows > 0): ?>
                <table class="table table-vsms mb-0">
                    <thead><tr><th>#</th><th>Vehicle</th><th>Customer</th><th>Staff</th><th>Date</th><th>Amount</th><th>Pay</th><th>Act</th></tr></thead>
                    <tbody>
                    <?php while ($s = $sales->fetch_assoc()): ?>
                        <tr>
                            <td style="font-family:'DM Mono',monospace;color:#6366f1;"><?= $s['Sale_ID'] ?></td>
                            <td><?= htmlspecialchars($s['Model_Name']) ?></td>
                            <td><?= htmlspecialchars($s['customer_name']) ?></td>
                            <td><?= htmlspecialchars($s['staff_name']) ?></td>
                            <td><?= date('d M Y', strtotime($s['Sale_Date'])) ?></td>
                            <td><strong>₹<?= number_format($s['Amount']) ?></strong></td>
                            <td><span class="badge badge-<?= strtolower($s['Payment_Method']) ?>"><?= $s['Payment_Method'] ?></span></td>
                            <td>
                                <a href="sales.php?edit=<?= $s['Sale_ID'] ?>" class="btn btn-sm btn-outline-secondary me-1"><i class="bi-pencil"></i></a>
                                <?php if(isAdmin()): ?>
                                <a href="sales.php?delete=<?= $s['Sale_ID'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete sale and restore vehicle?')"><i class="bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty-state"><i class="bi-receipt"></i><p>No sales recorded.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
