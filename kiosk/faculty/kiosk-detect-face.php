<?php
session_start();
require_once('../../connection/connection.php');

$rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);

// Fetch the image path from the database based on rfid_no
$query = "SELECT image_path FROM FaceData WHERE rfid_no = ?";
$params = array($rfid_no);
$stmt = sqlsrv_query($conn, $query, $params);

// Check if the query executed successfully and fetch the image path
$image_path = null;
if ($stmt) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($row) {
        $image_path = $row['image_path'];
    }
}

// Close the connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

if (!$image_path) {
    // Handle case if no image path is found
    $_SESSION['error_message'] = "Image not found for the provided RFID.";
    header("Location: kiosk-faculty.php");
    exit();
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

    <div id="body-container">
        <p id="action-message-rfid">Position your face on the camera</p>
        <p id="action-message-small">Remain still to ensure accurate recognition</p>
        <br>
        <!-- Video element for live camera feed -->
        <video id="video" width="640" height="480" autoplay muted></video>
        <canvas id="canvas" width="640" height="480"></canvas>
    </div>

    <div id="top-right-button">
        <button type="button" class="small-button" title="Need help?">
            <i class="bi bi-question-lg"></i>
        </button>
    </div>

    <div id="top-left-button">
        <a href="kiosk-faculty.php?rfid_no=<?php echo urlencode($rfid_no); ?>" class="no-underline">
            <button type="button" class="small-button" title="Back">
                <i class="bi bi-arrow-left"></i>
            </button>
        </a>
    </div>

    <footer>
        <p id="collaboration-text">In collaboration with Colegio de Sta. Teresa de Avila</p>
    </footer>

    <div id="noFaceModal" class="modal">
        <div class="modal-content">
            <p>No Match Face Detected. Please try again.</p>
            <button id="modalOkay">Okay</button>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <p>Face Match Successful! Proceeding with attendance.</p>
            <button id="successOkay">Okay</button>
        </div>
    </div>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="../../assets/js/custom-javascript.js"></script>
    <script src="../../assets/js/face-api.min.js"></script>

    <script>
        // Pass the image path dynamically from PHP to JavaScript
        const imagePath = "<?php echo htmlspecialchars($image_path, ENT_QUOTES, 'UTF-8'); ?>";

        // Load face-api.js models
        Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri('../../assets/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('../../assets/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('../../assets/models'),
            faceapi.nets.ageGenderNet.loadFromUri('../../assets/models'),
            faceapi.nets.faceExpressionNet.loadFromUri('../../assets/models')
        ]).then(startVideo);

        // Start video stream from the camera
        async function startVideo() {
            const video = document.getElementById('video');
            const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
            video.srcObject = stream;

            video.onplay = () => {
                detectFaceFromVideo(video);
            };
        }

        // Detect faces from the video stream
        async function detectFaceFromVideo(video) {
            const canvas = document.getElementById('canvas');
            const displaySize = { width: video.width, height: video.height };

            faceapi.matchDimensions(canvas, displaySize);

            const img = await faceapi.fetchImage(imagePath);
            let faceAIData = await faceapi
                .detectAllFaces(video)
                .withFaceLandmarks()
                .withFaceDescriptors();

            if (faceAIData.length === 0) {
                showNoFaceModal();
                console.log("No matching face found.");
                return;
            }

            const labeledFaceDescriptors = await loadLabeledImages(img);
            const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.4);

            let matchFound = false;

            faceAIData.forEach(detection => {
                const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                if (bestMatch.distance <= 0.4) {
                    matchFound = true;
                }
            });

            if (matchFound) {
                showSuccessModal();
                console.log("Matching face found.");
            } else {
                showNoFaceModal();
                console.log("No matching face found.");
            }

            const resizedResults = faceapi.resizeResults(faceAIData, displaySize);
            faceapi.draw.drawDetections(canvas, resizedResults);
        }

        function showNoFaceModal() {
            const noFaceModal = document.getElementById('noFaceModal');
            noFaceModal.style.display = 'block';

            document.getElementById('modalOkay').addEventListener('click', () => {
                location.reload();
            });
        }

        function showSuccessModal() {
            const successModal = document.getElementById('successModal');
            successModal.style.display = 'block';

            document.getElementById('successOkay').addEventListener('click', () => {
                window.location.href = `../functions/insert-attendance.php?rfid_no=${encodeURIComponent("<?php echo $rfid_no; ?>")}`;
            });
        }

        // Load labeled images for comparison
        async function loadLabeledImages(img) {
            const labeledFaceDescriptors = [];
            const labels = ['user']; // Assuming you only have one user, you can add more labels if needed

            const faceDescriptors = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
            if (faceDescriptors) {
                labeledFaceDescriptors.push(new faceapi.LabeledFaceDescriptors(labels[0], [faceDescriptors.descriptor]));
            }

            return labeledFaceDescriptors;
        }
    </script>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            text-align: center;
        }

        #modalOkay {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        #modalOkay:hover {
            background-color: #45a049;
        }

        #successOkay {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        #successOkay:hover {
            background-color: #45a049;
        }
    </style>

</body>

</html>