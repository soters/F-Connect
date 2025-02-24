<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Fetch faculty members from the database and sort by last name, then first name
$sql = "SELECT fname, lname, email, picture_path FROM Faculty ORDER BY lname ASC, fname ASC";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Members</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: yellow;
        }

        h2 {
            font-size: 24px;
            color: #333;
        }

        .create-btn {
            background-color: #5a67d8;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .faculty-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .faculty-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .faculty-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .faculty-card h3 {
            font-size: 18px;
            color: #333;
        }

        .faculty-card .email {
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Faculty Members</h2>
    <button class="create-btn">Create</button>
    <div class="faculty-grid">
        <?php while ($faculty = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
            <div class="faculty-card">
                <img src="<?= htmlspecialchars($faculty['picture_path']) ?>" alt="Faculty Image">
                <h3><?= htmlspecialchars($faculty['fname'] . ' ' . $faculty['lname']) ?></h3>
                <p class="email"><?= htmlspecialchars($faculty['email']) ?></p>
                <p>English (US)</p>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>

<?php
// Free the statement and close the connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
