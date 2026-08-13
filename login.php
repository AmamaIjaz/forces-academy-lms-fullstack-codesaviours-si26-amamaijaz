<?php

session_start();

require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {

        $error = 'Email and password are required.';

    } else {

        $sql = "SELECT id, full_name, password
                FROM students
                WHERE email = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, 's', $email);

            mysqli_stmt_execute($stmt);

            mysqli_stmt_bind_result(
                $stmt,
                $student_id,
                $student_name,
                $student_password
            );

            if (mysqli_stmt_fetch($stmt)) {

                /*
                 * Check that the password value is not null
                 * before passing it to password_verify().
                 */
                if (
                    $student_password !== null &&
                    password_verify($password, $student_password)
                ) {

                    $_SESSION['student_id'] = $student_id;
                    $_SESSION['student_name'] = $student_name;

                    mysqli_stmt_close($stmt);

                    header('Location: dashboard.php');
                    exit;

                } else {

                    $error = 'Invalid email or password.';
                }

            } else {

                $error = 'Invalid email or password.';
            }

            mysqli_stmt_close($stmt);

        } else {

            $error = 'Database query failed: ' . mysqli_error($conn);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Student Login | Forces Academy LMS</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="container">

    <h2>Student Login</h2>

    <?php if (!empty($error)): ?>

        <p class="error">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </p>

    <?php endif; ?>

    <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>

        <p class="success">
            Registration successful. Please login.
        </p>

    <?php endif; ?>

    <form method="POST" action="">

        <label for="email">
            Email:
        </label>

        <input
            type="email"
            id="email"
            name="email"
            required
            autocomplete="email"
        >

        <label for="password">
            Password:
        </label>

        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
        >

        <button type="submit">
            Login
        </button>

    </form>

    <p class="account-link">

        Don't have an account?

        <a href="register.php">
            Register here
        </a>

    </p>

</div>

</body>

</html>