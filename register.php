<?php

require_once 'config/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $roll_number = trim($_POST['roll_number'] ?? '');
    $class = trim($_POST['class'] ?? '');

    if (
        empty($full_name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm) ||
        empty($roll_number) ||
        empty($class)
    ) {

        $error = 'All fields are required.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } elseif ($password !== $confirm) {

        $error = 'Passwords do not match.';

    } else {

        /* Check whether email or roll number already exists */

        $check_sql = "
            SELECT id
            FROM students
            WHERE email = ? OR roll_number = ?
        ";

        $check_stmt = mysqli_prepare($conn, $check_sql);

        if ($check_stmt) {

            mysqli_stmt_bind_param(
                $check_stmt,
                'ss',
                $email,
                $roll_number
            );

            mysqli_stmt_execute($check_stmt);

            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {

                $error = 'Email or roll number already exists.';

            } else {

                $hashed = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $sql = "
                    INSERT INTO students
                    (
                        full_name,
                        email,
                        password,
                        roll_number,
                        class
                    )
                    VALUES (?, ?, ?, ?, ?)
                ";

                $stmt = mysqli_prepare($conn, $sql);

                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        'sssss',
                        $full_name,
                        $email,
                        $hashed,
                        $roll_number,
                        $class
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        mysqli_stmt_close($stmt);
                        mysqli_stmt_close($check_stmt);

                        header(
                            'Location: login.php?registered=1'
                        );

                        exit;

                    } else {

                        $error =
                            'Registration failed: ' .
                            mysqli_error($conn);
                    }

                    mysqli_stmt_close($stmt);

                } else {

                    $error =
                        'Database query preparation failed: ' .
                        mysqli_error($conn);
                }
            }

            mysqli_stmt_close($check_stmt);

        } else {

            $error =
                'Could not check student information.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Registration | Forces Academy LMS</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="container">

    <h2>Student Registration</h2>

    <?php if (!empty($error)): ?>
        <p class="error">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="full_name">Full Name:</label>
        <input
            type="text"
            id="full_name"
            name="full_name"
            required
        >

        <label for="email">Email:</label>
        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <label for="password">Password:</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <label for="confirm_password">Confirm Password:</label>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            required
        >

        <label for="roll_number">Roll Number:</label>
        <input
            type="text"
            id="roll_number"
            name="roll_number"
            required
        >

        <label for="class">Class:</label>
        <input
            type="text"
            id="class"
            name="class"
            required
        >

        <button type="submit">Register</button>

    </form>

    <p class="account-link">
        Already have an account?
        <a href="login.php">Login here</a>
    </p>

</div>

</body>
</html>