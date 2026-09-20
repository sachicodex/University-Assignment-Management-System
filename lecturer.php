<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "assignment_db");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$lecturerUsername = trim($_GET['username'] ?? '');
$lecturer = null;
$assignments = null;

if ($lecturerUsername !== '') {
    $lecturerQuery = mysqli_prepare($conn, "SELECT username, subject FROM lecturers WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($lecturerQuery, "s", $lecturerUsername);
    mysqli_stmt_execute($lecturerQuery);
    $lecturerResult = mysqli_stmt_get_result($lecturerQuery);
    $lecturer = mysqli_fetch_assoc($lecturerResult);
    mysqli_stmt_close($lecturerQuery);

    if ($lecturer) {
        $assignmentQuery = mysqli_prepare($conn, "SELECT id, title, file_path, deadline FROM assignments WHERE lecturer_username = ? ORDER BY deadline ASC");
        mysqli_stmt_bind_param($assignmentQuery, "s", $lecturerUsername);
        mysqli_stmt_execute($assignmentQuery);
        $assignments = mysqli_stmt_get_result($assignmentQuery);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lecturer Profile - UAMS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="dashboard.php"><span class="brand-mark">U</span> UAMS</a>
        <a class="text-link" href="dashboard.php">&larr; Dashboard</a>
    </header>
    <main class="narrow-container">
        <?php if ($lecturer) { ?>
            <section class="content-card lecturer-profile-card">
                <span class="avatar profile-avatar"><?php echo strtoupper(substr($lecturer['username'], 0, 1)); ?></span>
                <span class="profile-role">Lecturer</span>
                <h1><?php echo htmlspecialchars(strtoupper($lecturer['username'])); ?></h1>
                <div class="profile-subject"><strong>[<?php echo htmlspecialchars($lecturer['subject'] ?: 'Subject not added yet'); ?>]</strong></div>
            </section>

            <section class="content-card">
                <?php if ($assignments && mysqli_num_rows($assignments) > 0) { ?>
                    <div class="table-wrap"><table>
                        <tr><th>Title</th><th>Deadline</th><th>File</th></tr>
                        <?php while ($assignment = mysqli_fetch_assoc($assignments)) { ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($assignment['title']); ?></strong></td>
                                <td><span class="deadline"><?php echo date('d M Y', strtotime($assignment['deadline'])); ?></span></td>
                                <td><a class="text-link" href="view.php?id=<?php echo $assignment['id']; ?>">View assignment</a></td>
                            </tr>
                        <?php } ?>
                    </table></div>
                <?php } else { ?>
                    <div class="empty-state">This lecturer has not uploaded any assignments yet.</div>
                <?php } ?>
            </section>
        <?php } else { ?>
            <p class="error">Lecturer profile not found.</p>
        <?php } ?>
    </main>
</body>
</html>
