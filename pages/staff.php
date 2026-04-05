<?php
session_start();
define('BASE_URL', '/vsms/');
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Staff Management';
$msg = '';

if (isset($_GET['delete']) && isAdmin()) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM STAFF WHERE Emp_ID=$id");
    $msg = '<div class="alert alert-success alert-dismissible">Staff member deleted.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['emp_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desig = trim($_POST['designation'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $doj = $_POST['doj'] ?? '';
    if ($name) {
        if ($id) {
            $stmt = $conn->prepare("UPDATE STAFF SET Name=?,Designation=?,Contact=?,DOJ=? WHERE Emp_ID=?");
            $stmt->bind_param("ssssi", $name, $desig, $contact, $doj, $id);
        }
        else {
            $stmt = $conn->prepare("INSERT INTO STAFF(Name,Designation,Contact,DOJ) VALUES(?,?,?,?)");
            $stmt->bind_param("ssss", $name, $desig, $contact, $doj);
        }
        $stmt->execute();
        $msg = '<div class="alert alert-success alert-dismissible">Staff ' . ($id ? 'updated' : 'added') . '.</div>';
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $editData = $conn->query("SELECT * FROM STAFF WHERE Emp_ID=" . (int)$_GET['edit'])->fetch_assoc();
}

$staff = $conn->query("SELECT * FROM STAFF ORDER BY Emp_ID DESC");
require_once '../includes/header.php';
?>
<?= $msg?>
<div class="row g-3">
    <div class="col-lg-4">
        <?php if (isAdmin()): ?>
        <div class="card-vsms">
            <div class="card-header"><i class="bi-person-badge me-2 text-primary"></i>
                <?= $editData ? 'Edit' : 'Add'?> Staff
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="emp_id" value="<?= $editData['Emp_ID'] ?? 0?>">
                    <div class="mb-2">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" required
                            value="<?= htmlspecialchars($editData['Name'] ?? '')?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" class="form-control"
                            value="<?= htmlspecialchars($editData['Designation'] ?? '')?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contact</label>
                        <input type="text" name="contact" class="form-control" maxlength="15"
                            value="<?= htmlspecialchars($editData['Contact'] ?? '')?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date of Joining</label>
                        <input type="date" name="doj" class="form-control" value="<?= $editData['DOJ'] ?? ''?>">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-vsms">
                            <?= $editData ? 'Update' : 'Add Staff'?>
                        </button>
                        <?php if ($editData): ?><a href="staff.php" class="btn btn-outline-vsms">Cancel</a>
                        <?php
    endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php
else: ?>
        <div class="card-vsms">
            <div class="card-body py-5 text-center text-muted">
                <i class="bi-lock fs-1 d-block mb-3 opacity-50"></i>
                <h6 class="fw-bold text-dark">Access Restricted</h6>
                <p class="mb-0 small">Contact admin to manage staff records.</p>
            </div>
        </div>
        <?php
endif; ?>
    </div>
    <div class="col-lg-8">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-person-badge me-2 text-primary"></i>All Staff (
                <?= $staff->num_rows?>)
            </div>
            <div class="card-body p-0">
                <?php if ($staff->num_rows > 0): ?>
                <table class="table table-vsms mb-0">
                    <thead>
                        <tr>
                            <th>Emp ID</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Contact</th>
                            <th>DOJ</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($s = $staff->fetch_assoc()): ?>
                        <tr>
                            <td style="font-family:'DM Mono',monospace;">#
                                <?= $s['Emp_ID']?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($s['Name'])?>
                                </strong></td>
                            <td>
                                <?= htmlspecialchars($s['Designation'] ?? '—')?>
                            </td>
                            <td>
                                <?= htmlspecialchars($s['Contact'] ?? '—')?>
                            </td>
                            <td>
                                <?= $s['DOJ'] ? date('d M Y', strtotime($s['DOJ'])) : '—'?>
                            </td>
                            <td>
                                <?php if (isAdmin()): ?>
                                <a href="staff.php?edit=<?= $s['Emp_ID']?>"
                                    class="btn btn-sm btn-outline-secondary me-1"><i class="bi-pencil"></i></a>
                                <a href="staff.php?delete=<?= $s['Emp_ID']?>" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Are you sure?')"><i class="bi-trash"></i></a>
                                <?php
        else: ?>
                                <span class="text-muted small">—</span>
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
                <div class="empty-state"><i class="bi-person-badge"></i>
                    <p>No staff records.</p>
                </div>
                <?php
endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>