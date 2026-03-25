<?php
session_start();
define('BASE_URL', '/vsms/');
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit();
}

$loginError = '';

// Handle login form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $loginError = 'Please fill in both username and password.';
    } else {
        $findUser = $conn->prepare('SELECT * FROM users WHERE username = ?');
        $findUser->bind_param('s', $username);
        $findUser->execute();
        $user = $findUser->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['emp_id'] = $user['emp_id'];

            header('Location: pages/dashboard.php');
            exit();
        }

        $loginError = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VSMS - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top left, #ccfbf1 0%, #e0f2fe 35%, #f8fafc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 2.25rem;
            width: 100%;
            max-width: 430px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 24px 60px rgba(15,23,42,0.14);
        }

        .brand-icon {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #14b8a6, #0ea5e9);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            margin: 0 auto 1rem;
        }

        h2 {
            font-weight: 800;
            font-size: 1.5rem;
            color: #0f172a;
        }

        .subtitle {
            color: #64748b;
            font-size: 0.9rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.8rem;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        .form-control {
            border-radius: 10px;
            border: 1.5px solid #dbe2ea;
            padding: 0.62rem 0.9rem;
            font-size: 0.92rem;
        }

        .form-control:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 3px rgba(15,118,110,0.12);
        }

        .btn-login {
            background: linear-gradient(135deg, #0f766e, #0ea5a4);
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.92rem;
            padding: 0.68rem;
            color: #fff;
            width: 100%;
            transition: all 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(15,118,110,0.28);
            color: #fff;
        }

        .hint-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 0.8rem 0.95rem;
            font-size: 0.78rem;
            color: #475569;
            font-family: 'DM Mono', monospace;
            border: 1px solid #e2e8f0;
        }

        .hint-box strong {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8rem;
            color: #0f172a;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-3">
        <div class="brand-icon">🚗</div>
        <h2>VSMS</h2>
        <p class="subtitle mb-0">Vehicle Showroom Management System</p>
    </div>

    <?php if ($loginError): ?>
        <div class="alert alert-danger py-2" style="font-size:0.85rem;border-radius:10px;"><?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn-login">Sign In</button>
    </form>

    <div class="hint-box mt-3">
        <strong>Demo credentials</strong><br>
        admin / admin123<br>
        staff1 / staff123
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
