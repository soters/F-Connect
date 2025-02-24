<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $rfid_no = filter_input(INPUT_POST, 'rfid_no', FILTER_SANITIZE_STRING);
    $student_number = filter_input(INPUT_POST, 'student_number', FILTER_SANITIZE_STRING); // Added field
    $fname = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
    $mname = filter_input(INPUT_POST, 'mname', FILTER_SANITIZE_STRING);
    $lname = filter_input(INPUT_POST, 'lname', FILTER_SANITIZE_STRING);
    $suffix = filter_input(INPUT_POST, 'suffix', FILTER_SANITIZE_STRING);
    $sex = filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_STRING);
    $dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING);
    $phone_no = filter_input(INPUT_POST, 'phone_no', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $acc_type = 'Student'; // Set to "Student" or based on your logic
    $section_id = filter_input(INPUT_POST, 'section_id', FILTER_SANITIZE_NUMBER_INT);
    $region = filter_input(INPUT_POST, 'region', FILTER_SANITIZE_STRING);
    $province = filter_input(INPUT_POST, 'province', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $zip_code = filter_input(INPUT_POST, 'zip_code', FILTER_SANITIZE_STRING);
    $address_dtl = filter_input(INPUT_POST, 'address_dtl', FILTER_SANITIZE_STRING);

    // File upload handling
    $picture_path = null;
    $image_binary = null;

    if (isset($_FILES['picture_path']) && $_FILES['picture_path']['error'] === UPLOAD_ERR_OK) {
        $target_dir = '../../uploads/student_images/';
        $picture_path = $target_dir . basename($_FILES['picture_path']['name']);

        // Move uploaded file
        if (move_uploaded_file($_FILES['picture_path']['tmp_name'], $picture_path)) {
            // Read the binary content of the uploaded file
            $image_binary = file_get_contents($picture_path);
        } else {
            $message = "Failed to upload image.";
            $type = "error";
            header("Location: ../pages/admin-new-student.php?message=" . urlencode($message) . "&type=" . urlencode($type));
            exit();
        }
    }

    // Begin transaction
    sqlsrv_begin_transaction($conn);

    try {
        // Validate if RFID already exists
        $sql_check_rfid = "SELECT COUNT(*) AS count FROM Students WHERE rfid_no = ?";
        $stmt_check_rfid = sqlsrv_query($conn, $sql_check_rfid, [$rfid_no]);
        $row_check_rfid = sqlsrv_fetch_array($stmt_check_rfid, SQLSRV_FETCH_ASSOC);

        if ($row_check_rfid['count'] > 0) {
            throw new Exception("RFID number already exists.");
        }

        // Validate if student number already exists
        $sql_check_student_number = "SELECT COUNT(*) AS count FROM Students WHERE student_number = ?";
        $stmt_check_student_number = sqlsrv_query($conn, $sql_check_student_number, [$student_number]);
        $row_check_student_number = sqlsrv_fetch_array($stmt_check_student_number, SQLSRV_FETCH_ASSOC);

        if ($row_check_student_number['count'] > 0) {
            throw new Exception("Student number already exists.");
        }

        // Validate if name combination (fname, mname, lname) already exists
        $sql_check_name = "SELECT COUNT(*) AS count FROM Students WHERE fname = ? AND mname = ? AND lname = ?";
        $stmt_check_name = sqlsrv_query($conn, $sql_check_name, [$fname, $mname, $lname]);
        $row_check_name = sqlsrv_fetch_array($stmt_check_name, SQLSRV_FETCH_ASSOC);

        if ($row_check_name['count'] > 0) {
            throw new Exception("A student with the same name combination already exists.");
        }

        // Validate if email already exists
        $sql_check_email = "SELECT COUNT(*) AS count FROM Students WHERE email = ?";
        $stmt_check_email = sqlsrv_query($conn, $sql_check_email, [$email]);
        $row_check_email = sqlsrv_fetch_array($stmt_check_email, SQLSRV_FETCH_ASSOC);

        if ($row_check_email['count'] > 0) {
            throw new Exception("Email address already used.");
        }

        // Insert into Students table
        $sql_student = "INSERT INTO Students (rfid_no, student_number, fname, mname, lname, suffix, sex, dob, phone_no, email, acc_type, picture_path, image_binary, section_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CONVERT(VARBINARY(MAX), ?), ?)";
        $params_student = [$rfid_no, $student_number, $fname, $mname, $lname, $suffix, $sex, $dob, $phone_no, $email, $acc_type, $picture_path, $image_binary, $section_id];
        $stmt_student = sqlsrv_query($conn, $sql_student, $params_student);

        if ($stmt_student === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        // Insert into StudentAddresses table
        $sql_address = "INSERT INTO StudentAddresses (region, province, city, brgy, zip_code, address_dtl, rfid_no) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params_address = [$region, $province, $city, $brgy, $zip_code, $address_dtl, $rfid_no];
        $stmt_address = sqlsrv_query($conn, $sql_address, $params_address);

        if ($stmt_address === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        // Commit transaction
        sqlsrv_commit($conn);

        $message = "Student data successfully added.";
        $type = "success";
    } catch (Exception $e) {
        sqlsrv_rollback($conn); // Rollback transaction
        $message = "Failed to insert data: " . $e->getMessage();
        $type = "error";
    }

    // Redirect with message in URL
    header("Location: ../pages/admin-new-student.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
