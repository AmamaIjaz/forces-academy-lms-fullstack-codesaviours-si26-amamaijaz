<?php

session_start();

require_once 'config/db.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['student_id'];

$assignments = [];

$sql = "SELECT
            a.id,
            a.title,
            a.description,
            a.due_date,
            c.course_name,
            CASE
                WHEN s.id IS NOT NULL THEN 1
                ELSE 0
            END AS submitted
        FROM assignments a
        INNER JOIN courses c
            ON a.course_id = c.id
        LEFT JOIN submissions s
            ON a.id = s.assignment_id
            AND s.student_id = ?
        ORDER BY a.due_date ASC";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = $row;
    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Assignments | Forces Academy LMS</title>

    <link rel="stylesheet"
          href="css/style.css">

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

            <a href="assignments.php" class="active">
                Assignments
            </a>

            <a href="results.php">
                My Results
            </a>

            <a href="notices.php">
                Notices
            </a>
          <a href="timetable.php">
        Timetable
</a>
    <a href="profile.php">
 My Profile
</a>
</a>
 <a href="fees.php" >
                My Fees
            </a>
            <a href="logout.php" class="logout">
                Logout
            </a>

        </nav>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <div class="page-header">

            <h1>Assignments</h1>

            <p>
                View your assignments and submit your work.
            </p>

        </div>


        <?php if (empty($assignments)): ?>

            <div class="empty-state">

                <h3>No Assignments Available</h3>

                <p>
                    There are currently no assignments available.
                </p>

            </div>

        <?php else: ?>

            <div class="assignment-grid">

                <?php foreach ($assignments as $assignment): ?>

                    <div class="assignment-card">

                        <div class="assignment-card-top">

                            <span class="assignment-course">
                                <?php
                                echo htmlspecialchars(
                                    $assignment['course_name']
                                );
                                ?>
                            </span>

                            <?php if ($assignment['submitted']): ?>

                                <span class="submission-badge submitted-badge">
                                    Submitted
                                </span>

                            <?php endif; ?>

                        </div>


                        <h2>
                            <?php
                            echo htmlspecialchars(
                                $assignment['title']
                            );
                            ?>
                        </h2>


                        <p class="assignment-description">
                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $assignment['description']
                                )
                            );
                            ?>
                        </p>


                        <div class="assignment-footer">

                            <div>

                                <span class="due-label">
                                    Due Date
                                </span>

                                <strong>
                                    <?php
                                    echo date(
                                        'd M Y',
                                        strtotime(
                                            $assignment['due_date']
                                        )
                                    );
                                    ?>
                                </strong>

                            </div>


                            <?php if (!$assignment['submitted']): ?>

                                <a
                                    href="submit_assignment.php?assignment_id=<?php echo $assignment['id']; ?>"
                                    class="btn"
                                >
                                    Submit Assignment
                                </a>

                            <?php else: ?>

                                <span class="already-submitted">
                                    ✓ Submitted
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </main>

</div>

</body>
</html>