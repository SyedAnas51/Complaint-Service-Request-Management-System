<?php
include '../includes/auth_admin.php';
include '../includes/header.php';
include '../includes/sidebar_admin.php';
include '../includes/db_connection.php';

if (isset($_POST['assign'])) {
    $cid   = (int)$_POST['complaint_id'];
    $tech  = mysqli_real_escape_string($conn, $_POST['tech_email']);
    mysqli_query($conn, "UPDATE complaints SET assigned_to='$tech', status='In Progress' WHERE id=$cid");
    header("Location: assign_complaints.php?msg=Assigned+successfully");
    exit();
}

// Preselect a complaint if ?id= passed
$preselect_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$unassigned = mysqli_query($conn,
    "SELECT c.*, u.name as uname, cat.name as cat_name
     FROM complaints c
     JOIN users u ON u.email=c.user_email
     JOIN categories cat ON cat.id=c.category_id
     WHERE (c.assigned_to IS NULL OR c.status='Reopened')
       AND c.status NOT IN ('Resolved','Closed')
     ORDER BY FIELD(c.priority,'Critical','High','Medium','Low'), c.created_at");

$technicians = mysqli_query($conn, "SELECT * FROM users WHERE role='technician' ORDER BY name");

// Count active tasks per technician
$tech_load = [];
$tl = mysqli_query($conn, "SELECT assigned_to, COUNT(*) as cnt FROM complaints WHERE status='In Progress' GROUP BY assigned_to");
while ($t = mysqli_fetch_assoc($tl)) $tech_load[$t['assigned_to']] = $t['cnt'];
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Assign Complaints</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Assign open complaints to technicians based on workload and expertise</p>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success py-2"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="section-header"><i class="bi bi-person-check"></i> Unassigned / Reopened Complaints</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>#</th><th>Title</th><th>Category</th><th>Priority</th><th>Submitted By</th><th>Date</th><th>Assign To</th></tr>
            </thead>
            <tbody>
            <?php
            $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
            $found = false;
            while ($row = mysqli_fetch_assoc($unassigned)):
                $found = true;
            ?>
                <tr <?= $row['id'] == $preselect_id ? 'class="table-warning"' : '' ?>>
                    <td><b>#<?= $row['id'] ?></b></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><span class="badge bg-secondary"><?= $row['cat_name'] ?></span></td>
                    <td><span class="badge <?= $pc[$row['priority']] ?>"><?= $row['priority'] ?></span></td>
                    <td><?= htmlspecialchars($row['uname']) ?></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-2 align-items-center">
                            <input type="hidden" name="complaint_id" value="<?= $row['id'] ?>">
                            <select name="tech_email" class="form-select form-select-sm" required>
                                <option value="">Select Technician</option>
                                <?php
                                mysqli_data_seek($technicians, 0);
                                while ($t = mysqli_fetch_assoc($technicians)):
                                    $load = $tech_load[$t['email']] ?? 0;
                                ?>
                                    <option value="<?= $t['email'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= $load ?> active)</option>
                                <?php endwhile; ?>
                            </select>
                            <button name="assign" class="btn btn-sm btn-primary text-nowrap">Assign</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (!$found): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">All complaints are assigned.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Technician Workload -->
    <div class="section-header mt-4"><i class="bi bi-bar-chart-steps"></i> Technician Workload</div>
    <div class="section-body">
        <div class="row g-3">
        <?php
        $techs2 = mysqli_query($conn, "SELECT * FROM users WHERE role='technician'");
        while ($t = mysqli_fetch_assoc($techs2)):
            $load = $tech_load[$t['email']] ?? 0;
            $pct  = min($load * 20, 100);
            $color = $pct < 40 ? 'success' : ($pct < 80 ? 'warning' : 'danger');
        ?>
        <div class="col-md-4">
            <div class="border rounded p-3 bg-white">
                <b><?= htmlspecialchars($t['name']) ?></b>
                <small class="text-muted d-block"><?= $t['department'] ?></small>
                <div class="progress mt-2" style="height:8px;">
                    <div class="progress-bar bg-<?= $color ?>" style="width:<?= $pct ?>%"></div>
                </div>
                <small><?= $load ?> active task(s)</small>
            </div>
        </div>
        <?php endwhile; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
