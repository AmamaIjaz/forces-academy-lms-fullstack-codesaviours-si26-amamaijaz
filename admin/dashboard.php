<?php

session_start();

require_once '../config/db.php';

/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Database Counts
|--------------------------------------------------------------------------
*/

function getCount($conn, $table)
{
    $allowedTables = [
        'students',
        'courses',
        'assignments',
        'notices'
    ];

    if (!in_array($table, $allowedTables)) {
        return 0;
    }

    $sql = "SELECT COUNT(*) AS total FROM `$table`";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return (int) $row['total'];
    }

    return 0;
}

$totalStudents = getCount($conn, 'students');
$totalCourses = getCount($conn, 'courses');
$totalAssignments = getCount($conn, 'assignments');
$totalNotices = getCount($conn, 'notices');

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Forces Academy</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="admin.css">

</head>

<body>

<div class="admin-layout">

    <!-- =========================
         ADMIN SIDEBAR
    ========================== -->

    <aside class="admin-sidebar">

        <div class="sidebar-brand">

            <div class="brand-icon">
                ⚙
            </div>

            <div>
                <h3>Forces Academy</h3>
                <span>Admin Panel</span>
            </div>

        </div>


        <nav class="admin-nav">

            <a href="dashboard.php" class="active">
                <span>🏠</span>
                <span>Dashboard</span>
            </a>

            <a href="students.php">
                <span>👨‍🎓</span>
                <span>Manage Students</span>
            </a>

            <a href="courses.php">
                <span>📚</span>
                <span>Manage Courses</span>
            </a>

            <a href="assignments.php">
                <span>📝</span>
                <span>Manage Assignments</span>
            </a>

            <a href="results.php">
                <span>📊</span>
                <span>Upload Results</span>
            </a>

            <a href="notices.php">
                <span>📢</span>
                <span>Post Notice</span>
            </a>

            <a href="logout.php" class="logout-link">
                <span>🚪</span>
                <span>Logout</span>
            </a>

        </nav>

    </aside>


    <!-- =========================
         MAIN CONTENT
    ========================== -->

    <main class="admin-content">

        <!-- TOP HEADER -->

        <div class="top-header">

            <div>

                <h1>Admin Dashboard</h1>

                <p>
                    Welcome,
                    <strong>
                        <?= htmlspecialchars(
                            $_SESSION['admin_username'] ?? 'Administrator'
                        ) ?>
                    </strong>
                </p>

            </div>

            <div class="admin-badge">
                Administrator
            </div>

        </div>


        <!-- =========================
             STATISTICS
        ========================== -->

        <div class="row g-4">


            <!-- STUDENTS -->

            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">
                        👨‍🎓
                    </div>

                    <div>

                        <h2>
                            <?= $totalStudents ?>
                        </h2>

                        <p>
                            Total Students
                        </p>

                    </div>

                </div>

            </div>


            <!-- COURSES -->

            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">
                        📚
                    </div>

                    <div>

                        <h2>
                            <?= $totalCourses ?>
                        </h2>

                        <p>
                            Total Courses
                        </p>

                    </div>

                </div>

            </div>


            <!-- ASSIGNMENTS -->

            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">
                        📝
                    </div>

                    <div>

                        <h2>
                            <?= $totalAssignments ?>
                        </h2>

                        <p>
                            Total Assignments
                        </p>

                    </div>

                </div>

            </div>


            <!-- NOTICES -->

            <div class="col-md-6 col-xl-3">

                <div class="stat-card">

                    <div class="stat-icon">
                        📢
                    </div>

                    <div>

                        <h2>
                            <?= $totalNotices ?>
                        </h2>

                        <p>
                            Total Notices
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================
             QUICK ACTIONS
        ========================== -->

        <div class="content-card quick-actions">

            <div class="section-heading">

                <div>

                    <h3>
                        Quick Actions
                    </h3>

                    <p>
                        Manage your LMS from the options below.
                    </p>

                </div>

            </div>


            <div class="quick-grid">


                <a href="students.php"
                   class="quick-card">

                    <div class="quick-icon">
                        👨‍🎓
                    </div>

                    <div>

                        <h4>
                            Manage Students
                        </h4>

                        <p>
                            View, search and manage students.
                        </p>

                    </div>

                </a>


                <a href="courses.php"
                   class="quick-card">

                    <div class="quick-icon">
                        📚
                    </div>

                    <div>

                        <h4>
                            Manage Courses
                        </h4>

                        <p>
                            Add, edit and delete courses.
                        </p>

                    </div>

                </a>


                <a href="results.php"
                   class="quick-card">

                    <div class="quick-icon">
                        📊
                    </div>

                    <div>

                        <h4>
                            Upload Results
                        </h4>

                        <p>
                            Add student marks and grades.
                        </p>

                    </div>

                </a>


                <a href="notices.php"
                   class="quick-card">

                    <div class="quick-icon">
                        📢
                    </div>

                    <div>

                        <h4>
                            Post Notice
                        </h4>

                        <p>
                            Publish announcements for students.
                        </p>

                    </div>

                </a>


            </div>

        </div>


        <!-- =========================
             ADMIN INFORMATION
        ========================== -->

        <div class="content-card admin-info">

            <div class="info-icon">
                ℹ
            </div>

            <div>

                <h3>
                    Admin Control Panel
                </h3>

                <p>
                    From this panel you can manage students,
                    courses, assignments, results and notices
                    for the Forces Academy Learning Management System.
                </p>

            </div>

        </div>

    </main>

</div>


</body>
</html>