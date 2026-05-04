<?php
include '../includes/auth_technician.php';
include '../includes/header.php';
include '../includes/sidebar_technician.php';
include '../includes/db_connection.php';

$em = mysqli_real_escape_string($conn, $_SESSION['tech_email']);

$notes = mysqli_query($conn,
    "SELECT sn.*, c.title, c.status FROM service_notes sn
     JOIN complaints c ON c.id=sn.complaint_id
     WHERE sn.tech_email='$em'
     ORDER BY sn.created_at DESC");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">My Service Notes</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">All notes you have entered across complaints</p>

    <div class="section-header"><i class="bi bi-journal-text"></i> All Service Notes</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Complaint</th><th>Status</th><th>Note</th><th>Date</th></tr></thead>
            <tbody>
            <?php
            $sc = ['Open'=>'warning','In Progress'=>'primary','Resolved'=>'success','Closed'=>'dark','Escalated'=>'danger'];
            while ($n = mysqli_fetch_assoc($notes)):
            ?>
                <tr>
                    <td>
                        <b>#<?= $n['complaint_id'] ?></b><br>
                        <small><?= htmlspecialchars($n['title']) ?></small>
                    </td>
                    <td><span class="badge bg-<?= $sc[$n['status']] ?? 'secondary' ?>"><?= $n['status'] ?></span></td>
                    <td><?= nl2br(htmlspecialchars($n['note'])) ?></td>
                    <td><?= date('d M Y H:i', strtotime($n['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
