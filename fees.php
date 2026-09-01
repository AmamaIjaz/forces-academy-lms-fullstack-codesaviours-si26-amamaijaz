<?php

session_start();

require_once 'config/db.php';

// Student authentication
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

$error = '';
$fees = [];
$total_pending = 0;

/*
|--------------------------------------------------------------------------
| Get Fee Records
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, amount, due_date, paid_date, status, description
     FROM fees
     WHERE student_id = ?
     ORDER BY due_date DESC"
);

mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($fee = mysqli_fetch_assoc($result)) {

    /*
     * Automatically display overdue for unpaid fees
     * whose due date has passed.
     */
    if (
        $fee['status'] !== 'paid'
        && strtotime($fee['due_date']) < strtotime(date('Y-m-d'))
    ) {
        $fee['display_status'] = 'overdue';
    } else {
        $fee['display_status'] = $fee['status'];
    }

    $fees[] = $fee;

    /*
     * Pending amount
     */
    if ($fee['status'] === 'pending') {
        $total_pending += (float)$fee['amount'];
    }
}

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Fees | Forces Academy</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<div class="student-layout">

    <!-- Sidebar -->

    <aside class="student-sidebar">

        <div class="sidebar-brand">

            <div class="brand-icon">
                FA
            </div>

            <div>
                <h4>Forces Academy</h4>
                <span>Student Panel</span>
            </div>

        </div>

        <nav class="student-nav">

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="courses.php">
                My Courses
            </a>

            <a href="assignments.php">
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
                Profile
            </a>

            <a href="fees.php" class="active">
                My Fees
            </a>

            <a href="logout.php" class="logout-link">
                Logout
            </a>

        </nav>

    </aside>


    <!-- Main Content -->

    <main class="student-content">

        <!-- Header -->

        <div class="top-header">

            <div>
                <h1>My Fees</h1>
                <p>View your fee records and payment status.</p>
            </div>

            <div class="student-badge">
                <?= htmlspecialchars($_SESSION['student_name']) ?>
            </div>

        </div>


        <!-- Pending Amount -->

        <div class="pending-fee-card">

            <div>

                <span class="pending-label">
                    Total Pending Amount
                </span>

                <h2>
                    Rs. <?= number_format($total_pending, 2) ?>
                </h2>

            </div>

            <div class="pending-icon">
                Rs
            </div>

        </div>


        <!-- Fee Records -->

        <div class="student-content-card">

            <div class="section-heading">

                <div>
                    <h2>Fee Records</h2>
                    <p>Your complete fee history.</p>
                </div>

            </div>


            <?php if (empty($fees)): ?>

                <div class="empty-fees">

                    <div class="empty-fees-icon">
                        Rs
                    </div>

                    <h3>No Fee Records</h3>

                    <p>
                        You currently have no fee records.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="student-fee-table">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Paid Date</th>
                                <th>Description</th>
                                <th>Status</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($fees as $index => $fee): ?>

                            <tr>

                                <td>
                                    <?= $index + 1 ?>
                                </td>

                                <td class="student-fee-amount">
                                    Rs. <?= number_format($fee['amount'], 2) ?>
                                </td>

                                <td>
                                    <?= date(
                                        'd M Y',
                                        strtotime($fee['due_date'])
                                    ) ?>
                                </td>

                                <td>

                                    <?php if (!empty($fee['paid_date'])): ?>

                                        <?= date(
                                            'd M Y',
                                            strtotime($fee['paid_date'])
                                        ) ?>

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $fee['description'] ?? '-'
                                    ) ?>
                                </td>

                                <td>

                                    <?php if (
                                        $fee['display_status'] === 'paid'
                                    ): ?>

                                        <span class="fee-status fee-paid">
                                            Paid
                                        </span>

                                    <?php elseif (
                                        $fee['display_status'] === 'overdue'
                                    ): ?>

                                        <span class="fee-status fee-overdue">
                                            Overdue
                                        </span>

                                    <?php else: ?>

                                        <span class="fee-status fee-pending">
                                            Pending
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>

</html>