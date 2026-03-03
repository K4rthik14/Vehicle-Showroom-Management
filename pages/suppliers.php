<?php
session_start();
define('BASE_URL', '/vsms/');
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Supplier Management';
$msg = '';

if (isset($_GET['delete']) && isAdmin()) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM SUPPLIER WHERE Supplier_ID=$id");
    $msg = '<div class="alert alert-success alert-dismissible">Supplier deleted.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)($_POST['supplier_id'] ?? 0);
    $name  = trim($_POST['s_name'] ?? '');
    $phone = trim($_POST['phone_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($name) {
        if ($id) {
            $stmt = $conn->prepare("UPDATE SUPPLIER SET S_Name=?,Phone_No=?,Email=? WHERE Supplier_ID=?");
            $stmt->bind_param("sssi",$name,$phone,$email,$id);
        } else {
            $stmt = $conn->prepare("INSERT INTO SUPPLIER(S_Name,Phone_No,Email) VALUES(?,?,?)");
            $stmt->bind_param("sss",$name,$phone,$email);
        }
        $stmt->execute();
        $msg = '<div class="alert alert-success alert-dismissible">Supplier '.($id?'updated':'added').'.</div>';
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $editData = $conn->query("SELECT * FROM SUPPLIER WHERE Supplier_ID=".(int)$_GET['edit'])->fetch_assoc();
}

$suppliers = $conn->query("
    SELECT s.*, COUNT(v.VIN) AS vehicle_count
    FROM SUPPLIER s LEFT JOIN VEHICLE v ON s.Supplier_ID=v.Supplier_ID
    GROUP BY s.Supplier_ID ORDER BY s.Supplier_ID DESC
");
require_once '../includes/header.php';
?>
<?= $msg ?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-truck me-2 text-primary"></i><?= $editData?'Edit':'Add' ?> Supplier</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="supplier_id" value="<?= $editData['Supplier_ID']??0 ?>">
                    <div class="mb-2">
                        <label class="form-label">Supplier Name *</label>
                        <input type="text" name="s_name" class="form-control" required value="<?= htmlspecialchars($editData['S_Name']??'') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_no" class="form-control" maxlength="15" value="<?= htmlspecialchars($editData['Phone_No']??'') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editData['Email']??'') ?>">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-vsms"><?= $editData?'Update':'Add Supplier' ?></button>
                        <?php if($editData): ?><a href="suppliers.php" class="btn btn-outline-vsms">Cancel</a><?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-truck me-2 text-primary"></i>All Suppliers (<?= $suppliers->num_rows ?>)</div>
            <div class="card-body p-0">
                <?php if ($suppliers->num_rows > 0): ?>
                <table class="table table-vsms mb-0">
                    <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Vehicles</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php while ($s = $suppliers->fetch_assoc()): ?>
                        <tr>
                            <td style="font-family:'DM Mono',monospace;">#<?= $s['Supplier_ID'] ?></td>
                            <td><strong><?= htmlspecialchars($s['S_Name']) ?></strong></td>
                            <td><?= htmlspecialchars($s['Phone_No']??'—') ?></td>
                            <td><?= htmlspecialchars($s['Email']??'—') ?></td>
                            <td><span class="badge bg-primary rounded-pill"><?= $s['vehicle_count'] ?></span></td>
                            <td>
                                <a href="suppliers.php?edit=<?= $s['Supplier_ID'] ?>" class="btn btn-sm btn-outline-secondary me-1"><i class="bi-pencil"></i></a>
                                <?php if(isAdmin()): ?>
                                <a href="suppliers.php?delete=<?= $s['Supplier_ID'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty-state"><i class="bi-truck"></i><p>No suppliers found.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
