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

    // Address fields
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $zip_code = $_POST['zip_code'];
    $address_dtl = $_POST['address_dtl'];

    // Handle picture upload and image binary
    $picture_path = null;
    $image_binary = null;
    if (isset($_FILES['picture_path']) && $_FILES['picture_path']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../../uploads/admin_images";
        $file_name = basename($_FILES['picture_path']['name']);
        $target_file = $target_dir . $file_name;

        // Check if the directory exists, create if it doesn't
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        if (move_uploaded_file($_FILES['picture_path']['tmp_name'], $target_file)) {
            $picture_path = $target_file;
            $image_binary = file_get_contents($target_file); // Read file as binary
        }
    }

    // Update Admin table
    $sqlUpdateAdmin = "
UPDATE Admin 
SET fname = ?, 
    mname = ?, 
    lname = ?, 
    suffix = ?, 
    sex = ?, 
    phone_no = ?, 
    dob = ?, 
    email = ?,  
    picture_path = ISNULL(?, picture_path),
    image_binary = ISNULL(CONVERT(VARBINARY(MAX), ?), image_binary),
    acc_type = ?
WHERE rfid_no = ?
";

    $paramsAdmin = [
        $fname,
        $mname,
        $lname,
        $suffix,
        $sex,
        $phone_no,
        $dob,
        $email,
        $picture_path, // Path to the uploaded image
        $image_binary, // Binary image data
        $acc_type,
        $rfid_no
    ];

    $stmtAdmin = sqlsrv_query($conn, $sqlUpdateAdmin, $paramsAdmin);

    if ($stmtAdmin === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Update AdminAddresses table
    $sqlUpdateAddress = "
        UPDATE AdminAddresses 
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

    // Redirect to the admin page with success message
    $message = "Admin updated successfully!";
    $type = "success";
    header("Location: ../pages/admin-manage.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
} else {
    // Redirect if the request method is not POST
    $message = "Invalid request method!";
    $type = "error";
    header("Location: ../pages/admin-manage.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>