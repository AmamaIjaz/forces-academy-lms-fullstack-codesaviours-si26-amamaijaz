
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
| Fetch Results
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        results.id,
        results.subject,
        results.mids_marks,
        results.assignment_marks,
        results.quiz_marks,
        results.sessional_marks,
        results.final_marks,
        results.total_marks,
        results.grade,
        students.full_name,
        students.email,
        courses.course_name
    FROM results
    INNER JOIN students
        ON results.student_id = students.id
    INNER JOIN courses
        ON results.course_id = courses.id
    ORDER BY students.full_name ASC, results.subject ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}
$success = '';

if (isset($_GET['updated'])) {
    $success = "Result updated successfully.";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Results | Forces Academy</title>

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

            <a href="dashboard.php">

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


            <a href="results.php" class="active">

                <span>📊</span>
                <span>Upload Results</span>

            </a>


            <a href="notices.php">

                <span>📢</span>
                <span>Post Notice</span>

            </a>
          <a href="timetable.php">

    <span>📅</span>

    <span>
        Timetable
    </span>

</a>
 <a
                href="fees.php"
              
            >

                <span>📅</span>

                <span>
                    Fees
                </span>

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

                <h1>Student Results</h1>

                <p>
                    View and manage student academic results.
                </p>

            </div>


            <div class="admin-badge">
                Administrator
            </div>

        </div>
        <?php if (!empty($success)): ?>

    <div class="result-success">
        <?= htmlspecialchars($success) ?>
    </div>

<?php endif; ?>


        <!-- =========================
             RESULTS INFORMATION
        ========================== -->

        <div class="content-card results-info">

            <div class="section-heading">

                <div>

                    <h3>
                        Results Management
                    </h3>

                    <p>
                        Each subject result is displayed in a single row.
                    </p>

                </div>


                <a href="add_result.php"
                   class="add-result-btn">

                    + Add Result

                </a>

            </div>


            <!-- MARKS DISTRIBUTION -->

            <div class="marks-distribution">

                <div class="mark-box">

                    <strong>25</strong>
                    <span>Mid</span>

                </div>


                <div class="mark-box">

                    <strong>15</strong>
                    <span>Assignment</span>

                </div>


                <div class="mark-box">

                    <strong>10</strong>
                    <span>Quiz</span>

                </div>


                <div class="mark-box">

                    <strong>10</strong>
                    <span>Sessional</span>

                </div>


                <div class="mark-box">

                    <strong>40</strong>
                    <span>Final</span>

                </div>


                <div class="mark-box total-box">

                    <strong>100</strong>
                    <span>Total</span>

                </div>

            </div>

        </div>


        <!-- =========================
             RESULTS TABLE
        ========================== -->

        <div class="content-card results-table-card">

            <div class="section-heading">

                <div>

                    <h3>
                        All Student Results
                    </h3>

                    <p>
                        Complete marks for each subject.
                    </p>

                </div>

            </div>


            <div class="table-wrapper">

                <table class="results-table">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Student</th>

                            <th>Subject</th>

                            <th>Mid<br><small>25</small></th>

                            <th>Assignment<br><small>15</small></th>

                            <th>Quiz<br><small>10</small></th>

                            <th>Sessional<br><small>10</small></th>

                            <th>Final<br><small>40</small></th>

                            <th>Total<br><small>100</small></th>

                            <th>Grade</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    $count = 1;

                    if (mysqli_num_rows($result) > 0):

                        while ($row = mysqli_fetch_assoc($result)):

                    ?>

                        <tr>

                            <!-- NUMBER -->

                            <td class="serial-number">

                                <?= $count++; ?>

                            </td>


                            <!-- STUDENT -->

                            <td class="student-cell">

                                <strong>
                                    <?= htmlspecialchars($row['full_name']); ?>
                                </strong>

                                <span>
                                    <?= htmlspecialchars($row['email']); ?>
                                </span>

                            </td>


                            <!-- SUBJECT -->

                            <td class="subject-cell">

                                <strong>
                                    <?= htmlspecialchars($row['subject']); ?>
                                </strong>

                                <span>
                                    <?= htmlspecialchars($row['course_name']); ?>
                                </span>

                            </td>


                            <!-- MID -->

                            <td>

                                <?= (int)$row['mids_marks']; ?>

                            </td>


                            <!-- ASSIGNMENT -->

                            <td>

                                <?= (int)$row['assignment_marks']; ?>

                            </td>


                            <!-- QUIZ -->

                            <td>

                                <?= (int)$row['quiz_marks']; ?>

                            </td>


                            <!-- SESSIONAL -->

                            <td>

                                <?= (int)$row['sessional_marks']; ?>

                            </td>


                            <!-- FINAL -->

                            <td>

                                <?= (int)$row['final_marks']; ?>

                            </td>


                            <!-- TOTAL -->

                            <td class="total-cell">

                                <?= (int)$row['total_marks']; ?>

                                <small>/100</small>

                            </td>


                            <!-- GRADE -->

                            <td>

                                <span class="grade-badge">

                                    <?= htmlspecialchars($row['grade']); ?>

                                </span>

                            </td>


                            <!-- ACTIONS -->

                            <td class="action-cell">

                                <a
                                    href="edit_result.php?id=<?= (int)$row['id']; ?>"
                                    class="edit-btn">
                                    Edit
                                </a>


                                <a
                                    href="delete_result.php?id=<?= (int)$row['id']; ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this result?');">
                                    Delete
                                </a>

                            </td>

                        </tr>


                    <?php

                        endwhile;

                    else:

                    ?>

                        <tr>

                            <td colspan="11">

                                <div class="empty-results">

                                    <div class="empty-results-icon">
                                        📊
                                    </div>

                                    <h3>
                                        No Results Found
                                    </h3>

                                    <p>
                                        No student results have been uploaded yet.
                                    </p>

                                    <a
                                        href="add_result.php"
                                        class="add-result-btn">
                                        + Add First Result
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

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
                    Result Information
                </h3>

                <p>

                    Each subject result contains Mid marks out of 25,
                    Assignment marks out of 15, Quiz marks out of 10,
                    Sessional marks out of 10 and Final marks out of 40.
                    The complete result is calculated out of 100.

                </p>

            </div>

        </div>


    </main>

</div>

</body>

</html>

