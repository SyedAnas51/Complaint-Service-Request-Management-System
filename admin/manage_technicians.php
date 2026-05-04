<?php
include '../includes/auth_admin.php';
include '../includes/header.php';
include '../includes/sidebar_admin.php';
include '../includes/db_connection.php';

if (isset($_POST['add'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass  = mysqli_real_escape_string($conn, $_POST['password']);
    $dept  = mysqli_real_escape_string($conn, $_POST['department']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $msg = ['type'=>'danger', 'text'=>'Email already exists!'];
    } else {
        mysqli_query($conn, "INSERT INTO users (name,email,password,role,department,phone) VALUES ('$name','$email','$pass','technician','$dept','$phone')");
        $msg = ['type'=>'success', 'text'=>'Technician added!'];
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='technician'");
    $msg = ['type'=>'danger', 'text'=>'Technician deleted.'];
}

$techs = mysqli_query($conn, "SELECT * FROM users WHERE role='technician' ORDER BY name");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Manage Technicians</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Add and remove technicians</p>

    <?php if (isset($msg)): ?>
        <div class="alert alert-<?= $msg['type'] ?> py-2"><?= $msg['text'] ?></div>
    <?php endif; ?>

    <div class="section-header mb-0"><i class="bi bi-tools"></i> Add Technician</div>
    <div class="section-body mb-4">
        <form method="POST" class="row g-2">
            <div class="col-md-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Full Name" required></div>
            <div class="col-md-3"><input type="email" name="email" class="form-control form-control-sm" placeholder="Email" required></div>
            <div class="col-md-2"><input type="text" name="password" class="form-control form-control-sm" placeholder="Password" required></div>
            <div class="col-md-2"><input type="text" name="department" class="form-control form-control-sm" placeholder="Specialty (e.g. IT)"></div>
            <div class="col-md-2"><input type="text" name="phone" class="form-control form-control-sm" placeholder="Phone"></div>
            <div class="col-md-1"><button name="add" class="btn btn-sm btn-success w-100">Add</button></div>
        </form>
    </div>

    <div class="section-header"><i class="bi bi-people-fill"></i> Technician List</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Specialty</th><th>Phone</th><th>Active Tasks</th><th>Resolved</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php while ($t = mysqli_fetch_assoc($techs)):
                $active   = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE assigned_to='{$t['email']}' AND status='In Progress'"))[0];
                $resolved = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE assigned_to='{$t['email']}' AND status='Resolved'"))[0];
            ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td><?= htmlspecialchars($t['email']) ?></td>
                    <td><?= htmlspecialchars($t['department'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($t['phone'] ?? '—') ?></td>
                    <td><span class="badge bg-primary"><?= $active ?></span></td>
                    <td><span class="badge bg-success"><?= $resolved ?></span></td>
                    <td>
                        <a href="?delete=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete technician?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
