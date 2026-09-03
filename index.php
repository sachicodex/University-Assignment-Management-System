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

function passwordMatches($providedPassword, $storedPassword) {
    if (password_verify($providedPassword, $storedPassword)) {
        return true;
    }

    return $storedPassword === $providedPassword;
}

// when login button is clicked
if (isset($_POST['login'])) {
    // get email and password
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // check email and password are not empty
    if ($email != "" && $password != "") {
        // make email safe for query
        $email2 = mysqli_real_escape_string($conn, $email);
        $password2 = mysqli_real_escape_string($conn, $password);

        // check lecturer table first
        $lecturerSql = "SELECT * FROM lecturers WHERE email = '$email2' LIMIT 1";
        $lecturerResult = mysqli_query($conn, $lecturerSql);

        if ($lecturerResult && mysqli_num_rows($lecturerResult) > 0) {
            $lecturer = mysqli_fetch_assoc($lecturerResult);

            if (passwordMatches($password2, $lecturer['password'])) {
                $_SESSION['email'] = $email;
                $_SESSION['role'] = "lecturer";
                header("Location: dashboard.php");
                exit();
            }

            $error = "Invalid email or password.";
        } else {
            $studentSql = "SELECT * FROM students WHERE email = '$email2' LIMIT 1";
            $studentResult = mysqli_query($conn, $studentSql);

            if ($studentResult && mysqli_num_rows($studentResult) > 0) {
                $student = mysqli_fetch_assoc($studentResult);

                if (passwordMatches($password2, $student['password'])) {
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = "student";
                    header("Location: dashboard.php");
                    exit();
                }
            }

            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please enter both your email and password.";
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

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" name="login">Continue to dashboard <span>→</span></button>
        </form>
        </section>
    </main>
</body>
</html>
