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
| Variables
|--------------------------------------------------------------------------
*/

$message = '';
$error = '';

$editMode = false;

$editCourse = [
    'id' => '',
    'course_name' => '',
    'description' => '',
    'teacher_name' => ''
];


/*
|--------------------------------------------------------------------------
| Add Course
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {

    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $teacher_name = trim($_POST['teacher_name'] ?? '');

    if (
        empty($course_name) ||
        empty($description) ||
        empty($teacher_name)
    ) {

        $error = "All course fields are required.";

    } else {

        $sql = "INSERT INTO courses
                (course_name, description, teacher_name)
                VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $course_name,
                $description,
                $teacher_name
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: courses.php?added=1");
                exit;

            } else {

                $error = "Unable to add course.";

            }

            mysqli_stmt_close($stmt);

        } else {

            $error = "Database error. Please try again.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| Update Course
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {

    $course_id = (int) ($_POST['course_id'] ?? 0);

    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $teacher_name = trim($_POST['teacher_name'] ?? '');

    if ($course_id <= 0) {

        $error = "Invalid course.";

    } elseif (
        empty($course_name) ||
        empty($description) ||
        empty($teacher_name)
    ) {

        $error = "All course fields are required.";

    } else {

        $sql = "UPDATE courses
                SET course_name = ?,
                    description = ?,
                    teacher_name = ?
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sssi",
                $course_name,
                $description,
                $teacher_name,
                $course_id
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: courses.php?updated=1");
                exit;

            } else {

                $error = "Unable to update course.";
            }

            mysqli_stmt_close($stmt);

        } else {

            $error = "Database error. Please try again.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| Delete Course
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $course_id = (int) $_GET['delete'];

    if ($course_id > 0) {

        $sql = "DELETE FROM courses WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $course_id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);
        }
    }

    header("Location: courses.php?deleted=1");
    exit;
}


/*
|--------------------------------------------------------------------------
| Edit Course
|--------------------------------------------------------------------------
*/

if (isset($_GET['edit'])) {

    $course_id = (int) $_GET['edit'];

    if ($course_id > 0) {

        $sql = "SELECT
                    id,
                    course_name,
                    description,
                    teacher_name
                FROM courses
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $course_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $course = mysqli_fetch_assoc($result);

            if ($course) {

                $editMode = true;

                $editCourse = $course;

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
    $message = "Course added successfully.";
}

if (isset($_GET['updated'])) {
    $message = "Course updated successfully.";
}

if (isset($_GET['deleted'])) {
    $message = "Course deleted successfully.";
}


/*
|--------------------------------------------------------------------------
| Get All Courses
|--------------------------------------------------------------------------
*/

$courses_sql = "SELECT
                    id,
                    course_name,
                    description,
                    teacher_name
                FROM courses
                ORDER BY id DESC";

$courses_result = mysqli_query($conn, $courses_sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Courses | Forces Academy</title>


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


    <!-- Course Page Styling -->

    <style>

        /* =================================================
           COURSE FORM
           ================================================= */

        .course-form {
            margin-top: 20px;
        }


        .course-form-group {
            margin-bottom: 18px;
        }


        .course-form-group label {
            display: block;

            margin-bottom: 7px;

            color: #0A2947;

            font-size: 14px;

            font-weight: 600;
        }


        .course-form-group input,
        .course-form-group textarea {
            width: 100%;

            padding: 10px 13px;

            border: 1px solid #d6d8cc;

            border-radius: 6px;

            background: #ffffff;

            color: #0A2947;

            outline: none;

            font-size: 14px;
        }


        .course-form-group input {
            height: 43px;
        }


        .course-form-group textarea {
            min-height: 105px;

            resize: vertical;
        }


        .course-form-group input:focus,
        .course-form-group textarea:focus {
            border-color: #0A2947;

            box-shadow:
                0 0 0 3px rgba(10, 41, 71, 0.08);
        }


        /* =================================================
           FORM BUTTONS
           ================================================= */

        .course-form-actions {
            display: flex;

            gap: 10px;

            margin-top: 5px;
        }


        .course-submit-btn,
        .course-cancel-btn {
            height: 42px;

            padding: 0 20px;

            border-radius: 6px;

            font-size: 13px;

            font-weight: 600;

            text-decoration: none;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;
        }


        .course-submit-btn {
            border: none;

            background: #0A2947;

            color: #EAE6BC;
        }


        .course-submit-btn:hover {
            background: #16466D;

            color: #EAE6BC;
        }


        .course-cancel-btn {
            background: #8B5E3C;

            color: #ffffff;
        }


        .course-cancel-btn:hover {
            background: #70472F;

            color: #ffffff;
        }


        /* =================================================
           COURSE TABLE
           ================================================= */

        .course-table-wrapper {
            width: 100%;

            overflow-x: auto;
        }


        .course-table {
            width: 100%;

            border-collapse: collapse;

            margin-top: 5px;
        }


        .course-table th {
            padding: 14px 12px;

            background: #0A2947;

            color: #EAE6BC;

            text-align: left;

            font-size: 13px;

            font-weight: 600;

            white-space: nowrap;
        }


        .course-table td {
            padding: 13px 12px;

            border-bottom: 1px solid #e0e1d8;

            color: #0A2947;

            font-size: 13px;

            vertical-align: middle;
        }


        .course-table tbody tr:hover {
            background: #f5f5e8;
        }


        /* =================================================
           DESCRIPTION
           ================================================= */

        .course-description {
            max-width: 350px;

            line-height: 1.5;

            color: #647481;
        }


        /* =================================================
           COURSE ACTIONS
           ================================================= */

        .course-actions {
            display: flex;

            align-items: center;

            gap: 7px;
        }


        .edit-course-btn,
        .delete-course-btn {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 7px 12px;

            border-radius: 5px;

            text-decoration: none;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;
        }


        .edit-course-btn {
            background: #0A2947;

            color: #EAE6BC;
        }


        .edit-course-btn:hover {
            background: #16466D;

            color: #EAE6BC;
        }


        .delete-course-btn {
            background: #8B5E3C;

            color: #ffffff;
        }


        .delete-course-btn:hover {
            background: #70472F;

            color: #ffffff;
        }


        /* =================================================
           ALERTS
           ================================================= */

        .course-success {
            padding: 12px 15px;

            margin-bottom: 20px;

            background: #f1eecf;

            color: #0A2947;

            border-left: 4px solid #0A2947;

            border-radius: 5px;

            font-size: 13px;
        }


        .course-error {
            padding: 12px 15px;

            margin-bottom: 20px;

            background: #f1eecf;

            color: #0A2947;

            border-left: 4px solid #8B5E3C;

            border-radius: 5px;

            font-size: 13px;
        }


        /* =================================================
           EMPTY STATE
           ================================================= */

        .no-courses {
            padding: 40px 20px !important;

            text-align: center;

            color: #647481 !important;
        }


        /* =================================================
           MOBILE
           ================================================= */

        @media (max-width: 768px) {

            .course-form-actions {
                flex-direction: column;

                align-items: stretch;
            }


            .course-submit-btn,
            .course-cancel-btn {
                width: 100%;
            }


            .course-actions {
                flex-direction: column;

                align-items: stretch;
            }


            .edit-course-btn,
            .delete-course-btn {
                width: 100%;
            }

        }

    </style>

</head>


<body>


<div class="admin-layout">


    <!-- =====================================================
         ADMIN SIDEBAR
         SAME AS DASHBOARD
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


            <a href="courses.php" class="active">

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
                    Manage Courses
                </h1>

                <p>
                    Add, edit and manage academy courses.
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

            <div class="course-success">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <?php if (!empty($error)): ?>

            <div class="course-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ADD / EDIT COURSE FORM
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">


                <div>

                    <h3>

                        <?= $editMode
                            ? 'Edit Course'
                            : 'Add New Course'
                        ?>

                    </h3>


                    <p>

                        <?= $editMode
                            ? 'Update the course information below.'
                            : 'Create a new course for students.'
                        ?>

                    </p>

                </div>


            </div>



            <form
                method="POST"
                class="course-form"
            >


                <?php if ($editMode): ?>

                    <input
                        type="hidden"
                        name="course_id"
                        value="<?= (int) $editCourse['id'] ?>"
                    >

                <?php endif; ?>


                <!-- COURSE NAME -->

                <div class="course-form-group">

                    <label for="course_name">
                        Course Name
                    </label>

                    <input
                        type="text"
                        id="course_name"
                        name="course_name"
                        value="<?= htmlspecialchars(
                            $editCourse['course_name']
                        ) ?>"
                        placeholder="Enter course name"
                        required
                    >

                </div>


                <!-- DESCRIPTION -->

                <div class="course-form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Enter course description"
                        required
                    ><?= htmlspecialchars(
                        $editCourse['description']
                    ) ?></textarea>

                </div>


                <!-- TEACHER -->

                <div class="course-form-group">

                    <label for="teacher_name">
                        Teacher Name
                    </label>

                    <input
                        type="text"
                        id="teacher_name"
                        name="teacher_name"
                        value="<?= htmlspecialchars(
                            $editCourse['teacher_name']
                        ) ?>"
                        placeholder="Enter teacher name"
                        required
                    >

                </div>


                <!-- BUTTONS -->

                <div class="course-form-actions">


                    <?php if ($editMode): ?>

                        <button
                            type="submit"
                            name="update_course"
                            class="course-submit-btn"
                        >

                            Update Course

                        </button>


                        <a
                            href="courses.php"
                            class="course-cancel-btn"
                        >

                            Cancel

                        </a>


                    <?php else: ?>

                        <button
                            type="submit"
                            name="add_course"
                            class="course-submit-btn"
                        >

                            Add Course

                        </button>

                    <?php endif; ?>


                </div>


            </form>


        </div>



        <!-- =================================================
             COURSE LIST
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">


                <div>

                    <h3>
                        All Courses
                    </h3>

                    <p>
                        Manage all courses available in the LMS.
                    </p>

                </div>


            </div>



            <div class="course-table-wrapper">


                <table class="course-table">


                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Course Name
                            </th>

                            <th>
                                Description
                            </th>

                            <th>
                                Teacher
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $courses_result &&
                        mysqli_num_rows($courses_result) > 0
                    ): ?>


                        <?php $counter = 1; ?>


                        <?php while (
                            $course = mysqli_fetch_assoc(
                                $courses_result
                            )
                        ): ?>


                            <tr>


                                <td>

                                    <?= $counter++ ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $course['course_name']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <div class="course-description">

                                        <?= htmlspecialchars(
                                            $course['description']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $course['teacher_name']
                                    ) ?>

                                </td>


                                <td>

                                    <div class="course-actions">


                                        <a
                                            href="courses.php?edit=<?= (int) $course['id'] ?>"
                                            class="edit-course-btn"
                                        >

                                            Edit

                                        </a>


                                        <a
                                            href="courses.php?delete=<?= (int) $course['id'] ?>"
                                            class="delete-course-btn"
                                            onclick="return confirm(
                                                'Are you sure you want to delete this course? This action cannot be undone.'
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
                                colspan="5"
                                class="no-courses"
                            >

                                No courses have been added yet.

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