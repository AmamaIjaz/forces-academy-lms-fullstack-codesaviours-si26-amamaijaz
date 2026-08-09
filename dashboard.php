<?php

session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_name = $_SESSION['student_name'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard | Forces Academy LMS</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="dashboard">

    <h2>
        Welcome, <?php echo htmlspecialchars($student_name); ?>!
    </h2>

    <div class="dashboard-card">
        <h3>Student Dashboard</h3>

        <p>
            Welcome to Forces Academy Learning Management System.
        </p>

        <p>
            You are successfully logged in to your student account.
        </p>
    </div>

    <a href="logout.php" class="logout-btn">
        Logout
    </a>

</div>

</body>
</html>