<?php
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
    <link rel="stylesheet" href="../../assets/css/kiosk-design.css" />
    <link rel="shortcut icon" href="../../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
    <title>Register Labeled Images</title>
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


    <div id="u-action-message-container">
        <p id="action-message-rfid">Register Your Facial Data</p>
        <p id="action-message-small">Position your face in front of the camera and touch "Capture"</p>
    </div>

    <div id="body-container">
        <video id="video" width="540" height="400" autoplay></video>
        <div class="preview-container" id="previewContainer"></div>
        <canvas id="canvas" width="540" height="400"></canvas>
        <div class="button-container">
            <button class="up-button" id="captureButton">Capture</button>
            <button class="up-button" id="saveButton" disabled>Upload Images</button>
            <button class="up-button" id="retakeButton" disabled>Retake</button>
        </div>
        <div class="preview-container" id="previewContainer"></div>
        <p id="status"></p>
        <!--<p id="message" class="message">You can only capture up to 3 photos.</p> -->
    </div>
    <div id="top-right-button">
        <button type="button" class="small-button" data-bs-toggle="tooltip" title="Need help?" data-bs-placement="left">
            <i class="bi bi-question-lg"></i>
    </div>

    <div id="top-left-button">
        <a href="kiosk-reminder-3.php?rfid_no=<?php echo urlencode($rfid_no); ?>" class="no-underline">
            <button type="button" class="small-button" data-bs-toggle="tooltip" title="Back" data-bs-placement="right">
                <i class="bi bi-arrow-left"></i>
            </button>
        </a>
    </div>

    <footer>
        <p id="collaboration-text">In collaboration with Colegio de Sta. Teresa de Avila</p>
    </footer>

    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to save the captured images?</p>
            <button id="confirmYes">Yes</button>
            <button id="confirmNo">No</button>
        </div>
    </div>

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

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureButton = document.getElementById('captureButton');
        const saveButton = document.getElementById('saveButton');
        const retakeButton = document.getElementById('retakeButton');
        const previewContainer = document.getElementById('previewContainer');
        const status = document.getElementById('status');
        const message = document.getElementById('message');
        const confirmModal = document.getElementById('confirmModal');
        const confirmYes = document.getElementById('confirmYes');
        const confirmNo = document.getElementById('confirmNo');
        let capturedImages = [];

        // Start video stream
        navigator.getUserMedia(
            { video: {} },
            stream => video.srcObject = stream,
            err => console.error(err)
        );

        captureButton.addEventListener('click', async () => {
            if (capturedImages.length >= 3) {
                message.style.display = 'block';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 3000);
                return;
            }

            status.textContent = "Capturing 3 photos...";

            for (let i = 0; i < 3; i++) {
                await new Promise(resolve => setTimeout(resolve, 1000)); // Wait for 1 second between captures

                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = canvas.toDataURL('image/jpeg');
                capturedImages.push(imageData);

                updatePreview();

                status.textContent = `Captured ${capturedImages.length} images.`;
                if (capturedImages.length >= 3) break; // Stop capturing after 3 images
            }

            saveButton.disabled = capturedImages.length < 3; // Enable save after 3 captures
            retakeButton.disabled = capturedImages.length === 0; // Enable retake if there are images

            if (capturedImages.length === 3) {
                status.textContent = "3 photos captured successfully!";
            }
        });


        retakeButton.addEventListener('click', async () => {
            if (capturedImages.length === 0) {
                status.textContent = "No images to retake.";
                return;
            }

            // Clear previously captured images
            capturedImages = [];
            updatePreview();

            status.textContent = "Retaking 3 photos...";

            for (let i = 0; i < 3; i++) {
                await new Promise(resolve => setTimeout(resolve, 1000)); // Wait for 1 second between captures

                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = canvas.toDataURL('image/jpeg');
                capturedImages.push(imageData);

                updatePreview();

                status.textContent = `Captured ${capturedImages.length} images.`;
            }

            saveButton.disabled = capturedImages.length < 3; // Enable save after 3 captures
            retakeButton.disabled = capturedImages.length === 0; // Enable retake if there are images

            if (capturedImages.length === 3) {
                status.textContent = "3 photos retaken successfully!";
            }
        });


        saveButton.addEventListener('click', () => {
            confirmModal.style.display = 'block';
        });

        confirmYes.addEventListener('click', () => {
            confirmModal.style.display = 'none';

            const rfid_no = "<?php echo $rfid_no; ?>";
            if (!rfid_no) {
                alert("RFID number is required.");
                return;
            }

            fetch('../functions/save-face-data.php', {
                method: 'POST',
                body: JSON.stringify({ rfid_no, images: capturedImages }),
                headers: { 'Content-Type': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to the success page
                        window.location.href = `kiosk-upload-success.php?rfid_no=${encodeURIComponent(rfid_no)}`;
                    } else {
                        status.textContent = "Error saving images.";
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        confirmNo.addEventListener('click', () => {
            confirmModal.style.display = 'none';
        });

        function updatePreview() {
            previewContainer.innerHTML = '';
            capturedImages.forEach((image, index) => {
                const imgElement = document.createElement('img');
                imgElement.src = image;
                imgElement.alt = `Captured Image ${index + 1}`;
                previewContainer.appendChild(imgElement);
            });
        }
    </script>

</body>

</html>