<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/db_connection.php';

$user_name = "Guest";

if (isset($_SESSION['admin_email'])) {
    $em = mysqli_real_escape_string($conn, $_SESSION['admin_email']);
    $r  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE email='$em'"));
    $user_name = $r['name'];
    $role_label = "Admin";
} elseif (isset($_SESSION['user_email'])) {
    $em = mysqli_real_escape_string($conn, $_SESSION['user_email']);
    $r  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE email='$em'"));
    $user_name = $r['name'];
    $role_label = "User";
} elseif (isset($_SESSION['tech_email'])) {
    $em = mysqli_real_escape_string($conn, $_SESSION['tech_email']);
    $r  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE email='$em'"));
    $user_name = $r['name'];
    $role_label = "Technician";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSRMS – Complaint & Service Request Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #f0f2f5; }

        /* ── Sidebar ── */
        .sidebar {
            width: 250px;
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            background: linear-gradient(180deg, #1a2236 0%, #243050 100%);
            color: #cdd6f4;
            padding-top: 0;
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar-brand {
            background: rgba(0,0,0,.25);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand span {
            font-size: .95rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }
        .sidebar-brand small { font-size: .7rem; color: #8a9bbf; font-weight: 400; }
        .sidebar-section {
            font-size: .68rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #5a6a8a;
            padding: 18px 20px 6px;
        }
        .sidebar a {
            color: #b0bdd6;
            padding: 9px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-size: .88rem;
            transition: all .2s;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,.07);
            color: #fff;
            border-left-color: #4f7ef8;
        }
        .sidebar a i { font-size: 1rem; width: 18px; text-align: center; }
        .sidebar hr { border-color: rgba(255,255,255,.08); margin: 8px 0; }

        /* ── Topbar ── */
        .topbar {
            margin-left: 250px;
            background: #fff;
            padding: 10px 28px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .topbar .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .88rem;
            color: #374151;
        }
        .topbar .role-badge {
            background: #eef2ff;
            color: #4338ca;
            font-size: .72rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }
        .topbar img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }

        /* ── Main content ── */
        .content {
            margin-left: 250px;
            padding: 28px;
        }

        /* ── Cards ── */
        .stat-card {
            border-radius: 12px;
            padding: 22px 20px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 14px rgba(0,0,0,.12);
        }
        .stat-card .icon { font-size: 2rem; opacity: .85; }
        .stat-card .label { font-size: .78rem; opacity: .85; margin-bottom: 2px; }
        .stat-card .value { font-size: 1.7rem; font-weight: 700; }

        /* ── Priority badges ── */
        .badge-low      { background: #d1fae5; color: #065f46; }
        .badge-medium   { background: #fef3c7; color: #92400e; }
        .badge-high     { background: #fee2e2; color: #991b1b; }
        .badge-critical { background: #fce7f3; color: #9d174d; }

        /* ── Section header ── */
        .section-header {
            background: #4051b5;
            color: #fff;
            padding: 12px 18px;
            border-radius: 8px 8px 0 0;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-body {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 20px;
        }

        /* ── Table ── */
        .table th { background: #f8fafc; font-size: .82rem; text-transform: uppercase; letter-spacing: .04em; }
        .table td { font-size: .88rem; vertical-align: middle; }
    </style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
    <div class="user-pill">
        <span class="role-badge"><?= $role_label ?? '' ?></span>
        <span><?= htmlspecialchars($user_name) ?></span>
        <div class="dropdown">
            <img src="../assets/images/profile.png" alt="profile" role="button" data-bs-toggle="dropdown">
            <ul class="dropdown-menu dropdown-menu-end">
                <?php if (isset($_SESSION['user_email'])): ?>
                    <li><a class="dropdown-item" href="../user/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</div>
