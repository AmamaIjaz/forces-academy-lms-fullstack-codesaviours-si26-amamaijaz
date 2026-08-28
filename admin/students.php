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
| Delete Student
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $student_id = (int) $_GET['delete'];

    if ($student_id > 0) {

        $delete_sql = "DELETE FROM students WHERE id = ?";

        $delete_stmt = mysqli_prepare($conn, $delete_sql);

        if ($delete_stmt) {

            mysqli_stmt_bind_param(
                $delete_stmt,
                "i",
                $student_id
            );

            mysqli_stmt_execute($delete_stmt);

            mysqli_stmt_close($delete_stmt);
        }
    }

    header("Location: students.php?deleted=1");
    exit;
}


/*
|--------------------------------------------------------------------------
| Search Students
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $sql = "SELECT
                id,
                full_name,
                email,
                roll_number,
                class,
                created_at
            FROM students
            WHERE full_name LIKE ?
               OR roll_number LIKE ?
            ORDER BY id DESC";

    $stmt = mysqli_prepare($conn, $sql);

    $search_value = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $search_value,
        $search_value
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $sql = "SELECT
                id,
                full_name,
                email,
                roll_number,
                class,
                created_at
            FROM students
            ORDER BY id DESC";

    $result = mysqli_query($conn, $sql);
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

    <title>Manage Students | Forces Academy</title>


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


    <!-- Small page-specific styling -->

    <style>

        /* Search area */

        .student-search {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 20px;
        }

        .student-search input {
            flex: 1;
            height: 42px;
            padding: 0 13px;
            border: 1px solid #d6d8cc;
            border-radius: 6px;
            outline: none;
            font-size: 14px;
        }

        .student-search input:focus {
            border-color: #0A2947;
            box-shadow: 0 0 0 3px rgba(10, 41, 71, 0.08);
        }


        /* Search button */

        .student-search button {
            height: 42px;
            padding: 0 20px;
            border: none;
            border-radius: 6px;
            background: #0A2947;
            color: #EAE6BC;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .student-search button:hover {
            background: #16466D;
        }


        /* Clear button */

        .clear-search {
            height: 42px;
            padding: 0 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: #8B5E3C;
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .clear-search:hover {
            background: #70472F;
            color: #ffffff;
        }


        /* Student table */

        .student-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .student-table th {
            padding: 14px 12px;
            background: #0A2947;
            color: #EAE6BC;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .student-table td {
            padding: 13px 12px;
            border-bottom: 1px solid #e0e1d8;
            color: #0A2947;
            font-size: 13px;
            vertical-align: middle;
        }

        .student-table tbody tr:hover {
            background: #f5f5e8;
        }


        /* Student name */

        .student-name {
            font-weight: 600;
        }


        /* Action buttons */

        .student-actions {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        .view-student-btn,
        .delete-student-btn {
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


        .view-student-btn {
            background: #0A2947;
            color: #EAE6BC;
        }

        .view-student-btn:hover {
            background: #16466D;
            color: #EAE6BC;
        }


        .delete-student-btn {
            background: #8B5E3C;
            color: #ffffff;
        }

        .delete-student-btn:hover {
            background: #70472F;
            color: #ffffff;
        }


        /* Empty table */

        .no-students {
            padding: 40px 20px !important;
            text-align: center;
            color: #647481 !important;
        }


        /* Success message */

        .student-success {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-left: 4px solid #0A2947;
            background: #f1eecf;
            color: #0A2947;
            border-radius: 5px;
            font-size: 13px;
        }


        /* Mobile */

        @media (max-width: 768px) {

            .student-search {
                flex-direction: column;
                align-items: stretch;
            }

            .student-search button,
            .clear-search {
                width: 100%;
            }

            .student-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .view-student-btn,
            .delete-student-btn {
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


            <a href="students.php" class="active">

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
                    Manage Students
                </h1>

                <p>
                    View, search and manage registered students.
                </p>

            </div>


            <div class="admin-badge">

                Administrator

            </div>


        </div>



        <!-- =================================================
             SUCCESS MESSAGE
             ================================================= -->

        <?php if (isset($_GET['deleted'])): ?>

            <div class="student-success">

                Student deleted successfully.

            </div>

        <?php endif; ?>



        <!-- =================================================
             SEARCH CARD
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">

                <div>

                    <h3>
                        Search Students
                    </h3>

                    <p>
                        Search by student name or roll number.
                    </p>

                </div>

            </div>


            <form
                method="GET"
                class="student-search"
            >


                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Enter name or roll number..."
                >


                <button type="submit">

                    Search

                </button>


                <?php if ($search !== ''): ?>

                    <a
                        href="students.php"
                        class="clear-search"
                    >
                        Clear
                    </a>

                <?php endif; ?>


            </form>


        </div>



        <!-- =================================================
             STUDENTS TABLE
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">

                <div>

                    <h3>
                        Registered Students
                    </h3>

                    <p>
                        All students registered in the LMS.
                    </p>

                </div>

            </div>


            <div class="student-table-wrapper">


                <table class="student-table">


                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Name
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Roll Number
                            </th>

                            <th>
                                Class
                            </th>

                            <th>
                                Registered Date
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if ($result && mysqli_num_rows($result) > 0): ?>


                        <?php $counter = 1; ?>


                        <?php while ($student = mysqli_fetch_assoc($result)): ?>


                            <tr>


                                <!-- NUMBER -->

                                <td>

                                    <?= $counter++ ?>

                                </td>



                                <!-- NAME -->

                                <td>

                                    <span class="student-name">

                                        <?= htmlspecialchars(
                                            $student['full_name']
                                        ) ?>

                                    </span>

                                </td>



                                <!-- EMAIL -->

                                <td>

                                    <?= htmlspecialchars(
                                        $student['email']
                                    ) ?>

                                </td>



                                <!-- ROLL NUMBER -->

                                <td>

                                    <?= htmlspecialchars(
                                        $student['roll_number']
                                    ) ?>

                                </td>



                                <!-- CLASS -->

                                <td>

                                    <?= htmlspecialchars(
                                        $student['class']
                                    ) ?>

                                </td>



                                <!-- REGISTERED DATE -->

                                <td>

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

                                </td>



                                <!-- ACTIONS -->

                                <td>

                                    <div class="student-actions">


                                        <a
                                            href="student_details.php?id=<?= $student['id'] ?>"
                                            class="view-student-btn"
                                        >

                                            View

                                        </a>


                                        <a
                                            href="students.php?delete=<?= $student['id'] ?>"
                                            class="delete-student-btn"
                                            onclick="return confirm(
                                                'Are you sure you want to delete this student? This action cannot be undone.'
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
                                colspan="7"
                                class="no-students"
                            >

                                <?php if ($search !== ''): ?>

                                    No students found for
                                    "<strong><?= htmlspecialchars($search) ?></strong>".

                                <?php else: ?>

                                    No students registered yet.

                                <?php endif; ?>

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