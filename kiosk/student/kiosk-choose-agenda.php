<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');


// Retrieve hidden input values from the form submission
$selected_rfid = filter_input(INPUT_POST, 'selected_rfid', FILTER_SANITIZE_STRING);
$stud_rf = filter_input(INPUT_POST, 'stud_rf', FILTER_SANITIZE_STRING);
$start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
$end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
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

    <!-- Display Error Message -->
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="error-message alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            <?= htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <nav class="navi-bar" role="banner">
        <a id="current-time"></a>
        <a id="live-date"></a>
    </nav>

    <div id="appointment-container">
        <p id="action-message-info">Pick an Agenda</p>
        <i>
            <p id="action-message-info-small">For your appointment</p>
        </i>
        <br>

        <!-- Faculty Selection Form -->
        <form action="kiosk-summary.php" method="POST">
            <div class="agenda-cards">
                <label>
                    <input type="radio" name="selected_agenda" value="Project/Research Discussion" required>
                    <div class="agenda-card">
                        <div class="agenda-logo">
                            <i class="bi bi-clipboard-check agenda-logo"></i>
                        </div>
                        <div class="info">
                            <p class="agenda-name">Project/Research Discussion</p>
                        </div>
                    </div>
                </label>
                <label>
                    <input type="radio" name="selected_agenda" value="Mentorship" required>
                    <div class="agenda-card">
                        <div class="agenda-logo">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="info">
                            <p class="agenda-name">Mentorship</p>
                        </div>
                    </div>
                </label>
                <label>
                    <input type="radio" name="selected_agenda" value="Internship or Practical Experience Advice"
                        required>
                    <div class="agenda-card">
                        <div class="agenda-logo">
                            <i class="bi bi-person-video3"></i>
                        </div>
                        <div class="info">
                            <p class="agenda-name">Internship or Practical Experience Advice</p>
                        </div>
                    </div>
                </label>
                <label>
                    <input type="radio" name="selected_agenda" value="Personal Academic Concerns" required>
                    <div class="agenda-card">
                        <div class="agenda-logo">
                            <i class="bi bi-mortarboard"></i>
                        </div>
                        <div class="info">
                            <p class="agenda-name">Personal Academic Concerns</p>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Hidden Inputs to pass the values -->
            <input type="hidden" name="selected_rfid" value="<?= htmlspecialchars($selected_rfid); ?>">
            <input type="hidden" name="stud_rf" value="<?= htmlspecialchars($stud_rf); ?>">
            <input type="hidden" name="start_time" value="<?= htmlspecialchars($start_time); ?>">
            <input type="hidden" name="end_time" value="<?= htmlspecialchars($end_time); ?>">

            <!-- Submit Button -->
            <button class="appoint-btn" type="submit">
                <span class="btn-text">NEXT</span>
            </button>
        </form>

    </div>

    <div id="top-right-button">
        <button type="button" class="small-button" data-bs-toggle="tooltip" title="Need help?" data-bs-placement="left">
            <i class="bi bi-question-lg"></i>
        </button>
    </div>
    
    <div id="top-left-button">
        <a href="kiosk-student.php" class="no-underline">
            <button type="button" class="small-button" data-bs-toggle="tooltip" title="Back" data-bs-placement="right">
                <i class="bi bi-arrow-left-short"></i>
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