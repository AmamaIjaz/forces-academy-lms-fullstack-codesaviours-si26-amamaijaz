<?php

session_start();

require_once '../config/db.php';


/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['admin_role']) ||
    $_SESSION['admin_role'] !== 'admin'
) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Student ID
|--------------------------------------------------------------------------
*/

$student_id = (int) ($_GET['id'] ?? 0);

if ($student_id <= 0) {

    header("Location: students.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Student Details
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            full_name,
            email,
            roll_number,
            class,
            created_at
        FROM students
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error.");
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| Student Not Found
|--------------------------------------------------------------------------
*/

if (!$student) {

    header("Location: students.php?error=student_not_found");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Student Assignments
|--------------------------------------------------------------------------
*/

$assignment_sql = "SELECT
                        assignments.title,
                        assignments.due_date,
                        assignments.created_at,
                        courses.course_name
                   FROM assignments
                   INNER JOIN courses
                       ON assignments.course_id = courses.id
                   ORDER BY assignments.due_date ASC";

$assignment_result = mysqli_query(
    $conn,
    $assignment_sql
);


/*
|--------------------------------------------------------------------------
| Get Student Submissions
|--------------------------------------------------------------------------
*/

$submission_sql = "SELECT
                       assignments.title,
                       assignments.due_date,
                       submissions.file_path,
                       submissions.submitted_at,
                       submissions.status,
                       courses.course_name
                   FROM submissions
                   INNER JOIN assignments
                       ON submissions.assignment_id = assignments.id
                   INNER JOIN courses
                       ON assignments.course_id = courses.id
                   WHERE submissions.student_id = ?
                   ORDER BY submissions.submitted_at DESC";

$submission_stmt = mysqli_prepare(
    $conn,
    $submission_sql
);

$submissions = [];

if ($submission_stmt) {

    mysqli_stmt_bind_param(
        $submission_stmt,
        "i",
        $student_id
    );

    mysqli_stmt_execute($submission_stmt);

    $submission_result = mysqli_stmt_get_result(
        $submission_stmt
    );

    while (
        $submission = mysqli_fetch_assoc(
            $submission_result
        )
    ) {

        $submissions[] = $submission;
    }

    mysqli_stmt_close($submission_stmt);
}


/*
|--------------------------------------------------------------------------
| Get Student Results
|--------------------------------------------------------------------------
*/

$result_sql = "SELECT
                   subject,
                   mids_marks,
                   assignment_marks,
                   quiz_marks,
                   sessional_marks,
                   final_marks,
                   total_marks,
                   grade
               FROM results
               WHERE student_id = ?
               ORDER BY id DESC";

$result_stmt = mysqli_prepare(
    $conn,
    $result_sql
);

$student_results = [];

if ($result_stmt) {

    mysqli_stmt_bind_param(
        $result_stmt,
        "i",
        $student_id
    );

    mysqli_stmt_execute($result_stmt);

    $student_result_data = mysqli_stmt_get_result(
        $result_stmt
    );

    while (
        $result_row = mysqli_fetch_assoc(
            $student_result_data
        )
    ) {

        $student_results[] = $result_row;
    }

    mysqli_stmt_close($result_stmt);
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
        Student Details | Forces Academy
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="admin.css"
    >

</head>


<body>


<div class="admin-layout">


    <!-- =====================================================
         SIDEBAR
         ===================================================== -->

    <aside class="admin-sidebar">


        <div class="sidebar-brand">

            <div class="brand-icon">
                ⚙
            </div>

            <div>

                <h3>
                    Forces Academy
                </h3>

                <span>
                    Admin Panel
                </span>

            </div>

        </div>


        <nav class="admin-nav">


            <a href="dashboard.php">

                <span>🏠</span>

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="students.php"
                class="active"
            >

                <span>👨‍🎓</span>

                <span>
                    Manage Students
                </span>

            </a>


            <a href="courses.php">

                <span>📚</span>

                <span>
                    Manage Courses
                </span>

            </a>


            <a href="assignments.php">

                <span>📝</span>

                <span>
                    Manage Assignments
                </span>

            </a>


            <a href="results.php">

                <span>📊</span>

                <span>
                    Upload Results
                </span>

            </a>


            <a href="notices.php">

                <span>📢</span>

                <span>
                    Post Notice
                </span>

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
            <a
                href="logout.php"
                class="logout-link"
            >

                <span>🚪</span>

                <span>
                    Logout
                </span>

            </a>


        </nav>

    </aside>



    <!-- =====================================================
         MAIN CONTENT
         ===================================================== -->

    <main class="admin-content">


        <!-- TOP HEADER -->

        <div class="top-header">

            <div>

                <h1>
                    Student Details
                </h1>

                <p>
                    View complete information about the student.
                </p>

            </div>


            <div class="admin-badge">
                Administrator
            </div>

        </div>



        <!-- BACK BUTTON -->

        <div class="student-view-back">

            <a href="students.php">
                ← Back to Students
            </a>

        </div>



        <!-- =================================================
             STUDENT PROFILE
             ================================================= -->

        <div class="content-card student-profile-card">


            <div class="student-profile-header">


                <div class="student-avatar">

                    <?= strtoupper(
                        substr(
                            $student['full_name'],
                            0,
                            1
                        )
                    ) ?>

                </div>


                <div>

                    <h2>

                        <?= htmlspecialchars(
                            $student['full_name']
                        ) ?>

                    </h2>

                    <p>
                        Student Account
                    </p>

                </div>


            </div>



            <div class="student-details-grid">


                <!-- FULL NAME -->

                <div class="student-detail-item">

                    <span>
                        Full Name
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $student['full_name']
                        ) ?>

                    </strong>

                </div>


                <!-- EMAIL -->

                <div class="student-detail-item">

                    <span>
                        Email
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $student['email']
                        ) ?>

                    </strong>

                </div>


                <!-- ROLL NUMBER -->

                <div class="student-detail-item">

                    <span>
                        Roll Number
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $student['roll_number']
                        ) ?>

                    </strong>

                </div>


                <!-- CLASS -->

                <div class="student-detail-item">

                    <span>
                        Class
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $student['class']
                        ) ?>

                    </strong>

                </div>


                <!-- REGISTERED DATE -->

                <div class="student-detail-item">

                    <span>
                        Registered Date
                    </span>

                    <strong>

                        <?php

                        if (!empty($student['created_at'])) {

                            echo date(
                                'd M Y',
                                strtotime(
                                    $student['created_at']
                                )
                            );

                        } else {

                            echo 'N/A';

                        }

                        ?>

                    </strong>

                </div>


                <!-- STUDENT ID -->

                <div class="student-detail-item">

                    <span>
                        Student ID
                    </span>

                    <strong>

                        #<?= (int) $student['id'] ?>

                    </strong>

                </div>


            </div>

        </div>



        <!-- =================================================
             SUBMISSIONS
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">

                <div>

                    <h3>
                        Assignment Submissions
                    </h3>

                    <p>
                        Assignments submitted by this student.
                    </p>

                </div>

            </div>


            <div class="student-table-wrapper">


                <table class="student-view-table">


                    <thead>

                        <tr>

                            <th>
                                Assignment
                            </th>

                            <th>
                                Course
                            </th>

                            <th>
                                Due Date
                            </th>

                            <th>
                                Submitted
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!empty($submissions)): ?>


                        <?php foreach (
                            $submissions as $submission
                        ): ?>


                            <tr>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $submission['title']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $submission['course_name']
                                    ) ?>

                                </td>


                                <td>

                                    <?= date(
                                        'd M Y',
                                        strtotime(
                                            $submission['due_date']
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <?= date(
                                        'd M Y, h:i A',
                                        strtotime(
                                            $submission['submitted_at']
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <span class="student-status-badge">

                                        <?= htmlspecialchars(
                                            ucfirst(
                                                $submission['status']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="5"
                                class="student-empty"
                            >

                                This student has not submitted
                                any assignments yet.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>



        <!-- =================================================
             RESULTS
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">

                <div>

                    <h3>
                        Academic Results
                    </h3>

                    <p>
                        Results uploaded for this student.
                    </p>

                </div>

            </div>


            <div class="student-table-wrapper">


                <table class="student-view-table">


                    <thead>

                        <tr>

                            <th>
                                Subject
                            </th>

                            <th>
                                Mid / 25
                            </th>

                            <th>
                                Assignment / 15
                            </th>

                            <th>
                                Quiz / 10
                            </th>

                            <th>
                                Sessional / 10
                            </th>

                            <th>
                                Final / 40
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Grade
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!empty($student_results)): ?>


                        <?php foreach (
                            $student_results as $result_row
                        ): ?>


                            <tr>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $result_row['subject']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $result_row['mids_marks']
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $result_row['assignment_marks']
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $result_row['quiz_marks']
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $result_row['sessional_marks']
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $result_row['final_marks']
                                    ) ?>
                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $result_row['total_marks']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <span class="student-grade-badge">

                                        <?= htmlspecialchars(
                                            $result_row['grade']
                                        ) ?>

                                    </span>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="8"
                                class="student-empty"
                            >

                                No results have been uploaded
                                for this student yet.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>



    </main>

</div>


</body>

</html>