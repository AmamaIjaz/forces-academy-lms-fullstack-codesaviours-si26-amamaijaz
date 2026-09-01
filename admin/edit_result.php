<?php

session_start();

require_once '../config/db.php';


/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
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
| GET RESULT ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: results.php");
    exit;
}

$result_id = (int) $_GET['id'];

if ($result_id <= 0) {
    header("Location: results.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| FETCH EXISTING RESULT
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        student_id,
        course_id,
        subject,
        mids_marks,
        assignment_marks,
        quiz_marks,
        sessional_marks,
        final_marks,
        total_marks,
        grade
    FROM results
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $result_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$result_data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| RESULT NOT FOUND
|--------------------------------------------------------------------------
*/

if (!$result_data) {
    header("Location: results.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| EXISTING VALUES
|--------------------------------------------------------------------------
*/

$student_id = (int) $result_data['student_id'];

$course_id = (int) $result_data['course_id'];

$subject = $result_data['subject'];

$mids_marks = (float) $result_data['mids_marks'];

$assignment_marks = (float) $result_data['assignment_marks'];

$quiz_marks = (float) $result_data['quiz_marks'];

$sessional_marks = (float) $result_data['sessional_marks'];

$final_marks = (float) $result_data['final_marks'];

$grade = $result_data['grade'];

$error = '';


/*
|--------------------------------------------------------------------------
| UPDATE RESULT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | GET FORM DATA
    |--------------------------------------------------------------------------
    */

    $student_id = (int) ($_POST['student_id'] ?? 0);

    $course_id = (int) ($_POST['course_id'] ?? 0);

    $subject = trim($_POST['subject'] ?? '');

    $mids_marks = (float) ($_POST['mids_marks'] ?? 0);

    $assignment_marks = (float) ($_POST['assignment_marks'] ?? 0);

    $quiz_marks = (float) ($_POST['quiz_marks'] ?? 0);

    $sessional_marks = (float) ($_POST['sessional_marks'] ?? 0);

    $final_marks = (float) ($_POST['final_marks'] ?? 0);

    $grade = trim($_POST['grade'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($student_id <= 0) {

        $error = "Please select a student.";

    } elseif ($course_id <= 0) {

        $error = "Please select a course.";

    } elseif ($subject === '') {

        $error = "Please enter the subject.";

    } elseif ($mids_marks < 0 || $mids_marks > 25) {

        $error = "Mid marks must be between 0 and 25.";

    } elseif ($assignment_marks < 0 || $assignment_marks > 15) {

        $error = "Assignment marks must be between 0 and 15.";

    } elseif ($quiz_marks < 0 || $quiz_marks > 10) {

        $error = "Quiz marks must be between 0 and 10.";

    } elseif ($sessional_marks < 0 || $sessional_marks > 10) {

        $error = "Sessional marks must be between 0 and 10.";

    } elseif ($final_marks < 0 || $final_marks > 40) {

        $error = "Final marks must be between 0 and 40.";

    } elseif ($grade === '') {

        $error = "Please select a grade.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | CALCULATE TOTAL
        |--------------------------------------------------------------------------
        */

        $total_marks =
            $mids_marks +
            $assignment_marks +
            $quiz_marks +
            $sessional_marks +
            $final_marks;


        /*
        |--------------------------------------------------------------------------
        | UPDATE DATABASE
        |--------------------------------------------------------------------------
        */

        $update_sql = "
            UPDATE results
            SET
                student_id = ?,
                course_id = ?,
                subject = ?,
                mids_marks = ?,
                assignment_marks = ?,
                quiz_marks = ?,
                sessional_marks = ?,
                final_marks = ?,
                total_marks = ?,
                grade = ?
            WHERE id = ?
        ";


        $update_stmt = mysqli_prepare(
            $conn,
            $update_sql
        );


        if (!$update_stmt) {

            $error = "Database Error: " . mysqli_error($conn);

        } else {


            /*
            |--------------------------------------------------------------------------
            | BIND PARAMETERS
            |--------------------------------------------------------------------------
            */

            mysqli_stmt_bind_param(
                $update_stmt,
                "iisddddddsi",
                $student_id,
                $course_id,
                $subject,
                $mids_marks,
                $assignment_marks,
                $quiz_marks,
                $sessional_marks,
                $final_marks,
                $total_marks,
                $grade,
                $result_id
            );


            /*
            |--------------------------------------------------------------------------
            | EXECUTE UPDATE
            |--------------------------------------------------------------------------
            */

            if (mysqli_stmt_execute($update_stmt)) {

                mysqli_stmt_close($update_stmt);

                header(
                    "Location: results.php?updated=1"
                );

                exit;

            } else {

                $error =
                    "Unable to update result: " .
                    mysqli_stmt_error($update_stmt);

                mysqli_stmt_close($update_stmt);
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| FETCH STUDENTS
|--------------------------------------------------------------------------
*/

$students_sql = "
    SELECT
        id,
        full_name,
        roll_number
    FROM students
    ORDER BY full_name ASC
";

$students_result = mysqli_query(
    $conn,
    $students_sql
);

if (!$students_result) {
    die("Database Error: " . mysqli_error($conn));
}


/*
|--------------------------------------------------------------------------
| FETCH COURSES
|--------------------------------------------------------------------------
*/

$courses_sql = "
    SELECT
        id,
        course_name
    FROM courses
    ORDER BY course_name ASC
";

$courses_result = mysqli_query(
    $conn,
    $courses_sql
);

if (!$courses_result) {
    die("Database Error: " . mysqli_error($conn));
}


/*
|--------------------------------------------------------------------------
| CURRENT TOTAL
|--------------------------------------------------------------------------
*/

$current_total =
    $mids_marks +
    $assignment_marks +
    $quiz_marks +
    $sessional_marks +
    $final_marks;

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
        Edit Result | Forces Academy
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



            <a href="students.php">

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



            <a
                href="results.php"
                class="active"
            >

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
                    Edit Student Result
                </h1>

                <p>
                    Update student marks and grade.
                </p>

            </div>


            <div class="admin-badge">
                Administrator
            </div>


        </div>



        <!-- BACK LINK -->

        <div class="result-back">

            <a href="results.php">
                ← Back to Results
            </a>

        </div>



        <!-- ERROR MESSAGE -->

        <?php if (!empty($error)): ?>

            <div class="result-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             EDIT RESULT CARD
             ================================================= -->

        <div class="content-card add-result-card">


            <div class="section-heading">

                <div>

                    <h3>
                        Edit Result
                    </h3>

                    <p>
                        Modify the student's academic result.
                    </p>

                </div>

            </div>



            <form
                method="POST"
                class="add-result-form"
            >


                <!-- STUDENT -->

                <div class="form-field">

                    <label for="student_id">
                        Student
                    </label>


                    <select
                        id="student_id"
                        name="student_id"
                        required
                    >

                        <option value="">
                            Select Student
                        </option>


                        <?php while (
                            $student =
                            mysqli_fetch_assoc(
                                $students_result
                            )
                        ): ?>

                            <option
                                value="<?= (int) $student['id'] ?>"
                                <?= (
                                    (int) $student['id']
                                    === $student_id
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $student['full_name']
                                ) ?>

                                <?php if (
                                    !empty(
                                        $student['roll_number']
                                    )
                                ): ?>

                                    -
                                    <?= htmlspecialchars(
                                        $student['roll_number']
                                    ) ?>

                                <?php endif; ?>

                            </option>

                        <?php endwhile; ?>


                    </select>

                </div>



                <!-- COURSE -->

                <div class="form-field">

                    <label for="course_id">
                        Course
                    </label>


                    <select
                        id="course_id"
                        name="course_id"
                        required
                    >

                        <option value="">
                            Select Course
                        </option>


                        <?php while (
                            $course =
                            mysqli_fetch_assoc(
                                $courses_result
                            )
                        ): ?>

                            <option
                                value="<?= (int) $course['id'] ?>"
                                <?= (
                                    (int) $course['id']
                                    === $course_id
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $course['course_name']
                                ) ?>

                            </option>

                        <?php endwhile; ?>


                    </select>

                </div>



                <!-- SUBJECT -->

                <div class="form-field">

                    <label for="subject">
                        Subject
                    </label>


                    <input
                        type="text"
                        id="subject"
                        name="subject"
                        value="<?= htmlspecialchars($subject) ?>"
                        placeholder="Enter subject name"
                        required
                    >

                </div>



                <!-- MARKS HEADING -->

                <div class="marks-title">

                    Marks Distribution

                </div>



                <!-- MARKS GRID -->

                <div class="marks-form-grid">


                    <!-- MID -->

                    <div class="form-field">

                        <label for="mids_marks">

                            Mid Marks
                            <span>(25)</span>

                        </label>


                        <input
                            type="number"
                            id="mids_marks"
                            name="mids_marks"
                            min="0"
                            max="25"
                            step="0.01"
                            value="<?= htmlspecialchars($mids_marks) ?>"
                            required
                        >

                    </div>



                    <!-- ASSIGNMENT -->

                    <div class="form-field">

                        <label for="assignment_marks">

                            Assignment Marks
                            <span>(15)</span>

                        </label>


                        <input
                            type="number"
                            id="assignment_marks"
                            name="assignment_marks"
                            min="0"
                            max="15"
                            step="0.01"
                            value="<?= htmlspecialchars($assignment_marks) ?>"
                            required
                        >

                    </div>



                    <!-- QUIZ -->

                    <div class="form-field">

                        <label for="quiz_marks">

                            Quiz Marks
                            <span>(10)</span>

                        </label>


                        <input
                            type="number"
                            id="quiz_marks"
                            name="quiz_marks"
                            min="0"
                            max="10"
                            step="0.01"
                            value="<?= htmlspecialchars($quiz_marks) ?>"
                            required
                        >

                    </div>



                    <!-- SESSIONAL -->

                    <div class="form-field">

                        <label for="sessional_marks">

                            Sessional Marks
                            <span>(10)</span>

                        </label>


                        <input
                            type="number"
                            id="sessional_marks"
                            name="sessional_marks"
                            min="0"
                            max="10"
                            step="0.01"
                            value="<?= htmlspecialchars($sessional_marks) ?>"
                            required
                        >

                    </div>



                    <!-- FINAL -->

                    <div class="form-field">

                        <label for="final_marks">

                            Final Marks
                            <span>(40)</span>

                        </label>


                        <input
                            type="number"
                            id="final_marks"
                            name="final_marks"
                            min="0"
                            max="40"
                            step="0.01"
                            value="<?= htmlspecialchars($final_marks) ?>"
                            required
                        >

                    </div>



                    <!-- GRADE -->

                    <div class="form-field">

                        <label for="grade">
                            Grade
                        </label>


                        <select
                            id="grade"
                            name="grade"
                            required
                        >

                            <option value="">
                                Select Grade
                            </option>


                            <?php
                            $grades = [
                                'A+',
                                'A',
                                'B+',
                                'B',
                                'C+',
                                'C',
                                'D',
                                'F'
                            ];
                            ?>


                            <?php foreach (
                                $grades as $grade_option
                            ): ?>

                                <option
                                    value="<?= htmlspecialchars($grade_option) ?>"
                                    <?= (
                                        $grade === $grade_option
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $grade_option
                                    ) ?>

                                </option>

                            <?php endforeach; ?>


                        </select>

                    </div>


                </div>



                <!-- TOTAL -->

                <div class="result-total-box">


                    <div>

                        <span>
                            Total Marks
                        </span>


                        <strong id="totalMarks">

                            <?= number_format(
                                $current_total,
                                2
                            ) ?>

                            / 100

                        </strong>

                    </div>


                </div>



                <!-- BUTTONS -->

                <div class="add-result-actions">


                    <button
                        type="submit"
                        class="add-result-submit"
                    >

                        Update Result

                    </button>


                    <a
                        href="results.php"
                        class="add-result-cancel"
                    >

                        Cancel

                    </a>


                </div>


            </form>


        </div>


    </main>


</div>



<!-- =====================================================
     JAVASCRIPT
     AUTOMATIC TOTAL CALCULATION
     ===================================================== -->

<script>

function calculateTotal() {

    const mid =
        parseFloat(
            document.getElementById('mids_marks').value
        ) || 0;


    const assignment =
        parseFloat(
            document.getElementById('assignment_marks').value
        ) || 0;


    const quiz =
        parseFloat(
            document.getElementById('quiz_marks').value
        ) || 0;


    const sessional =
        parseFloat(
            document.getElementById('sessional_marks').value
        ) || 0;


    const finalMarks =
        parseFloat(
            document.getElementById('final_marks').value
        ) || 0;


    const total =
        mid +
        assignment +
        quiz +
        sessional +
        finalMarks;


    document.getElementById(
        'totalMarks'
    ).textContent =
        total.toFixed(2) + ' / 100';

}


/*
|--------------------------------------------------------------------------
| Add Event Listeners
|--------------------------------------------------------------------------
*/

document
    .getElementById('mids_marks')
    .addEventListener(
        'input',
        calculateTotal
    );


document
    .getElementById('assignment_marks')
    .addEventListener(
        'input',
        calculateTotal
    );


document
    .getElementById('quiz_marks')
    .addEventListener(
        'input',
        calculateTotal
    );


document
    .getElementById('sessional_marks')
    .addEventListener(
        'input',
        calculateTotal
    );


document
    .getElementById('final_marks')
    .addEventListener(
        'input',
        calculateTotal
    );


/*
|--------------------------------------------------------------------------
| Calculate On Page Load
|--------------------------------------------------------------------------
*/

calculateTotal();

</script>


</body>

</html>