<?php

require_once 'config/db.php';

$username = "admin";
$email = "admin@forcesacademy.com";
$password = password_hash("admin123", PASSWORD_DEFAULT);

$sql = "INSERT INTO admins (username, password, email)
        VALUES (?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "sss",
    $username,
    $password,
    $email
);

if (mysqli_stmt_execute($stmt)) {
    echo "Admin created successfully.";
} else {
    echo "Error: " . mysqli_error($conn);
}