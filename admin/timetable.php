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
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = '';
$success = '';


/*
|--------------------------------------------------------------------------
| DELETE TIMETABLE ENTRY
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['delete']) &&
    is_numeric($_GET['delete'])
) {

    $delete_id = (int) $_GET['delete'];

    if ($delete_id > 0) {

        $delete_sql = "
            DELETE FROM timetable
            WHERE id = ?
        ";

        $delete_stmt = mysqli_prepare(
            $conn,
            $delete_sql
        );

        if ($delete_stmt) {

            mysqli_stmt_bind_param(
                $delete_stmt,
                "i",
                $delete_id
            );

            if (mysqli_stmt_execute($delete_stmt)) {

                mysqli_stmt_close($delete_stmt);

                header(
                    "Location: timetable.php?deleted=1"
                );

                exit;

            } else {

                $error =
                    "Unable to delete timetable entry.";
            }

            mysqli_stmt_close($delete_stmt);

        } else {

            $error =
                "Database Error: " .
                mysqli_error($conn);
        }
    }
}


/*
|--------------------------------------------------------------------------
| SUCCESS MESSAGE AFTER DELETE
|--------------------------------------------------------------------------
*/

if (isset($_GET['deleted'])) {

    $success =
        "Timetable entry deleted successfully.";
}


/*
|--------------------------------------------------------------------------
| ADD TIMETABLE ENTRY
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $class = trim($_POST['class'] ?? '');

    $day = trim($_POST['day'] ?? '');

    $time_slot = trim($_POST['time_slot'] ?? '');

    $subject = trim($_POST['subject'] ?? '');

    $teacher = trim($_POST['teacher'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($class === '') {

        $error = "Please select a class.";

    } elseif ($day === '') {

        $error = "Please select a day.";

    } elseif ($time_slot === '') {

        $error = "Please enter a time slot.";

    } elseif ($subject === '') {

        $error = "Please enter the subject.";

    } elseif ($teacher === '') {

        $error = "Please enter the teacher name.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | INSERT TIMETABLE
        |--------------------------------------------------------------------------
        */

        $insert_sql = "
            INSERT INTO timetable
            (
                class,
                day,
                time_slot,
                subject,
                teacher
            )
            VALUES
            (?, ?, ?, ?, ?)
        ";


        $insert_stmt = mysqli_prepare(
            $conn,
            $insert_sql
        );


        if (!$insert_stmt) {

            $error =
                "Database Error: " .
                mysqli_error($conn);

        } else {


            mysqli_stmt_bind_param(
                $insert_stmt,
                "sssss",
                $class,
                $day,
                $time_slot,
                $subject,
                $teacher
            );


            if (
                mysqli_stmt_execute(
                    $insert_stmt
                )
            ) {

                mysqli_stmt_close($insert_stmt);

                header(
                    "Location: timetable.php?added=1"
                );

                exit;

            } else {

                $error =
                    "Unable to add timetable entry: " .
                    mysqli_stmt_error($insert_stmt);

                mysqli_stmt_close($insert_stmt);
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| SUCCESS MESSAGE AFTER ADD
|--------------------------------------------------------------------------
*/

if (isset($_GET['added'])) {

    $success =
        "Timetable entry added successfully.";
}


/*
|--------------------------------------------------------------------------
| FETCH ALL TIMETABLE ENTRIES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        class,
        day,
        time_slot,
        subject,
        teacher
    FROM timetable
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
        time_slot ASC,
        class ASC
";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {

    die(
        "Database Error: " .
        mysqli_error($conn)
    );
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
        Timetable Management | Forces Academy
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Existing Admin CSS -->

    <link
        rel="stylesheet"
        href="admin.css"
    >

</head>


<body>


<div class="admin-layout">


    <!-- =====================================================
         ADMIN SIDEBAR
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



            <a
                href="timetable.php"
                class="active"
            >

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
                    Timetable Management
                </h1>

                <p>
                    Add and manage class timetable entries.
                </p>

            </div>


            <div class="admin-badge">
                Administrator
            </div>


        </div>



        <!-- =================================================
             SUCCESS MESSAGE
             ================================================= -->

        <?php if (!empty($success)): ?>

            <div class="result-success">

                <?= htmlspecialchars($success) ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ERROR MESSAGE
             ================================================= -->

        <?php if (!empty($error)): ?>

            <div class="result-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ADD TIMETABLE FORM
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">


                <div>

                    <h3>
                        Add Timetable Entry
                    </h3>

                    <p>
                        Create a new class timetable entry.
                    </p>

                </div>


            </div>



            <form
                method="POST"
                class="timetable-form"
            >


                <!-- CLASS -->

                <div class="form-field">

                    <label for="class">
                        Class
                    </label>


                    <select
    name="class"
    id="class"
    required
>

    <option value="">
        Select Program / Class
    </option>

    <option value="BS-IT">
        BS-IT
    </option>

    <option value="BS-CS">
        BS-CS
    </option>

    <option value="BS-SE">
        BS-SE
    </option>

    <option value="BS-AI">
        BS-AI
    </option>

    <option value="BS-DS">
        BS-DS
    </option>

    <option value="BBA">
        BBA
    </option>

    <option value="MBA">
        MBA
    </option>

    <option value="MCS">
        MCS
    </option>

    <option value="MIT">
        MIT
    </option>

</select>
                        

                </div>



                <!-- DAY -->

                <div class="form-field">

                    <label for="day">
                        Day
                    </label>


                    <select
                        name="day"
                        id="day"
                        required
                    >

                        <option value="">
                            Select Day
                        </option>

                        <option value="Monday">
                            Monday
                        </option>

                        <option value="Tuesday">
                            Tuesday
                        </option>

                        <option value="Wednesday">
                            Wednesday
                        </option>

                        <option value="Thursday">
                            Thursday
                        </option>

                        <option value="Friday">
                            Friday
                        </option>

                        <option value="Saturday">
                            Saturday
                        </option>

                        <option value="Sunday">
                            Sunday
                        </option>

                    </select>

                </div>



                <!-- TIME SLOT -->

                <div class="form-field">

                    <label for="time_slot">
                        Time Slot
                    </label>


                    <input
                        type="text"
                        name="time_slot"
                        id="time_slot"
                        placeholder="e.g. 08:00 - 09:00"
                        required
                    >

                </div>



                <!-- SUBJECT -->

                <div class="form-field">

                    <label for="subject">
                        Subject
                    </label>


                    <input
                        type="text"
                        name="subject"
                        id="subject"
                        placeholder="Enter subject name"
                        required
                    >

                </div>



                <!-- TEACHER -->

                <div class="form-field">

                    <label for="teacher">
                        Teacher
                    </label>


                    <input
                        type="text"
                        name="teacher"
                        id="teacher"
                        placeholder="Enter teacher name"
                        required
                    >

                </div>



                <!-- BUTTON -->

                <div class="timetable-submit-area">

                    <button
                        type="submit"
                        class="add-result-submit"
                    >

                        + Add Timetable Entry

                    </button>

                </div>


            </form>


        </div>



        <!-- =================================================
             EXISTING TIMETABLE
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">


                <div>

                    <h3>
                        All Timetable Entries
                    </h3>

                    <p>
                        View and manage existing timetable entries.
                    </p>

                </div>


            </div>



            <div class="table-wrapper">


                <table class="results-table">


                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Class
                            </th>

                            <th>
                                Day
                            </th>

                            <th>
                                Time Slot
                            </th>

                            <th>
                                Subject
                            </th>

                            <th>
                                Teacher
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>



                    <tbody>


                    <?php

                    $count = 1;

                    if (
                        mysqli_num_rows($result) > 0
                    ):

                    ?>


                        <?php while (
                            $row =
                            mysqli_fetch_assoc($result)
                        ): ?>


                            <tr>


                                <!-- NUMBER -->

                                <td>

                                    <?= $count++; ?>

                                </td>



                                <!-- CLASS -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $row['class']
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- DAY -->

                                <td>

                                    <?= htmlspecialchars(
                                        $row['day']
                                    ); ?>

                                </td>



                                <!-- TIME -->

                                <td>

                                    <?= htmlspecialchars(
                                        $row['time_slot']
                                    ); ?>

                                </td>



                                <!-- SUBJECT -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $row['subject']
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- TEACHER -->

                                <td>

                                    <?= htmlspecialchars(
                                        $row['teacher']
                                    ); ?>

                                </td>



                                <!-- DELETE -->

                                <td>

                                    <a
                                        href="timetable.php?delete=<?= (int) $row['id']; ?>"
                                        class="delete-btn"
                                        onclick="return confirm('Are you sure you want to delete this timetable entry?');"
                                    >

                                        Delete

                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="7"
                                class="text-center"
                            >

                                <div class="empty-results">


                                    <div class="empty-results-icon">
                                        📅
                                    </div>


                                    <h3>
                                        No Timetable Entries
                                    </h3>


                                    <p>
                                        No timetable entries have been added yet.
                                    </p>


                                </div>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </div>



        <!-- =================================================
             INFORMATION
             ================================================= -->

        <div class="content-card admin-info">


            <div class="info-icon">
                ℹ
            </div>


            <div>

                <h3>
                    Timetable Information
                </h3>


                <p>

                    Add timetable entries for each class.
                    Students will only see timetable entries
                    belonging to their own class.

                </p>

            </div>


        </div>


    </main>


</div>


</body>

</html>