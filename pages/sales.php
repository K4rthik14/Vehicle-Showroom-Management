<?php
session_start();
define('BASE_URL', '/vsms/');
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'Sales Management';
$messageHtml = '';

function renderAlert(string $type, string $message): string
{
    return '<div class="alert alert-' . $type . ' alert-dismissible">' . htmlspecialchars($message) . '</div>';
}

// -----------------------------
// Handle delete request (admin)
// -----------------------------
if (isset($_GET['delete']) && isAdmin()) {
    $saleId = (int)$_GET['delete'];

    // Restore vehicle stock for the deleted sale.
    $saleToDelete = $conn->query("SELECT VIN FROM SALE WHERE Sale_ID=$saleId")->fetch_assoc();
    if ($saleToDelete) {
        $vin = $conn->real_escape_string($saleToDelete['VIN']);
        $conn->query("UPDATE VEHICLE SET Status='Available', Stock_Quantity=Stock_Quantity+1 WHERE VIN='$vin'");
    }

    $conn->query("DELETE FROM SALE WHERE Sale_ID=$saleId");
    $messageHtml = renderAlert('success', 'Sale deleted and vehicle restored.');
}

// -------------------------
// Handle add/update request
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saleId = (int)($_POST['sale_id'] ?? 0);
    $vin = trim($_POST['vin'] ?? '');
    $staffId = (int)($_POST['emp_id'] ?? 0);
    $customerId = (int)($_POST['customer_id'] ?? 0);
    $saleDate = $_POST['sale_date'] ?? date('Y-m-d');
    $saleAmount = (float)($_POST['amount'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? 'Cash';

    if ($vin && $staffId && $customerId && $saleAmount > 0) {
        if ($saleId > 0) {
            $updateSale = $conn->prepare(
                'UPDATE SALE SET VIN=?, Emp_ID=?, Customer_ID=?, Sale_Date=?, Amount=?, Payment_Method=? WHERE Sale_ID=?'
            );
            $updateSale->bind_param('siisdsi', $vin, $staffId, $customerId, $saleDate, $saleAmount, $paymentMethod, $saleId);
            $updateSale->execute();

            $messageHtml = renderAlert('success', 'Sale updated.');
        }
        else {
            // Allow sale only if the vehicle has stock and is not sold out.
            $safeVin = $conn->real_escape_string($vin);
            $vehicleInfo = $conn->query("SELECT Status, Stock_Quantity FROM VEHICLE WHERE VIN='$safeVin'")->fetch_assoc();

            if (!$vehicleInfo || $vehicleInfo['Status'] === 'Sold' || (int)$vehicleInfo['Stock_Quantity'] < 1) {
                $messageHtml = renderAlert('danger', 'Vehicle not available or out of stock.');
            }
            else {
                $insertSale = $conn->prepare(
                    'INSERT INTO SALE(VIN, Emp_ID, Customer_ID, Sale_Date, Amount, Payment_Method) VALUES(?, ?, ?, ?, ?, ?)'
                );
                $insertSale->bind_param('siisds', $vin, $staffId, $customerId, $saleDate, $saleAmount, $paymentMethod);
                $insertSale->execute();

                $updatedQuantity = (int)$vehicleInfo['Stock_Quantity'] - 1;
                $updatedStatus = $updatedQuantity <= 0 ? 'Sold' : 'Available';
                $conn->query("UPDATE VEHICLE SET Stock_Quantity=$updatedQuantity, Status='$updatedStatus' WHERE VIN='$safeVin'");

                $messageHtml = renderAlert('success', 'Sale recorded. Vehicle inventory updated.');
            }
        }
    }
    else {
        $messageHtml = renderAlert('warning', 'Please fill all required fields and enter a valid amount.');
    }
}

// ---------------------
// Load data for the page
// ---------------------
$editSale = null;
if (isset($_GET['edit'])) {
    $editSaleId = (int)$_GET['edit'];
    $editSale = $conn->query("SELECT * FROM SALE WHERE Sale_ID=$editSaleId")->fetch_assoc();
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

$availableVehicles = $conn->query("SELECT VIN, Model_Name FROM VEHICLE WHERE Status='Available' ORDER BY Model_Name");
$allVehicles = $conn->query("SELECT VIN, Model_Name FROM VEHICLE ORDER BY Model_Name");
$customers = $conn->query("SELECT Customer_ID, CONCAT(Fname,' ',Lname) AS name FROM CUSTOMER ORDER BY Fname");
$staffMembers = $conn->query("SELECT Emp_ID, Name FROM STAFF ORDER BY Name");
$totalRevenue = $conn->query("SELECT COALESCE(SUM(Amount),0) FROM SALE")->fetch_row()[0];

require_once '../includes/header.php';
?>

<?= $messageHtml?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-plus-circle me-2 text-primary"></i>
                <?= $editSale ? 'Edit' : 'Record'?> Sale
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="sale_id" value="<?= $editSale['Sale_ID'] ?? 0?>">

                    <div class="mb-2">
                        <label class="form-label">Vehicle *</label>
                        <select name="vin" class="form-select" required>
                            <option value="">-- Select Vehicle --</option>
                            <?php
$vehicleOptions = $editSale ? $allVehicles : $availableVehicles;
$vehicleOptions->data_seek(0);
while ($vehicle = $vehicleOptions->fetch_assoc()):
?>
                            <option value="<?= htmlspecialchars($vehicle['VIN'])?>" <?=($editSale['VIN'] ?? ''
                                )===$vehicle['VIN'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($vehicle['Model_Name'])?> (
                                <?= substr($vehicle['VIN'], -6)?>)
                            </option>
                            <?php
endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Customer *</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Select Customer --</option>
                            <?php $customers->data_seek(0);
while ($customer = $customers->fetch_assoc()): ?>
                            <option value="<?= $customer['Customer_ID']?>" <?=($editSale['Customer_ID'] ?? ''
        ) == $customer['Customer_ID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($customer['name'])?>
                            </option>
                            <?php
endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Staff Member *</label>
                        <select name="emp_id" class="form-select" required>
                            <option value="">-- Select Staff --</option>
                            <?php $staffMembers->data_seek(0);
while ($staff = $staffMembers->fetch_assoc()): ?>
                            <option value="<?= $staff['Emp_ID']?>" <?=($editSale['Emp_ID'] ?? '') == $staff['Emp_ID']
        ? 'selected' : '' ?>>
                                <?= htmlspecialchars($staff['Name'])?>
                            </option>
                            <?php
endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Sale Date *</label>
                        <input type="date" name="sale_date" class="form-control" required
                            value="<?= $editSale['Sale_Date'] ?? date('Y-m-d')?>">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Amount (₹) *</label>
                        <input type="number" name="amount" class="form-control" min="0" max="99999999" step="0.01"
                            required value="<?= $editSale['Amount'] ?? ''?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <?php foreach (['Cash', 'Card', 'UPI'] as $method): ?>
                            <option <?=($editSale['Payment_Method'] ?? 'Cash') === $method ? 'selected' : '' ?>>
                                <?= $method?>
                            </option>
                            <?php
endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-vsms">
                            <?= $editSale ? 'Update' : 'Record Sale'?>
                        </button>
                        <?php if ($editSale): ?><a href="sales.php" class="btn btn-outline-vsms">Cancel</a>
                        <?php
endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-vsms">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi-receipt me-2 text-primary"></i>All Sales (
                    <?= $sales->num_rows?>)
                </span>
                <span class="badge bg-success" style="font-size:.8rem;">Total: ₹
                    <?= number_format($totalRevenue)?>
                </span>
            </div>
            <div class="card-body p-0">
                <?php if ($sales->num_rows > 0): ?>
                <table class="table table-vsms mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Staff</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Pay</th>
                            <th>Act</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sale = $sales->fetch_assoc()): ?>
                        <tr>
                            <td style="font-family:'DM Mono',monospace;color:#0f766e;">
                                <?= $sale['Sale_ID']?>
                            </td>
                            <td>
                                <?= htmlspecialchars($sale['Model_Name'])?>
                            </td>
                            <td>
                                <?= htmlspecialchars($sale['customer_name'])?>
                            </td>
                            <td>
                                <?= htmlspecialchars($sale['staff_name'])?>
                            </td>
                            <td>
                                <?= date('d M Y', strtotime($sale['Sale_Date']))?>
                            </td>
                            <td><strong>₹
                                    <?= number_format($sale['Amount'])?>
                                </strong></td>
                            <td><span class="badge badge-<?= strtolower($sale['Payment_Method'])?>">
                                    <?= $sale['Payment_Method']?>
                                </span></td>
                            <td>
                                <a href="sales.php?edit=<?= $sale['Sale_ID']?>"
                                    class="btn btn-sm btn-outline-secondary me-1"><i class="bi-pencil"></i></a>
                                <?php if (isAdmin()): ?>
                                <a href="sales.php?delete=<?= $sale['Sale_ID']?>" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Are you sure?')"><i class="bi-trash"></i></a>
                                <?php
        endif; ?>
                            </td>
                        </tr>
                        <?php
    endwhile; ?>
                    </tbody>
                </table>
                <?php
else: ?>
                <div class="empty-state"><i class="bi-receipt"></i>
                    <p>No sales recorded.</p>
                </div>
                <?php
endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>