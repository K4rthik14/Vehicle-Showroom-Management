<?php
session_start();
define('BASE_URL', '/vsms/');
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Customer Management';
$msg = '';

if (isset($_GET['delete']) && isAdmin()) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM CUSTOMER WHERE Customer_ID=$id");
    $msg = '<div class="alert alert-success alert-dismissible">Customer deleted.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)($_POST['customer_id'] ?? 0);
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $addr  = trim($_POST['address'] ?? '');
    if ($fname && $lname && $phone) {
        if ($id) {
            $stmt = $conn->prepare("UPDATE CUSTOMER SET Fname=?,Lname=?,Phone=?,Email=?,Address=? WHERE Customer_ID=?");
            $stmt->bind_param("sssssi",$fname,$lname,$phone,$email,$addr,$id);
        } else {
            $stmt = $conn->prepare("INSERT INTO CUSTOMER(Fname,Lname,Phone,Email,Address) VALUES(?,?,?,?,?)");
            $stmt->bind_param("sssss",$fname,$lname,$phone,$email,$addr);
        }
        $stmt->execute();
        $msg = '<div class="alert alert-success alert-dismissible">Customer '.($id?'updated':'added').'.</div>';
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $editData = $conn->query("SELECT * FROM CUSTOMER WHERE Customer_ID=". (int)$_GET['edit'])->fetch_assoc();
}

$customers = $conn->query("SELECT * FROM CUSTOMER ORDER BY Customer_ID DESC");
require_once '../includes/header.php';
?>
<?= $msg ?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-person-plus me-2 text-primary"></i><?= $editData?'Edit':'Add' ?> Customer</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="customer_id" value="<?= $editData['Customer_ID']??0 ?>">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="fname" class="form-control" required value="<?= htmlspecialchars($editData['Fname']??'') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="lname" class="form-control" required value="<?= htmlspecialchars($editData['Lname']??'') ?>">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" class="form-control" maxlength="15" required value="<?= htmlspecialchars($editData['Phone']??'') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editData['Email']??'') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($editData['Address']??'') ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-vsms"><?= $editData?'Update':'Add Customer' ?></button>
                        <?php if($editData): ?><a href="customers.php" class="btn btn-outline-vsms">Cancel</a><?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-people me-2 text-primary"></i>All Customers (<?= $customers->num_rows ?>)</div>
            <div class="card-body p-0">
                <?php if ($customers->num_rows > 0): ?>
                <table class="table table-vsms mb-0">
                    <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Address</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php while ($c = $customers->fetch_assoc()): ?>
                        <tr>
                            <td style="font-family:'DM Mono',monospace;">#<?= $c['Customer_ID'] ?></td>
                            <td><strong><?= htmlspecialchars($c['Fname'].' '.$c['Lname']) ?></strong></td>
                            <td><?= htmlspecialchars($c['Phone']) ?></td>
                            <td><?= htmlspecialchars($c['Email']??'—') ?></td>
                            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($c['Address']??'—') ?></td>
                            <td>
                                <a href="customers.php?edit=<?= $c['Customer_ID'] ?>" class="btn btn-sm btn-outline-secondary me-1"><i class="bi-pencil"></i></a>
                                <?php if(isAdmin()): ?>
                                <a href="customers.php?delete=<?= $c['Customer_ID'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty-state"><i class="bi-people"></i><p>No customers found.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
