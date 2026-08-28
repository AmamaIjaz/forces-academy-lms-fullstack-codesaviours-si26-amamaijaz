<?php

session_start();

require_once '../config/db.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {

        $error = "Username and password are required.";

    } else {

        $sql = "SELECT id, username, password, email
                FROM admins
                WHERE username = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $admin = mysqli_fetch_assoc($result);

            if ($admin && password_verify($password, $admin['password'])) {

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = 'admin';

                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Invalid username or password.";
            }

            mysqli_stmt_close($stmt);

        } else {

            $error = "Unable to process login. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Forces Academy</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="admin.css">

</head>


<body class="login-page">


<div class="login-container">

    <div class="login-card">


        <!-- ADMIN ICON -->

        <div class="login-header">

            <div class="admin-icon">
                ⚙
            </div>

            <h2>
                Admin Login
            </h2>

            <p>
                Forces Academy LMS
            </p>

        </div>


        <!-- ERROR MESSAGE -->

        <?php if (!empty($error)): ?>

            <div class="login-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <!-- LOGIN FORM -->

        <form method="POST">


            <!-- USERNAME -->

            <div class="login-form-group">

                <label for="username">
                    Username
                </label>

                <div class="login-input-wrapper">

                    <span class="input-icon">
                        👤
                    </span>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter admin username"
                        autocomplete="username"
                        required
                    >

                </div>

            </div>


            <!-- PASSWORD -->

            <div class="login-form-group">

                <label for="password">
                    Password
                </label>

                <div class="login-input-wrapper">

                    <span class="input-icon">
                        🔒
                    </span>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter admin password"
                        autocomplete="current-password"
                        required
                    >

                </div>

            </div>


            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                class="admin-login-btn">

                Login as Admin

            </button>


        </form>


        <!-- FOOTER -->

        <div class="login-footer">

            <a href="../login.php">
                ← Student Login
            </a>

        </div>


    </div>

</div>


</body>

</html>