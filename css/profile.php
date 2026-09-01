```php
<?php

session_start();

require_once 'config/db.php';

/* =========================================================
   AUTHENTICATION
   ========================================================= */

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = (int) $_SESSION['student_id'];

$success = '';
$error = '';
$password_success = '';
$password_error = '';

/* =========================================================
   UPDATE PROFILE
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($full_name === '' || $email === '') {

        $error = "Name and email are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /*
         * Check whether another student is already using this email.
         */
        $check_sql = "
            SELECT id
            FROM students
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ";

        $check_stmt = mysqli_prepare($conn, $check_sql);

        if ($check_stmt) {

            mysqli_stmt_bind_param(
                $check_stmt,
                "si",
                $email,
                $student_id
            );

            mysqli_stmt_execute($check_stmt);

            $check_result = mysqli_stmt_get_result($check_stmt);

            if (mysqli_num_rows($check_result) > 0) {

                $error = "This email address is already being used.";

            } else {

                /*
                 * Update student's name and email.
                 */
                $update_sql = "
                    UPDATE students
                    SET full_name = ?, email = ?
                    WHERE id = ?
                ";

                $update_stmt = mysqli_prepare($conn, $update_sql);

                if ($update_stmt) {

                    mysqli_stmt_bind_param(
                        $update_stmt,
                        "ssi",
                        $full_name,
                        $email,
                        $student_id
                    );

                    if (mysqli_stmt_execute($update_stmt)) {

                        /*
                         * Update session name immediately.
                         */
                        $_SESSION['student_name'] = $full_name;

                        $success = "Your profile has been updated successfully.";

                    } else {

                        $error = "Unable to update your profile.";
                    }

                    mysqli_stmt_close($update_stmt);

                } else {

                    $error = "Database error while updating profile.";
                }
            }

            mysqli_stmt_close($check_stmt);

        } else {

            $error = "Database error while checking email.";
        }
    }
}


/* =========================================================
   CHANGE PASSWORD
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (
        $current_password === '' ||
        $new_password === '' ||
        $confirm_password === ''
    ) {

        $password_error = "All password fields are required.";

    } elseif ($new_password !== $confirm_password) {

        $password_error = "New password and confirm password do not match.";

    } elseif (strlen($new_password) < 6) {

        $password_error = "New password must be at least 6 characters long.";

    } else {

        /*
         * Get current password from database.
         */
        $password_sql = "
            SELECT password
            FROM students
            WHERE id = ?
            LIMIT 1
        ";

        $password_stmt = mysqli_prepare($conn, $password_sql);

        if ($password_stmt) {

            mysqli_stmt_bind_param(
                $password_stmt,
                "i",
                $student_id
            );

            mysqli_stmt_execute($password_stmt);

            $password_result = mysqli_stmt_get_result($password_stmt);
            $student_password = mysqli_fetch_assoc($password_result);

            if (!$student_password) {

                $password_error = "Student account could not be found.";

            } elseif (!password_verify(
                $current_password,
                $student_password['password']
            )) {

                $password_error = "Current password is incorrect.";

            } else {

                /*
                 * Securely hash the new password.
                 */
                $hashed_password = password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );

                $update_password_sql = "
                    UPDATE students
                    SET password = ?
                    WHERE id = ?
                ";

                $update_password_stmt = mysqli_prepare(
                    $conn,
                    $update_password_sql
                );

                if ($update_password_stmt) {

                    mysqli_stmt_bind_param(
                        $update_password_stmt,
                        "si",
                        $hashed_password,
                        $student_id
                    );

                    if (mysqli_stmt_execute($update_password_stmt)) {

                        $password_success =
                            "Your password has been changed successfully.";

                    } else {

                        $password_error =
                            "Unable to change your password.";
                    }

                    mysqli_stmt_close($update_password_stmt);

                } else {

                    $password_error =
                        "Database error while changing password.";
                }
            }

            mysqli_stmt_close($password_stmt);

        } else {

            $password_error =
                "Database error while checking your password.";
        }
    }
}


/* =========================================================
   GET CURRENT STUDENT DATA
   ========================================================= */

$sql = "
    SELECT id, full_name, email, roll_number, class
    FROM students
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error.");
}

mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$student) {
    session_destroy();

    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile | Forces Academy LMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/style.css">

    <style>

        body {
            background: #EAE6BC;
            margin: 0;
        }

        .profile-content {
            padding: 35px;
        }

        .profile-header {
            margin-bottom: 30px;
        }

        .profile-header h1 {
            color: #0A2947;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .profile-header p {
            color: #555;
            margin: 0;
            font-size: 16px;
        }

        .profile-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 4px 15px rgba(10, 41, 71, 0.08);
            height: 100%;
        }

        .profile-card h3 {
            color: #0A2947;
            font-weight: 700;
            margin-bottom: 25px;
            border-bottom: 2px solid #EAE6BC;
            padding-bottom: 12px;
        }

        .info-box {
            background: #F8F9F8;
            border-left: 4px solid #0A2947;
            padding: 15px 18px;
            margin-bottom: 15px;
            border-radius: 7px;
        }

        .info-label {
            display: block;
            color: #777;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .info-value {
            color: #0A2947;
            font-size: 17px;
            font-weight: 600;
        }

        .form-label {
            color: #0A2947;
            font-weight: 600;
        }

        .form-control {
            padding: 12px 14px;
            border: 1px solid #d5d5d5;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: #0A2947;
            box-shadow: 0 0 0 0.2rem rgba(10, 41, 71, 0.12);
        }

        .btn-navy {
            background: #0A2947;
            color: #EAE6BC;
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-navy:hover {
            background: #123d63;
            color: #ffffff;
        }

        .alert {
            border-radius: 8px;
        }

        .profile-icon {
            width: 70px;
            height: 70px;
            background: #0A2947;
            color: #EAE6BC;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {

            .profile-content {
                padding: 20px;
            }

        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- =================================================
             SIDEBAR
             ================================================= -->

        <aside class="col-md-3 col-lg-2 p-0">

            <div class="student-sidebar">

                <div class="sidebar-brand">
                    <h4>Forces Academy</h4>
                    <small>Student Panel</small>
                </div>

                <nav class="sidebar-nav">

                    <a href="dashboard.php">
                        <span>🏠</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="courses.php">
                        <span>📚</span>
                        <span>My Courses</span>
                    </a>

                    <a href="assignments.php">
                        <span>📝</span>
                        <span>Assignments</span>
                    </a>

                    <a href="results.php">
                        <span>📊</span>
                        <span>My Results</span>
                    </a>

                    <a href="timetable.php">
                        <span>📅</span>
                        <span>Timetable</span>
                    </a>

                    <a href="notices.php">
                        <span>📢</span>
                        <span>Notices</span>
                    </a>

                    <a href="profile.php" class="active">
                        <span>👤</span>
                        <span>My Profile</span>
                    </a>

                    <a href="logout.php">
                        <span>🚪</span>
                        <span>Logout</span>
                    </a>

                </nav>

            </div>

        </aside>


        <!-- =================================================
             MAIN CONTENT
             ================================================= -->

        <main class="col-md-9 col-lg-10">

            <div class="profile-content">

                <div class="profile-header">

                    <h1>My Profile</h1>

                    <p>
                        View and manage your personal account information.
                    </p>

                </div>


                <!-- PROFILE UPDATE MESSAGE -->

                <?php if ($success): ?>

                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>

                <?php endif; ?>


                <?php if ($error): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>


                <div class="row g-4">

                    <!-- =================================================
                         CURRENT PROFILE
                         ================================================= -->

                    <div class="col-lg-5">

                        <div class="profile-card">

                            <div class="profile-icon">
                                👤
                            </div>

                            <h3>Profile Information</h3>

                            <div class="info-box">

                                <span class="info-label">
                                    Name
                                </span>

                                <span class="info-value">
                                    <?= htmlspecialchars($student['full_name']) ?>
                                </span>

                            </div>


                            <div class="info-box">

                                <span class="info-label">
                                    Email
                                </span>

                                <span class="info-value">
                                    <?= htmlspecialchars($student['email']) ?>
                                </span>

                            </div>


                            <div class="info-box">

                                <span class="info-label">
                                    Roll Number
                                </span>

                                <span class="info-value">
                                    <?= htmlspecialchars($student['roll_number'] ?? 'Not available') ?>
                                </span>

                            </div>


                            <div class="info-box">

                                <span class="info-label">
                                    Class
                                </span>

                                <span class="info-value">
                                    <?= htmlspecialchars($student['class'] ?? 'Not available') ?>
                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                         EDIT PROFILE
                         ================================================= -->

                    <div class="col-lg-7">

                        <div class="profile-card">

                            <h3>Edit Profile</h3>

                            <form method="POST">

                                <div class="mb-3">

                                    <label
                                        for="full_name"
                                        class="form-label"
                                    >
                                        Full Name
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="full_name"
                                        name="full_name"
                                        value="<?= htmlspecialchars($student['full_name']) ?>"
                                        required
                                    >

                                </div>


                                <div class="mb-4">

                                    <label
                                        for="email"
                                        class="form-label"
                                    >
                                        Email Address
                                    </label>

                                    <input
                                        type="email"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        value="<?= htmlspecialchars($student['email']) ?>"
                                        required
                                    >

                                </div>


                                <button
                                    type="submit"
                                    name="update_profile"
                                    class="btn btn-navy"
                                >
                                    Save Changes
                                </button>

                            </form>

                        </div>

                    </div>


                    <!-- =================================================
                         CHANGE PASSWORD
                         ================================================= -->

                    <div class="col-lg-7">

                        <div class="profile-card">

                            <h3>Change Password</h3>


                            <?php if ($password_success): ?>

                                <div class="alert alert-success">
                                    <?= htmlspecialchars($password_success) ?>
                                </div>

                            <?php endif; ?>


                            <?php if ($password_error): ?>

                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($password_error) ?>
                                </div>

                            <?php endif; ?>


                            <form method="POST">

                                <div class="mb-3">

                                    <label
                                        for="current_password"
                                        class="form-label"
                                    >
                                        Current Password
                                    </label>

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="current_password"
                                        name="current_password"
                                        required
                                    >

                                </div>


                                <div class="mb-3">

                                    <label
                                        for="new_password"
                                        class="form-label"
                                    >
                                        New Password
                                    </label>

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="new_password"
                                        name="new_password"
                                        minlength="6"
                                        required
                                    >

                                    <small class="text-muted">
                                        Password must be at least 6 characters.
                                    </small>

                                </div>


                                <div class="mb-4">

                                    <label
                                        for="confirm_password"
                                        class="form-label"
                                    >
                                        Confirm New Password
                                    </label>

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="confirm_password"
                                        name="confirm_password"
                                        minlength="6"
                                        required
                                    >

                                </div>


                                <button
                                    type="submit"
                                    name="change_password"
                                    class="btn btn-navy"
                                >
                                    Change Password
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

</body>

</html>
```
