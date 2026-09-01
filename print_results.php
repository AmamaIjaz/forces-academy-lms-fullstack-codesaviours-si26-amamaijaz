<?php

session_start();

require_once 'config/db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

/*
|--------------------------------------------------------------------------
| Get Student Information
|--------------------------------------------------------------------------
*/

$student_stmt = mysqli_prepare(
    $conn,
    "SELECT full_name, email, roll_number, class
     FROM students
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $student_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($student_stmt);

$student_result = mysqli_stmt_get_result($student_stmt);

$student = mysqli_fetch_assoc($student_result);

mysqli_stmt_close($student_stmt);


/*
|--------------------------------------------------------------------------
| Get Results
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        results.subject,
        results.mids_marks,
        results.assignment_marks,
        results.quiz_marks,
        results.sessional_marks,
        results.final_marks,
        results.total_marks,
        results.grade
     FROM results
     WHERE results.student_id = ?
     ORDER BY results.subject ASC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$results = [];

while ($row = mysqli_fetch_assoc($result)) {
    $results[] = $row;
}

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| Calculate Overall Information
|--------------------------------------------------------------------------
*/

$total_marks = 0;
$total_subjects = count($results);

foreach ($results as $row) {
    $total_marks += (float) $row['total_marks'];
}

$average = $total_subjects > 0
    ? $total_marks / $total_subjects
    : 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Print Results | Forces Academy</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<div class="print-page">

    <!-- =========================================
         HEADER
    ========================================== -->

    <div class="print-header">

        <div class="academy-name">

            <h1>Forces Academy</h1>

            <p>
                Student Academic Results
            </p>

        </div>

        <div class="print-date">

            Date:
            <?= date('d M Y') ?>

        </div>

    </div>


    <!-- =========================================
         STUDENT INFORMATION
    ========================================== -->

    <div class="student-information">

        <div class="student-info-item">

            <span>Name</span>

            <strong>
                <?= htmlspecialchars($student['full_name']) ?>
            </strong>

        </div>


        <div class="student-info-item">

            <span>Email</span>

            <strong>
                <?= htmlspecialchars($student['email']) ?>
            </strong>

        </div>


        <div class="student-info-item">

            <span>Roll Number</span>

            <strong>
                <?= htmlspecialchars($student['roll_number']) ?>
            </strong>

        </div>


        <div class="student-info-item">

            <span>Class</span>

            <strong>
                <?= htmlspecialchars($student['class']) ?>
            </strong>

        </div>

    </div>


    <!-- =========================================
         RESULTS HEADING
    ========================================== -->

    <div class="results-heading">

        <h2>
            Academic Results
        </h2>

    </div>


    <!-- =========================================
         RESULTS TABLE
    ========================================== -->

    <?php if (empty($results)): ?>

        <div class="no-results">

            No results available.

        </div>

    <?php else: ?>

        <div class="table-container">

            <table class="results-table">

                <thead>

                    <tr>

                        <th>Subject</th>

                        <th>
                            Mids<br>
                            <span>(25)</span>
                        </th>

                        <th>
                            Assignment<br>
                            <span>(15)</span>
                        </th>

                        <th>
                            Quiz<br>
                            <span>(10)</span>
                        </th>

                        <th>
                            Sessional<br>
                            <span>(10)</span>
                        </th>

                        <th>
                            Final<br>
                            <span>(40)</span>
                        </th>

                        <th>
                            Total<br>
                            <span>(100)</span>
                        </th>

                        <th>Grade</th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($results as $row): ?>

                    <tr>

                        <td class="subject-cell">

                            <?= htmlspecialchars(
                                $row['subject']
                            ) ?>

                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $row['mids_marks']
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $row['assignment_marks']
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $row['quiz_marks']
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $row['sessional_marks']
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $row['final_marks']
                            ) ?>
                        </td>


                        <td class="total-cell">

                            <?= htmlspecialchars(
                                $row['total_marks']
                            ) ?>

                        </td>


                        <td class="grade-cell">

                            <?= htmlspecialchars(
                                $row['grade']
                            ) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>


        <!-- =========================================
             RESULT SUMMARY
        ========================================== -->

        <div class="result-summary">

            <div class="summary-box">

                <span>
                    Total Subjects
                </span>

                <strong>
                    <?= $total_subjects ?>
                </strong>

            </div>


            <div class="summary-box">

                <span>
                    Total Marks
                </span>

                <strong>
                    <?= number_format($total_marks, 2) ?>
                </strong>

            </div>


            <div class="summary-box">

                <span>
                    Average
                </span>

                <strong>
                    <?= number_format($average, 2) ?>%
                </strong>

            </div>

        </div>

    <?php endif; ?>


    <!-- =========================================
         FOOTER
    ========================================== -->

    <div class="print-footer">

        <p>
            This is a computer-generated result.
        </p>
<div class="print-actions">
    <button type="button" class="back-button" onclick="history.back()">
        ← Back
    </button>

    <button type="button" class="print-button" onclick="window.print()">
        🖨 Print Results
    </button>
</div>
    </div>

</div>

</body>

</html>