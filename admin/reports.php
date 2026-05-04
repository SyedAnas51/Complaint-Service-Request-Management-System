<?php
include '../includes/auth_admin.php';
include '../includes/header.php';
include '../includes/sidebar_admin.php';
include '../includes/db_connection.php';

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

// Monthly stats
$monthly_total    = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE MONTH(created_at)=$month AND YEAR(created_at)=$year"))[0];
$monthly_resolved = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status='Resolved' AND MONTH(created_at)=$month AND YEAR(created_at)=$year"))[0];
$monthly_open     = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status='Open' AND MONTH(created_at)=$month AND YEAR(created_at)=$year"))[0];
$monthly_esc      = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE is_escalated=1 AND MONTH(created_at)=$month AND YEAR(created_at)=$year"))[0];

$avg_res = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_h
     FROM complaints WHERE resolved_at IS NOT NULL AND MONTH(created_at)=$month AND YEAR(created_at)=$year"));
$avg_hours = $avg_res['avg_h'] ? round($avg_res['avg_h'], 1) : 'N/A';

// Category-wise
$cat_stats = mysqli_query($conn,
    "SELECT cat.name, COUNT(c.id) as total,
            SUM(c.status='Resolved') as resolved,
            SUM(c.status='Open' OR c.status='In Progress') as pending
     FROM categories cat
     LEFT JOIN complaints c ON c.category_id=cat.id
       AND MONTH(c.created_at)=$month AND YEAR(c.created_at)=$year
     GROUP BY cat.id ORDER BY total DESC");

// Monthly trend (last 12 months)
$trend = mysqli_query($conn,
    "SELECT DATE_FORMAT(created_at,'%b %Y') as month_label,
            YEAR(created_at) as yr, MONTH(created_at) as mn,
            COUNT(*) as total
     FROM complaints
     GROUP BY yr, mn ORDER BY yr DESC, mn DESC LIMIT 12");
$trend_data = [];
while ($t = mysqli_fetch_assoc($trend)) $trend_data[] = $t;
$trend_data = array_reverse($trend_data);

// CSV Export
if (isset($_GET['export_csv'])) {
    $res = mysqli_query($conn,
        "SELECT c.id, c.user_email, cat.name as category, c.title, c.priority, c.status,
                c.assigned_to, c.created_at, c.resolved_at,
                TIMESTAMPDIFF(HOUR, c.created_at, c.resolved_at) as resolution_hours
         FROM complaints c
         JOIN categories cat ON cat.id=c.category_id
         WHERE MONTH(c.created_at)=$month AND YEAR(c.created_at)=$year
         ORDER BY c.created_at DESC");
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="report_' . date('Y', mktime(0,0,0,$month,1,$year)) . '_' . str_pad($month,2,'0',STR_PAD_LEFT) . '.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['ID','User Email','Category','Title','Priority','Status','Assigned To','Created At','Resolved At','Resolution (hrs)']);
    while ($r = mysqli_fetch_assoc($res)) fputcsv($out, $r);
    fclose($out); exit();
}
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-semibold mb-0">Reports & Analytics</h4>
            <p class="text-muted mb-0" style="font-size:.83rem;">Monthly complaint report and category-wise analysis</p>
        </div>
        <a href="?month=<?= $month ?>&year=<?= $year ?>&export_csv=1" class="btn btn-sm btn-success">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>

    <!-- Month Selector -->
    <form method="GET" class="row g-2 mb-4 bg-white p-3 rounded border">
        <div class="col-md-2">
            <select name="month" class="form-select form-select-sm">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="year" class="form-select form-select-sm">
                <?php for ($y = date('Y'); $y >= date('Y')-4; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-primary w-100"><i class="bi bi-bar-chart me-1"></i> Generate</button>
        </div>
    </form>

    <!-- Monthly Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="stat-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <div class="icon"><i class="bi bi-card-list"></i></div>
                <div><div class="label">Total</div><div class="value"><?= $monthly_total ?></div></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <div class="icon"><i class="bi bi-hourglass"></i></div>
                <div><div class="label">Open</div><div class="value"><?= $monthly_open ?></div></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:linear-gradient(135deg,#10b981,#059669);">
                <div class="icon"><i class="bi bi-check-circle"></i></div>
                <div><div class="label">Resolved</div><div class="value"><?= $monthly_resolved ?></div></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
                <div class="icon"><i class="bi bi-arrow-up-circle"></i></div>
                <div><div class="label">Escalated</div><div class="value"><?= $monthly_esc ?></div></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);">
                <div class="icon"><i class="bi bi-clock"></i></div>
                <div><div class="label">Avg Resolution Time</div><div class="value"><?= $avg_hours ?> hrs</div></div>
            </div>
        </div>
    </div>

    <!-- Category-wise -->
    <div class="section-header"><i class="bi bi-tags"></i> Category-wise Analysis — <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Category</th><th>Total</th><th>Resolved</th><th>Pending</th><th>Resolution Rate</th></tr></thead>
            <tbody>
            <?php while ($c = mysqli_fetch_assoc($cat_stats)):
                $rate = $c['total'] > 0 ? round($c['resolved'] / $c['total'] * 100) : 0;
                $color = $rate >= 75 ? 'success' : ($rate >= 40 ? 'warning' : 'danger');
            ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= $c['total'] ?></td>
                    <td><span class="badge bg-success"><?= $c['resolved'] ?></span></td>
                    <td><span class="badge bg-warning text-dark"><?= $c['pending'] ?></span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;">
                                <div class="progress-bar bg-<?= $color ?>" style="width:<?= $rate ?>%"></div>
                            </div>
                            <small><?= $rate ?>%</small>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Monthly Trend Chart -->
    <div class="section-header mt-4"><i class="bi bi-graph-up"></i> Monthly Complaint Trend (Last 12 Months)</div>
    <div class="section-body">
        <canvas id="trendChart" height="80"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode(array_column($trend_data,'month_label')) ?>;
const data   = <?= json_encode(array_column($trend_data,'total')) ?>;
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [{
            label: 'Complaints Submitted',
            data,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79,70,229,.1)',
            fill: true,
            tension: .4,
            pointRadius: 5,
            pointBackgroundColor: '#4f46e5'
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
    }
});
</script>
