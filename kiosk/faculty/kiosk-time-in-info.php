<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');

$rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);
$attd_ref = filter_input(INPUT_GET, 'attd_ref', FILTER_SANITIZE_STRING);

try {
    // SQL Query
    $query = "
        SELECT 
            f.rfid_no, 
            f.fname, 
            f.mname, 
            f.lname, 
            f.suffix, 
            f.picture_path,
            atd.date_logged AS attendance_date, 
            atd.time_in, 
            atd.status
        FROM Faculty f
        LEFT JOIN AttendanceToday atd 
        ON f.rfid_no = atd.rfid_no
        WHERE f.rfid_no = ? AND atd.date_logged = CAST(GETDATE() AS DATE)
    ";

    // Prepare and execute the query
    $params = [$rfid_no];
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    // Fetch the result
    $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($result) {
        $name = htmlspecialchars(
            $result['fname'] . ' ' .
            ($result['mname'] ? $result['mname'] . ' ' : '') .
            $result['lname'] .
            ($result['suffix'] ? ', ' . $result['suffix'] : ''),
            ENT_QUOTES,
            'UTF-8'
        );
        $picture = $result['picture_path'] ?: '../../assets/images/Prof.png';
        $attendanceDate = htmlspecialchars($result['attendance_date']->format('Y-m-d'), ENT_QUOTES, 'UTF-8');
        $timeIn = htmlspecialchars($result['time_in'] ? $result['time_in']->format('h:i A') : 'N/A', ENT_QUOTES, 'UTF-8');
        $status = htmlspecialchars($result['status'] ?: 'N/A', ENT_QUOTES, 'UTF-8');
    } else {
        throw new Exception("No attendance data found for today.");
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: kiosk-faculty.php?rfid_no=" . urlencode($rfid_no));
    exit;
} finally {
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="view-transition" content="same-origin" />
    <!-- Custom Links -->
    <link rel="stylesheet" href="../../assets/css/kiosk-design.css" />
    <link rel="shortcut icon" href="../../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
</head>

<body class="fade-out">

    <?php
    if (!empty($_SESSION['error_message'])) {
        echo '<div id="error-message" class="error-message alert alert-danger" role="alert">';
        echo '<i class="bi bi-exclamation-triangle"></i> ';
        echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8');
        echo '</div>';
        echo '<br>';
        unset($_SESSION['error_message']); // Clear the error message after displaying it
    }
    ?>

    <nav class="navi-bar" role="banner">
        <a id="current-time"></a>
        <a id="live-date"></a>
    </nav>

    <div class="t-container">
        <div class="t-card">
            <h2 class="t-main-message">Confirm Time In</h2>
            <p class="t-sub-message">Time in and start timer</p>
            <div class="t-info-box">
                <img src="<?php echo $picture; ?>" alt="Profile Img" class="t-avatar">
                <div class="details">
                    <h3 class="t-name"><?php echo $name; ?></h3>
                    <div class="t-time-info">
                        <div class="t-date">
                            <span class="t-icon">&#x1F4C5;</span>
                            <span><?php echo date('l, d F Y', strtotime($attendanceDate)); ?></span>
                        </div>
                        <div class="t-time">
                            <span><?php echo $timeIn; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <a href="../kiosk-index.php" class="no-underline">
                <button class="t-confirm-button">CONFIRM</button>
            </a>
        </div>
    </div>



    <div id="top-right-button">
        <button type="button" class="small-button" data-bs-toggle="tooltip" title="Need help?" data-bs-placement="left">
            <i class="bi bi-question-lg"></i>
    </div>


    <footer>
        <p id="collaboration-text">In collaboration with Colegio de Sta. Teresa de Avila</p>
    </footer>


    <!-- Scripts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


    <script>
        $(do cument).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
    <script>
                // Automatically hide the error message after 2 seconds
                setT imeout(() => {
                    const errorMessage = document.getElementById('error-message');
                    if (errorMessage) {
                        errorMessage.style.transition = 'opacity 0.5s ease';
                        errorMessage.style.opacity = '0';
                setT imeout(() => {
                            errorMessage.remove(); // Remove the element completely after fade-out
                        }, 500); // Delay to match the fade-out duration
                    }
                }, 3000); // 2 seconds delay before hiding
    </script>


    <script>
        document.querySelectorAll('a.no-underline').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent immediate navigation
                const targetUrl = this.href; // Store the URL

                // Add the 'hidden' class to start the fade-out effect
                document.body.classList.add('hidden');

                // Wait for the transition to complete before navigating
                setTimeout(() => {
                    window.location.href = targetUrl;
                }, 500); // Match the CSS transition duration
            });
        });

    </script>

</body>

</html>