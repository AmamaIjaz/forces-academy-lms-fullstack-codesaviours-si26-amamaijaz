<?php

session_start();

require_once 'config/db.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['student_id'];

$error = '';
$success = '';

$assignment_id = isset($_GET['assignment_id'])
    ? (int) $_GET['assignment_id']
    : (int) ($_POST['assignment_id'] ?? 0);

if ($assignment_id <= 0) {
    header('Location: assignments.php');
    exit;
}


/* =========================================================
   GET ASSIGNMENT
   ========================================================= */

$sql = "SELECT
            a.id,
            a.title,
            a.description,
            a.due_date,
            c.course_name
        FROM assignments a
        INNER JOIN courses c
            ON a.course_id = c.id
        WHERE a.id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die('Database query failed.');
}

mysqli_stmt_bind_param(
    $stmt,
    'i',
    $assignment_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$assignment = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$assignment) {
    header('Location: assignments.php');
    exit;
}


/* =========================================================
   CHECK EXISTING SUBMISSION
   ========================================================= */

$sql = "SELECT id
        FROM submissions
        WHERE assignment_id = ?
        AND student_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    'ii',
    $assignment_id,
    $student_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$existing_submission = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================================
   PROCESS UPLOAD
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($existing_submission) {

        $error = 'You have already submitted this assignment.';

    } elseif (
        !isset($_FILES['assignment_file']) ||
        $_FILES['assignment_file']['error'] !== UPLOAD_ERR_OK
    ) {

        $error = 'Please select a file to upload.';

    } else {

        $file = $_FILES['assignment_file'];

        $original_name = $file['name'];
        $tmp_name = $file['tmp_name'];
        $file_size = $file['size'];

        $extension = strtolower(
            pathinfo(
                $original_name,
                PATHINFO_EXTENSION
            )
        );


        /* =================================================
           ALLOWED FILE TYPES
           ================================================= */

        $allowed_extensions = [
            'pdf',
            'jpg',
            'jpeg',
            'png'
        ];


        if (!in_array(
            $extension,
            $allowed_extensions,
            true
        )) {

            $error =
                'Only PDF, JPG, JPEG and PNG files are allowed.';

        } elseif ($file_size > 10 * 1024 * 1024) {

            $error =
                'File size must not exceed 10 MB.';

        } else {

            /* =============================================
               VERIFY ACTUAL MIME TYPE
               ============================================= */

            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            $mime_type = finfo_file(
                $finfo,
                $tmp_name
            );

            finfo_close($finfo);


            $allowed_mimes = [
                'application/pdf',
                'image/jpeg',
                'image/png'
            ];


            if (!in_array(
                $mime_type,
                $allowed_mimes,
                true
            )) {

                $error =
                    'Invalid file type detected.';

            } else {

                /* =========================================
                   CREATE UPLOAD DIRECTORY
                   ========================================= */

                $upload_directory =
                    __DIR__ . '/uploads/assignments/';

                if (!is_dir($upload_directory)) {

                    mkdir(
                        $upload_directory,
                        0755,
                        true
                    );
                }


                /* =========================================
                   UNIQUE FILE NAME
                   ========================================= */

                $unique_name =
                    'assignment_' .
                    $student_id .
                    '_' .
                    $assignment_id .
                    '_' .
                    bin2hex(random_bytes(8)) .
                    '.' .
                    $extension;


                $destination =
                    $upload_directory .
                    $unique_name;


                /* =========================================
                   MOVE FILE
                   ========================================= */

                if (move_uploaded_file(
                    $tmp_name,
                    $destination
                )) {

                    $relative_path =
                        'uploads/assignments/' .
                        $unique_name;


                    /* =====================================
                       INSERT SUBMISSION
                       ===================================== */

                    $sql = "INSERT INTO submissions
                            (
                                assignment_id,
                                student_id,
                                file_path,
                                status
                            )
                            VALUES (?, ?, ?, 'submitted')";


                    $stmt = mysqli_prepare(
                        $conn,
                        $sql
                    );


                    if ($stmt) {

                        mysqli_stmt_bind_param(
                            $stmt,
                            'iis',
                            $assignment_id,
                            $student_id,
                            $relative_path
                        );


                        if (
                            mysqli_stmt_execute($stmt)
                        ) {

                            $success =
                                'Assignment submitted successfully.';

                            $existing_submission = true;

                        } else {

                            if (file_exists($destination)) {
                                unlink($destination);
                            }

                            $error =
                                'Unable to save submission.';
                        }


                        mysqli_stmt_close($stmt);

                    } else {

                        if (file_exists($destination)) {
                            unlink($destination);
                        }

                        $error =
                            'Database query failed.';
                    }

                } else {

                    $error =
                        'Unable to upload the file.';
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Submit Assignment | Forces Academy LMS
    </title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>

<div class="dashboard-layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2>Forces Academy</h2>

        <p>Student Portal</p>

        <nav class="sidebar-nav">

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="courses.php">
                My Courses
            </a>

            <a href="assignments.php" class="active">
                Assignments
            </a>

            <a href="results.php">
                My Results
            </a>

            <a href="notices.php">
                Notices
            </a>
          <a href="timetable.php">
        Timetable
</a>
<a href="profile.php">
    
    <span>My Profile</span>
</a>
</a>
 <a href="fees.php" >
                My Fees
            </a>
            <a href="logout.php" class="logout">
                Logout
            </a>

        </nav>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <div class="page-header">

            <h1>Submit Assignment</h1>

            <p>
                Upload your completed assignment.
            </p>

        </div>


        <div class="submission-container">

            <div class="submission-card">

                <div class="assignment-course">
                    <?php
                    echo htmlspecialchars(
                        $assignment['course_name']
                    );
                    ?>
                </div>


                <h2>
                    <?php
                    echo htmlspecialchars(
                        $assignment['title']
                    );
                    ?>
                </h2>


                <p>
                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $assignment['description']
                        )
                    );
                    ?>
                </p>


                <div class="submission-due">

                    <strong>Due Date:</strong>

                    <?php
                    echo date(
                        'd M Y',
                        strtotime(
                            $assignment['due_date']
                        )
                    );
                    ?>

                </div>


                <?php if (!empty($error)): ?>

                    <div class="form-message error">
                        <?php
                        echo htmlspecialchars($error);
                        ?>
                    </div>

                <?php endif; ?>


                <?php if (!empty($success)): ?>

                    <div class="form-message success">
                        <?php
                        echo htmlspecialchars($success);
                        ?>
                    </div>

                    <a
                        href="assignments.php"
                        class="btn"
                    >
                        Back to Assignments
                    </a>

                <?php elseif (!$existing_submission): ?>

                    <form
                        method="POST"
                        action=""
                        enctype="multipart/form-data"
                        class="upload-form"
                    >

                        <input
                            type="hidden"
                            name="assignment_id"
                            value="<?php
                            echo $assignment_id;
                            ?>"
                        >


                        <label for="assignment_file">
                            Select Assignment File
                        </label>


                        <input
                            type="file"
                            id="assignment_file"
                            name="assignment_file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            required
                        >


                        <small>
                            Allowed: PDF, JPG, JPEG, PNG.
                            Maximum size: 10 MB.
                        </small>


                        <button
                            type="submit"
                            class="btn"
                        >
                            Submit Assignment
                        </button>

                    </form>

                <?php else: ?>

                    <div class="form-message success">

                        You have already submitted
                        this assignment.

                    </div>

                    <a
                        href="assignments.php"
                        class="btn"
                    >
                        Back to Assignments
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </main>

</div>

</body>
</html>