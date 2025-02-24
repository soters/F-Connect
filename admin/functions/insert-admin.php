<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $rfid_no = filter_input(INPUT_POST, 'rfid_no', FILTER_SANITIZE_STRING);
    $fname = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
    $mname = filter_input(INPUT_POST, 'mname', FILTER_SANITIZE_STRING);
    $lname = filter_input(INPUT_POST, 'lname', FILTER_SANITIZE_STRING);
    $suffix = filter_input(INPUT_POST, 'suffix', FILTER_SANITIZE_STRING);
    $sex = filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_STRING);
    $dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING);
    $phone_no = filter_input(INPUT_POST, 'phone_no', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $acc_type = filter_input(INPUT_POST, 'acc_type', FILTER_SANITIZE_STRING);
    $region = filter_input(INPUT_POST, 'region', FILTER_SANITIZE_STRING);
    $province = filter_input(INPUT_POST, 'province', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $zip_code = filter_input(INPUT_POST, 'zip_code', FILTER_SANITIZE_STRING);
    $address_dtl = filter_input(INPUT_POST, 'address_dtl', FILTER_SANITIZE_STRING);
    
    $password = password_hash('12345', PASSWORD_DEFAULT); // Default password

    // File upload handling
    $picture_path = null;
    $image_binary = null;

    if (isset($_FILES['picture_path']) && $_FILES['picture_path']['error'] === UPLOAD_ERR_OK) {
        $target_dir = '../../uploads/admin_images/';
        $picture_path = $target_dir . basename($_FILES['picture_path']['name']);

        if (move_uploaded_file($_FILES['picture_path']['tmp_name'], $picture_path)) {
            $image_binary = file_get_contents($picture_path);
        } else {
            $message = "Failed to upload image.";
            $type = "error";
            header("Location: ../pages/admin-new-admin.php?message=" . urlencode($message) . "&type=" . urlencode($type));
            exit();
        }
    }

    // Begin transaction
    sqlsrv_begin_transaction($conn);

    try {
        // Validate if RFID already exists
        $sql_check_rfid = "SELECT COUNT(*) AS count FROM Admin WHERE rfid_no = ?";
        $stmt_check_rfid = sqlsrv_query($conn, $sql_check_rfid, [$rfid_no]);
        $row_check_rfid = sqlsrv_fetch_array($stmt_check_rfid, SQLSRV_FETCH_ASSOC);

        if ($row_check_rfid['count'] > 0) {
            throw new Exception("RFID number already exists.");
        }

        // Validate if email already exists
        $sql_check_email = "SELECT COUNT(*) AS count FROM Admin WHERE email = ?";
        $stmt_check_email = sqlsrv_query($conn, $sql_check_email, [$email]);
        $row_check_email = sqlsrv_fetch_array($stmt_check_email, SQLSRV_FETCH_ASSOC);

        if ($row_check_email['count'] > 0) {
            throw new Exception("Email address already used.");
        }

        // Insert into Admins table
        $sql_admin = "INSERT INTO Admin (rfid_no, fname, mname, lname, suffix, sex, dob, phone_no, email, acc_type, picture_path, image_binary) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CONVERT(VARBINARY(MAX), ?))";
        $params_admin = [$rfid_no, $fname, $mname, $lname, $suffix, $sex, $dob, $phone_no, $email, $acc_type, $picture_path, $image_binary];
        $stmt_admin = sqlsrv_query($conn, $sql_admin, $params_admin);

        if ($stmt_admin === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        // Insert into AdminAddresses table
        $sql_address = "INSERT INTO AdminAddresses (region, province, city, zip_code, address_dtl, rfid_no) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $params_address = [$region, $province, $city, $zip_code, $address_dtl, $rfid_no];
        $stmt_address = sqlsrv_query($conn, $sql_address, $params_address);

        if ($stmt_address === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        // Insert into AdminAccount table
        $sql_account = "INSERT INTO AdminAccount (email, password) 
                        VALUES (?, ?)";
        $params_account = [$email, $password];
        $stmt_account = sqlsrv_query($conn, $sql_account, $params_account);

        if ($stmt_account === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        // Commit transaction
        sqlsrv_commit($conn);

        $message = "Admin data successfully added.";
        $type = "success";
    } catch (Exception $e) {
        sqlsrv_rollback($conn); // Rollback transaction
        $message = "Failed to insert data: " . $e->getMessage();
        $type = "error";
    }

    // Redirect with message in URL
    header("Location: ../pages/admin-new-admin.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
