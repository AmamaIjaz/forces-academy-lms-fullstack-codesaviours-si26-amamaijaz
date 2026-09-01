<?php

session_start();

require_once 'config/db.php';


/*
|--------------------------------------------------------------------------
| STUDENT AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET LOGGED-IN STUDENT
|--------------------------------------------------------------------------
*/

$student_id = (int) $_SESSION['student_id'];

$student_sql = "
    SELECT
        id,
        full_name,
        class
    FROM students
    WHERE id = ?
";

$student_stmt = mysqli_prepare($conn, $student_sql);

if (!$student_stmt) {
    die("Database Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $student_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($student_stmt);

$student_result = mysqli_stmt_get_result($student_stmt);

$student = mysqli_fetch_assoc($student_result);

mysqli_stmt_close($student_stmt);


if (!$student) {
    session_destroy();
    header("Location: login.php");
    exit;
}


$student_class = $student['class'];


/*
|--------------------------------------------------------------------------
| DAYS OF THE WEEK
|--------------------------------------------------------------------------
*/

$days = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday'
];


/*
|--------------------------------------------------------------------------
| FETCH TIMETABLE FOR STUDENT'S CLASS
|--------------------------------------------------------------------------
*/

$timetable_sql = "
    SELECT
        id,
        class,
        day,
        time_slot,
        subject,
        teacher
    FROM timetable
    WHERE class = ?
    ORDER BY
        FIELD(
            day,
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ),
        time_slot ASC
";

$timetable_stmt = mysqli_prepare(
    $conn,
    $timetable_sql
);

if (!$timetable_stmt) {
    die("Database Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $timetable_stmt,
    "s",
    $student_class
);

mysqli_stmt_execute($timetable_stmt);

$timetable_result = mysqli_stmt_get_result(
    $timetable_stmt
);


/*
|--------------------------------------------------------------------------
| ORGANIZE TIMETABLE
|--------------------------------------------------------------------------
|
| $timetable[day][time_slot]
|
*/

$timetable = [];

$time_slots = [];


while ($row = mysqli_fetch_assoc($timetable_result)) {

    $day = $row['day'];

    $time_slot = $row['time_slot'];

    $timetable[$day][$time_slot] = $row;

    if (!in_array($time_slot, $time_slots, true)) {
        $time_slots[] = $time_slot;
    }
}


mysqli_stmt_close($timetable_stmt);

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
        Timetable | Forces Academy
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Student CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body>


<div class="student-layout">


    <!-- =====================================================
         STUDENT SIDEBAR
         ===================================================== -->

    <aside class="student-sidebar">


        <div class="sidebar-brand">

            <div class="brand-icon">
                ⚙
            </div>


            <div>

                <h3>
                    Forces Academy
                </h3>

                <span>
                    Student Panel
                </span>

            </div>

        </div>



        <nav class="student-nav">


            <a href="dashboard.php">

                <span>🏠</span>

                <span>
                    Dashboard
                </span>

            </a>



            <a href="courses.php">

                <span>📚</span>

                <span>
                    My Courses
                </span>

            </a>



            <a href="assignments.php">

                <span>📝</span>

                <span>
                    Assignments
                </span>

            </a>



            <a href="results.php">

                <span>📊</span>

                <span>
                    My Results
                </span>

            </a>



            <a href="notices.php">

                <span>📢</span>

                <span>
                    Notices
                </span>

            </a>



            <a
                href="timetable.php"
                class="active"
            >

                <span>📅</span>

                <span>
                    Timetable
                </span>

            </a>



            <a href="profile.php">

                <span>👤</span>

                <span>
                    My Profile
                </span>

            </a>
</a>
 <a href="fees.php" >
                My Fees
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

    <main class="student-content">


        <!-- TOP HEADER -->

        <div class="top-header">


            <div>

                <h1>
                    My Timetable
                </h1>

                <p>
                    Weekly class schedule for
                    <strong>
                        <?= htmlspecialchars($student_class); ?>
                    </strong>
                </p>

            </div>


            <div class="student-badge">

                <?= htmlspecialchars($student['full_name']); ?>

            </div>


        </div>



        <!-- =================================================
             TIMETABLE CARD
             ================================================= -->

        <div class="content-card student-timetable-card">


            <div class="section-heading">


                <div>

                    <h3>
                        Weekly Timetable
                    </h3>

                    <p>
                        Your classes are shown according to your
                        registered program/class.
                    </p>

                </div>


                <div class="class-badge">

                    <?= htmlspecialchars($student_class); ?>

                </div>


            </div>



            <?php if (empty($time_slots)): ?>


                <!-- EMPTY STATE -->

                <div class="student-timetable-empty">


                    <div class="empty-timetable-icon">
                        📅
                    </div>


                    <h3>
                        No Timetable Available
                    </h3>


                    <p>
                        No timetable has been added for your class yet.
                    </p>


                </div>


            <?php else: ?>


                <!-- =================================================
                     WEEKLY GRID
                     ================================================= -->

                <div class="student-timetable-wrapper">


                    <table class="student-timetable">


                        <thead>

                            <tr>

                                <th class="time-header">
                                    Time
                                </th>


                                <?php foreach ($days as $day): ?>

                                    <th>
                                        <?= htmlspecialchars($day); ?>
                                    </th>

                                <?php endforeach; ?>


                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($time_slots as $time_slot): ?>


                            <tr>


                                <!-- TIME -->

                                <td class="time-slot-cell">

                                    <?= htmlspecialchars($time_slot); ?>

                                </td>



                                <!-- DAYS -->

                                <?php foreach ($days as $day): ?>


                                    <td class="timetable-class-cell">


                                        <?php

                                        if (
                                            isset(
                                                $timetable[$day][$time_slot]
                                            )
                                        ):

                                            $entry =
                                                $timetable[$day][$time_slot];

                                        ?>


                                            <div class="class-entry">


                                                <div class="class-subject">

                                                    <?= htmlspecialchars(
                                                        $entry['subject']
                                                    ); ?>

                                                </div>


                                                <div class="class-teacher">

                                                    👨‍🏫

                                                    <?= htmlspecialchars(
                                                        $entry['teacher']
                                                    ); ?>

                                                </div>


                                            </div>


                                        <?php else: ?>


                                            <span class="no-class">
                                                —
                                            </span>


                                        <?php endif; ?>


                                    </td>


                                <?php endforeach; ?>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            <?php endif; ?>


        </div>



        <!-- =================================================
             INFORMATION CARD
             ================================================= -->

        <div class="content-card timetable-note">


            <div class="info-icon">
                ℹ
            </div>


            <div>

                <h3>
                    Timetable Information
                </h3>


                <p>

                    This timetable is automatically displayed
                    according to your registered class/program.
                    If you believe your timetable is incorrect,
                    please contact the administration.

                </p>

            </div>


        </div>


    </main>


</div>


</body>

</html>