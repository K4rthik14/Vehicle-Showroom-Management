<?php
session_start();
define('BASE_URL', '/vsms/');
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

$pageTitle = 'User Accounts';
$messageHtml = '';

function buildAlert(string $type, string $message): string {
    return '<div class="alert alert-' . $type . ' alert-dismissible">' . htmlspecialchars($message) . '</div>';
}

// -----------------------------
// Handle delete request (admin)
// -----------------------------
if (isset($_GET['delete'])) {
    $userIdToDelete = (int) $_GET['delete'];
    $loggedInUserId = (int) ($_SESSION['user_id'] ?? 0);

    if ($userIdToDelete !== $loggedInUserId) {
        $conn->query("DELETE FROM users WHERE user_id=$userIdToDelete");
        $messageHtml = buildAlert('success', 'User deleted.');
    } else {
        $messageHtml = buildAlert('warning', 'Cannot delete your own account.');
    }
}

// -------------------------
// Handle add/update request
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $employeeId = (int) ($_POST['emp_id'] ?? 0) ?: null;

    if ($username === '') {
        $messageHtml = buildAlert('warning', 'Username is required.');
    } elseif ($userId === 0 && $password === '') {
        $messageHtml = buildAlert('danger', 'Password is required for a new user.');
    } else {
        if ($userId > 0) {
            if ($password !== '') {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $updateWithPassword = $conn->prepare('UPDATE users SET username=?, password=?, role=?, emp_id=? WHERE user_id=?');
                $updateWithPassword->bind_param('sssii', $username, $passwordHash, $role, $employeeId, $userId);
                $queryOk = $updateWithPassword->execute();
            } else {
                $updateWithoutPassword = $conn->prepare('UPDATE users SET username=?, role=?, emp_id=? WHERE user_id=?');
                $updateWithoutPassword->bind_param('ssii', $username, $role, $employeeId, $userId);
                $queryOk = $updateWithoutPassword->execute();
            }

            $messageHtml = $queryOk
                ? buildAlert('success', 'User updated.')
                : buildAlert('danger', 'Error: ' . $conn->error);
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $createUser = $conn->prepare('INSERT INTO users(username, password, role, emp_id) VALUES(?, ?, ?, ?)');
            $createUser->bind_param('sssi', $username, $passwordHash, $role, $employeeId);

            $queryOk = $createUser->execute();
            $messageHtml = $queryOk
                ? buildAlert('success', 'User created.')
                : buildAlert('danger', 'Error: ' . $conn->error);
        }
    }
}

// ---------------------
// Load data for the page
// ---------------------
$editUser = null;
if (isset($_GET['edit'])) {
    $editUserId = (int) $_GET['edit'];
    $editUser = $conn->query("SELECT * FROM users WHERE user_id=$editUserId")->fetch_assoc();
}

$users = $conn->query('SELECT u.*, s.Name AS staff_name FROM users u LEFT JOIN STAFF s ON u.emp_id=s.Emp_ID ORDER BY u.user_id');
$staffMembers = $conn->query('SELECT Emp_ID, Name FROM STAFF ORDER BY Name');

require_once '../includes/header.php';
?>

<?= $messageHtml ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card-vsms">
            <div class="card-header"><i class="bi-shield-lock me-2 text-primary"></i><?= $editUser ? 'Edit' : 'Create' ?> User</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?= $editUser['user_id'] ?? 0 ?>">

                    <div class="mb-2">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($editUser['username'] ?? '') ?>">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Password <?= $editUser ? '(leave blank to keep)' : '*' ?></label>
                        <input type="password" name="password" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="staff" <?= ($editUser['role'] ?? 'staff') === 'staff' ? 'selected' : '' ?>>Staff</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Linked Staff Member</label>
                        <select name="emp_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php $staffMembers->data_seek(0); while ($staff = $staffMembers->fetch_assoc()): ?>
                                <option value="<?= $staff['Emp_ID'] ?>" <?= ($editUser['emp_id'] ?? '') == $staff['Emp_ID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($staff['Name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-vsms"><?= $editUser ? 'Update' : 'Create User' ?></button>
                        <?php if ($editUser): ?><a href="users.php" class="btn btn-outline-vsms">Cancel</a><?php endif; ?>
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
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td style="font-family:'DM Mono',monospace;">#<?= $user['user_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                                <?= $user['user_id'] == $_SESSION['user_id'] ? ' <span class="badge bg-secondary ms-1" style="font-size:.65rem;">You</span>' : '' ?>
                            </td>
                            <td><span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>"><?= ucfirst($user['role']) ?></span></td>
                            <td><?= htmlspecialchars($user['staff_name'] ?? '—') ?></td>
                            <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <a href="users.php?edit=<?= $user['user_id'] ?>" class="btn btn-sm btn-outline-secondary me-1"><i class="bi-pencil"></i></a>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?delete=<?= $user['user_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete user?')"><i class="bi-trash"></i></a>
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
