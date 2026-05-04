<?php
include '../includes/auth_admin.php';
include '../includes/header.php';
include '../includes/sidebar_admin.php';
include '../includes/db_connection.php';

if (isset($_POST['reassign'])) {
    $cid  = (int)$_POST['complaint_id'];
    $tech = mysqli_real_escape_string($conn, $_POST['tech_email']);
    mysqli_query($conn, "UPDATE complaints SET assigned_to='$tech', status='In Progress', is_escalated=0 WHERE id=$cid");
    header("Location: escalate_complaints.php?msg=Reassigned+and+de-escalated");
    exit();
}

$escalated = mysqli_query($conn,
    "SELECT c.*, u.name as uname, cat.name as cat_name
     FROM complaints c
     JOIN users u ON u.email=c.user_email
     JOIN categories cat ON cat.id=c.category_id
     WHERE c.is_escalated=1
     ORDER BY c.escalated_at DESC");

$techs = mysqli_query($conn, "SELECT * FROM users WHERE role='technician' ORDER BY name");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Escalated Complaints</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Complaints that have been escalated and require immediate attention</p>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success py-2"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="section-header"><i class="bi bi-arrow-up-circle text-danger"></i> Escalated Complaints</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>#</th><th>Title</th><th>Category</th><th>Priority</th><th>User</th><th>Escalated At</th><th>Currently Assigned</th><th>Reassign</th></tr>
            </thead>
            <tbody>
            <?php
            $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
            $found = false;
            while ($row = mysqli_fetch_assoc($escalated)):
                $found = true;
            ?>
                <tr class="table-danger">
                    <td><b>#<?= $row['id'] ?></b></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><span class="badge bg-secondary"><?= $row['cat_name'] ?></span></td>
                    <td><span class="badge <?= $pc[$row['priority']] ?>"><?= $row['priority'] ?></span></td>
                    <td><?= htmlspecialchars($row['uname']) ?></td>
                    <td><?= $row['escalated_at'] ? date('d M Y H:i', strtotime($row['escalated_at'])) : '—' ?></td>
                    <td><?= $row['assigned_to'] ?: '—' ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="complaint_id" value="<?= $row['id'] ?>">
                            <select name="tech_email" class="form-select form-select-sm" required>
                                <option value="">Select Tech</option>
                                <?php
                                mysqli_data_seek($techs, 0);
                                while ($t = mysqli_fetch_assoc($techs)):
                                ?>
                                    <option value="<?= $t['email'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <button name="reassign" class="btn btn-sm btn-warning text-nowrap">Reassign</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (!$found): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No escalated complaints.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
