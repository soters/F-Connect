<?php
include('../../connection/connection.php'); // Ensure this contains SQLSRV connection

$sql = "SELECT image_id, picture_path, image_binary FROM TestImage";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die("Error retrieving images: " . print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Images</title>
</head>
<body>
    <h2>Stored Images</h2>
    <div style="display: flex; flex-wrap: wrap;">
        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
            <div style="margin: 10px; text-align: center;">
                <p>Image ID: <?= $row['image_id'] ?></p>

                <!-- Display Image from File Path -->
                <?php if (!empty($row['picture_path'])): ?>
                    <p>From Path:</p>
                    <img src="<?= $row['picture_path'] ?>" width="150" height="150">
                <?php endif; ?>

                <!-- Display Image from Binary Data -->
                <?php if (!empty($row['image_binary'])): ?>
                    <p>From Binary:</p>
                    <img src="data:image/jpeg;base64,<?= base64_encode($row['image_binary']) ?>" width="150" height="150">
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
