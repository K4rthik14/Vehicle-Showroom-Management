<?php
session_start();
define('BASE_URL', '/vsms/');
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['emp_id'] = $user['emp_id'];
            header("Location: pages/dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VSMS — Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(160deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.3);
        }
        .brand-icon {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #a78bfa, #60a5fa);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1rem;
        }
        h2 { font-weight: 800; font-size: 1.5rem; color: #1e1b4b; }
        .form-label { font-weight: 600; font-size: 0.82rem; color: #64748b; }
        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding: 0.6rem 0.9rem;
        }
        .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.12); }
        .btn-login {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.65rem;
            color: #fff;
            width: 100%;
            transition: all 0.2s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(79,70,229,0.4); color: #fff; }
        .hint-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.78rem;
            color: #64748b;
            font-family: 'DM Mono', monospace;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-3">
        <div class="brand-icon">🚗</div>
        <h2>VSMS</h2>
        <p class="text-muted" style="font-size:0.85rem;">Vehicle Showroom Management System</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2" style="font-size:0.85rem;border-radius:10px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">USERNAME</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
        <div class="mb-4">
            <label class="form-label">PASSWORD</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn-login">Sign In →</button>
    </form>

    <div class="hint-box mt-3">
        <strong>Demo credentials:</strong><br>
        Admin: admin / admin123<br>
        Staff: staff1 / staff123
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
