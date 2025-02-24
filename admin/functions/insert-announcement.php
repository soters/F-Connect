<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $date = $_POST["date"];

    // Check if required fields are empty
    if (empty($title) || empty($content) || empty($date)) {
        header("Location: ../pages/admin-announcement.php?message=" . urlencode("Please fill in all required fields.") . "&type=error");
        exit();
    }

    // Handle file upload
    $imagePath = NULL;
    $imageBinary = NULL;

    if (isset($_FILES["picture_path"]) && $_FILES["picture_path"]["error"] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES["picture_path"]["tmp_name"];
        $fileName = basename($_FILES["picture_path"]["name"]);
        $fileSize = $_FILES["picture_path"]["size"];
        $fileType = $_FILES["picture_path"]["type"];
        $allowedTypes = ["image/jpeg", "image/png", "image/gif"];

        if (!in_array($fileType, $allowedTypes)) {
            header("Location: ../pages/admin-announcement.php?message=" . urlencode("Invalid file type. Only JPG, PNG, and GIF allowed.") . "&type=error");
            exit();
        }

        if ($fileSize > 2 * 1024 * 1024) { // 2MB limit
            header("Location: ../pages/admin-announcement.php?message=" . urlencode("File size exceeds 2MB limit.") . "&type=error");
            exit();
        }

        // Move uploaded file
        $uploadDir = "../../uploads/announcement_images/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newFilePath = $uploadDir . uniqid() . "_" . $fileName;
        if (move_uploaded_file($fileTmpPath, $newFilePath)) {
            $imagePath = $newFilePath;
            $imageBinary = file_get_contents($newFilePath); // Read binary data
        } else {
            header("Location: ../pages/admin-announcement.php?message=" . urlencode("Failed to upload image.") . "&type=error");
            exit();
        }
    }

    // Insert into database
    $sql = "INSERT INTO Announcement (title, content, date, picture_path, image) 
            VALUES (?, ?, ?, ?, CONVERT(VARBINARY(MAX), ?))";

    $params = [$title, $content, $date, $imagePath, $imageBinary];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        $message = "Announcement added successfully.";
        $type = "success";
    } else {
        $message = "Failed to insert announcement: " . print_r(sqlsrv_errors(), true);
        $type = "error";
    }

    header("Location: ../pages/admin-announcement.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}

?>
