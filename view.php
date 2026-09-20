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

$role = $_SESSION['role'];

$id = 0;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}

$username = mysqli_real_escape_string($conn, $_SESSION['username']);
if ($role == "lecturer") {
    $q = "SELECT * FROM assignments WHERE id = $id AND lecturer_username = '$username'";
} else {
    $q = "SELECT * FROM assignments WHERE id = $id";
}
$r = mysqli_query($conn, $q);
$row = mysqli_fetch_assoc($r);

if ($row && $role == "student") {
    $submissionQuery = "SELECT * FROM submissions WHERE assignment_id = $id AND student_username = '$username'";
} elseif ($row && $role == "lecturer") {
    $submissionQuery = "SELECT * FROM submissions WHERE assignment_id = $id ORDER BY submitted_at DESC";
}
if (isset($submissionQuery)) {
    $submissions = mysqli_query($conn, $submissionQuery);
}

$hasStudentSubmitted = false;
if ($role == "student" && isset($submissions) && mysqli_num_rows($submissions) > 0) {
    $hasStudentSubmitted = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Assignment</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="topbar"><a class="brand" href="dashboard.php"><span class="brand-mark">U</span> UAMS</a><a class="text-link" href="dashboard.php">&larr; Dashboard</a></header>
    <main class="narrow-container">
        <p class="eyebrow">ASSIGNMENT DETAILS</p>
        <div class="detail-header"><h1>Assignment details</h1></div>

        <?php if ($row) { ?>
            <section class="content-card detail-card"><h2><?php echo htmlspecialchars($row['title']); ?></h2><div class="detail-grid"><div><span class="summary-label">DEADLINE</span><p class="deadline"><?php echo date('l, d F Y', strtotime($row['deadline'])); ?></p></div><div><span class="summary-label">TEACHER UPLOADED ASSIGNMENT</span><p><a class="file-link teacher-file-button" href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">Download teacher assignment file</a></p></div></div>

            <?php if ($role == "lecturer") { ?>
                <p><a class="button-link secondary" href="edit.php?id=<?php echo $row['id']; ?>">Edit assignment</a></p>
            <?php } ?>
            </section>

            <section class="content-card"><div class="section-heading"><div><h2><?php echo $role == 'lecturer' ? 'Student submissions' : 'Your submission'; ?></h2><p class="muted"><?php echo $role == 'lecturer' ? 'Files submitted by students for this assignment.' : 'Your submitted file for this assignment.'; ?></p></div></div>
            <?php if (isset($submissions) && mysqli_num_rows($submissions) > 0) { ?><div class="table-wrap"><table><tr><th><?php echo $role == 'lecturer' ? 'Student' : 'Status'; ?></th><th>Submitted on</th><th>File</th></tr><?php while ($submission = mysqli_fetch_assoc($submissions)) { ?><tr><td><?php echo $role == 'lecturer' ? htmlspecialchars($submission['student_username']) : '<span class="status complete">Submitted</span>'; ?></td><td><?php echo date('d M Y, H:i', strtotime($submission['submitted_at'])); ?></td><td><a class="text-link" href="<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank">Download file</a></td></tr><?php } ?></table></div><?php } else { ?><div class="empty-state">No work has been submitted yet.<?php if ($role == 'student' && !$hasStudentSubmitted) { ?><br><br><a class="button-link" href="submit.php?id=<?php echo $row['id']; ?>">Submit your work</a><?php } ?></div><?php } ?>
            </section>
        <?php } else { ?>
            <p class="error">Assignment not found.</p>
        <?php } ?>
    </main>
</body>
</html>
