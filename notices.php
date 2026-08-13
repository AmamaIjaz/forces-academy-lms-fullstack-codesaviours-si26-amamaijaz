<?php

session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$student_name = $_SESSION['student_name'];


/* ==========================================
   GET NOTICES
   ========================================== */

$notices_query = "
    SELECT id, title, content, created_at
    FROM notices
    ORDER BY created_at DESC
";

$notices_result = mysqli_query($conn, $notices_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Notices | Forces Academy LMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="css/style.css">

</head>


<body>


<!-- MOBILE NAVBAR -->

<nav class="navbar mobile-navbar d-lg-none">

    <div class="container-fluid">

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#sidebar"
        >
            <i class="bi bi-list"></i>
        </button>

        <span class="mobile-brand">
            Forces Academy LMS
        </span>

    </div>

</nav>


<!-- SIDEBAR -->

<aside
    class="sidebar offcanvas-lg offcanvas-start"
    tabindex="-1"
    id="sidebar"
>

    <div class="sidebar-header">

        <div class="academy-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>

        <div>

            <h5>
                Forces Academy
            </h5>

            <small>
                Learning Management System
            </small>

        </div>

        <button
            type="button"
            class="btn-close btn-close-white d-lg-none ms-auto"
            data-bs-dismiss="offcanvas"
        ></button>

    </div>


    <div class="student-box">

        <div class="student-avatar">

            <?php
            echo strtoupper(
                substr($student_name, 0, 1)
            );
            ?>

        </div>

        <div>

            <strong>
                <?php echo htmlspecialchars($student_name); ?>
            </strong>

            <small>
                Student
            </small>

        </div>

    </div>


    <nav class="sidebar-nav">

        <a
            href="dashboard.php"
            class="sidebar-link"
        >
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        <a
            href="courses.php"
            class="sidebar-link"
        >
            <i class="bi bi-book-fill"></i>
            <span>My Courses</span>
        </a>

        <a
            href="assignments.php"
            class="sidebar-link"
        >
            <i class="bi bi-file-earmark-text-fill"></i>
            <span>Assignments</span>
        </a>

        <a
            href="results.php"
            class="sidebar-link"
        >
            <i class="bi bi-bar-chart-fill"></i>
            <span>My Results</span>
        </a>

        <a
            href="notices.php"
            class="sidebar-link active"
        >
            <i class="bi bi-megaphone-fill"></i>
            <span>Notices</span>
        </a>

        <div class="sidebar-divider"></div>

        <a
            href="logout.php"
            class="sidebar-link logout-link"
        >
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>

    </nav>

</aside>


<!-- MAIN CONTENT -->

<main class="main-content">


    <div class="page-header">

        <div>

            <p class="page-label">
                COMMUNICATION
            </p>

            <h1>
                Notice Board
            </h1>

            <p class="welcome-text">
                Stay updated with the latest academy announcements.
            </p>

        </div>

    </div>


    <?php if ($notices_result && mysqli_num_rows($notices_result) > 0): ?>


        <div class="notice-board">

            <?php while ($notice = mysqli_fetch_assoc($notices_result)): ?>

                <div class="notice-board-item">

                    <div class="notice-board-icon">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>


                    <div class="notice-board-content">

                        <div class="notice-title-row">

                            <h4>
                                <?php
                                echo htmlspecialchars(
                                    $notice['title']
                                );
                                ?>
                            </h4>

                            <span class="notice-date">

                                <i class="bi bi-calendar3"></i>

                                <?php
                                echo date(
                                    'd M Y',
                                    strtotime(
                                        $notice['created_at']
                                    )
                                );
                                ?>

                            </span>

                        </div>


                        <p>
                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $notice['content']
                                )
                            );
                            ?>
                        </p>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>


    <?php else: ?>


        <div class="empty-state large-empty">

            <i class="bi bi-megaphone"></i>

            <h3>
                No notices available
            </h3>

            <p>
                There are currently no announcements or notices.
            </p>

        </div>


    <?php endif; ?>


</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>