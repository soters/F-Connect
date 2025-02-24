<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted form data
    $rfid_no = $_POST['rfid_no'];
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $suffix = $_POST['suffix'];
    $sex = $_POST['sex'];
    $phone_no = $_POST['phone_no'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $acc_type = $_POST['acc_type'];
    $dept_id = $_POST['dept_id'];

    // Address fields
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $zip_code = $_POST['zip_code'];
    $address_dtl = $_POST['address_dtl'];

    // Validation
    $errors = [];

    // RFID Validation: Check if rfid_no already exists in the Faculty table
    $sqlCheckRFID = "SELECT * FROM Faculty WHERE rfid_no = ?";
    $paramsRFID = [$rfid_no];
    $stmtRFID = sqlsrv_query($conn, $sqlCheckRFID, $paramsRFID);

    if ($stmtRFID === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $rowRFID = sqlsrv_fetch_array($stmtRFID, SQLSRV_FETCH_ASSOC);
    if (!$rowRFID) {
        $errors[] = "RFID number does not exist in the Faculty table.";
    }

    // Role Validation: Ensure only one Program Chair or Dean per department
    if ($acc_type == 'Program Chair' || $acc_type == 'Dean') {
        // Get the current faculty's acc_type and dept_id from the Faculty table and UserDepartment table
        $sqlCheckExistingRole = "
        SELECT f.acc_type
        FROM Faculty f
        JOIN UserDepartment ud ON f.rfid_no = ud.rfid_no
        WHERE ud.dept_id = ? AND f.acc_type IN ('Program Chair', 'Dean') AND f.rfid_no != ?
    ";
        $paramsExistingRole = [$dept_id, $rfid_no];
        $stmtExistingRole = sqlsrv_query($conn, $sqlCheckExistingRole, $paramsExistingRole);

        if ($stmtExistingRole === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $existingRole = sqlsrv_fetch_array($stmtExistingRole, SQLSRV_FETCH_ASSOC);
        if ($existingRole) {
            $errors[] = "Only one $acc_type is allowed.";
        }
    }


    // If there are errors, redirect with the error message
    if (count($errors) > 0) {
        $message = implode(" ", $errors);
        $type = "error";
        header("Location: ../pages/admin-faculty.php?message=" . urlencode($message) . "&type=" . urlencode($type));
        exit();
    }

    // Handle picture upload
    $picture_path = null;
    if (isset($_FILES['picture_path']) && $_FILES['picture_path']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../../uploads/faculty_images/";
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

    // Update Faculty table
    $sqlUpdateFaculty = "
        UPDATE Faculty 
        SET fname = ?, 
            mname = ?, 
            lname = ?, 
            suffix = ?, 
            sex = ?, 
            phone_no = ?, 
            dob = ?, 
            email = ?, 
            acc_type = ?, 
            picture_path = ISNULL(?, picture_path)
        WHERE rfid_no = ?";
    $paramsFaculty = [
        $fname,
        $mname,
        $lname,
        $suffix,
        $sex,
        $phone_no,
        $dob,
        $email,
        $acc_type,
        $picture_path,
        $rfid_no
    ];

    $stmtFaculty = sqlsrv_query($conn, $sqlUpdateFaculty, $paramsFaculty);

    if ($stmtFaculty === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Update FacultyAddress table
    $sqlUpdateAddress = "
        UPDATE FacultyAdresses 
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

    // Update UserDepartment table
    $sqlUpdateDepartment = "
        UPDATE UserDepartment 
        SET dept_id = ? 
        WHERE rfid_no = ?";
    $paramsDepartment = [$dept_id, $rfid_no];

    $stmtDepartment = sqlsrv_query($conn, $sqlUpdateDepartment, $paramsDepartment);

    if ($stmtDepartment === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Redirect to the admin-faculty page with a success message
    $message = "Faculty updated successfully!";
    $type = "success";
    header("Location: ../pages/admin-faculty.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
} else {
    // Redirect if the request method is not POST
    $message = "Invalid request method!";
    $type = "error";
    header("Location: ../pages/admin-faculty.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>