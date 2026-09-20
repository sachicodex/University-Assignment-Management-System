<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "assignment_db");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (isset($_POST['signup'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($username === '' || $password === '' || $confirmPassword === '' || $role === '') {
        $error = "Please fill in all fields.";
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $error = "Username must be 3-50 characters and use only letters, numbers, dot, underscore, or hyphen.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif ($role !== 'lecturer' && $role !== 'student') {
        $error = "Please choose a valid role.";
    } else {
        $table = $role === 'lecturer' ? 'lecturers' : 'students';
        $check = mysqli_prepare($conn, "SELECT id FROM $table WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $username);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "That username is already registered for this role.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_prepare($conn, "INSERT INTO $table (username, password) VALUES (?, ?)");
            mysqli_stmt_bind_param($insert, "ss", $username, $hashedPassword);

            if (mysqli_stmt_execute($insert)) {
                header("Location: index.php?registered=1");
                exit();
            }

            $error = "Signup failed. Please try again.";
            mysqli_stmt_close($insert);
        }

        mysqli_stmt_close($check);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - UAMS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="login-page">
        <section class="login-intro">
            <p class="eyebrow">JOIN THE PORTAL</p>
            <h1>Create your UAMS account</h1>
            <p>Choose whether you are registering as a lecturer or a student.</p>
        </section>
        <section class="form-box login-card">
            <p class="eyebrow">NEW ACCOUNT</p>
            <h2>Sign up</h2>

            <?php if (isset($error)) { ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php } ?>

            <form method="POST" action="">
                <label>Username</label>
                <input type="text" name="username" minlength="3" maxlength="50" required>

                <label>Password</label>
                <input type="password" name="password" minlength="6" required>

                <label>Confirm Password</label>
                <input type="password" name="confirm_password" minlength="6" required>

                <label>Register as</label>
                <select name="role" required>
                    <option value="">Select your role</option>
                    <option value="lecturer">Lecturer</option>
                    <option value="student">Student</option>
                </select>

                <button type="submit" name="signup">Create account</button>
            </form>
            <p class="muted signup-prompt">Already have an account? <a class="text-link" href="index.php">Sign in</a></p>
        </section>
    </main>
</body>
</html>
