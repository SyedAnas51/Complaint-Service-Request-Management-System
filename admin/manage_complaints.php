<?php
include '../includes/auth_admin.php';
include '../includes/header.php';
include '../includes/sidebar_admin.php';
include '../includes/db_connection.php';

// ── Update status
if (isset($_POST['update_status'])) {
    $id     = (int)$_POST['complaint_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $resolved_sql = ($status === 'Resolved') ? ", resolved_at=NOW()" : "";
    mysqli_query($conn, "UPDATE complaints SET status='$status'$resolved_sql WHERE id=$id");
    header("Location: manage_complaints.php?msg=Status+updated");
    exit();
}

// ── Update priority
if (isset($_POST['update_priority'])) {
    $id  = (int)$_POST['complaint_id'];
    $pri = mysqli_real_escape_string($conn, $_POST['priority']);
    mysqli_query($conn, "UPDATE complaints SET priority='$pri' WHERE id=$id");
    header("Location: manage_complaints.php?msg=Priority+updated");
    exit();
}

// ── Escalate
if (isset($_GET['escalate'])) {
    $id = (int)$_GET['escalate'];
    mysqli_query($conn, "UPDATE complaints SET status='Escalated', is_escalated=1, escalated_at=NOW() WHERE id=$id");
    header("Location: manage_complaints.php?msg=Complaint+escalated");
    exit();
}

// ── Reopen
if (isset($_GET['reopen'])) {
    $id = (int)$_GET['reopen'];
    mysqli_query($conn, "UPDATE complaints SET status='Reopened', resolved_at=NULL WHERE id=$id");
    header("Location: manage_complaints.php?msg=Complaint+reopened");
    exit();
}

// ── Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM complaints WHERE id=$id");
    header("Location: manage_complaints.php?msg=Complaint+deleted");
    exit();
}

// ── Filters
$where = "1=1";
if (!empty($_GET['search'])) {
    $s = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (c.title LIKE '%$s%' OR c.description LIKE '%$s%' OR c.user_email LIKE '%$s%')";
}
if (!empty($_GET['status_filter'])) {
    $sf = mysqli_real_escape_string($conn, $_GET['status_filter']);
    $where .= " AND c.status='$sf'";
}
if (!empty($_GET['priority_filter'])) {
    $pf = mysqli_real_escape_string($conn, $_GET['priority_filter']);
    $where .= " AND c.priority='$pf'";
}
if (!empty($_GET['category_filter'])) {
    $cf = (int)$_GET['category_filter'];
    $where .= " AND c.category_id=$cf";
}

// ── CSV Export
if (isset($_GET['export'])) {
    $res = mysqli_query($conn,
        "SELECT c.id, c.user_email, cat.name as category, c.title, c.priority, c.status,
                c.assigned_to, c.created_at, c.resolved_at
         FROM complaints c
         JOIN categories cat ON cat.id=c.category_id
         WHERE $where ORDER BY c.created_at DESC");
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="complaints_' . date('Ymd') . '.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['ID','User Email','Category','Title','Priority','Status','Assigned To','Created At','Resolved At']);
    while ($r = mysqli_fetch_assoc($res)) fputcsv($out, $r);
    fclose($out); exit();
}

$complaints = mysqli_query($conn,
    "SELECT c.*, u.name as uname, cat.name as cat_name
     FROM complaints c
     JOIN users u ON u.email=c.user_email
     JOIN categories cat ON cat.id=c.category_id
     WHERE $where ORDER BY c.created_at DESC");

$categories = mysqli_query($conn, "SELECT * FROM categories");
$technicians = mysqli_query($conn, "SELECT * FROM users WHERE role='technician'");

// Detail view
$detail = null;
if (isset($_GET['view'])) {
    $vid = (int)$_GET['view'];
    $detail = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT c.*, u.name as uname, u.phone as uphone, cat.name as cat_name
         FROM complaints c
         JOIN users u ON u.email=c.user_email
         JOIN categories cat ON cat.id=c.category_id
         WHERE c.id=$vid"));
    $notes = mysqli_query($conn,
        "SELECT sn.*, u.name as tname FROM service_notes sn
         JOIN users u ON u.email=sn.tech_email
         WHERE sn.complaint_id=$vid ORDER BY sn.created_at");
}
?>

<div class="content">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="fw-semibold mb-0">Manage Complaints</h4>
            <p class="text-muted mb-0" style="font-size:.83rem;">View, filter, update and export all complaints</p>
        </div>
        <a href="?<?= http_build_query(array_merge($_GET, ['export'=>1])) ?>"
           class="btn btn-sm btn-success"><i class="bi bi-download me-1"></i> Export CSV</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ── Filters ── -->
    <form method="GET" class="row g-2 mb-4 bg-white p-3 rounded border">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search title / user..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <select name="status_filter" class="form-select form-select-sm">
                <option value="">All Status</option>
                <?php foreach (['Open','In Progress','On Hold','Resolved','Closed','Escalated','Reopened'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($_GET['status_filter'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="priority_filter" class="form-select form-select-sm">
                <option value="">All Priority</option>
                <?php foreach (['Low','Medium','High','Critical'] as $p): ?>
                    <option value="<?= $p ?>" <?= ($_GET['priority_filter'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="category_filter" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <?php
                $cats_tmp = mysqli_query($conn, "SELECT * FROM categories");
                while ($c = mysqli_fetch_assoc($cats_tmp)):
                ?>
                    <option value="<?= $c['id'] ?>" <?= ($_GET['category_filter'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= $c['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
            <a href="manage_complaints.php" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
        </div>
    </form>

    <?php if ($detail): ?>
    <!-- ── Detail Panel ── -->
    <div class="mb-4 p-0 border rounded">
        <div class="section-header"><i class="bi bi-info-circle"></i> Complaint #<?= $detail['id'] ?> Details
            <a href="manage_complaints.php" class="btn btn-sm btn-light ms-auto">← Back</a>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-7">
                    <p><b>Title:</b> <?= htmlspecialchars($detail['title']) ?></p>
                    <p><b>Description:</b><br><?= nl2br(htmlspecialchars($detail['description'])) ?></p>
                    <?php if ($detail['image_path']): ?>
                        <p><b>Attachment:</b><br>
                            <img src="../assets/uploads/<?= htmlspecialchars($detail['image_path']) ?>"
                                 style="max-width:300px; border-radius:8px; border:1px solid #ddd; padding:4px;" alt="attachment">
                        </p>
                    <?php endif; ?>
                    <p><b>Submitted by:</b> <?= htmlspecialchars($detail['uname']) ?> (<?= $detail['uphone'] ?>)</p>
                    <p><b>Category:</b> <?= $detail['cat_name'] ?></p>
                    <p><b>Priority:</b> <?= $detail['priority'] ?></p>
                    <p><b>Status:</b> <?= $detail['status'] ?></p>
                    <p><b>Assigned To:</b> <?= $detail['assigned_to'] ?: '—' ?></p>
                    <p><b>Created:</b> <?= $detail['created_at'] ?></p>
                    <p><b>Resolved:</b> <?= $detail['resolved_at'] ?: '—' ?></p>
                    <?php if ($detail['is_escalated']): ?>
                        <span class="badge bg-danger">Escalated on <?= $detail['escalated_at'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-md-5">
                    <!-- Update Status -->
                    <div class="card border-0 bg-light mb-3 p-3">
                        <b class="mb-2 d-block">Update Status</b>
                        <form method="POST">
                            <input type="hidden" name="complaint_id" value="<?= $detail['id'] ?>">
                            <select name="status" class="form-select form-select-sm mb-2">
                                <?php foreach (['Open','In Progress','On Hold','Resolved','Closed'] as $st): ?>
                                    <option value="<?= $st ?>" <?= $detail['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button name="update_status" class="btn btn-sm btn-primary w-100">Update Status</button>
                        </form>
                    </div>
                    <!-- Update Priority -->
                    <div class="card border-0 bg-light mb-3 p-3">
                        <b class="mb-2 d-block">Update Priority</b>
                        <form method="POST">
                            <input type="hidden" name="complaint_id" value="<?= $detail['id'] ?>">
                            <select name="priority" class="form-select form-select-sm mb-2">
                                <?php foreach (['Low','Medium','High','Critical'] as $p): ?>
                                    <option value="<?= $p ?>" <?= $detail['priority'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button name="update_priority" class="btn btn-sm btn-warning w-100">Update Priority</button>
                        </form>
                    </div>
                    <!-- Escalate / Reopen -->
                    <div class="d-flex gap-2">
                        <?php if (!$detail['is_escalated']): ?>
                            <a href="?escalate=<?= $detail['id'] ?>" class="btn btn-sm btn-danger w-100"
                               onclick="return confirm('Escalate this complaint?')">
                               <i class="bi bi-arrow-up-circle"></i> Escalate
                            </a>
                        <?php endif; ?>
                        <?php if (in_array($detail['status'], ['Resolved','Closed'])): ?>
                            <a href="?reopen=<?= $detail['id'] ?>" class="btn btn-sm btn-info w-100"
                               onclick="return confirm('Reopen this complaint?')">
                               <i class="bi bi-arrow-counterclockwise"></i> Reopen
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Service Notes -->
            <hr>
            <b>Service Notes</b>
            <div class="mt-2">
                <?php while ($n = mysqli_fetch_assoc($notes)): ?>
                    <div class="border rounded p-2 mb-2 bg-light">
                        <small class="text-muted"><?= $n['tname'] ?> — <?= $n['created_at'] ?></small>
                        <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($n['note'])) ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Table ── -->
    <div class="section-header"><i class="bi bi-table"></i> Complaints List</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th><th>User</th><th>Title</th><th>Category</th>
                    <th>Priority</th><th>Status</th><th>Assigned</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
            $sc = ['Open'=>'warning','In Progress'=>'primary','Resolved'=>'success','Closed'=>'dark',
                   'Escalated'=>'danger','On Hold'=>'secondary','Reopened'=>'info'];
            while ($row = mysqli_fetch_assoc($complaints)):
            ?>
                <tr>
                    <td><b>#<?= $row['id'] ?></b></td>
                    <td><?= htmlspecialchars($row['uname']) ?></td>
                    <td><?= htmlspecialchars(substr($row['title'],0,40)) ?><?= strlen($row['title'])>40?'...':'' ?></td>
                    <td><span class="badge bg-secondary"><?= $row['cat_name'] ?></span></td>
                    <td><span class="badge <?= $pc[$row['priority']] ?>"><?= $row['priority'] ?></span></td>
                    <td><span class="badge bg-<?= $sc[$row['status']] ?>"><?= $row['status'] ?></span></td>
                    <td><?= $row['assigned_to'] ? htmlspecialchars($row['assigned_to']) : '<span class="text-muted">Unassigned</span>' ?></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <td class="d-flex gap-1 flex-wrap">
                        <a href="?view=<?= $row['id'] ?>" class="btn btn-xs btn-outline-primary" style="font-size:.75rem;padding:2px 8px;">View</a>
                        <a href="assign_complaints.php?id=<?= $row['id'] ?>" class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px;">Assign</a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-xs btn-outline-danger" style="font-size:.75rem;padding:2px 8px;"
                           onclick="return confirm('Delete complaint #<?= $row['id'] ?>?')">Del</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
