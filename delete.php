<?php
// start session
session_start();

// connect database
$dbHost = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "assignment_db";

$conn = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// check login
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

// check role
if ($_SESSION['role'] != "lecturer") {
    header("Location: dashboard.php");
    exit();
}

// get assignment id
$id = 0;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}

// get assignment data
$q = "SELECT * FROM assignments WHERE id = $id";
$r = mysqli_query($conn, $q);
$row = mysqli_fetch_assoc($r);

// if assignment exists
if ($row) {
    // delete file
    if (file_exists($row['file_path'])) {
        unlink($row['file_path']);
    }

    // delete from database
    $q2 = "DELETE FROM assignments WHERE id = $id";
    mysqli_query($conn, $q2);
}

// go back to dashboard
header("Location: dashboard.php");
exit();
?>
