<?php
include '../includes/auth_admin.php';
include '../includes/header.php';
include '../includes/sidebar_admin.php';
include '../includes/db_connection.php';

$perf = mysqli_query($conn,
    "SELECT u.name, u.email, u.department,
            COUNT(c.id) as total_assigned,
            SUM(c.status='Resolved') as resolved,
            SUM(c.status='In Progress') as inprog,
            SUM(c.status='Open') as open_c,
            AVG(TIMESTAMPDIFF(HOUR, c.created_at, c.resolved_at)) as avg_res_hrs
     FROM users u
     LEFT JOIN complaints c ON c.assigned_to=u.email
     WHERE u.role='technician'
     GROUP BY u.id ORDER BY resolved DESC");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Staff Performance</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Technician-level resolution metrics and workload overview</p>

    <div class="section-header"><i class="bi bi-graph-up"></i> Technician Performance Report</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Technician</th><th>Specialty</th><th>Assigned</th>
                    <th>Resolved</th><th>In Progress</th><th>Open</th>
                    <th>Avg Res. Time</th><th>Resolution Rate</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($t = mysqli_fetch_assoc($perf)):
                $rate  = $t['total_assigned'] > 0 ? round($t['resolved'] / $t['total_assigned'] * 100) : 0;
                $color = $rate >= 75 ? 'success' : ($rate >= 40 ? 'warning' : 'danger');
                $avg   = $t['avg_res_hrs'] ? round($t['avg_res_hrs'],1).' hrs' : 'N/A';
            ?>
                <tr>
                    <td><b><?= htmlspecialchars($t['name']) ?></b><br>
                        <small class="text-muted"><?= $t['email'] ?></small></td>
                    <td><?= htmlspecialchars($t['department'] ?? '—') ?></td>
                    <td><?= $t['total_assigned'] ?></td>
                    <td><span class="badge bg-success"><?= $t['resolved'] ?></span></td>
                    <td><span class="badge bg-primary"><?= $t['inprog'] ?></span></td>
                    <td><span class="badge bg-warning text-dark"><?= $t['open_c'] ?></span></td>
                    <td><?= $avg ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;">
                                <div class="progress-bar bg-<?= $color ?>" style="width:<?= $rate ?>%"></div>
                            </div>
                            <small class="text-<?= $color ?>"><?= $rate ?>%</small>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
