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

// when login button is clicked
if (isset($_POST['login'])) {
    // get email
    $email = $_POST['email'];
    $email = trim($email);

    // check email is not empty
    if ($email != "") {
        // make email safe for query
        $email2 = mysqli_real_escape_string($conn, $email);

        // check if email is in lecturer table
        $sql = "SELECT * FROM lecturers WHERE email = '$email2'";
        $result = mysqli_query($conn, $sql);

        // default role
        $role = "student";

        // if found, change role
        if ($result && mysqli_num_rows($result) > 0) {
            $role = "lecturer";
        }

        // save session
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;

        // go to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Please enter your email address.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - UAMS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="login-page">
        <section class="login-intro">
            <p class="eyebrow">Keep every assignment on track.</p>
            <h1>University Assignment Management System</h1>
            <p>View coursework, submit your work, and manage deadlines from one simple place.</p>
            <div class="login-feature"><span>✓</span> Students submit work securely</div>
            <div class="login-feature"><span>✓</span> Lecturers review every submission</div>
        </section>
        <section class="form-box login-card">
        <p class="eyebrow">WELCOME BACK</p>
        <h2>Sign in to your portal</h2>
        <p class="muted">Use your university email address to continue.</p>

        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST" action="">
            <label>Email Address</label>
            <input type="email" name="email" required>

            <button type="submit" name="login">Continue to dashboard <span>→</span></button>
        </form>
        </section>
    </main>
</body>
</html>
