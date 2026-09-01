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


$message = '';
$error = '';

$editMode = false;

$editAssignment = [
    'id' => '',
    'title' => '',
    'description' => '',
    'course_id' => '',
    'due_date' => ''
];


/*
|--------------------------------------------------------------------------
| Add Assignment
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_assignment'])
) {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $course_id = (int) ($_POST['course_id'] ?? 0);
    $due_date = $_POST['due_date'] ?? '';


    if (
        empty($title) ||
        empty($description) ||
        $course_id <= 0 ||
        empty($due_date)
    ) {

        $error = "All assignment fields are required.";

    } else {

        $sql = "INSERT INTO assignments
                (title, description, course_id, due_date)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssis",
                $title,
                $description,
                $course_id,
                $due_date
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: assignments.php?added=1");
                exit;

            } else {

                $error = "Unable to add assignment.";

                mysqli_stmt_close($stmt);
            }

        } else {

            $error = "Database error. Please try again.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| Update Assignment
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_assignment'])
) {

    $assignment_id = (int) ($_POST['assignment_id'] ?? 0);

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $course_id = (int) ($_POST['course_id'] ?? 0);
    $due_date = $_POST['due_date'] ?? '';


    if ($assignment_id <= 0) {

        $error = "Invalid assignment.";

    } elseif (
        empty($title) ||
        empty($description) ||
        $course_id <= 0 ||
        empty($due_date)
    ) {

        $error = "All assignment fields are required.";

    } else {

        $sql = "UPDATE assignments
                SET title = ?,
                    description = ?,
                    course_id = ?,
                    due_date = ?
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssisi",
                $title,
                $description,
                $course_id,
                $due_date,
                $assignment_id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: assignments.php?updated=1");
                exit;

            } else {

                $error = "Unable to update assignment.";

                mysqli_stmt_close($stmt);
            }

        } else {

            $error = "Database error. Please try again.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| Delete Assignment
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $assignment_id = (int) $_GET['delete'];


    if ($assignment_id > 0) {

        $sql = "DELETE FROM assignments
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $assignment_id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);
        }
    }


    header("Location: assignments.php?deleted=1");
    exit;
}


/*
|--------------------------------------------------------------------------
| Edit Assignment
|--------------------------------------------------------------------------
*/

if (isset($_GET['edit'])) {

    $assignment_id = (int) $_GET['edit'];


    if ($assignment_id > 0) {

        $sql = "SELECT
                    id,
                    title,
                    description,
                    course_id,
                    due_date
                FROM assignments
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $assignment_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $assignment = mysqli_fetch_assoc($result);


            if ($assignment) {

                $editMode = true;

                $editAssignment = $assignment;
            }


            mysqli_stmt_close($stmt);
        }
    }
}


/*
|--------------------------------------------------------------------------
| Success Messages
|--------------------------------------------------------------------------
*/

if (isset($_GET['added'])) {

    $message = "Assignment added successfully.";
}


if (isset($_GET['updated'])) {

    $message = "Assignment updated successfully.";
}


if (isset($_GET['deleted'])) {

    $message = "Assignment deleted successfully.";
}


/*
|--------------------------------------------------------------------------
| Get Courses
|--------------------------------------------------------------------------
*/

$courses_sql = "SELECT
                    id,
                    course_name
                FROM courses
                ORDER BY course_name ASC";

$courses_result = mysqli_query(
    $conn,
    $courses_sql
);


/*
|--------------------------------------------------------------------------
| Get Assignments
|--------------------------------------------------------------------------
*/

$assignments_sql = "SELECT
                        assignments.id,
                        assignments.title,
                        assignments.description,
                        assignments.course_id,
                        assignments.due_date,
                        assignments.created_at,
                        courses.course_name
                    FROM assignments
                    INNER JOIN courses
                        ON assignments.course_id = courses.id
                    ORDER BY assignments.id DESC";

$assignments_result = mysqli_query(
    $conn,
    $assignments_sql
);

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
        Manage Assignments | Forces Academy
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


            <a
                href="assignments.php"
                class="active"
            >

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
                    Manage Assignments
                </h1>

                <p>
                    Create and manage assignments for students.
                </p>

            </div>


            <div class="admin-badge">

                Administrator

            </div>


        </div>



        <!-- =================================================
             MESSAGES
             ================================================= -->

        <?php if (!empty($message)): ?>

            <div class="assignment-success">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <?php if (!empty($error)): ?>

            <div class="assignment-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ADD / EDIT ASSIGNMENT
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">


                <div>

                    <h3>

                        <?= $editMode
                            ? 'Edit Assignment'
                            : 'Add New Assignment'
                        ?>

                    </h3>


                    <p>

                        <?= $editMode
                            ? 'Update assignment information below.'
                            : 'Create a new assignment for a course.'
                        ?>

                    </p>

                </div>


            </div>



            <form
                method="POST"
                class="assignment-form"
            >


                <?php if ($editMode): ?>

                    <input
                        type="hidden"
                        name="assignment_id"
                        value="<?= (int) $editAssignment['id'] ?>"
                    >

                <?php endif; ?>



                <!-- TITLE -->

                <div class="assignment-form-group">

                    <label for="title">
                        Assignment Title
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars(
                            $editAssignment['title']
                        ) ?>"
                        placeholder="Enter assignment title"
                        required
                    >

                </div>



                <!-- DESCRIPTION -->

                <div class="assignment-form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Enter assignment description"
                        required
                    ><?= htmlspecialchars(
                        $editAssignment['description']
                    ) ?></textarea>

                </div>



                <!-- COURSE -->

                <div class="assignment-form-group">

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


                        <?php if (
                            $courses_result &&
                            mysqli_num_rows($courses_result) > 0
                        ): ?>


                            <?php while (
                                $course = mysqli_fetch_assoc(
                                    $courses_result
                                )
                            ): ?>


                                <option
                                    value="<?= (int) $course['id'] ?>"
                                    <?= (
                                        (int) $editAssignment['course_id']
                                        === (int) $course['id']
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


                        <?php endif; ?>


                    </select>

                </div>



                <!-- DUE DATE -->

                <div class="assignment-form-group">

                    <label for="due_date">
                        Due Date
                    </label>

                    <input
                        type="date"
                        id="due_date"
                        name="due_date"
                        value="<?= htmlspecialchars(
                            $editAssignment['due_date']
                        ) ?>"
                        required
                    >

                </div>



                <!-- BUTTONS -->

                <div class="assignment-form-actions">


                    <?php if ($editMode): ?>


                        <button
                            type="submit"
                            name="update_assignment"
                            class="assignment-submit-btn"
                        >

                            Update Assignment

                        </button>


                        <a
                            href="assignments.php"
                            class="assignment-cancel-btn"
                        >

                            Cancel

                        </a>


                    <?php else: ?>


                        <button
                            type="submit"
                            name="add_assignment"
                            class="assignment-submit-btn"
                        >

                            Add Assignment

                        </button>


                    <?php endif; ?>


                </div>


            </form>


        </div>



        <!-- =================================================
             ASSIGNMENT LIST
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">


                <div>

                    <h3>
                        All Assignments
                    </h3>

                    <p>
                        View and manage assignments created by the administrator.
                    </p>

                </div>


            </div>



            <div class="assignment-table-wrapper">


                <table class="assignment-table">


                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Title
                            </th>

                            <th>
                                Description
                            </th>

                            <th>
                                Course
                            </th>

                            <th>
                                Due Date
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $assignments_result &&
                        mysqli_num_rows($assignments_result) > 0
                    ): ?>


                        <?php $counter = 1; ?>


                        <?php while (
                            $assignment = mysqli_fetch_assoc(
                                $assignments_result
                            )
                        ): ?>


                            <tr>


                                <td>

                                    <?= $counter++ ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $assignment['title']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <div class="assignment-description">

                                        <?= htmlspecialchars(
                                            $assignment['description']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <span class="course-badge">

                                        <?= htmlspecialchars(
                                            $assignment['course_name']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?= date(
                                        'd M Y',
                                        strtotime(
                                            $assignment['due_date']
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <div class="assignment-actions">


                                        <a
                                            href="assignments.php?edit=<?= (int) $assignment['id'] ?>"
                                            class="edit-assignment-btn"
                                        >

                                            Edit

                                        </a>


                                        <a
                                            href="assignments.php?delete=<?= (int) $assignment['id'] ?>"
                                            class="delete-assignment-btn"
                                            onclick="return confirm(
                                                'Are you sure you want to delete this assignment? This action cannot be undone.'
                                            );"
                                        >

                                            Delete

                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="6"
                                class="no-assignments"
                            >

                                No assignments have been added yet.

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