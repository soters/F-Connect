<?php
session_start();
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_POST['login'])) {
    // Get input values and sanitize
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        // Step 1: Fetch the admin record by email
        $sql = "
            SELECT 
                a.rfid_no, a.acc_type, 
                acc.password AS stored_password, acc.email AS account_email
            FROM Admin a
            INNER JOIN AdminAccount acc ON a.email = acc.email
            WHERE a.email = ?
        ";

        // Prepare the query
        $params = array($email);
        $stmt = sqlsrv_query($conn, $sql, $params);

        // Step 2: Check if the account exists
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        if (sqlsrv_has_rows($stmt)) {
            $fetch = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

            // Step 3: Verify the entered password
            if (password_verify($password, $fetch['stored_password'])) {
                // Step 4: Set session variables
                $_SESSION['account_email'] = $fetch['account_email'];
                $_SESSION['rfid_no'] = $fetch['rfid_no'];
                $_SESSION['acc_type'] = $fetch['acc_type'];

                // Step 5: Redirect based on role
                if ($fetch['acc_type'] === "Super Admin" || $fetch['acc_type'] === "Admin") {
                    header("Location: ../pages/admin-index.php?admin_rfid_no={$fetch['rfid_no']}");
                    exit();
                }
            } else {
                // Incorrect password message
                header('Location: ../authentication/admin-login.php?error=incorrect_password');
                exit();
            }           
        } else {
            // Account does not exist message
            header('Location: ../authentication/admin-login.php?error=account_not_found');
            exit();
        }
    } catch (Exception $e) {
        // Handle errors
        error_log("Error: " . $e->getMessage());
        die("An error occurred. Please contact the administrator.");
    }
}
?>
