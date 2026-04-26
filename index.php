<?php
session_start();
include 'includes/db_connection.php';

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    if (mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);
        if ($row['role'] === 'admin') {
            $_SESSION['admin_email'] = $row['email'];
            header("Location: admin/dashboard.php");
        } elseif ($row['role'] === 'user') {
            $_SESSION['user_email'] = $row['email'];
            header("Location: user/dashboard.php");
        } elseif ($row['role'] === 'technician') {
            $_SESSION['tech_email'] = $row['email'];
            header("Location: technician/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSRMS – Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, body { font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; }

        .login-wrap { display: flex; height: 100vh; }

        /* Left panel */
        .login-aside {
            width: 480px;
            min-width: 380px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px 60px;
        }
        .brand-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 24px;
        }
        .brand-icon i { font-size: 2rem; color: #fff; }
        .login-title { font-size: 1.6rem; font-weight: 700; color: #1e1b4b; margin-bottom: 4px; }
        .login-sub   { font-size: .85rem; color: #6b7280; margin-bottom: 36px; }

        .form-label { font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .input-group-text {
            background: #f9fafb; border: 1px solid #e5e7eb; border-right: none; color: #9ca3af;
        }
        .form-control {
            background: #f9fafb; border: 1px solid #e5e7eb; border-left: none;
            font-size: .9rem; height: 44px;
        }
        .form-control:focus { background: #fff; box-shadow: none; border-color: #4f46e5; }
        .input-group:focus-within .input-group-text { border-color: #4f46e5; background: #fff; color: #4f46e5; }

        .btn-signin {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none; color: #fff; border-radius: 8px;
            padding: 12px; font-weight: 600; width: 100%;
            transition: opacity .2s;
        }
        .btn-signin:hover { opacity: .88; color: #fff; }

        .demo-accounts { background: #f0f2ff; border-radius: 10px; padding: 14px 18px; font-size: .78rem; color: #4338ca; margin-top: 28px; }
        .demo-accounts b { display: block; margin-bottom: 6px; color: #312e81; }

        /* Right panel */
        .login-right {
            flex: 1;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        .login-right .circle {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .c1 { width: 400px; height: 400px; top: -80px; right: -80px; }
        .c2 { width: 280px; height: 280px; bottom: 60px; left: -60px; }
        .c3 { width: 160px; height: 160px; bottom: 180px; right: 100px; }

        .right-content { text-align: center; color: #fff; position: relative; z-index: 2; padding: 40px; }
        .right-content h2 { font-size: 2rem; font-weight: 700; margin-bottom: 14px; }
        .right-content p  { font-size: .95rem; opacity: .75; max-width: 380px; margin: 0 auto 32px; line-height: 1.7; }

        .feature-pills { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        .pill {
            background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18);
            border-radius: 20px; padding: 6px 16px; font-size: .8rem; color: #c7d2fe;
        }

        @media (max-width: 900px) { .login-right { display: none; } .login-aside { width: 100%; min-width: unset; } }
    </style>
</head>
<body>
<div class="login-wrap">

    <!-- Left -->
    <div class="login-aside">
        <div class="brand-icon"><i class="bi bi-shield-check"></i></div>
        <div class="login-title">Welcome Back</div>
        <div class="login-sub">Sign in to Complaint & Service Request Management System</div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem;"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input class="form-control" type="email" name="email" placeholder="you@example.com" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input class="form-control" type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" name="login" class="btn-signin">
                Sign In &nbsp;<i class="bi bi-arrow-right-circle"></i>
            </button>
        </form>

        <div class="demo-accounts">
            <b><i class="bi bi-info-circle me-1"></i> Demo Accounts</b>
            <span>Admin:</span> admin@csrms.com / admin123<br>
            <span>User:</span> ali@csrms.com / user123<br>
            <span>Technician:</span> usman@csrms.com / tech123
        </div>
    </div>

    <!-- Right -->
    <div class="login-right">
        <div class="circle c1"></div>
        <div class="circle c2"></div>
        <div class="circle c3"></div>
        <div class="right-content">
            <h2>CSRMS Portal</h2>
            <p>Streamline complaint submissions, technician assignments, and resolution tracking — all in one place.</p>
            <div class="feature-pills">
                <span class="pill"><i class="bi bi-plus-circle me-1"></i>Submit Complaints</span>
                <span class="pill"><i class="bi bi-person-check me-1"></i>Assign Technicians</span>
                <span class="pill"><i class="bi bi-geo-alt me-1"></i>Track Status</span>
                <span class="pill"><i class="bi bi-bar-chart me-1"></i>Reports &amp; Analytics</span>
                <span class="pill"><i class="bi bi-arrow-up-circle me-1"></i>Escalation</span>
                <span class="pill"><i class="bi bi-download me-1"></i>CSV Export</span>
            </div>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
