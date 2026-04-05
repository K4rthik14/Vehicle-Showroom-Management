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
    }
    else {
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
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            margin: 0;
        }

        .split-layout {
            display: flex;
            min-height: 100vh;
        }

        .split-left {
            flex: 1;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 992px) {
            .split-left {
                display: flex;
            }
        }

        .split-left::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
        }

        .split-left::after {
            content: '';
            position: absolute;
            bottom: -10%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
        }

        .left-content {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 480px;
        }

        .left-content h1 {
            font-weight: 800;
            font-size: 2.8rem;
            margin-bottom: 1rem;
        }

        .left-content p {
            font-size: 1.1rem;
            opacity: 0.85;
            line-height: 1.6;
        }

        .split-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #ffffff;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .brand-icon-mobile {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .brand-icon-mobile-wrapper {
            display: block;
        }

        @media (min-width: 992px) {
            .brand-icon-mobile-wrapper {
                display: none;
            }
        }

        h2 {
            font-weight: 800;
            font-size: 1.75rem;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .subtitle {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 0.4rem;
        }

        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn-login {
            background: #2563eb;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 0.8rem;
            color: #fff;
            width: 100%;
            transition: all 0.2s;
            margin-top: 0.5rem;
        }

        .btn-login:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
            color: #fff;
        }



        .car-illustration {
            font-size: 8rem;
            margin-bottom: 1rem;
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.15));
        }
    </style>
</head>

<body>

    <div class="split-layout">
        <div class="split-left">
            <div class="left-content">
                <div class="car-illustration">🏎️</div>
                <h1>Drivr Showroom</h1>
                <p>Experience the ultimate vehicle showroom management system. Powerful tools, elegant interface,
                    seamless workflow.</p>
            </div>
        </div>
        <div class="split-right">
            <div class="login-container">
                <div class="brand-icon-mobile-wrapper">
                    <div class="brand-icon-mobile">🚗</div>
                </div>
                <h2>Welcome back</h2>
                <p class="subtitle">Please enter your details to sign in.</p>

                <?php if ($loginError): ?>
                <div class="alert alert-danger py-2" style="font-size:0.85rem;border-radius:10px;">
                    <?= htmlspecialchars($loginError)?>
                </div>
                <?php
endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Enter your username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '')?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn-login">Sign in</button>
                </form>


            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>