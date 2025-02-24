<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');

// Retrieve the RFID ID from POST data and sanitize it
$rfid_id = filter_input(INPUT_POST, 'rfid_id', FILTER_SANITIZE_STRING);

if (!$rfid_id) {
    // No RFID ID provided
    setErrorAndRedirect('Please try again, RFID does not exist.');
}

try {
    // Check if RFID exists in the student table
    $query = "SELECT TOP 1 1 FROM Students WHERE rfid_no = ?";
    $stmt = sqlsrv_prepare($conn, $query, array(&$rfid_id));
    
    if (sqlsrv_execute($stmt)) {
        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
        if ($result) {
            // If found in the student table, redirect to kiosk-student.php
            setPageRedirect("../student/kiosk-student.php?rfid_no=" . urlencode($rfid_id));
            exit;
        }
    }

    // Check if RFID exists in the faculty table
    $query = "SELECT TOP 1 1 FROM Faculty WHERE rfid_no = ?";
    $stmt = sqlsrv_prepare($conn, $query, array(&$rfid_id));
    
    if (sqlsrv_execute($stmt)) {
        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
        if ($result) {
            // If found in the faculty table, redirect to kiosk-faculty.php
            setPageRedirect("../faculty/kiosk-faculty.php?rfid_no=" . urlencode($rfid_id));
            exit;
        }
    }

    // RFID not found in either table
    setErrorAndRedirect('RFID does not exist in either the student or faculty database.');

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
    setPageRedirect('../kiosk-index.php');
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
    // Send the HTML for the loading animation with background color #2B5876
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
