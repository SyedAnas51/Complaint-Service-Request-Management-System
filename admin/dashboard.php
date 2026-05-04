<?php
include '../includes/auth_admin.php';
include '../includes/header.php';
include '../includes/sidebar_admin.php';
include '../includes/db_connection.php';

// Stats
$total      = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints"))[0];
$open       = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status='Open'"))[0];
$inprog     = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status='In Progress'"))[0];
$resolved   = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status='Resolved'"))[0];
$escalated  = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE is_escalated=1"))[0];
$users_c    = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='user'"))[0];
$techs_c    = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='technician'"))[0];
$unassigned = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE assigned_to IS NULL AND status NOT IN ('Resolved','Closed')"))[0];

// Avg resolution time (hours)
$avg_res = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_h FROM complaints WHERE resolved_at IS NOT NULL"));
$avg_hours = $avg_res['avg_h'] ? round($avg_res['avg_h'], 1) : 'N/A';

// Recent complaints
$recent = mysqli_query($conn,
    "SELECT c.*, u.name as uname, cat.name as cat_name
     FROM complaints c
     JOIN users u ON u.email = c.user_email
     JOIN categories cat ON cat.id = c.category_id
     ORDER BY c.created_at DESC LIMIT 8");
?>

<div class="content">
    <h4 class="mb-1 fw-semibold">Admin Dashboard</h4>
    <p class="text-muted mb-4" style="font-size:.85rem;">Overview of all complaint activities</p>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#4f46e5,#7c3aed);">
                <div class="icon"><i class="bi bi-card-list"></i></div>
                <div><div class="label">Total Complaints</div><div class="value"><?= $total ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
                <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                <div><div class="label">Open</div><div class="value"><?= $open ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#3b82f6,#1d4ed8);">
                <div class="icon"><i class="bi bi-arrow-repeat"></i></div>
                <div><div class="label">In Progress</div><div class="value"><?= $inprog ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#10b981,#059669);">
                <div class="icon"><i class="bi bi-check-circle"></i></div>
                <div><div class="label">Resolved</div><div class="value"><?= $resolved ?></div></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#ef4444,#b91c1c);">
                <div class="icon"><i class="bi bi-arrow-up-circle"></i></div>
                <div><div class="label">Escalated</div><div class="value"><?= $escalated ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#8b5cf6,#6d28d9);">
                <div class="icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div><div class="label">Unassigned</div><div class="value"><?= $unassigned ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#06b6d4,#0284c7);">
                <div class="icon"><i class="bi bi-people"></i></div>
                <div><div class="label">Registered Users</div><div class="value"><?= $users_c ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#f97316,#c2410c);">
                <div class="icon"><i class="bi bi-tools"></i></div>
                <div><div class="label">Avg Resolution (hrs)</div><div class="value"><?= $avg_hours ?></div></div>
            </div>
        </div>
    </div>

    <!-- Recent Complaints -->
    <div class="section-header"><i class="bi bi-clock-history"></i> Recent Complaints</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#ID</th><th>User</th><th>Title</th><th>Category</th>
                    <th>Priority</th><th>Status</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($recent)): ?>
                <tr>
                    <td><b>#<?= $row['id'] ?></b></td>
                    <td><?= htmlspecialchars($row['uname']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><span class="badge bg-secondary"><?= $row['cat_name'] ?></span></td>
                    <td>
                        <?php
                        $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
                        echo "<span class='badge {$pc[$row['priority']]}'>{$row['priority']}</span>";
                        ?>
                    </td>
                    <td><?php
                        $sc = ['Open'=>'warning','In Progress'=>'primary','Resolved'=>'success','Closed'=>'dark','Escalated'=>'danger','On Hold'=>'secondary','Reopened'=>'info'];
                        $s = $row['status'];
                        echo "<span class='badge bg-{$sc[$s]}'>{$s}</span>";
                    ?></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <td><a href="manage_complaints.php?view=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
