<?php
session_start();
define('BASE_URL', '/vsms/');
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
$pageTitle = 'User Accounts';
$msg = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE user_id=$id");
        $msg = '<div class="alert alert-success alert-dismissible">User deleted.</div>';
    } else {
        $msg = '<div class="alert alert-warning alert-dismissible">Cannot delete your own account.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid      = (int)($_POST['user_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'staff';
    $emp_id   = (int)($_POST['emp_id'] ?? 0) ?: null;

    if ($username) {
        if ($uid) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username=?,password=?,role=?,emp_id=? WHERE user_id=?");
                $stmt->bind_param("sssii",$username,$hash,$role,$emp_id,$uid);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username=?,role=?,emp_id=? WHERE user_id=?");
                $stmt->bind_param("ssii",$username,$role,$emp_id,$uid);
            }
        } else {
            if (!$password) { $msg = '<div class="alert alert-danger alert-dismissible">Password required for new user.</div>'; goto skip; }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users(username,password,role,emp_id) VALUES(?,?,?,?)");
            $stmt->bind_param("sssi",$username,$hash,$role,$emp_id);
        }
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success alert-dismissible">User '.($uid?'updated':'created').'.</div>';
        } else {
            $msg = '<div class="alert alert-danger alert-dismissible">Error: '.$conn->error.'</div>';
        }
    }
    skip:
}

$editData = null;
if (isset($_GET['edit'])) {
    $editData = $conn->query("SELECT * FROM users WHERE user_id=".(int)$_GET['edit'])->fetch_assoc();
}

$users = $conn->query("SELECT u.*, s.Name AS staff_name FROM users u LEFT JOIN STAFF s ON u.emp_id=s.Emp_ID ORDER BY u.user_id");
$staff = $conn->query("SELECT Emp_ID, Name FROM STAFF ORDER BY Name");
require_once '../includes/header.php';
?>
<?= $msg ?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-shield-lock me-2 text-primary"></i><?= $editData?'Edit':'Create' ?> User</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?= $editData['user_id']??0 ?>">
                    <div class="mb-2">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($editData['username']??'') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password <?= $editData?'(leave blank to keep)':' *' ?></label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="admin" <?= ($editData['role']??'')==='admin'?'selected':'' ?>>Admin</option>
                            <option value="staff" <?= ($editData['role']??'staff')==='staff'?'selected':'' ?>>Staff</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Linked Staff Member</label>
                        <select name="emp_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php $staff->data_seek(0); while ($s = $staff->fetch_assoc()): ?>
                                <option value="<?= $s['Emp_ID'] ?>" <?= ($editData['emp_id']??'')==$s['Emp_ID']?'selected':'' ?>><?= htmlspecialchars($s['Name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-vsms"><?= $editData?'Update':'Create User' ?></button>
                        <?php if($editData): ?><a href="users.php" class="btn btn-outline-vsms">Cancel</a><?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-shield-lock me-2 text-primary"></i>System Users (<?= $users->num_rows ?>)</div>
            <div class="card-body p-0">
                <table class="table table-vsms mb-0">
                    <thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Linked Staff</th><th>Created</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td style="font-family:'DM Mono',monospace;">#<?= $u['user_id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['username']) ?></strong><?= $u['user_id']==$_SESSION['user_id']?' <span class="badge bg-secondary ms-1" style="font-size:.65rem;">You</span>':'' ?></td>
                            <td><span class="badge <?= $u['role']==='admin'?'bg-danger':'bg-primary' ?>"><?= ucfirst($u['role']) ?></span></td>
                            <td><?= htmlspecialchars($u['staff_name']??'—') ?></td>
                            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <a href="users.php?edit=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-secondary me-1"><i class="bi-pencil"></i></a>
                                <?php if($u['user_id'] != $_SESSION['user_id']): ?>
                                <a href="users.php?delete=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete user?')"><i class="bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
