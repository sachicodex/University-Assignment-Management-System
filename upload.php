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
    header("Location: index.php");
    exit();
}

if (isset($_POST['upload'])) {
    $title = $_POST['title'];
    $deadline = $_POST['deadline'];
    $file = $_FILES['assignment_file'];

    if ($title != "" && $deadline != "" && $file['name'] != "") {
        $folder = "uploads/";

        if (!is_dir($folder)) {
            mkdir($folder);
        }

        $name = time() . "_" . $file['name'];
        $path = $folder . $name;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            $title = mysqli_real_escape_string($conn, $title);
            $path = mysqli_real_escape_string($conn, $path);
            $deadline = mysqli_real_escape_string($conn, $deadline);
            $lecturerUsername = mysqli_real_escape_string($conn, $_SESSION['username']);

            $q = "INSERT INTO assignments (title, file_path, lecturer_username, deadline) VALUES ('$title', '$path', '$lecturerUsername', '$deadline')";

            if (mysqli_query($conn, $q)) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        } else {
            $error = "File upload failed.";
        }
    } else {
        $error = "Please fill all fields and choose a file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Assignment</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="topbar"><a class="brand" href="dashboard.php"><span class="brand-mark">U</span> UAMS</a><a class="text-link" href="dashboard.php">← Dashboard</a></header>
    <main class="narrow-container"><p class="eyebrow">LECTURER SPACE</p><div class="form-box wide-form">
        <h1>Create an assignment</h1>
        <p class="muted">Students will see the brief and can submit their completed work.</p>

        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <label>Assignment Title</label>
            <input type="text" name="title" required>

            <label>Assignment File</label>
            <input type="file" name="assignment_file" required>

            <label>Deadline</label>
            <input type="date" name="deadline" required>

            <button type="submit" name="upload">Upload</button>
        </form>

    </div></main>
</body>
</html>
