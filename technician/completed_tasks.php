<?php
include '../includes/auth_technician.php';
include '../includes/header.php';
include '../includes/sidebar_technician.php';
include '../includes/db_connection.php';

$em = mysqli_real_escape_string($conn, $_SESSION['tech_email']);

$tasks = mysqli_query($conn,
    "SELECT c.*, cat.name as cat_name, u.name as uname,
            TIMESTAMPDIFF(HOUR, c.created_at, c.resolved_at) as res_hours
     FROM complaints c
     JOIN categories cat ON cat.id=c.category_id
     JOIN users u ON u.email=c.user_email
     WHERE c.assigned_to='$em' AND c.status='Resolved'
     ORDER BY c.resolved_at DESC");

$total_resolved = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE assigned_to='$em' AND status='Resolved'"))[0];
$avg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(TIMESTAMPDIFF(HOUR,created_at,resolved_at)) as a FROM complaints WHERE assigned_to='$em' AND resolved_at IS NOT NULL"));
$avg_hrs = $avg['a'] ? round($avg['a'],1) : 'N/A';
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Completed Tasks</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Complaints you have resolved</p>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#10b981,#059669);">
                <div class="icon"><i class="bi bi-check-circle"></i></div>
                <div><div class="label">Total Resolved</div><div class="value"><?= $total_resolved ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);">
                <div class="icon"><i class="bi bi-clock"></i></div>
                <div><div class="label">Avg Resolution</div><div class="value"><?= $avg_hrs ?> hrs</div></div>
            </div>
        </div>
    </div>

    <div class="section-header"><i class="bi bi-check2-all"></i> Resolved Complaints</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Priority</th><th>User</th><th>Created</th><th>Resolved</th><th>Time Taken</th></tr></thead>
            <tbody>
            <?php
            $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
            while ($row = mysqli_fetch_assoc($tasks)):
            ?>
                <tr>
                    <td>#<?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><span class="badge bg-secondary"><?= $row['cat_name'] ?></span></td>
                    <td><span class="badge <?= $pc[$row['priority']] ?>"><?= $row['priority'] ?></span></td>
                    <td><?= htmlspecialchars($row['uname']) ?></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <td><?= $row['resolved_at'] ? date('d M Y', strtotime($row['resolved_at'])) : '—' ?></td>
                    <td><?= $row['res_hours'] !== null ? $row['res_hours'].' hrs' : '—' ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
