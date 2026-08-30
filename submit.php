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

if (!isset($_SESSION['email']) || $_SESSION['role'] != "student") { header("Location: index.php"); exit(); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$assignmentResult = mysqli_query($conn, "SELECT * FROM assignments WHERE id = $id");
$assignment = mysqli_fetch_assoc($assignmentResult);

// Check if student already submitted
$email = mysqli_real_escape_string($conn, $_SESSION['email']);
$existingSubmission = mysqli_query($conn, "SELECT * FROM submissions WHERE assignment_id = $id AND student_email = '$email'");
$hasSubmitted = $existingSubmission && mysqli_num_rows($existingSubmission) > 0;

if (isset($_POST['submit_work']) && $assignment) {
    // Check if student has already submitted
    if ($hasSubmitted) {
        $error = "You have already submitted this assignment. Only one submission per assignment is allowed.";
    } else {
        $file = $_FILES['submission_file'];
        if ($file['name'] != "" && $file['error'] == 0) {
            $folder = "uploads/submissions/";
            if (!is_dir($folder)) { mkdir($folder, 0777, true); }
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
            $path = $folder . time() . "_" . $safeName;
            if (move_uploaded_file($file['tmp_name'], $path)) {
                $path = mysqli_real_escape_string($conn, $path);
                $now = date("Y-m-d H:i:s");
                $q = "INSERT INTO submissions (assignment_id, student_email, file_path, submitted_at) VALUES ($id, '$email', '$path', '$now')";
                if (mysqli_query($conn, $q)) { header("Location: view.php?id=$id"); exit(); }
                $error = "Database error: " . mysqli_error($conn);
            } else { $error = "File upload failed. Please try again."; }
        } else { $error = "Please choose a file to submit."; }
    }
}
?>
<!DOCTYPE html><html><head><title>Submit Assignment</title><link rel="stylesheet" href="styles.css"></head><body>
<header class="topbar"><a class="brand" href="dashboard.php"><span class="brand-mark">U</span> UAMS</a><a class="text-link" href="view.php?id=<?php echo $id; ?>">← Assignment details</a></header>
<main class="narrow-container"><p class="eyebrow">STUDENT SUBMISSION</p><div class="form-box wide-form"><h1>Submit your work</h1><?php if ($assignment) { ?><p class="muted">Upload your completed file for <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>.</p><?php } ?><?php if (isset($error)) { ?><p class="error"><?php echo $error; ?></p><?php } ?>
<?php if ($assignment) { ?><?php if ($hasSubmitted) { ?><p class="error">You have already submitted this assignment. Only one submission per assignment is allowed.</p><?php } else { ?><form method="POST" enctype="multipart/form-data"><label>Your completed file</label><input type="file" name="submission_file" required><button type="submit" name="submit_work">Submit assignment →</button></form><?php } ?><?php } else { ?><p class="error">Assignment not found.</p><?php } ?></div></main></body></html>
