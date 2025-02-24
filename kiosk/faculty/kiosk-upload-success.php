<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');

$rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="view-transition" content="same-origin" />
    <!-- Custom Links -->
    <link rel="stylesheet" href="../../assets/css/kiosk-design.css"/>
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

    <div id="body-container">
        <p id="action-message-rfid">Facial data successfully saved!</p>
        <p id="action-message-small">You can now use the Time In/Out functionality.</p>

        <br>
        <div class="action-box-s">
            <a href="kiosk-faculty.php?rfid_no=<?php echo $rfid_no; ?>" class="no-underline">
                <button class="buttonDesign">
                    Proceed
                </button>
            </a>
        </div>


        <div id="top-right-button">
            <button type="button" class="small-button" data-bs-toggle="tooltip" title="Need help?"
                data-bs-placement="left">
                <i class="bi bi-question-lg"></i>
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