<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted form data
    $rfid_no = $_POST['rfid_no'];
    $student_number = $_POST['student_number'];
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $suffix = $_POST['suffix'];
    $sex = $_POST['sex'];
    $phone_no = $_POST['phone_no'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $section_id = $_POST['section_id'];

    // Address fields
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $zip_code = $_POST['zip_code'];
    $address_dtl = $_POST['address_dtl'];

    // Validation
    $errors = [];

    // RFID Validation: Check if rfid_no exists in the Students table
    $sqlCheckRFID = "SELECT * FROM Students WHERE rfid_no = ?";
    $paramsRFID = [$rfid_no];
    $stmtRFID = sqlsrv_query($conn, $sqlCheckRFID, $paramsRFID);

    if ($stmtRFID === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (!sqlsrv_fetch_array($stmtRFID, SQLSRV_FETCH_ASSOC)) {
        $errors[] = "RFID number does not exist in the Students table.";
    }

    // Student Number Validation: Check if student_number exists
    $sqlCheckStudentNumber = "SELECT * FROM Students WHERE student_number = ? AND rfid_no != ?";
    $paramsStudentNumber = [$student_number, $rfid_no];
    $stmtStudentNumber = sqlsrv_query($conn, $sqlCheckStudentNumber, $paramsStudentNumber);

    if ($stmtStudentNumber === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_fetch_array($stmtStudentNumber, SQLSRV_FETCH_ASSOC)) {
        $errors[] = "Student number already exists.";
    }

    // Name Combination Validation
    $sqlCheckName = "SELECT * FROM Students WHERE fname = ? AND mname = ? AND lname = ? AND suffix = ? AND rfid_no != ?";
    $paramsName = [$fname, $mname, $lname, $suffix, $rfid_no];
    $stmtName = sqlsrv_query($conn, $sqlCheckName, $paramsName);

    if ($stmtName === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_fetch_array($stmtName, SQLSRV_FETCH_ASSOC)) {
        $errors[] = "A student with the same name combination already exists.";
    }

    // Email Validation
    $sqlCheckEmail = "SELECT * FROM Students WHERE email = ? AND rfid_no != ?";
    $paramsEmail = [$email, $rfid_no];
    $stmtEmail = sqlsrv_query($conn, $sqlCheckEmail, $paramsEmail);

    if ($stmtEmail === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_fetch_array($stmtEmail, SQLSRV_FETCH_ASSOC)) {
        $errors[] = "Email address already used.";
    }

    // If there are validation errors, redirect back with error messages
    if (!empty($errors)) {
        $message = implode(" ", $errors);
        $type = "error";
        header("Location: ../pages/admin-student.php?message=" . urlencode($message) . "&type=" . urlencode($type));
        exit();
    }

    // Handle picture upload
    $picture_path = null;
    if (isset($_FILES['picture_path']) && $_FILES['picture_path']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../../uploads/student_images/";
        $file_name = basename($_FILES['picture_path']['name']);
        $target_file = $target_dir . $file_name;

        // Check if the directory exists, create if it doesn't
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        if (move_uploaded_file($_FILES['picture_path']['tmp_name'], $target_file)) {
            $picture_path = $target_file;
        }
    }

    // Update Students table
    $sqlUpdateStudents = "
        UPDATE Students 
        SET student_number = ?, 
            fname = ?, 
            mname = ?, 
            lname = ?, 
            suffix = ?, 
            sex = ?, 
            phone_no = ?, 
            dob = ?, 
            email = ?,  
            picture_path = ISNULL(?, picture_path),
            section_id = ?
        WHERE rfid_no = ?";
    $paramsStudents = [
        $student_number,
        $fname,
        $mname,
        $lname,
        $suffix,
        $sex,
        $phone_no,
        $dob,
        $email,
        $picture_path,
        $section_id,
        $rfid_no
    ];

    $stmtStudents = sqlsrv_query($conn, $sqlUpdateStudents, $paramsStudents);

    if ($stmtStudents === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Update StudentAddresses table
    $sqlUpdateAddress = "
        UPDATE StudentAddresses 
        SET region = ?, 
            province = ?, 
            city = ?, 
            zip_code = ?, 
            address_dtl = ? 
        WHERE rfid_no = ?";
    $paramsAddress = [$region, $province, $city, $zip_code, $address_dtl, $rfid_no];

    $stmtAddress = sqlsrv_query($conn, $sqlUpdateAddress, $paramsAddress);

    if ($stmtAddress === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Redirect to the admin-students page with a success message
    $message = "Student updated successfully!";
    $type = "success";
    header("Location: ../pages/admin-student.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
} else {
    // Redirect if the request method is not POST
    $message = "Invalid request method!";
    $type = "error";
    header("Location: ../pages/admin-student.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
