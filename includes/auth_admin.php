<?php
// auth_admin.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: ../index.php");
    exit();
}
?>
