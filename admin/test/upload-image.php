<?php
include('../../connection/connection.php'); // Ensure this contains SQLSRV connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["picture_path"])) {
    $file = $_FILES["picture_path"];

    if ($file["error"] !== 0) {
        die("File upload error: " . $file["error"]);
    }

    // Get file details
    $fileName = basename($file["name"]);
    $uploadDir = "../../uploads/";  // Adjust the upload directory
    $uploadPath = $uploadDir . $fileName;
    
    // Move file to server directory
    if (!move_uploaded_file($file["tmp_name"], $uploadPath)) {
        die("Error moving uploaded file.");
    }

    // Convert image to binary data
    $imageData = file_get_contents($uploadPath);

    // SQL statement with CAST to explicitly convert binary data
    $sql = "INSERT INTO TestImage (picture_path, image_binary) VALUES (?, CAST(? AS VARBINARY(MAX)))";
    $params = array($uploadPath, $imageData);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        echo "Image uploaded and stored successfully!";
    } else {
        echo "Error uploading image: " . print_r(sqlsrv_errors(), true);
    }
} else {
    echo "No file uploaded.";
}
?>
