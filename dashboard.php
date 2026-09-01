<?php

session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$student_name = $_SESSION['student_name'];

/* ==========================================
   TOTAL COURSES
   ========================================== */

$total_courses = 0;

$course_count_query = "SELECT COUNT(*) AS total FROM courses";

$course_count_result = mysqli_query($conn, $course_count_query);

if ($course_count_result) {
    $course_count_data = mysqli_fetch_assoc($course_count_result);
    $total_courses = $course_count_data['total'];
}


/* ==========================================
   PENDING ASSIGNMENTS
   Placeholder for now
   ========================================== */

$pending_assignments = 0;


/* ==========================================
   LATEST NOTICE
   ========================================== */

$latest_notice = null;

$latest_notice_query = "
    SELECT id, title, content, created_at
    FROM notices
    ORDER BY created_at DESC
    LIMIT 1
";

$latest_notice_result = mysqli_query($conn, $latest_notice_query);

if ($latest_notice_result) {
    $latest_notice = mysqli_fetch_assoc($latest_notice_result);
}


/* ==========================================
   RECENT 3 NOTICES
   ========================================== */

$recent_notices_query = "
    SELECT id, title, content, created_at
    FROM notices
    ORDER BY created_at DESC
    LIMIT 3
";

$recent_notices_result = mysqli_query($conn, $recent_notices_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Forces Academy LMS</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">

</head>


<body>


<!-- ==========================================
     MOBILE TOP BAR
     ========================================== -->

<nav class="navbar mobile-navbar d-lg-none">

    <div class="container-fluid">

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#sidebar"
            aria-controls="sidebar"
        >
            <i class="bi bi-list"></i>
        </button>

        <span class="mobile-brand">
            Forces Academy LMS
        </span>

    </div>

</nav>


<!-- ==========================================
     SIDEBAR
     ========================================== -->

<aside
    class="sidebar offcanvas-lg offcanvas-start"
    tabindex="-1"
    id="sidebar"
    aria-labelledby="sidebarTitle"
>

    <div class="sidebar-header">

        <div class="academy-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>

        <div>

            <h5 id="sidebarTitle">
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
            aria-label="Close"
        ></button>

    </div>


    <!-- Student information -->

    <div class="student-box">

        <div class="student-avatar">
            <?php echo strtoupper(substr($student_name, 0, 1)); ?>
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


    <!-- Navigation -->

    <nav class="sidebar-nav">

        <a
            href="dashboard.php"
            class="sidebar-link active"
        >
            Dashboard
        </a>


        <a
            href="courses.php"
            class="sidebar-link"
        >
           
            My Courses
        </a>


        <a
            href="assignments.php"
            class="sidebar-link"
        >
        
            Assignments
        </a>


        <a
            href="results.php"
            class="sidebar-link"
        >
            
            My Results
        </a>


        <a
            href="notices.php"
            class="sidebar-link"
        >
           
            Notices
        </a>


        <div class="sidebar-divider"></div>

          <a href="timetable.php">
        Timetable
</a>
    <a href="profile.php">
     Profile
</a>
 <a href="fees.php" >
                My Fees
            </a>

        <a
            href="logout.php"
            class="sidebar-link logout-link"
        >
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>

    </nav>

</aside>


<!-- ==========================================
     MAIN CONTENT
     ========================================== -->

<main class="main-content">

    <!-- Page Header -->

    <div class="page-header">

        <div>

            <h3 class="page-label">
                STUDENT PORTAL
            </h3>

            <h3>
                Dashboard
            </h3>

            <p class="welcome-text">
                Hello,
                <strong><b>
                    <?php echo htmlspecialchars($student_name); ?>
               </b> </strong>!
                Welcome back.
            </p>

        </div>

        <div class="header-date">

            <i class="bi bi-calendar3"></i>

            <?php echo date('d M Y'); ?>

        </div>

    </div>


    <!-- ==========================================
         STAT CARDS
         ========================================== -->

    <div class="row g-4 mb-4">


        <!-- Total Courses -->

        <div class="col-12 col-md-6 col-xl-4">

            <div class="stat-card">

                <div class="stat-icon blue-icon">
                    <i class="bi bi-book-fill"></i>
                </div>

                <div>

                    <p>
                        Total Courses
                    </p>

                    <h3>
                        <?php echo $total_courses; ?>
                    </h3>

                </div>

            </div>

        </div>


        <!-- Pending Assignments -->

        <div class="col-12 col-md-6 col-xl-4">

            <div class="stat-card">

                <div class="stat-icon brown-icon">
                    <i class="bi bi-file-earmark-check-fill"></i>
                </div>

                <div>

                    <p>
                        Pending Assignments
                    </p>

                    <h3>
                        <?php echo $pending_assignments; ?>
                    </h3>

                    <small>
                        Coming soon
                    </small>

                </div>

            </div>

        </div>


        <!-- Latest Notice -->

        <div class="col-12 col-xl-4">

            <div class="stat-card notice-stat">

                <div class="stat-icon cream-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </div>

                <div>

                    <p>
                        Latest Notice
                    </p>

                    <?php if ($latest_notice): ?>

                        <h6>
                            <?php
                            echo htmlspecialchars(
                                $latest_notice['title']
                            );
                            ?>
                        </h6>

                    <?php else: ?>

                        <h6>
                            No notices yet
                        </h6>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    <!-- ==========================================
         RECENT NOTICES + QUICK LINKS
         ========================================== -->

    <div class="row g-4">


        <!-- Recent Notices -->

        <div class="col-12 col-lg-8">

            <div class="content-card">

                <div class="card-heading">

                    <div>

                        <p class="section-label">
                            UPDATES
                        </p>

                        <h4>
                            Recent Notices
                        </h4>

                    </div>

                    <a
                        href="notices.php"
                        class="view-all"
                    >
                        View All
                        <i class="bi bi-arrow-right"></i>
                    </a>

                </div>


                <?php if ($recent_notices_result && mysqli_num_rows($recent_notices_result) > 0): ?>

                    <div class="notice-list">

                        <?php while ($notice = mysqli_fetch_assoc($recent_notices_result)): ?>

                            <div class="notice-item">

                                <div class="notice-icon">
                                    <i class="bi bi-megaphone"></i>
                                </div>

                                <div class="notice-content">

                                    <h5>
                                        <?php
                                        echo htmlspecialchars(
                                            $notice['title']
                                        );
                                        ?>
                                    </h5>

                                    <p>
                                        <?php
                                        echo htmlspecialchars(
                                            $notice['content']
                                        );
                                        ?>
                                    </p>

                                    <small>
                                        <i class="bi bi-clock"></i>

                                        <?php
                                        echo date(
                                            'd M Y',
                                            strtotime(
                                                $notice['created_at']
                                            )
                                        );
                                        ?>
                                    </small>

                                </div>

                            </div>

                        <?php endwhile; ?>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <i class="bi bi-megaphone"></i>

                        <h5>
                            No notices available
                        </h5>

                        <p>
                            There are currently no notices to display.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- Quick Links -->

        <div class="col-12 col-lg-4">

            <div class="content-card quick-card">

                <div class="card-heading">

                    <div>

                        <p class="section-label">
                            QUICK ACCESS
                        </p>

                        <h4>
                            Quick Links
                        </h4>

                    </div>

                </div>


                <a
                    href="courses.php"
                    class="quick-link"
                >

                    <div class="quick-link-icon">
                        <i class="bi bi-book"></i>
                    </div>

                    <div>

                        <strong>
                            My Courses
                        </strong>

                        <small>
                            View enrolled courses
                        </small>

                    </div>

                    <i class="bi bi-chevron-right"></i>

                </a>


                <a
                    href="assignments.php"
                    class="quick-link"
                >

                    <div class="quick-link-icon brown-quick-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <div>

                        <strong>
                            Assignments
                        </strong>

                        <small>
                            View your assignments
                        </small>

                    </div>

                    <i class="bi bi-chevron-right"></i>

                </a>

            </div>

        </div>

    </div>

</main>


<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>