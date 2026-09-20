<?php
session_start();

$dbHost = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "assignment_db";

$conn = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['role'] != "lecturer") {
    header("Location: dashboard.php");
    exit();
}

$id = 0;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}

$q = "SELECT * FROM assignments WHERE id = $id";
$r = mysqli_query($conn, $q);
$row = mysqli_fetch_assoc($r);

if ($row) {
    if (file_exists($row['file_path'])) {
        unlink($row['file_path']);
    }

    $q2 = "DELETE FROM assignments WHERE id = $id";
    mysqli_query($conn, $q2);
}

header("Location: dashboard.php");
exit();
?>
