<?php
include '../includes/auth_technician.php';
include '../includes/header.php';
include '../includes/sidebar_technician.php';
include '../includes/db_connection.php';

$em = mysqli_real_escape_string($conn, $_SESSION['tech_email']);

$tasks = mysqli_query($conn,
    "SELECT c.*, cat.name as cat_name, u.name as uname FROM complaints c
     JOIN categories cat ON cat.id=c.category_id
     JOIN users u ON u.email=c.user_email
     WHERE c.assigned_to='$em'
     ORDER BY FIELD(c.priority,'Critical','High','Medium','Low'), c.created_at DESC");
?>
<div class="content">
    <h4 class="fw-semibold mb-1">All My Tasks</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">All complaints ever assigned to you</p>

    <div class="section-header"><i class="bi bi-card-checklist"></i> Task List</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>User</th><th>Assigned</th><th>Action</th></tr></thead>
            <tbody>
            <?php
            $pc = ['Low'=>'badge-low','Medium'=>'badge-medium','High'=>'badge-high','Critical'=>'badge-critical'];
            $sc = ['Open'=>'warning','In Progress'=>'primary','Resolved'=>'success','Closed'=>'dark','Escalated'=>'danger','On Hold'=>'secondary','Reopened'=>'info'];
            while ($row = mysqli_fetch_assoc($tasks)):
            ?>
                <tr>
                    <td><b>#<?= $row['id'] ?></b></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><span class="badge bg-secondary"><?= $row['cat_name'] ?></span></td>
                    <td><span class="badge <?= $pc[$row['priority']] ?>"><?= $row['priority'] ?></span></td>
                    <td><span class="badge bg-<?= $sc[$row['status']] ?>"><?= $row['status'] ?></span></td>
                    <td><?= htmlspecialchars($row['uname']) ?></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <td><a href="update_status.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">Open</a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
