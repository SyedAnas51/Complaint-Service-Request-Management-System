<?php
include '../includes/auth_user.php';
include '../includes/header.php';
include '../includes/sidebar_user.php';
include '../includes/db_connection.php';

$email = $_SESSION['user_email'];
$em    = mysqli_real_escape_string($conn, $email);
$user  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='$em'"));

$total    = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE user_email='$em'"))[0];
$open     = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE user_email='$em' AND status='Open'"))[0];
$inprog   = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE user_email='$em' AND status='In Progress'"))[0];
$resolved = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE user_email='$em' AND status='Resolved'"))[0];

$recent = mysqli_query($conn,
    "SELECT c.*, cat.name as cat_name FROM complaints c
     JOIN categories cat ON cat.id=c.category_id
     WHERE c.user_email='$em' ORDER BY c.created_at DESC LIMIT 5");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Welcome, <?= htmlspecialchars($user['name']) ?>!</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Here's a summary of your service requests</p>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <div class="icon"><i class="bi bi-card-list"></i></div>
                <div><div class="label">Total Submitted</div><div class="value"><?= $total ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <div class="icon"><i class="bi bi-hourglass"></i></div>
                <div><div class="label">Open</div><div class="value"><?= $open ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                <div class="icon"><i class="bi bi-arrow-repeat"></i></div>
                <div><div class="label">In Progress</div><div class="value"><?= $inprog ?></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#10b981,#059669);">
                <div class="icon"><i class="bi bi-check-circle"></i></div>
                <div><div class="label">Resolved</div><div class="value"><?= $resolved ?></div></div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-3 mb-4">
        <a href="submit_complaint.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>New Complaint</a>
        <a href="my_complaints.php" class="btn btn-outline-primary"><i class="bi bi-card-list me-2"></i>View All</a>
        <a href="track_complaint.php" class="btn btn-outline-secondary"><i class="bi bi-geo-alt me-2"></i>Track</a>
    </div>

    <div class="section-header"><i class="bi bi-clock-history"></i> Recent Complaints</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            <?php
            $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
            $sc = ['Open'=>'warning','In Progress'=>'primary','Resolved'=>'success','Closed'=>'dark','Escalated'=>'danger','On Hold'=>'secondary','Reopened'=>'info'];
            while ($row = mysqli_fetch_assoc($recent)):
            ?>
                <tr>
                    <td><b>#<?= $row['id'] ?></b></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><span class="badge bg-secondary"><?= $row['cat_name'] ?></span></td>
                    <td><span class="badge <?= $pc[$row['priority']] ?>"><?= $row['priority'] ?></span></td>
                    <td><span class="badge bg-<?= $sc[$row['status']] ?>"><?= $row['status'] ?></span></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <td><a href="track_complaint.php?id=<?= $row['id'] ?>" class="btn btn-xs btn-outline-primary" style="font-size:.75rem;padding:2px 8px;">Track</a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
