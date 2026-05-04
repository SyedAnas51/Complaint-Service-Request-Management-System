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
        mysqli_query($conn, "INSERT INTO users (name,email,password,role,department,phone) VALUES ('$name','$email','$pass','user','$dept','$phone')");
        $msg = ['type'=>'success', 'text'=>'User added successfully!'];
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='user'");
    $msg = ['type'=>'danger', 'text'=>'User deleted.'];
}

$users = mysqli_query($conn, "SELECT * FROM users WHERE role='user' ORDER BY name");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Manage Users</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Add, view and delete registered users</p>

    <?php if (isset($msg)): ?>
        <div class="alert alert-<?= $msg['type'] ?> py-2"><?= $msg['text'] ?></div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="section-header mb-0"><i class="bi bi-person-plus"></i> Add New User</div>
    <div class="section-body mb-4">
        <form method="POST" class="row g-2">
            <div class="col-md-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Full Name" required></div>
            <div class="col-md-3"><input type="email" name="email" class="form-control form-control-sm" placeholder="Email" required></div>
            <div class="col-md-2"><input type="text" name="password" class="form-control form-control-sm" placeholder="Password" required></div>
            <div class="col-md-2"><input type="text" name="department" class="form-control form-control-sm" placeholder="Department"></div>
            <div class="col-md-2"><input type="text" name="phone" class="form-control form-control-sm" placeholder="Phone"></div>
            <div class="col-md-1"><button name="add" class="btn btn-sm btn-success w-100">Add</button></div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="section-header"><i class="bi bi-people"></i> Registered Users</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Department</th><th>Phone</th><th>Joined</th><th>Complaints</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php while ($u = mysqli_fetch_assoc($users)):
                $cnt = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE user_email='{$u['email']}'"))[0];
            ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['department'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td><span class="badge bg-primary"><?= $cnt ?></span></td>
                    <td>
                        <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete user <?= htmlspecialchars($u['name']) ?>?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
