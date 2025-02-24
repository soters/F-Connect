<?php
session_start();
require_once '../../connection/connection.php'; // Include your existing connection file
date_default_timezone_set('Asia/Manila');

// Get room_id from the GET request
$room_id = filter_input(INPUT_GET, 'room_id', FILTER_SANITIZE_STRING);
$rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);
$floor = 1; // Fixed for Floor 1

// Fetch the selected room
function getSelectedRoom($room_id)
{
    global $conn;
    $query = "SELECT room_name, floor, type, x_coord, y_coord, room_id 
              FROM Locations 
              WHERE room_id = ? AND floor = 1 AND x_coord IS NOT NULL AND y_coord IS NOT NULL";
    $params = [$room_id];
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $selectedRoom = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);

    return $selectedRoom;
}


// Fetch other rooms
function getOtherRooms($floor, $room_id)
{
    global $conn;
    $query = "SELECT room_name, floor, type, x_coord, y_coord, room_id 
              FROM Locations 
              WHERE floor = ? AND room_id != ? AND x_coord IS NOT NULL AND y_coord IS NOT NULL";
    $params = [$floor, $room_id];
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $otherRooms = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $otherRooms[] = $row;
    }

    sqlsrv_free_stmt($stmt);

    return $otherRooms;
}

// Fetch data
$selectedRoom = getSelectedRoom($room_id);
$otherRooms = getOtherRooms($floor, $room_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="view-transition" content="same-origin" />
    <!-- Custom Links -->
    <link rel="stylesheet" href="../../assets/css/kiosk-org-chart.css" />
    <link rel="shortcut icon" href="../../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>

<body class="fade-out">

    <nav class="navi-bar" role="banner">
        <a id="current-time"></a>
        <a id="live-date"></a>
    </nav>


    <div id="m-org-chart-body-container">

        <label for="floorSelect" class="floorSelect">Select Floor:</label>
        <button class="org-buttons" onclick="loadMap(1)">1</button>
        <button class="org-buttons" onclick="loadMap(2)">2</button>
        <a href="../kiosk-sched.php?rfid_no=<?= $rfid_no ?>" class="no-underline">
            <button type="button" class="org-buttons" name="back">
                <i class="bi bi-arrow-left"></i>
            </button>
        </a>
        <div id="map"></div>

    </div>

    <footer>
        <p id="collaboration-text">In collaboration with Colegio de Sta. Teresa de Avila</p>
    </footer>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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
        let map;

        // Initialize the map
        function initMap(imagePath, bounds) {
            if (map) {
                map.remove(); // Remove existing map to avoid reinitialization errors
            }

            map = L.map('map', {
                crs: L.CRS.Simple,
                minZoom: -2,
                zoomControl: false, // Disable zoom controls
                dragging: false,    // Disable dragging
                scrollWheelZoom: false, // Disable scroll wheel zoom
                doubleClickZoom: false, // Disable double-click zoom
                boxZoom: false,         // Disable box zoom
                keyboard: false         // Disable keyboard controls
            });

            L.imageOverlay(imagePath, bounds).addTo(map);
            map.fitBounds(bounds);
        }

        // Add markers to the map
        function addMarkers() {
            // "You Are Here" Marker (Fixed Coordinates)
            L.marker([400, 1380], {
                icon: L.icon({
                    iconUrl: '../../assets/images/you_are_here.png', // Use an appropriate icon
                    iconSize: [58, 61],
                    iconAnchor: [22, 61],
                    popupAnchor: [0, -50]
                })
            }).addTo(map)
                .bindPopup('<b>You Are Here</b>')
                .openPopup();


            <?php if ($selectedRoom): ?>
                // Add blue marker for the selected room
                L.marker([<?= $selectedRoom['x_coord'] ?>, <?= $selectedRoom['y_coord'] ?>], {
                    icon: L.icon({
                        iconUrl: '../../assets/images/custom_icon_2.gif',
                        iconSize: [45, 61],
                        iconAnchor: [22, 61],
                        popupAnchor: [0, -50]
                    })
                }).addTo(map)
                    .bindPopup(`
                            <div class="room-popup">
                                <h3 class="room-name"><?= $selectedRoom['room_name'] ?></h3>
                                <p class="room-type">Type: <?= $selectedRoom['type'] ?></p>
                            </div>
                        `)
                    .openPopup();
            <?php endif; ?>

            <?php foreach ($otherRooms as $room): ?>
                // Add red circle marker for other rooms
                L.rectangle([
                    [<?= $room['x_coord'] ?> - 30, <?= $room['y_coord'] ?> - 30],
                    [<?= $room['x_coord'] ?> + 30, <?= $room['y_coord'] ?> + 30]
                ], {
                    color: 'transparent',
                    fillColor: '#f03',
                    fillOpacity: 0,
                    weight: 2
                }).addTo(map)
                    .bindPopup(`
                            <div class="room-popup">
                                <h3 class="room-name"><?= $room['room_name'] ?></h3>
                                <p class="room-type">Type: <?= $room['type'] ?></p>
                            </div>
                        `)
            <?php endforeach; ?>
        }

        // Load map and markers based on the selected floor
        function loadMap(floor) {
            if (floor === 1) {
                // Floor 1 map
                initMap('../../assets/maps/Map_Level_1.png', [[0, 0], [1000, 1700]]);
                addMarkers();
            } else if (floor === 2) {
                // Floor 2 map
                window.location.href = 'map_level_2.php?room_id=<?=$room_id ?>&rfid_no=<?= $rfid_no ?>';
            }
        }

        // Initialize the default map on page load
        window.onload = function () {
            loadMap(1);
        };
    </script>
</body>

</html>