<?php
session_start();
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Please enter both email and password.";
        $type = "error";
        header("Location: admin-admins.php?message=" . urlencode($message) . "&type=" . urlencode($type));
        exit();
    }

    try {
        // Prepare the SQL query
        $sql = "SELECT a.acc_type, ac.password 
                FROM AdminAccount ac
                JOIN Admin a ON ac.email = a.email
                WHERE ac.email = ? AND a.archived = 0";

        $params = array($email);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $message = "Database error: " . print_r(sqlsrv_errors(), true);
            $type = "error";
            header("Location: admin-admins.php?message=" . urlencode($message) . "&type=" . urlencode($type));
            exit();
        }

        // Check if user exists
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Verify password
            if (password_verify($password, $row['password'])) {
                // Check if Super Admin
                if ($row['acc_type'] === 'Super Admin') {
                    $_SESSION['email'] = $email;
                    $_SESSION['acc_type'] = $row['acc_type'];

                    header("Location: ../pages/admin-manage.php");
                    exit();
                } else {
                    $message = "Access denied. Only Super Admins can log in.";
                    $type = "error";
                    header("Location: admin-admins.php?message=" . urlencode($message) . "&type=" . urlencode($type));
                    exit();
                }
            } else {
                $message = "Incorrect password.";
                $type = "error";
                header("Location: admin-admins.php?message=" . urlencode($message) . "&type=" . urlencode($type));
                exit();
            }
        } else {
            $message = "Email not found.";
            $type = "error";
            header("Location: admin-admins.php?message=" . urlencode($message) . "&type=" . urlencode($type));
            exit();
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $type = "error";
        header("Location: admin-admins.php?message=" . urlencode($message) . "&type=" . urlencode($type));
        exit();
    }
}
?>
