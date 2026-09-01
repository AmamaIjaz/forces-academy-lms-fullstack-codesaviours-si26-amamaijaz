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


/*
|--------------------------------------------------------------------------
| Post Notice
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_notice'])) {

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title) || empty($content)) {

        $error = "Notice title and content are required.";

    } else {

        $sql = "INSERT INTO notices (title, content)
                VALUES (?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ss",
                $title,
                $content
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: notices.php?posted=1");
                exit;

            } else {

                $error = "Unable to post notice.";

                mysqli_stmt_close($stmt);
            }

        } else {

            $error = "Database error. Please try again.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| Delete Notice
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $notice_id = (int) $_GET['delete'];

    if ($notice_id > 0) {

        $sql = "DELETE FROM notices WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $notice_id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);
        }
    }

    header("Location: notices.php?deleted=1");
    exit;
}


/*
|--------------------------------------------------------------------------
| Success Messages
|--------------------------------------------------------------------------
*/

if (isset($_GET['posted'])) {

    $message = "Notice posted successfully.";
}

if (isset($_GET['deleted'])) {

    $message = "Notice deleted successfully.";
}


/*
|--------------------------------------------------------------------------
| Get Existing Notices
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            title,
            content,
            created_at
        FROM notices
        ORDER BY id DESC";

$notices_result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Post Notice | Forces Academy</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Main Admin CSS -->

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


            <a href="results.php">

                <span>📊</span>

                <span>
                    Upload Results
                </span>

            </a>


            <a href="notices.php" class="active">

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
                    Post Notice
                </h1>

                <p>
                    Publish important announcements for students.
                </p>

            </div>


            <div class="admin-badge">

                Administrator

            </div>


        </div>



        <!-- SUCCESS MESSAGE -->

        <?php if (!empty($message)): ?>

            <div class="notice-success">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>



        <!-- ERROR MESSAGE -->

        <?php if (!empty($error)): ?>

            <div class="notice-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             CREATE NOTICE
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">

                <div>

                    <h3>
                        Create New Notice
                    </h3>

                    <p>
                        Write an announcement for students.
                    </p>

                </div>

            </div>



            <form
                method="POST"
                class="notice-form"
            >


                <!-- TITLE -->

                <div class="notice-form-group">

                    <label for="title">
                        Notice Title
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="Enter notice title"
                        required
                    >

                </div>



                <!-- CONTENT -->

                <div class="notice-form-group">

                    <label for="content">
                        Notice Content
                    </label>

                    <textarea
                        id="content"
                        name="content"
                        placeholder="Write your announcement here..."
                        required
                    ></textarea>

                </div>



                <!-- POST BUTTON -->

                <button
                    type="submit"
                    name="post_notice"
                    class="post-notice-btn"
                >

                    Post Notice

                </button>


            </form>

        </div>



        <!-- =================================================
             EXISTING NOTICES
             ================================================= -->

        <div class="content-card">


            <div class="section-heading">

                <div>

                    <h3>
                        Existing Notices
                    </h3>

                    <p>
                        All announcements posted by the administrator.
                    </p>

                </div>

            </div>



            <div class="notice-list">


                <?php if (
                    $notices_result &&
                    mysqli_num_rows($notices_result) > 0
                ): ?>


                    <?php while (
                        $notice = mysqli_fetch_assoc(
                            $notices_result
                        )
                    ): ?>


                        <div class="notice-item">


                            <div class="notice-item-header">


                                <h4>

                                    <?= htmlspecialchars(
                                        $notice['title']
                                    ) ?>

                                </h4>


                                <span class="notice-date">

                                    <?php

                                    if (!empty($notice['created_at'])) {

                                        echo date(
                                            'd M Y, h:i A',
                                            strtotime(
                                                $notice['created_at']
                                            )
                                        );

                                    } else {

                                        echo 'Date unavailable';

                                    }

                                    ?>

                                </span>


                            </div>



                            <p class="notice-content">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $notice['content']
                                    )
                                ) ?>

                            </p>



                            <a
                                href="notices.php?delete=<?= (int) $notice['id'] ?>"
                                class="notice-delete-btn"
                                onclick="return confirm(
                                    'Are you sure you want to delete this notice? This action cannot be undone.'
                                );"
                            >

                                Delete Notice

                            </a>


                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="no-notices">

                        No notices have been posted yet.

                    </div>


                <?php endif; ?>


            </div>


        </div>


    </main>


</div>


</body>

</html>