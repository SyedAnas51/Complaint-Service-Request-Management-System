<?php
include '../includes/auth_technician.php';
include '../includes/header.php';
include '../includes/sidebar_technician.php';
include '../includes/db_connection.php';

$email = $_SESSION['tech_email'];
$em    = mysqli_real_escape_string($conn, $email);
$tech  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='$em'"));

$active   = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE assigned_to='$em' AND status='In Progress'"))[0];
$open_c   = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE assigned_to='$em' AND status='Open'"))[0];
$resolved = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE assigned_to='$em' AND status='Resolved'"))[0];
$onhold   = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE assigned_to='$em' AND status='On Hold'"))[0];

$tasks = mysqli_query($conn,
    "SELECT c.*, cat.name as cat_name, u.name as uname FROM complaints c
     JOIN categories cat ON cat.id=c.category_id
     JOIN users u ON u.email=c.user_email
     WHERE c.assigned_to='$em' AND c.status NOT IN ('Resolved','Closed')
     ORDER BY FIELD(c.priority,'Critical','High','Medium','Low'), c.created_at");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Technician Dashboard</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Welcome, <?= htmlspecialchars($tech['name']) ?> — <?= $tech['department'] ?></p>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                <div class="icon"><i class="bi bi-arrow-repeat"></i></div>
                <div><div class="label">In Progress</div><div class="value"><?= $active ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <div class="icon"><i class="bi bi-hourglass"></i></div>
                <div><div class="label">Open Tasks</div><div class="value"><?= $open_c ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#10b981,#059669);">
                <div class="icon"><i class="bi bi-check-circle"></i></div>
                <div><div class="label">Resolved (Total)</div><div class="value"><?= $resolved ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#6b7280,#374151);">
                <div class="icon"><i class="bi bi-pause-circle"></i></div>
                <div><div class="label">On Hold</div><div class="value"><?= $onhold ?></div></div>
            </div>
        </div>
    </div>

    <div class="section-header"><i class="bi bi-card-checklist"></i> My Active Tasks</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>#</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>User</th><th>Date</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php
            $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
            $sc = ['Open'=>'warning','In Progress'=>'primary','Resolved'=>'success','Closed'=>'dark','Escalated'=>'danger','On Hold'=>'secondary','Reopened'=>'info'];
            $found = false;
            while ($row = mysqli_fetch_assoc($tasks)):
                $found = true;
            ?>
                <tr>
                    <td><b>#<?= $row['id'] ?></b></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><span class="badge bg-secondary"><?= $row['cat_name'] ?></span></td>
                    <td><span class="badge <?= $pc[$row['priority']] ?>"><?= $row['priority'] ?></span></td>
                    <td><span class="badge bg-<?= $sc[$row['status']] ?>"><?= $row['status'] ?></span></td>
                    <td><?= htmlspecialchars($row['uname']) ?></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <a href="update_status.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">Update</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (!$found): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No active tasks assigned.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
