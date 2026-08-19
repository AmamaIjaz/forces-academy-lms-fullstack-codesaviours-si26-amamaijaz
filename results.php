<?php

session_start();

require_once 'config/db.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['student_id'];

$results = [];

$sql = "SELECT
            r.subject,
            r.mids_marks,
            r.assignment_marks,
            r.quiz_marks,
            r.sessional_marks,
            r.final_marks,
            r.total_marks,
            r.grade,
            c.course_name

        FROM results r

        INNER JOIN courses c
            ON r.course_id = c.id

        WHERE r.student_id = ?

        ORDER BY r.id DESC";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        'i',
        $student_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $results[] = $row;
    }

    mysqli_stmt_close($stmt);
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

    <title>
        My Results | Forces Academy LMS
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<div class="dashboard-layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2>Forces Academy</h2>

        <p>Student Portal</p>

        <nav class="sidebar-nav">

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="courses.php">
                My Courses
            </a>

            <a href="assignments.php">
                Assignments
            </a>

            <a
                href="results.php"
                class="active"
            >
                My Results
            </a>

            <a href="notices.php">
                Notices
            </a>

            <a
                href="logout.php"
                class="logout"
            >
                Logout
            </a>

        </nav>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <div class="page-header">

            <h1>My Results</h1>

            <p>
                View your academic performance.
            </p>

        </div>


        <?php if (empty($results)): ?>

            <div class="empty-state">

                <h3>No Results Available</h3>

                <p>
                    Your results have not been published yet.
                </p>

            </div>

        <?php else: ?>

            <div class="results-card">

                <div class="results-heading">

                    <h2>Academic Results</h2>

                    <p>
                        Assessment distribution:
                        Mids 25 + Assignment 15 +
                        Quiz 10 + Sessional 10 +
                        Final 40 = 100
                    </p>

                </div>


                <div class="table-container">

                    <table class="results-table">

                        <thead>

                            <tr>

                                <th>Subject</th>

                                <th>Mids<br><span>/ 25</span></th>

                                <th>Assignment<br><span>/ 15</span></th>

                                <th>Quiz<br><span>/ 10</span></th>

                                <th>Sessional<br><span>/ 10</span></th>

                                <th>Final<br><span>/ 40</span></th>

                                <th>Total<br><span>/ 100</span></th>

                                <th>Grade</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($results as $row): ?>

                                <tr>

                                    <td>

                                        <div class="result-subject">

                                            <strong>
                                                <?php
                                                echo htmlspecialchars(
                                                    $row['subject']
                                                );
                                                ?>
                                            </strong>

                                            <small>
                                                <?php
                                                echo htmlspecialchars(
                                                    $row['course_name']
                                                );
                                                ?>
                                            </small>

                                        </div>

                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $row['mids_marks']
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $row['assignment_marks']
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $row['quiz_marks']
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $row['sessional_marks']
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $row['final_marks']
                                        );
                                        ?>
                                    </td>


                                    <td>

                                        <strong class="total-marks">

                                            <?php
                                            echo htmlspecialchars(
                                                $row['total_marks']
                                            );
                                            ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <span class="grade-badge">

                                            <?php
                                            echo htmlspecialchars(
                                                $row['grade']
                                            );
                                            ?>

                                        </span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        <?php endif; ?>

    </main>

</div>

</body>
</html>