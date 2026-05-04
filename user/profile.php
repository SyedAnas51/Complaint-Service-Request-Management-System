<?php
include '../includes/auth_user.php';
include '../includes/header.php';
include '../includes/sidebar_user.php';
include '../includes/db_connection.php';

$email = mysqli_real_escape_string($conn, $_SESSION['user_email']);
$user  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='$email'"));

if (isset($_POST['update'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $dept  = mysqli_real_escape_string($conn, $_POST['department']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    mysqli_query($conn, "UPDATE users SET name='$name', department='$dept', phone='$phone' WHERE email='$email'");
    $msg = ['type'=>'success','text'=>'Profile updated!'];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='$email'"));
}
?>

<div class="content">
    <h4 class="fw-semibold mb-1">My Profile</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Update your account information</p>

    <?php if (isset($msg)): ?>
        <div class="alert alert-<?= $msg['type'] ?> py-2"><?= $msg['text'] ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-5">
            <div class="section-header mb-0"><i class="bi bi-person-circle"></i> Profile Info</div>
            <div class="section-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department</label>
                        <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <button name="update" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
