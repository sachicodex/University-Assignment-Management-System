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
    header("Location: index.php");
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

// update assignment
if (isset($_POST['update']) && $row) {
    // get form data
    $title = $_POST['title'];
    $deadline = $_POST['deadline'];

    // check fields
    if ($title != "" && $deadline != "") {
        $title = mysqli_real_escape_string($conn, $title);
        $deadline = mysqli_real_escape_string($conn, $deadline);

        // update assignment (title and deadline only - no file re-upload allowed)
        $q = "UPDATE assignments SET title = '$title', deadline = '$deadline' WHERE id = $id";

        if (mysqli_query($conn, $q)) {
            header("Location: view.php?id=" . $id);
            exit();
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    } else {
        $error = "Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Assignment</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="topbar"><a class="brand" href="dashboard.php"><span class="brand-mark">U</span> UAMS</a><a class="text-link" href="dashboard.php">← Dashboard</a></header>
    <main class="narrow-container"><p class="eyebrow">LECTURER SPACE</p><div class="form-box wide-form">
        <h1>Edit assignment</h1>

        <!-- show error -->
        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <?php if ($row) { ?>
            <form method="POST" action="">
                <label>Assignment Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                <label>Deadline</label>
                <input type="date" name="deadline" value="<?php echo $row['deadline']; ?>" required>

                <button type="submit" name="update">Update assignment</button>
            </form>
        <?php } else { ?>
            <p class="error">Assignment not found.</p>
        <?php } ?>

    </div></main>
</body>
</html>
