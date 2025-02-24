<?php
session_start();
require_once '../../connection/connection.php';

$rfid_no = $_GET['rfid_no'] ?? null;
$attd_ref = $_GET['attd_ref'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choice = $_POST['choice'];

    if ($choice === 'yes') {
        // Update AttendanceToday with time_out
        $current_time = date('H:i:s');
        $update_query = "
            UPDATE AttendanceToday
            SET time_out = ?
            WHERE attd_ref = ?
        ";
        $update_params = [$current_time, $attd_ref];
        $update_stmt = sqlsrv_query($conn, $update_query, $update_params);

        if ($update_stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // Update appointments
        $current_date = date('Y-m-d');
        $update_appointments_query = "
            UPDATE Appointments
            SET status = 'Canceled'
            WHERE prof_rfid_no = ? 
              AND date_logged = ?
              AND status IN ('Pending', 'Accepted')
        ";
        $update_appointments_params = [$rfid_no, $current_date];
        $update_appointments_stmt = sqlsrv_query($conn, $update_appointments_query, $update_appointments_params);

        if ($update_appointments_stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // Close the statements and connection
        sqlsrv_free_stmt($update_stmt);
        sqlsrv_free_stmt($update_appointments_stmt);
        sqlsrv_close($conn);

        // Redirect to success page
        header("Location: ../faculty/kiosk-time-out-info.php?rfid_no=" . urlencode($rfid_no) . "&attd_ref=" . urlencode($attd_ref));
        exit();
    } else {
        // Redirect back to the kiosk page
        header("Location: ../faculty/kiosk-faculty.php?rfid_no=" . urlencode($rfid_no));
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
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

    <p id="action-message-medium">Are you sure you want to time out before your scheduled end time?</p>
<div class="action-box-small">
    <form method="POST">
        <a class="no-underline"><button type="submit" class="yesBtn1" name="choice" value="yes">Yes</button></a>
        <br>
    <a class="no-underline"><button type="submit" class="noBtn1" name="choice" value="no">No</button></a>
       
    </form>
</div>

    <div id="top-right-button">
            <button type="button" class="small-button" data-bs-toggle="tooltip" title="Need help?"
                data-bs-placement="left">
                <i class="bi bi-question-lg"></i>
        </div>

        <div id="top-left-button">
            <a href="../kiosk-index.php" class="no-underline">
                <button type="button" class="small-button" data-bs-toggle="tooltip" title="Back"
                    data-bs-placement="right">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </a>
        </div>

    <footer>
            <p id="collaboration-text">In collaboration with Colegio de Sta. Teresa de Avila</p>
        </footer>

        <!-- Scripts -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script type="text/javascript" src="../../assets/js/custom-javascript.js"></script>

        <script>
            $(document).ready(function () {
                $('[data-bs-toggle="tooltip"]').tooltip();
            });
        </script>
        <script>
            // Automatically hide the error message after 2 seconds
            setTimeout(() => {
                const errorMessage = document.getElementById('error-message');
                if (errorMessage) {
                    errorMessage.style.transition = 'opacity 0.5s ease';
                    errorMessage.style.opacity = '0';
                    setTimeout(() => {
                        errorMessage.remove(); // Remove the element completely after fade-out
                    }, 500); // Delay to match the fade-out duration
                }
            }, 3000); // 2 seconds delay before hiding
        </script>

    </body>
</html>
