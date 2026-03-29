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
    $conn->query("DELETE FROM VEHICLE WHERE VIN='".  $conn->real_escape_string($vin)."'");
    $msg = '<div class="alert alert-success alert-dismissible"><i class="bi-check-circle me-2"></i>Vehicle deleted.</div>';
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vin      = trim($_POST['vin'] ?? '');
    $model    = trim($_POST['model_name'] ?? '');
    $status   = $_POST['status'] ?? 'Available';
    $fuel     = $_POST['fuel_type'] ?? 'Petrol';
    $qty      = (int)($_POST['stock_quantity'] ?? 0);
    $price    = (float)($_POST['price'] ?? 0);
    $img_url  = trim($_POST['image_url'] ?? '');
    $sup_id   = (int)($_POST['supplier_id'] ?? 0) ?: 'NULL';
    $editing  = $_POST['editing'] ?? '';

    if ($vin && $model) {
        if ($editing) {
            $stmt = $conn->prepare("UPDATE VEHICLE SET Model_Name=?,Status=?,Fuel_type=?,Stock_Quantity=?,Supplier_ID=?, Price=?, Image_URL=? WHERE VIN=?");
            $stmt->bind_param("sssiisds", $model,$status,$fuel,$qty,$sup_id,$price,$img_url,$vin);
        } else {
            $stmt = $conn->prepare("INSERT INTO VEHICLE(VIN,Model_Name,Status,Fuel_type,Stock_Quantity,Supplier_ID, Price, Image_URL) VALUES(?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssiiids", $vin,$model,$status,$fuel,$qty,$sup_id,$price,$img_url);
        }
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success alert-dismissible"><i class="bi-check-circle me-2"></i>Vehicle '.($editing?'updated':'added').' successfully.</div>';
        } else {
            $msg = '<div class="alert alert-danger alert-dismissible"><i class="bi-exclamation-triangle me-2"></i>Error: '.$conn->error.'</div>';
        }
    }
}

// Ensure the form shows again if editing
$editData = null;
$showModal = false;
if (isset($_GET['edit'])) {
    $vin = $_GET['edit'];
    $editData = $conn->query("SELECT * FROM VEHICLE WHERE VIN='".  $conn->real_escape_string($vin)."'")->fetch_assoc();
    $showModal = true;
}

// Fetch grouped brands
$brandsResult = $conn->query("SELECT DISTINCT SUBSTRING_INDEX(Model_Name, ' ', 1) as Brand FROM VEHICLE ORDER BY Brand");
$brands = [];
while($b = $brandsResult->fetch_assoc()) {
    $brands[] = $b['Brand'];
}

$activeBrand = $_GET['brand'] ?? 'All';

// Construct vehicles query
$queryStr = "SELECT v.*, s.S_Name FROM VEHICLE v LEFT JOIN SUPPLIER s ON v.Supplier_ID=s.Supplier_ID";
if ($activeBrand !== 'All') {
    $queryStr .= " WHERE v.Model_Name LIKE '" . $conn->real_escape_string($activeBrand) . "%'";
}
$queryStr .= " ORDER BY v.Model_Name";
$vehicles = $conn->query($queryStr);

$suppliers = $conn->query("SELECT * FROM SUPPLIER ORDER BY S_Name");

require_once '../includes/header.php';
?>

<?= $msg ?>

<!-- Brands Filter -->
<div class="brand-filters">
    <a href="vehicles.php?brand=All" class="brand-btn <?= $activeBrand === 'All' ? 'active' : '' ?>">
        All Brands
    </a>
    <?php foreach($brands as $brand): ?>
        <a href="vehicles.php?brand=<?= urlencode($brand) ?>" class="brand-btn <?= $activeBrand === $brand ? 'active' : '' ?>">
            <?= htmlspecialchars($brand) ?>
        </a>
    <?php endforeach; ?>
    
    <button class="btn btn-primary-vsms ms-auto" data-bs-toggle="modal" data-bs-target="#vehicleModal" style="white-space: nowrap;">
        <i class="bi-plus-circle me-1"></i> Add Vehicle
    </button>
</div>

<!-- Vehicles Grid -->
<div class="row g-4 mb-4">
    <?php if ($vehicles->num_rows > 0): ?>
        <?php while ($v = $vehicles->fetch_assoc()): 
            $isAvailable = $v['Status'] === 'Available' && $v['Stock_Quantity'] > 0;
            $badgeClass = $isAvailable ? 'badge-available' : 'badge-sold';
            $badgeText = $isAvailable ? 'Available Now' : 'Out of Stock';
            $img = !empty($v['Image_URL']) ? $v['Image_URL'] : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0be2?w=800&q=80';
            $brandName = explode(' ', $v['Model_Name'])[0];
        ?>
        <div class="col-sm-6 col-md-4 col-xl-3">
            <div class="vehicle-card" onclick="window.location='vehicles.php?edit=<?= urlencode($v['VIN']) ?>'">
                <div class="v-badge <?= $badgeClass ?>"><?= $badgeText ?></div>
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($v['Model_Name']) ?>" class="v-image">
                <div class="v-brand"><?= htmlspecialchars($brandName) ?></div>
                <div class="v-model"><?= htmlspecialchars($v['Model_Name']) ?></div>
                <div class="v-price">Rs. <?= number_format($v['Price'] ?? 0) ?></div>
                
                <div class="v-stats">
                    <div class="v-stat"><i class="bi-ev-front me-1"></i> <?= htmlspecialchars($v['Fuel_type']) ?></div>
                    <div class="v-stat"><i class="bi-box-seam me-1"></i> <?= $v['Stock_Quantity'] ?> left</div>
                </div>

                <?php if (isAdmin()): ?>
                <div class="mt-3 pt-3 border-top d-flex gap-2" onclick="event.stopPropagation();">
                    <a href="vehicles.php?delete=<?= urlencode($v['VIN']) ?>" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Delete this vehicle?')">
                        <i class="bi-trash"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="empty-state">
                <i class="bi-car-front" style="font-size:3rem;"></i>
                <p class="mt-3" style="font-size:1.1rem;font-weight:500;">No vehicles found for <?= htmlspecialchars($activeBrand) ?>.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Vehicle Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:20px;border:none;">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold fs-4"><?= $editData ? 'Edit Vehicle' : 'Add New Vehicle' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <form method="POST" action="vehicles.php">
                    <?php if ($editData): ?>
                        <input type="hidden" name="editing" value="1">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">VIN *</label>
                        <input type="text" name="vin" class="form-control" maxlength="20" required
                               value="<?= htmlspecialchars($editData['VIN'] ?? '') ?>"
                               <?= $editData ? 'readonly' : '' ?>>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-7">
                            <label class="form-label">Model Name *</label>
                            <input type="text" name="model_name" class="form-control" required value="<?= htmlspecialchars($editData['Model_Name'] ?? '') ?>">
                        </div>
                        <div class="col-5">
                            <label class="form-label">Price (Rs.) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" required value="<?= htmlspecialchars($editData['Price'] ?? 0) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="url" name="image_url" class="form-control" placeholder="https://..." value="<?= htmlspecialchars($editData['Image_URL'] ?? '') ?>">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Fuel Type</label>
                            <select name="fuel_type" class="form-select">
                                <?php foreach (['Petrol','Diesel','Electric','Hybrid','CNG'] as $f): ?>
                                    <option <?= ($editData['Fuel_type']??'Petrol')===$f?'selected':'' ?>><?= $f ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="Available" <?= ($editData['Status']??'Available')==='Available'?'selected':'' ?>>Available</option>
                                <option value="Sold" <?= ($editData['Status']??'')==='Sold'?'selected':'' ?>>Sold</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-5">
                            <label class="form-label">Stock Qty</label>
                            <input type="number" name="stock_quantity" class="form-control" min="0" value="<?= htmlspecialchars($editData['Stock_Quantity'] ?? 0) ?>">
                        </div>
                        <div class="col-7">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">-- None --</option>
                                <?php $suppliers->data_seek(0); while ($s = $suppliers->fetch_assoc()): ?>
                                    <option value="<?= $s['Supplier_ID'] ?>" <?= ($editData['Supplier_ID']??'')==$s['Supplier_ID']?'selected':'' ?>>
                                        <?= htmlspecialchars($s['S_Name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-vsms w-100 py-3 rounded-3 fs-6"><?= $editData ? 'Update Vehicle' : 'Save Vehicle' ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($showModal): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var myModal = new bootstrap.Modal(document.getElementById('vehicleModal'));
    myModal.show();
});
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
