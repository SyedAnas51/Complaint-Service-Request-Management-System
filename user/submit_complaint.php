<?php
include '../includes/auth_user.php';
include '../includes/header.php';
include '../includes/sidebar_user.php';
include '../includes/db_connection.php';

$email = mysqli_real_escape_string($conn, $_SESSION['user_email']);

if (isset($_POST['submit'])) {
    $category    = (int)$_POST['category_id'];
    $title       = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $priority    = mysqli_real_escape_string($conn, $_POST['priority']);

    // Handle image upload
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('img_') . '.' . $ext;
            $dest     = '../assets/uploads/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $image_path = "'" . $filename . "'";
            }
        } else {
            $error = "Only image files (jpg, png, gif) are allowed.";
        }
    }

    if (!isset($error)) {
        $img_sql = $image_path ? $image_path : "NULL";
        mysqli_query($conn,
            "INSERT INTO complaints (user_email, category_id, title, description, image_path, priority)
             VALUES ('$email', $category, '$title', '$description', $img_sql, '$priority')");
        $success = true;
        $new_id  = mysqli_insert_id($conn);
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Submit Complaint</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Fill in the details of your service request or complaint</p>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            Complaint <b>#<?= $new_id ?></b> submitted successfully!
            <a href="track_complaint.php?id=<?= $new_id ?>" class="alert-link ms-2">Track it →</a>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="section-header mb-0"><i class="bi bi-plus-circle"></i> New Complaint / Service Request</div>
    <div class="section-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Complaint Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Brief title of your issue" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">— Select Category —</option>
                        <?php while ($c = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="5"
                              placeholder="Describe the issue in detail — location, time, severity..." required></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Attach Image <small class="text-muted">(optional — jpg/png/gif)</small></label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-12">
                    <button type="submit" name="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send me-2"></i>Submit Complaint
                    </button>
                    <a href="my_complaints.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
