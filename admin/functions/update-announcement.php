<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get announcement ID (for update)
    $announcement_id = isset($_POST["announcement_id"]) ? $_POST["announcement_id"] : null;

    // Sanitize inputs
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $date = $_POST["date"];

    // Check if required fields are empty
    if (empty($title) || empty($content) || empty($date)) {
        header("Location: ../pages/admin-announcement.php?message=" . urlencode("Please fill in all required fields.") . "&type=error");
        exit();
    }

    // Initialize variables for file upload
    $imagePath = NULL;
    $imageBinary = NULL;
    $uploadDir = "../../uploads/announcement_images/";

    // Ensure upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle file upload if a new file is provided
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

        // Generate unique file name and move uploaded file
        $newFilePath = $uploadDir . uniqid() . "_" . $fileName;
        if (move_uploaded_file($fileTmpPath, $newFilePath)) {
            $imagePath = $newFilePath;
            $imageBinary = file_get_contents($newFilePath);
        } else {
            header("Location: ../pages/admin-announcement.php?message=" . urlencode("Failed to upload image.") . "&type=error");
            exit();
        }
    }

    // If updating an announcement
    if ($announcement_id) {
        // Get the existing image path if no new image is uploaded
        if (!$imagePath) {
            $query = "SELECT picture_path FROM Announcement WHERE announcement_id = ?";
            $stmt = sqlsrv_query($conn, $query, [$announcement_id]);
            if ($stmt !== false && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $imagePath = $row['picture_path'];
            }
            sqlsrv_free_stmt($stmt);
        }

        // Update announcement
        $sql = "UPDATE Announcement 
                SET title = ?, content = ?, date = ?, picture_path = ?, image = COALESCE(CONVERT(VARBINARY(MAX), ?), image)
                WHERE announcement_id = ?";
        $params = [$title, $content, $date, $imagePath, $imageBinary, $announcement_id];
    } else {
        // Insert new announcement
        $sql = "INSERT INTO Announcement (title, content, date, picture_path, image) 
                VALUES (?, ?, ?, ?, CONVERT(VARBINARY(MAX), ?))";
        $params = [$title, $content, $date, $imagePath, $imageBinary];
    }

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        $message = $announcement_id ? "Announcement updated successfully." : "Announcement updated successfully.";
        $type = "success";
    } else {
        $message = "Database error: " . print_r(sqlsrv_errors(), true);
        $type = "error";
    }

    // Redirect back to the announcements page
    header("Location: ../pages/admin-announcement.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
