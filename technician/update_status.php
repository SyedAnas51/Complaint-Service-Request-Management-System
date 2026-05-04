<?php
include '../includes/auth_technician.php';
include '../includes/header.php';
include '../includes/sidebar_technician.php';
include '../includes/db_connection.php';

$tech_email = mysqli_real_escape_string($conn, $_SESSION['tech_email']);

// Update status
if (isset($_POST['update_status'])) {
    $id     = (int)$_POST['complaint_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $resolved_sql = ($status === 'Resolved') ? ", resolved_at=NOW()" : "";
    mysqli_query($conn, "UPDATE complaints SET status='$status'$resolved_sql WHERE id=$id AND assigned_to='$tech_email'");
    header("Location: update_status.php?id=$id&msg=Status+updated");
    exit();
}

// Add service note
if (isset($_POST['add_note'])) {
    $id   = (int)$_POST['complaint_id'];
    $note = mysqli_real_escape_string($conn, trim($_POST['note']));
    if ($note) {
        mysqli_query($conn, "INSERT INTO service_notes (complaint_id, tech_email, note) VALUES ($id, '$tech_email', '$note')");
    }
    header("Location: update_status.php?id=$id&msg=Note+added");
    exit();
}

$complaint = null;
$notes     = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $complaint = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT c.*, cat.name as cat_name, u.name as uname, u.phone as uphone
         FROM complaints c
         JOIN categories cat ON cat.id=c.category_id
         JOIN users u ON u.email=c.user_email
         WHERE c.id=$id AND c.assigned_to='$tech_email'"));
    if ($complaint) {
        $notes = mysqli_query($conn,
            "SELECT sn.*, u.name as tname FROM service_notes sn
             JOIN users u ON u.email=sn.tech_email
             WHERE sn.complaint_id=$id ORDER BY sn.created_at");
    }
}

// All assigned tasks for list
$tasks = mysqli_query($conn,
    "SELECT c.*, cat.name as cat_name FROM complaints c
     JOIN categories cat ON cat.id=c.category_id
     WHERE c.assigned_to='$tech_email' AND c.status NOT IN ('Resolved','Closed')
     ORDER BY FIELD(c.priority,'Critical','High','Medium','Low')");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Update Complaint Status</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Update status and add service notes for your assigned complaints</p>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success py-2"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Task List -->
        <div class="col-md-4">
            <div class="section-header mb-0"><i class="bi bi-list-task"></i> My Tasks</div>
            <div class="section-body p-0">
                <?php
                $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
                $sc = ['Open'=>'warning','In Progress'=>'primary','Escalated'=>'danger','On Hold'=>'secondary','Reopened'=>'info'];
                while ($t = mysqli_fetch_assoc($tasks)):
                    $active_class = (isset($_GET['id']) && $_GET['id'] == $t['id']) ? 'bg-light border-start border-primary border-3' : '';
                ?>
                <a href="?id=<?= $t['id'] ?>" class="d-block p-2 text-decoration-none border-bottom <?= $active_class ?>">
                    <b style="font-size:.88rem;">#<?= $t['id'] ?> <?= htmlspecialchars(substr($t['title'],0,30)) ?></b><br>
                    <span class="badge <?= $pc[$t['priority']] ?>"><?= $t['priority'] ?></span>
                    <span class="badge bg-<?= $sc[$t['status']] ?? 'secondary' ?>"><?= $t['status'] ?></span>
                    <small class="text-muted d-block"><?= $t['cat_name'] ?></small>
                </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Detail + Actions -->
        <div class="col-md-8">
            <?php if ($complaint): ?>
            <div class="section-header mb-0"><i class="bi bi-info-circle"></i> Complaint #<?= $complaint['id'] ?></div>
            <div class="section-body mb-3">
                <p><b>Title:</b> <?= htmlspecialchars($complaint['title']) ?></p>
                <p><b>Category:</b> <?= $complaint['cat_name'] ?></p>
                <p><b>Description:</b><br><span class="text-muted"><?= nl2br(htmlspecialchars($complaint['description'])) ?></span></p>
                <p><b>Priority:</b>
                    <?php
                    $pc2 = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
                    echo "<span class='badge {$pc2[$complaint['priority']]}'>{$complaint['priority']}</span>";
                    ?>
                </p>
                <p><b>Submitted by:</b> <?= htmlspecialchars($complaint['uname']) ?> (<?= $complaint['uphone'] ?>)</p>
                <p><b>Current Status:</b>
                    <?php
                    $sc2 = ['Open'=>'warning','In Progress'=>'primary','On Hold'=>'secondary','Escalated'=>'danger','Reopened'=>'info'];
                    echo "<span class='badge bg-" . ($sc2[$complaint['status']] ?? 'dark') . "'>{$complaint['status']}</span>";
                    ?>
                </p>
                <?php if ($complaint['image_path']): ?>
                    <p><b>Attachment:</b><br>
                        <img src="../assets/uploads/<?= htmlspecialchars($complaint['image_path']) ?>"
                             style="max-width:200px;border-radius:6px;border:1px solid #ddd;" alt="attachment">
                    </p>
                <?php endif; ?>
            </div>

            <!-- Update Status Form -->
            <div class="section-header mb-0"><i class="bi bi-arrow-repeat"></i> Update Status</div>
            <div class="section-body mb-3">
                <form method="POST" class="row g-2">
                    <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
                    <div class="col-md-6">
                        <select name="status" class="form-select">
                            <?php foreach (['In Progress','On Hold','Resolved'] as $st): ?>
                                <option value="<?= $st ?>" <?= $complaint['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button name="update_status" class="btn btn-primary w-100">Update</button>
                    </div>
                </form>
            </div>

            <!-- Service Note Form -->
            <div class="section-header mb-0"><i class="bi bi-journal-plus"></i> Add Service Note</div>
            <div class="section-body mb-3">
                <form method="POST">
                    <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
                    <textarea name="note" class="form-control mb-2" rows="3"
                              placeholder="Describe what you did, materials used, next steps..." required></textarea>
                    <button name="add_note" class="btn btn-outline-primary">
                        <i class="bi bi-plus me-1"></i>Add Note
                    </button>
                </form>
            </div>

            <!-- Existing Notes -->
            <div class="section-header mb-0"><i class="bi bi-journal-text"></i> Service History</div>
            <div class="section-body">
                <?php
                $has = false;
                while ($n = mysqli_fetch_assoc($notes)):
                    $has = true;
                ?>
                    <div class="border-start border-primary border-3 ps-3 mb-3">
                        <small class="text-muted"><b><?= htmlspecialchars($n['tname']) ?></b> — <?= date('d M Y H:i', strtotime($n['created_at'])) ?></small>
                        <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($n['note'])) ?></p>
                    </div>
                <?php endwhile; ?>
                <?php if (!$has): ?><p class="text-muted mb-0">No notes yet.</p><?php endif; ?>
            </div>

            <?php elseif (isset($_GET['id'])): ?>
                <div class="alert alert-warning">Complaint not found or not assigned to you.</div>
            <?php else: ?>
                <div class="alert alert-info">← Select a task from the list to update its status.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
