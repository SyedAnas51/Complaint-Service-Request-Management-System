<?php
include '../includes/auth_user.php';
include '../includes/header.php';
include '../includes/sidebar_user.php';
include '../includes/db_connection.php';

$email = mysqli_real_escape_string($conn, $_SESSION['user_email']);
$complaint = null;
$notes = null;

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $complaint = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT c.*, cat.name as cat_name, u.name as assigned_name
         FROM complaints c
         JOIN categories cat ON cat.id=c.category_id
         LEFT JOIN users u ON u.email=c.assigned_to
         WHERE c.id=$id AND c.user_email='$email'"));
    if ($complaint) {
        $notes = mysqli_query($conn,
            "SELECT sn.*, u.name as tname FROM service_notes sn
             JOIN users u ON u.email=sn.tech_email
             WHERE sn.complaint_id=$id ORDER BY sn.created_at");
    }
}
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Track Complaint</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Real-time status tracking for your service requests</p>

    <!-- Search by ID -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3">
            <input type="number" name="id" class="form-control" placeholder="Enter Complaint ID" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Track</button>
        </div>
    </form>

    <?php if (isset($_GET['id']) && !$complaint): ?>
        <div class="alert alert-warning">Complaint not found or does not belong to your account.</div>
    <?php endif; ?>

    <?php if ($complaint): ?>
    <div class="row">
        <div class="col-md-7">
            <!-- Complaint Info Card -->
            <div class="section-header mb-0">
                <i class="bi bi-info-circle"></i> Complaint #<?= $complaint['id'] ?>
            </div>
            <div class="section-body mb-4">
                <p><b>Title:</b> <?= htmlspecialchars($complaint['title']) ?></p>
                <p><b>Category:</b> <?= $complaint['cat_name'] ?></p>
                <p><b>Description:</b><br><span class="text-muted"><?= nl2br(htmlspecialchars($complaint['description'])) ?></span></p>
                <p><b>Priority:</b>
                    <?php
                    $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
                    echo "<span class='badge {$pc[$complaint['priority']]}'>{$complaint['priority']}</span>";
                    ?>
                </p>
                <p><b>Assigned To:</b> <?= $complaint['assigned_name'] ?: '—' ?></p>
                <p><b>Submitted:</b> <?= date('d M Y H:i', strtotime($complaint['created_at'])) ?></p>
                <?php if ($complaint['resolved_at']): ?>
                    <p><b>Resolved:</b> <?= date('d M Y H:i', strtotime($complaint['resolved_at'])) ?></p>
                    <?php
                    $hrs = round((strtotime($complaint['resolved_at']) - strtotime($complaint['created_at'])) / 3600, 1);
                    ?>
                    <p><b>Resolution Time:</b> <?= $hrs ?> hours</p>
                <?php endif; ?>
                <?php if ($complaint['image_path']): ?>
                    <p><b>Attachment:</b><br>
                        <img src="../assets/uploads/<?= htmlspecialchars($complaint['image_path']) ?>"
                             style="max-width:250px;border-radius:8px;border:1px solid #ddd;" alt="attachment">
                    </p>
                <?php endif; ?>
            </div>

            <!-- Service Notes -->
            <div class="section-header mb-0"><i class="bi bi-journal-text"></i> Technician Updates</div>
            <div class="section-body">
                <?php
                $has_notes = false;
                while ($n = mysqli_fetch_assoc($notes)):
                    $has_notes = true;
                ?>
                    <div class="border-start border-primary border-3 ps-3 mb-3">
                        <small class="text-muted"><b><?= htmlspecialchars($n['tname']) ?></b> — <?= date('d M Y H:i', strtotime($n['created_at'])) ?></small>
                        <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($n['note'])) ?></p>
                    </div>
                <?php endwhile; ?>
                <?php if (!$has_notes): ?>
                    <p class="text-muted mb-0">No updates yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-5">
            <!-- Status Timeline -->
            <div class="section-header mb-0"><i class="bi bi-diagram-3"></i> Status Timeline</div>
            <div class="section-body">
                <?php
                $statuses = ['Open','In Progress','On Hold','Resolved','Closed'];
                $current  = $complaint['status'];
                $done_statuses = ['Resolved','Closed'];
                $is_done = in_array($current, $done_statuses);
                $is_esc  = $complaint['is_escalated'];

                $ordered = ['Open','In Progress','Resolved','Closed'];
                $pos = array_search($current, $ordered);
                if ($pos === false) $pos = 1;

                foreach ($ordered as $idx => $st):
                    $done    = $idx < $pos;
                    $active  = $idx === $pos;
                    $pending = $idx > $pos;
                ?>
                <div class="d-flex align-items-start mb-3">
                    <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-right:12px;
                        <?= $done ? 'background:#10b981;color:#fff' : ($active ? 'background:#4f46e5;color:#fff' : 'background:#e5e7eb;color:#9ca3af') ?>">
                        <?php if ($done): ?><i class="bi bi-check" style="font-size:.8rem;"></i>
                        <?php elseif ($active): ?><i class="bi bi-circle-fill" style="font-size:.5rem;"></i>
                        <?php else: ?><i class="bi bi-circle" style="font-size:.5rem;"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <b style="font-size:.88rem;color:<?= $active ? '#4f46e5' : ($done ? '#374151' : '#9ca3af') ?>"><?= $st ?></b>
                        <?php if ($active): ?>
                            <span class="badge bg-primary ms-1" style="font-size:.68rem;">Current</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if ($is_esc): ?>
                <div class="alert alert-danger py-2 mt-3" style="font-size:.83rem;">
                    <i class="bi bi-arrow-up-circle me-1"></i> This complaint has been escalated for priority handling.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- My recent complaints for quick access -->
    <?php if (!isset($_GET['id'])): ?>
    <div class="section-header mt-2"><i class="bi bi-list-ul"></i> Your Recent Complaints</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Title</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            <?php
            $sc = ['Open'=>'warning','In Progress'=>'primary','Resolved'=>'success','Closed'=>'dark','Escalated'=>'danger','On Hold'=>'secondary','Reopened'=>'info'];
            $list = mysqli_query($conn, "SELECT * FROM complaints WHERE user_email='$email' ORDER BY created_at DESC LIMIT 10");
            while ($r = mysqli_fetch_assoc($list)):
            ?>
                <tr>
                    <td>#<?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['title']) ?></td>
                    <td><span class="badge bg-<?= $sc[$r['status']] ?>"><?= $r['status'] ?></span></td>
                    <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                    <td><a href="?id=<?= $r['id'] ?>" class="btn btn-xs btn-outline-primary" style="font-size:.75rem;padding:2px 8px;">Track</a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
