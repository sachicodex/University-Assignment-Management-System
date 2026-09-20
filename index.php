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

function passwordMatches($providedPassword, $storedPassword) {
    if (password_verify($providedPassword, $storedPassword)) {
        return true;
    }

    return $storedPassword === $providedPassword;
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username != "" && $password != "") {
        $username2 = mysqli_real_escape_string($conn, $username);
        $password2 = mysqli_real_escape_string($conn, $password);

        $lecturerSql = "SELECT * FROM lecturers WHERE username = '$username2' LIMIT 1";
        $lecturerResult = mysqli_query($conn, $lecturerSql);

        if ($lecturerResult && mysqli_num_rows($lecturerResult) > 0) {
            $lecturer = mysqli_fetch_assoc($lecturerResult);

            if (passwordMatches($password2, $lecturer['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = "lecturer";
                header("Location: dashboard.php");
                exit();
            }

            $error = "Invalid username or password.";
        } else {
            $studentSql = "SELECT * FROM students WHERE username = '$username2' LIMIT 1";
            $studentResult = mysqli_query($conn, $studentSql);

            if ($studentResult && mysqli_num_rows($studentResult) > 0) {
                $student = mysqli_fetch_assoc($studentResult);

                if (passwordMatches($password2, $student['password'])) {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = "student";
                    header("Location: dashboard.php");
                    exit();
                }
            }

            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please enter both your username and password.";
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
        <p class="muted">Use your username to continue.</p>

        <?php if (isset($_GET['registered'])) { ?>
            <p class="success">Account created successfully. Please sign in.</p>
        <?php } ?>

        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST" action="">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" name="login">Continue to dashboard <span>→</span></button>
        </form>
        <p class="muted signup-prompt">Don't have an account? <a class="text-link" href="signup.php">Sign up</a></p>
        </section>
    </main>
</body>
</html>
