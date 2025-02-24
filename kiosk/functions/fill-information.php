<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');


// Retrieve the RFID ID from POST data and sanitize it
$rfid_id = filter_input(INPUT_POST, 'rfid_id', FILTER_SANITIZE_STRING);

if (!$rfid_id) {
    // No RFID ID provided
    setErrorAndRedirect('Please try again, RFID does not exist.');
}

try {
    // Check if RFID exists in the student table
    $query = "SELECT rfid_no FROM Students WHERE rfid_no = ?";
    $params = [$rfid_id];
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($result) {
        // If found in the student table, redirect to kiosk-student-info.php with RFID
        setPageRedirect('../student/kiosk-personal-info.php?rfid_no=' . urlencode($rfid_id));
        exit;
    }

    // RFID not found in the student table
    setErrorAndRedirect('RFID does not exist in the student database.');

} catch (Exception $e) {
    // Log the error for debugging and redirect with a generic message
    error_log("Database Error: " . $e->getMessage());
    setErrorAndRedirect('An error occurred. Please contact the administrator.');
}

/**
 * Sets an error message in the session and redirects to the kiosk index page.
 *
 * @param string $message
 * @return void
 */
function setErrorAndRedirect(string $message): void
{
    $_SESSION['error_message'] = $message;
    setPageRedirect('../student/kiosk-rfid.php');
    exit;
}

/**
 * Redirects the page with a loading spinner and background color #2B5876.
 *
 * @param string $location
 * @return void
 */
function setPageRedirect(string $location): void
{
    echo '
    <html>
    <head>
        <style>
            body {
                background-color: #2B5876; /* Set the background color */
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                overflow: hidden;
                position: relative;
            }

            .loading-spinner {
                border: 4px solid #f3f3f3; /* Light gray */
                border-top: 4px solid #3498db; /* Blue */
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

        </style>
    </head>
    <body onload="redirectAfterLoading()">
        <div class="loading-spinner"></div> <!-- Loading spinner -->
        <script>
            function redirectAfterLoading() {
                setTimeout(function() {
                    window.location.href = "' . $location . '";
                }, 700); // Wait for 0.7 seconds before redirect
            }
        </script>
    </body>
    </html>
    ';
}
?>
