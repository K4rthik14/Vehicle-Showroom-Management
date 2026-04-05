<?php
session_start();
define('BASE_URL', '/vsms/');
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Vehicle Inventory';

$msg = '';

// DELETE
if (isset($_GET['delete']) && isAdmin()) {
    $vin = $_GET['delete'];
    $conn->query("DELETE FROM VEHICLE WHERE VIN='" . $conn->real_escape_string($vin) . "'");
    $msg = '<div class="alert alert-success alert-dismissible">Vehicle deleted.</div>';
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vin = trim($_POST['vin'] ?? '');
    $model = trim($_POST['model_name'] ?? '');
    $status = $_POST['status'] ?? 'Available';
    $fuel = $_POST['fuel_type'] ?? 'Petrol';
    $qty = (int)($_POST['stock_quantity'] ?? 0);
    $sup_id = (int)($_POST['supplier_id'] ?? 0) ?: 'NULL';
    $editing = $_POST['editing'] ?? '';

    if ($vin && $model) {
        if ($editing) {
            $stmt = $conn->prepare("UPDATE VEHICLE SET Model_Name=?,Status=?,Fuel_type=?,Stock_Quantity=?,Supplier_ID=? WHERE VIN=?");
            $stmt->bind_param("sssiis", $model, $status, $fuel, $qty, $sup_id, $vin);
        }
        else {
            $stmt = $conn->prepare("INSERT INTO VEHICLE(VIN,Model_Name,Status,Fuel_type,Stock_Quantity,Supplier_ID) VALUES(?,?,?,?,?,?)");
            $stmt->bind_param("ssssii", $vin, $model, $status, $fuel, $qty, $sup_id);
        }
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success alert-dismissible">Vehicle ' . ($editing ? 'updated' : 'added') . ' successfully.</div>';
        }
        else {
            $msg = '<div class="alert alert-danger alert-dismissible">Error: ' . $conn->error . '</div>';
        }
    }
}

// Edit prefill
$editData = null;
if (isset($_GET['edit'])) {
    $vin = $_GET['edit'];
    $editData = $conn->query("SELECT * FROM VEHICLE WHERE VIN='" . $conn->real_escape_string($vin) . "'")->fetch_assoc();
}

$vehicles = $conn->query("SELECT v.*, s.S_Name FROM VEHICLE v LEFT JOIN SUPPLIER s ON v.Supplier_ID=s.Supplier_ID ORDER BY v.Model_Name");
$suppliers = $conn->query("SELECT * FROM SUPPLIER ORDER BY S_Name");

require_once '../includes/header.php';
?>

<?= $msg?>

<div class="row g-3">
    <!-- Form -->
    <div class="col-lg-4">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-plus-circle me-2 text-primary"></i>
                <?= $editData ? 'Edit Vehicle' : 'Add Vehicle'?>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($editData): ?>
                    <input type="hidden" name="editing" value="1">
                    <?php
endif; ?>
                    <div class="mb-3">
                        <label class="form-label">VIN *</label>
                        <input type="text" name="vin" class="form-control" maxlength="20" required
                            value="<?= htmlspecialchars($editData['VIN'] ?? '')?>" <?= $editData ? 'readonly' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model Name *</label>
                        <input type="text" name="model_name" class="form-control" required
                            value="<?= htmlspecialchars($editData['Model_Name'] ?? '')?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fuel Type</label>
                        <select name="fuel_type" class="form-select">
                            <?php foreach (['Petrol', 'Diesel', 'Electric', 'Hybrid', 'CNG'] as $f): ?>
                            <option <?=($editData['Fuel_type'] ?? 'Petrol' )===$f ? 'selected' : '' ?>>
                                <?= $f?>
                            </option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option <?=($editData['Status'] ?? 'Available' )==='Available' ? 'selected' : '' ?>
                                >Available
                            </option>
                            <option <?=($editData['Status'] ?? '' )==='Sold' ? 'selected' : '' ?>>Sold</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" min="0"
                            value="<?= htmlspecialchars($editData['Stock_Quantity'] ?? 0)?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php $suppliers->data_seek(0);
while ($s = $suppliers->fetch_assoc()): ?>
                            <option value="<?= $s['Supplier_ID']?>" <?=($editData['Supplier_ID'] ?? ''
                                )==$s['Supplier_ID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['S_Name'])?>
                            </option>
                            <?php
endwhile; ?>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-primary-vsms btn">
                            <?= $editData ? 'Update' : 'Add Vehicle'?>
                        </button>
                        <?php if ($editData): ?>
                        <a href="vehicles.php" class="btn btn-outline-vsms">Cancel</a>
                        <?php
endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="col-lg-8">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-car-front me-2 text-primary"></i>All Vehicles (
                <?= $vehicles->num_rows?>)
            </div>
            <div class="card-body p-0">
                <?php if ($vehicles->num_rows > 0): ?>
                <table class="table table-vsms mb-0">
                    <thead>
                        <tr>
                            <th>VIN</th>
                            <th>Model</th>
                            <th>Fuel</th>
                            <th>Status</th>
                            <th>Qty</th>
                            <th>Supplier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($v = $vehicles->fetch_assoc()): ?>
                        <tr>
                            <td><span style="font-family:'DM Mono',monospace;font-size:0.78rem;">
                                    <?= htmlspecialchars(substr($v['VIN'], 0, 14)) . '...'?>
                                </span></td>
                            <td><strong>
                                    <?= htmlspecialchars($v['Model_Name'])?>
                                </strong></td>
                            <td>
                                <?= $v['Fuel_type']?>
                            </td>
                            <td><span class="badge badge-<?= strtolower($v['Status'])?>">
                                    <?= $v['Status']?>
                                </span></td>
                            <td>
                                <?= $v['Stock_Quantity']?>
                            </td>
                            <td>
                                <?= htmlspecialchars($v['S_Name'] ?? '—')?>
                            </td>
                            <td>
                                <a href="vehicles.php?edit=<?= urlencode($v['VIN'])?>"
                                    class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi-pencil"></i>
                                </a>
                                <?php if (isAdmin()): ?>
                                <a href="vehicles.php?delete=<?= urlencode($v['VIN'])?>"
                                    class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                    <i class="bi-trash"></i>
                                </a>
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
                <div class="empty-state"><i class="bi-car-front"></i>
                    <p>No vehicles found.</p>
                </div>
                <?php
endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>