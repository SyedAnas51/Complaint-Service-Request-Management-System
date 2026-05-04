<?php
include '../includes/auth_admin.php';
include '../includes/header.php';
include '../includes/sidebar_admin.php';
include '../includes/db_connection.php';

if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    if ($name) {
        mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$name')");
        $msg = ['type'=>'success','text'=>'Category added!'];
    }
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $cnt = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE category_id=$id"))[0];
    if ($cnt > 0) {
        $msg = ['type'=>'danger','text'=>'Cannot delete — complaints exist in this category.'];
    } else {
        mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
        $msg = ['type'=>'info','text'=>'Category deleted.'];
    }
}

$cats = mysqli_query($conn, "SELECT c.*, COUNT(comp.id) as cnt FROM categories c LEFT JOIN complaints comp ON comp.category_id=c.id GROUP BY c.id ORDER BY c.name");
?>

<div class="content">
    <h4 class="fw-semibold mb-1">Complaint Categories</h4>
    <p class="text-muted mb-4" style="font-size:.83rem;">Manage categories for classifying complaints</p>

    <?php if (isset($msg)): ?>
        <div class="alert alert-<?= $msg['type'] ?> py-2"><?= $msg['text'] ?></div>
    <?php endif; ?>

    <div class="section-header mb-0"><i class="bi bi-tags"></i> Add Category</div>
    <div class="section-body mb-4">
        <form method="POST" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="e.g. Plumbing" required>
            </div>
            <div class="col-md-2">
                <button name="add" class="btn btn-success w-100">Add Category</button>
            </div>
        </form>
    </div>

    <div class="section-header"><i class="bi bi-list-ul"></i> All Categories</div>
    <div class="section-body p-0">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Category Name</th><th>Total Complaints</th><th>Action</th></tr></thead>
            <tbody>
            <?php while ($c = mysqli_fetch_assoc($cats)): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><span class="badge bg-primary"><?= $c['cnt'] ?></span></td>
                    <td>
                        <?php if ($c['cnt'] == 0): ?>
                            <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this category?')">Delete</a>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:.8rem;">In use</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
