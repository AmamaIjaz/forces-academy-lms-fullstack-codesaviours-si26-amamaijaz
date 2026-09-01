<?php

session_start();

require_once '../config/db.php';

// Admin authentication
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = '';
$error = '';

/*
|--------------------------------------------------------------------------
| Add Fee
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_fee'])) {

    $student_id = intval($_POST['student_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $due_date = $_POST['due_date'] ?? '';
    $description = trim($_POST['description'] ?? '');

    if ($student_id <= 0 || $amount <= 0 || empty($due_date)) {

        $error = "Please fill in all required fields.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO fees (student_id, amount, due_date, description, status)
             VALUES (?, ?, ?, ?, 'pending')"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "idss",
            $student_id,
            $amount,
            $due_date,
            $description
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Fee record added successfully.";
        } else {
            $error = "Failed to add fee record.";
        }

        mysqli_stmt_close($stmt);
    }
}

/*
|--------------------------------------------------------------------------
| Mark Fee as Paid
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {

    $fee_id = intval($_POST['fee_id'] ?? 0);

    if ($fee_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE fees
             SET status = 'paid', paid_date = CURDATE()
             WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $fee_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Fee marked as paid successfully.";
        } else {
            $error = "Failed to update fee status.";
        }

        mysqli_stmt_close($stmt);
    }
}

/*
|--------------------------------------------------------------------------
| Mark Fee as Pending
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_pending'])) {

    $fee_id = intval($_POST['fee_id'] ?? 0);

    if ($fee_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE fees
             SET status = 'pending', paid_date = NULL
             WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $fee_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Fee marked as pending successfully.";
        } else {
            $error = "Failed to update fee status.";
        }

        mysqli_stmt_close($stmt);
    }
}

/*
|--------------------------------------------------------------------------
| Delete Fee
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_fee'])) {

    $fee_id = intval($_POST['fee_id'] ?? 0);

    if ($fee_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM fees WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $fee_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Fee record deleted successfully.";
        } else {
            $error = "Failed to delete fee record.";
        }

        mysqli_stmt_close($stmt);
    }
}

/*
|--------------------------------------------------------------------------
| Get Students
|--------------------------------------------------------------------------
*/
$students = [];

$student_query = mysqli_query(
    $conn,
    "SELECT id, full_name, roll_number
     FROM students
     ORDER BY full_name ASC"
);

if ($student_query) {
    while ($student = mysqli_fetch_assoc($student_query)) {
        $students[] = $student;
    }
}

/*
|--------------------------------------------------------------------------
| Get Fee Records
|--------------------------------------------------------------------------
*/
$fees = [];

$fee_query = mysqli_query(
    $conn,
    "SELECT
        fees.id,
        fees.student_id,
        fees.amount,
        fees.due_date,
        fees.paid_date,
        fees.status,
        fees.description,
        students.full_name,
        students.roll_number
     FROM fees
     INNER JOIN students
        ON fees.student_id = students.id
     ORDER BY fees.due_date DESC, fees.id DESC"
);

if ($fee_query) {
    while ($fee = mysqli_fetch_assoc($fee_query)) {
        $fees[] = $fee;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Fee Management | Admin Panel</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="admin.css"
    >

    <link
        rel="stylesheet"
        href="admin/admin.css"
    >

</head>

<body>

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">

        <div class="sidebar-brand">

            <div class="brand-icon">
                FA
            </div>

            <div>
                <h4>Forces Academy</h4>
                <span>Admin Panel</span>
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
            >

                <span>📅</span>

                <span>
                    Timetable
                </span>

            </a>

 <a
                href="fees.php"
                class="active"
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


    <!-- Main Content -->
    <main class="admin-content">

        <!-- Header -->
        <div class="top-header">

            <div>
                <h1>Fee Management</h1>
                <p>Manage student fee records</p>
            </div>

            <div class="admin-badge">
                <?= htmlspecialchars($_SESSION['admin_username']) ?>
            </div>

        </div>


        <!-- Messages -->

        <?php if (!empty($message)): ?>

            <div class="alert alert-success fee-alert">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <?php if (!empty($error)): ?>

            <div class="alert alert-danger fee-alert">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <!-- Add Fee -->

        <div class="content-card fee-form-card">

            <div class="section-heading">

                <div>
                    <h2>Add Fee Record</h2>
                    <p>Create a new fee record for a student.</p>
                </div>

            </div>


            <form method="POST">

                <div class="row g-3">

                    <!-- Student -->

                    <div class="col-md-6">

                        <label for="student_id">
                            Student
                        </label>

                        <select
                            name="student_id"
                            id="student_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                Select Student
                            </option>

                            <?php foreach ($students as $student): ?>

                                <option value="<?= $student['id'] ?>">

                                    <?= htmlspecialchars($student['full_name']) ?>

                                    <?php if (!empty($student['roll_number'])): ?>
                                        - <?= htmlspecialchars($student['roll_number']) ?>
                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Amount -->

                    <div class="col-md-6">

                        <label for="amount">
                            Amount
                        </label>

                        <input
                            type="number"
                            name="amount"
                            id="amount"
                            class="form-control"
                            placeholder="Enter amount"
                            min="1"
                            step="0.01"
                            required
                        >

                    </div>


                    <!-- Due Date -->

                    <div class="col-md-6">

                        <label for="due_date">
                            Due Date
                        </label>

                        <input
                            type="date"
                            name="due_date"
                            id="due_date"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- Description -->

                    <div class="col-md-6">

                        <label for="description">
                            Description
                        </label>

                        <input
                            type="text"
                            name="description"
                            id="description"
                            class="form-control"
                            placeholder="e.g. Semester Fee"
                        >

                    </div>

                </div>


                <div class="form-button">

                    <button
                        type="submit"
                        name="add_fee"
                        class="btn-add-fee"
                    >
                        + Add Fee
                    </button>

                </div>

            </form>

        </div>


        <!-- Fee Records -->

        <div class="content-card">

            <div class="section-heading">

                <div>
                    <h2>All Fee Records</h2>
                    <p>View and manage student fee records.</p>
                </div>

            </div>


            <div class="table-responsive">

                <table class="fee-table">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Student</th>

                            <th>Roll Number</th>

                            <th>Amount</th>

                            <th>Due Date</th>

                            <th>Paid Date</th>

                            <th>Description</th>

                            <th>Status</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($fees)): ?>

                        <tr>

                            <td
                                colspan="9"
                                class="empty-fees"
                            >
                                No fee records found.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($fees as $index => $fee): ?>

                            <?php

                            /*
                             * Automatically show overdue when
                             * unpaid fee has passed its due date.
                             */

                            $display_status = $fee['status'];

                            if (
                                $fee['status'] !== 'paid'
                                && strtotime($fee['due_date']) < strtotime(date('Y-m-d'))
                            ) {
                                $display_status = 'overdue';
                            }

                            ?>

                            <tr>

                                <td>
                                    <?= $index + 1 ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($fee['full_name']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($fee['roll_number'] ?? '-') ?>
                                </td>

                                <td class="fee-amount">
                                    Rs. <?= number_format($fee['amount'], 2) ?>
                                </td>

                                <td>
                                    <?= date('d M Y', strtotime($fee['due_date'])) ?>
                                </td>

                                <td>

                                    <?php if (!empty($fee['paid_date'])): ?>

                                        <?= date('d M Y', strtotime($fee['paid_date'])) ?>

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= htmlspecialchars($fee['description'] ?? '-') ?>
                                </td>

                                <td>

                                    <?php if ($display_status === 'paid'): ?>

                                        <span class="status-badge status-paid">
                                            Paid
                                        </span>

                                    <?php elseif ($display_status === 'overdue'): ?>

                                        <span class="status-badge status-overdue">
                                            Overdue
                                        </span>

                                    <?php else: ?>

                                        <span class="status-badge status-pending">
                                            Pending
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <div class="fee-actions">

                                        <?php if ($fee['status'] === 'paid'): ?>

                                            <form
                                                method="POST"
                                                onsubmit="return confirm('Mark this fee as pending?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="fee_id"
                                                    value="<?= $fee['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="mark_pending"
                                                    class="action-btn pending-btn"
                                                >
                                                    Mark Pending
                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <form
                                                method="POST"
                                                onsubmit="return confirm('Mark this fee as paid?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="fee_id"
                                                    value="<?= $fee['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="mark_paid"
                                                    class="action-btn paid-btn"
                                                >
                                                    Mark Paid
                                                </button>

                                            </form>

                                        <?php endif; ?>


                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this fee record?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="fee_id"
                                                value="<?= $fee['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_fee"
                                                class="action-btn delete-btn"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

</body>

</html>