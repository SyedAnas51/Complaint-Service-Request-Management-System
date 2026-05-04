<?php
include '../includes/auth_user.php';
include '../includes/header.php';
include '../includes/sidebar_user.php';
include '../includes/db_connection.php';

$email = mysqli_real_escape_string($conn, $_SESSION['user_email']);

// Reopen
if (isset($_GET['reopen'])) {
    $id = (int)$_GET['reopen'];
    mysqli_query($conn, "UPDATE complaints SET status='Reopened', resolved_at=NULL WHERE id=$id AND user_email='$email'");
    header("Location: my_complaints.php?msg=Complaint+reopened");
    exit();
}

// Filters
$where = "c.user_email='$email'";
if (!empty($_GET['status_filter'])) {
    $sf = mysqli_real_escape_string($conn, $_GET['status_filter']);
    $where .= " AND c.status='$sf'";
}
if (!empty($_GET['search'])) {
    $s = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (c.title LIKE '%$s%' OR c.description LIKE '%$s%')";
}

$complaints = mysqli_query($conn,
    "SELECT c.*, cat.name as cat_name FROM complaints c
     JOIN categories cat ON cat.id=c.category_id
     WHERE $where ORDER BY c.created_at DESC");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">My Complaints</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Full history of your submitted complaints and service requests</p>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info py-2"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="GET" class="row g-2 mb-4 bg-white p-3 rounded border">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search complaints..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <select name="status_filter" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach (['Open','In Progress','On Hold','Resolved','Closed','Escalated','Reopened'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($_GET['status_filter'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Filter</button></div>
        <div class="col-md-2"><a href="my_complaints.php" class="btn btn-sm btn-outline-secondary w-100">Clear</a></div>
    </form>

    <div class="section-header"><i class="bi bi-card-list"></i> Complaint History</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>#</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>Assigned</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php
            $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
            $sc = ['Open'=>'warning','In Progress'=>'primary','Resolved'=>'success','Closed'=>'dark','Escalated'=>'danger','On Hold'=>'secondary','Reopened'=>'info'];
            $found = false;
            while ($row = mysqli_fetch_assoc($complaints)):
                $found = true;
            ?>
                <tr>
                    <td><b>#<?= $row['id'] ?></b></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><span class="badge bg-secondary"><?= $row['cat_name'] ?></span></td>
                    <td><span class="badge <?= $pc[$row['priority']] ?>"><?= $row['priority'] ?></span></td>
                    <td><span class="badge bg-<?= $sc[$row['status']] ?>"><?= $row['status'] ?></span></td>
                    <td><?= $row['assigned_to'] ? htmlspecialchars($row['assigned_to']) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <td class="d-flex gap-1">
                        <a href="track_complaint.php?id=<?= $row['id'] ?>"
                           class="btn btn-xs btn-outline-primary" style="font-size:.75rem;padding:2px 8px;">Track</a>
                        <?php if (in_array($row['status'], ['Resolved','Closed'])): ?>
                            <a href="?reopen=<?= $row['id'] ?>"
                               class="btn btn-xs btn-outline-info" style="font-size:.75rem;padding:2px 8px;"
                               onclick="return confirm('Reopen complaint #<?= $row['id'] ?>?')">Reopen</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (!$found): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No complaints found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
