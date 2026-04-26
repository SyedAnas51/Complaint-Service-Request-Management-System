<?php
$servername = "localhost";
$username   = "root";   // Default XAMPP username
$password   = "";       // Default XAMPP password
$dbname     = "csrms";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
