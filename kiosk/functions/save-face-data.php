<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['rfid_no']) || !isset($data['images'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data.']);
        exit;
    }

    $rfid_no = $data['rfid_no'];
    $images = $data['images'];
    $directory = "../labeled_images/{$rfid_no}";

    // Create directory for the faculty if it doesn't exist
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }

    // Include your database connection file
    require_once('../../connection/connection.php');

    try {
        foreach ($images as $index => $imageData) {
            $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
            $imageData = base64_decode($imageData);
            $fileName = "image_{$index}.jpg";
            $filePath = "{$directory}/{$fileName}";

            if (!file_put_contents($filePath, $imageData)) {
                echo json_encode(['success' => false, 'message' => 'Failed to save image.']);
                exit;
            }

            // Insert the image path into the FaceData table
            $sql = "INSERT INTO FaceData (rfid_no, image_path, upload_date, upload_time) 
                    VALUES (?, ?, GETDATE(), CONVERT(TIME, GETDATE()))";
            $params = array($rfid_no, $filePath);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                echo json_encode(['success' => false, 'message' => 'Database error.']);
                die(print_r(sqlsrv_errors(), true));
            }

            // Optional: Log successful insert
            error_log("Image path saved: {$filePath}");
        }

        echo json_encode(['success' => true, 'message' => 'Images saved successfully.']);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}
?>
