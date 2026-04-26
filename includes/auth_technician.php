<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['tech_email'])) {
    header("Location: ../index.php");
    exit();
}
?>
