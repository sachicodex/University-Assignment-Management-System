<?php
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

// get role
$role = $_SESSION['role'];

// show assignments and, for students, whether they have submitted their work
$email = 
mysqli_real_escape_string($conn, $_SESSION['email']);
if ($role == "student") {
    $q = "SELECT assignments.*, submissions.id AS submission_id, submissions.submitted_at
          FROM assignments LEFT JOIN submissions
          ON assignments.id = submissions.assignment_id AND submissions.student_email = '$email'
          ORDER BY deadline ASC";
} else {
    $q = "SELECT assignments.*, COUNT(submissions.id) AS submission_count
    
          FROM assignments LEFT JOIN submissions ON assignments.id = submissions.assignment_id
          WHERE assignments.lecturer_email = '$email'
          GROUP BY assignments.id ORDER BY deadline ASC";
}
$r = mysqli_query($conn, $q);
$assignmentCount = mysqli_num_rows($r);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Assignment Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="dashboard.php"><span class="brand-mark">U</span> UAMS</a>
        <div class="account"><span class="avatar"><?php echo strtoupper(substr($_SESSION['email'], 0, 1)); ?></span><span><strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong><small><?php echo ucfirst($role); ?></small></span><a href="logout.php">Sign out</a></div>
    </header>
    <main class="container">
        <div class="page-heading">
            <div><p class="eyebrow">ACADEMIC YEAR 2026</p><h1>Your assignment dashboard</h1><p class="muted"><?php echo $role == 'lecturer' ? 'Create coursework and follow student progress.' : 'Stay organised and submit your work before each deadline.'; ?></p></div>

        <!-- show upload button -->
        <?php if ($role == "lecturer") { ?>
            <a class="button-link" href="upload.php">+ Create assignment</a>
        <?php } ?>
        </div>
        <section class="summary-grid">
            <div class="summary-card"><span class="summary-label">TOTAL ASSIGNMENTS</span><strong><?php echo $assignmentCount; ?></strong><span>Available in this portal</span></div>
            <?php if ($role == 'student') { ?><div class="summary-card"><span class="summary-label">YOUR STATUS</span><strong>Ready</strong><span>Open an assignment to submit</span></div><?php } else { ?><div class="summary-card"><span class="summary-label">LECTURER SPACE</span><strong>Review</strong><span>Download student submissions</span></div><?php } ?>
        </section>

        <section class="content-card">
        <div class="section-heading"><div><h2>Assignments</h2><p class="muted">Coursework listed by nearest deadline.</p></div></div>
        <div class="table-wrap"><table>
            <tr>
                <th>Title</th>
                <th>Deadline</th>
                <th><?php echo $role == 'lecturer' ? 'Submissions' : 'Your progress'; ?></th>
                <th class="actions-heading">Actions</th>
            </tr>

            <?php if (mysqli_num_rows($r) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($r)) { ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong><br><small>Assignment #<?php echo $row['id']; ?></small></td>
                        <td><span class="deadline"><?php echo date('d M Y', strtotime($row['deadline'])); ?></span></td>
                        <td><?php if ($role == 'lecturer') { ?><span class="status neutral"><?php echo $row['submission_count']; ?> received</span><?php } elseif ($row['submission_id']) { ?><span class="status complete">Submitted</span><br><small><?php echo date('d M, H:i', strtotime($row['submitted_at'])); ?></small><?php } else { ?><span class="status pending">Not submitted</span><?php } ?></td>
                        <td class="actions-cell">
                            <div class="action-group">
                            <a class="text-link" href="view.php?id=<?php echo $row['id']; ?>"><?php echo $role == 'student' && !$row['submission_id'] ? 'Open & submit' : 'View details'; ?> →</a>
                            <?php if ($role == "lecturer") { ?>
                                <span class="icon-actions">
                                    <a class="icon-btn edit-btn" href="edit.php?id=<?php echo $row['id']; ?>" aria-label="Edit assignment" title="Edit assignment"><img src="assets/edit.svg" alt=""></a>
                                    <a class="icon-btn delete-btn" href="delete.php?id=<?php echo $row['id']; ?>" aria-label="Delete assignment" title="Delete assignment" onclick="return confirm('Delete this assignment?');"><img src="assets/delete.svg" alt=""></a>
                                </span>
                            <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
            <tr>
                    <td colspan="4" class="empty-state">No assignments uploaded yet.</td>
                </tr>
            <?php } ?>
        </table></div></section>
    </main>
</body>
</html>
