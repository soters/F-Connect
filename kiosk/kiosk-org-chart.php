<?php
declare(strict_types=1);
session_start();
require_once('../connection/connection.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="view-transition" content="same-origin" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous" />
    <!-- Bootstrap Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" />
    <!-- Custom Links -->
    <link rel="stylesheet" href="../assets/css/kiosk-org-chart.css" />
    <link rel="shortcut icon" href="../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
</head>

<body class="fade-out">

    <nav class="navi-bar" role="banner">
        <a id="current-time"></a>
        <a id="live-date"></a>
    </nav>


    <div id="org-chart-body-container">

        <a href="kiosk-index.php" class="no-underline">
            <button type="button" class="org-buttons">
                <i class="bi bi-arrow-left"></i>
            </button>
        </a>

        <button type="button" class="org-buttons" onclick="refreshPage()">
            <i class="bi bi-arrow-clockwise"></i>
        </button>

        <!--<button type="button" class="org-buttons" id="list-button">
            <i class="bi bi-list-task"></i>
        </button>-->

        <button type="button" class="org-buttons" data-bs-toggle="modal" data-bs-target="#toggleModal">
            <i class="bi bi-gear-fill"></i>
        </button>


        <p id="org-title">Department Overview</p>
        <p id="org-title-smaller">College of Computer Studies</p>

        <!-- Modal -->
        <div class="modal fade" id="toggleModal" tabindex="-1" aria-labelledby="toggleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title settings-title" id="toggleModalLabel">Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="toggle-collapse" class="mb-3">
                            <h6>Enable Collapse</h6>
                            <div class="toggle-switch">
                                <input type="checkbox" id="toggle" class="toggle-input">
                                <label for="toggle" class="toggle-label"></label>
                            </div>
                        </div>
                        <div id="toggle-zoom" class="mb-3">
                            <h6>Enable Zoom</h6>
                            <div class="toggle-switch">
                                <input type="checkbox" id="enable-zoom" class="toggle-input">
                                <label for="enable-zoom" class="toggle-label"></label>
                            </div>
                        </div>
                        <div id="toggle-drag">
                            <h6>Enable Drag</h6>
                            <div class="toggle-switch">
                                <input type="checkbox" id="enable-drag" class="toggle-input">
                                <label for="enable-drag" class="toggle-label"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Org Chart Content -->

        <div class="body genealogy-body genealogy-scroll">
            <div class="genealogy-tree" id="tree-view">
                <ul>
                    <?php
                    // Query to fetch the Dean
                    $dean_query = "SELECT * FROM Faculty WHERE acc_type = 'Dean'";
                    $stmt = sqlsrv_query($conn, $dean_query);

                    if ($stmt === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }

                    $dean_row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

                    if ($dean_row) {
                        // Check attendance for the Dean
                        $attendance_query = "SELECT * FROM AttendanceToday 
                        WHERE rfid_no = ? AND CONVERT(DATE, date_logged) = CONVERT(DATE, GETDATE())";
                        $attendance_stmt = sqlsrv_prepare($conn, $attendance_query, [$dean_row['rfid_no']]);
                        sqlsrv_execute($attendance_stmt);

                        $is_present = sqlsrv_fetch_array($attendance_stmt, SQLSRV_FETCH_ASSOC) ? true : false;
                        $status_color = $is_present ? "green" : "red";

                        ?>
                        <li>
                            <a href="javascript:void(0);">
                                <div class="card-header-holder">Dean</div>
                                <div class="member-view-box">
                                    <div class="avatar-container">
                                        <div class="avatar">
                                            <img src="<?= $dean_row["picture_path"] ?>" alt="Member" />
                                        </div>
                                        <div class="status" style="background-color: <?= $status_color; ?>;"></div>
                                    </div>
                                    <div class="member-details">
                                        <h6 class="name-des">
                                            <?= $dean_row["fname"] ?>     <?= $dean_row["mname"] ?>
                                            <?= $dean_row["lname"] ?>     <?= $dean_row["suffix"] ?>
                                        </h6>
                                    </div>
                                    <div class="new-container">
                                        <button data-bs-toggle="modal" data-bs-target="#exampleModal" class="dept-assign">
                                            College of Computer Studies
                                        </button>
                                    </div>
                                </div>
                                <div class="card-footer-holder">
                                    <button class="btn-view" onclick="redirectToSchedule('<?= $dean_row["rfid_no"] ?>');">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                </div>
                            </a>
                            <ul class="active">
                                <?php
                                // Query to fetch Program Chairs
                                $program_chair_query = "
                        SELECT 
                            f.rfid_no AS program_chair_id,
                            f.fname,
                            f.mname,
                            f.lname,
                            f.suffix,
                            f.picture_path,
                            d.dept_id,
                            d.department_name
                        FROM 
                            Faculty f
                        INNER JOIN 
                            UserDepartment ud ON f.rfid_no = ud.rfid_no
                        INNER JOIN 
                            Department d ON ud.dept_id = d.dept_id
                        WHERE 
                            f.acc_type = 'Program Chair'
                        ORDER BY 
                            d.department_name;
                        ";
                                $stmt = sqlsrv_query($conn, $program_chair_query);

                                if ($stmt === false) {
                                    die(print_r(sqlsrv_errors(), true));
                                }

                                while ($program_chair = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    // Check attendance for the Program Chair
                                    $attendance_stmt = sqlsrv_prepare($conn, $attendance_query, [$program_chair['program_chair_id']]);
                                    sqlsrv_execute($attendance_stmt);

                                    $is_present = sqlsrv_fetch_array($attendance_stmt, SQLSRV_FETCH_ASSOC) ? true : false;
                                    $status_color = $is_present ? "green" : "red";
                                    ?>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="card-header-holder">Program Chair</div>
                                            <div class="member-view-box">
                                                <div class="avatar-container">
                                                    <div class="avatar">
                                                        <img src="<?= $program_chair["picture_path"] ?>" alt="Member" />
                                                    </div>
                                                    <div class="status" style="background-color: <?= $status_color; ?>;"></div>
                                                </div>
                                                <div class="member-details">
                                                    <h6 class="name-des">
                                                        <?= $program_chair["fname"] ?>         <?= $program_chair["mname"] ?>
                                                        <?= $program_chair["lname"] ?>         <?= $program_chair["suffix"] ?>
                                                    </h6>
                                                </div>
                                                <div class="new-container">
                                                    <button data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                        class="dept-assign">
                                                        <?= $program_chair["department_name"] ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-footer-holder">
                                                <button class="btn-view"
                                                    onclick="redirectToSchedule('<?= $program_chair["program_chair_id"] ?>');">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                            </div>
                                        </a>
                                        <ul class="active">
                                            <?php
                                            // Query to fetch Professors under the current Program Chair's department
                                            $professor_query = "
                                    SELECT 
                                        f.rfid_no AS professor_id,
                                        f.fname,
                                        f.mname,
                                        f.lname,
                                        f.suffix,
                                        f.picture_path,
                                        d.department_name
                                    FROM 
                                        Faculty f
                                    INNER JOIN 
                                        UserDepartment ud ON f.rfid_no = ud.rfid_no
                                    INNER JOIN 
                                        Department d ON ud.dept_id = d.dept_id
                                    WHERE 
                                        f.acc_type = 'Professor' AND d.dept_id = ?
                                    ";
                                            $prof_stmt = sqlsrv_prepare($conn, $professor_query, [$program_chair['dept_id']]);
                                            sqlsrv_execute($prof_stmt);

                                            while ($professor = sqlsrv_fetch_array($prof_stmt, SQLSRV_FETCH_ASSOC)) {
                                                // Check attendance for the Professor
                                                $attendance_stmt = sqlsrv_prepare($conn, $attendance_query, [$professor['professor_id']]);
                                                sqlsrv_execute($attendance_stmt);

                                                $is_present = sqlsrv_fetch_array($attendance_stmt, SQLSRV_FETCH_ASSOC) ? true : false;
                                                $status_color = $is_present ? "green" : "red";
                                                ?>
                                                <li>
                                                    <a href="javascript:void(0);">
                                                        <div class="card-header-holder">Professor</div>
                                                        <div class="member-view-box">
                                                            <div class="avatar-container">
                                                                <div class="avatar">
                                                                    <img src="<?= $professor["picture_path"] ?>" alt="Member" />
                                                                </div>
                                                                <div class="status"
                                                                    style="background-color: <?= $status_color; ?>;"></div>
                                                            </div>
                                                            <div class="member-details">
                                                                <h6 class="name-des">
                                                                    <?= $professor["fname"] ?>             <?= $professor["mname"] ?>
                                                                    <?= $professor["lname"] ?>             <?= $professor["suffix"] ?>
                                                                </h6>
                                                            </div>
                                                            <div class="new-container">
                                                                <button class="dept-assign">
                                                                    <?= $professor["department_name"] ?>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer-holder">
                                                            <button class="btn-view"
                                                                onclick="redirectToSchedule('<?= $professor["professor_id"] ?>');">
                                                                <i class="bi bi-three-dots"></i>
                                                            </button>
                                                        </div>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            ?>
                                        </ul>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </li>
                        <?php
                    } else {
                        echo "Error fetching dean data.";
                    }
                    ?>
                </ul>
            </div>
        </div>

        <!-- List View -->
        <div class="genealogy-list" id="list-view" style="display: none;">
            <ul>
                <p class="position-title">Dean</p>
                <div class="card-header-holder-2">Dean</div>
                <div class="member-view-box-2">
                    <div class="avatar-container-2">
                        <div class="avatar">
                            <img src="<?= $dean_row["picture_path"] ?>" alt="Member" />
                        </div>
                        <div class="status" style="background-color: <?= $status_color; ?>;"></div>
                    </div>
                    <div class="member-details">
                        <h6 class="name-des">
                            <?= $dean_row["fname"] ?> <?= $dean_row["mname"] ?>
                            <?= $dean_row["lname"] ?> <?= $dean_row["suffix"] ?>
                        </h6>
                    </div>
                    <div class="new-container">
                        <button data-bs-toggle="modal" data-bs-target="#exampleModal" class="dept-assign">
                            College of Computer Studies
                        </button>
                    </div>
                </div>
                <div class="card-footer-holder-2">
                    <button class="btn-view" onclick="redirectToSchedule('<?= $dean_row["rfid_no"] ?>');">
                        <i class="bi bi-three-dots"></i>
                    </button>
                </div>

                <p class="position-title">Program Chair</p>
                <?php
                // Fetch Program Chairs
                $stmt = sqlsrv_query($conn, $program_chair_query);
                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
                while ($program_chair = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    ?>
                    <div class="card-header-holder-2">Program Chair</div>
                    <div class="member-view-box-2">
                        <div class="avatar-container-2">
                            <div class="avatar">
                                <img src="<?= $program_chair["picture_path"] ?>" alt="Member" />
                            </div>
                            <div class="status" style="background-color: <?= $status_color; ?>;"></div>
                        </div>
                        <div class="member-details">
                            <h6 class="name-des">
                                <?= $program_chair["fname"] ?>     <?= $program_chair["mname"] ?>
                                <?= $program_chair["lname"] ?>     <?= $program_chair["suffix"] ?>
                            </h6>
                        </div>
                        <div class="new-container">
                            <button data-bs-toggle="modal" data-bs-target="#exampleModal" class="dept-assign">
                                <?= $program_chair["department_name"] ?>
                            </button>
                        </div>
                    </div>
                    <div class="card-footer-holder-2">
                        <button class="btn-view" onclick="redirectToSchedule('<?= $program_chair["program_chair_id"] ?>');">
                            <i class="bi bi-three-dots"></i>
                        </button>
                    </div>

                    <p class="position-title">Professors</p>


                    <div class="professor-container">
                        <?php
                        // Fetch Professors under the Program Chair
                        $prof_stmt = sqlsrv_prepare($conn, $professor_query, [$program_chair['dept_id']]);
                        sqlsrv_execute($prof_stmt);
                        while ($professor = sqlsrv_fetch_array($prof_stmt, SQLSRV_FETCH_ASSOC)) {
                            ?>
                            <div class="professor-card">
                                <div class="card-header-holder-2">Professor</div>
                                <div class="member-view-box-2">
                                    <div class="avatar-container-2">
                                        <div class="avatar">
                                            <img src="<?= $professor["picture_path"] ?>" alt="Member" />
                                        </div>
                                        <div class="status" style="background-color: <?= $status_color; ?>;"></div>
                                    </div>
                                    <div class="member-details">
                                        <h6 class="name-des">
                                            <?= $professor["fname"] ?>         <?= $professor["mname"] ?>
                                            <?= $professor["lname"] ?>         <?= $professor["suffix"] ?>
                                        </h6>
                                    </div>
                                    <div class="new-container">
                                        <button class="dept-assign">
                                            <?= $professor["department_name"] ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-footer-holder-2">
                                    <button class="btn-view" onclick="redirectToSchedule('<?= $professor["professor_id"] ?>');">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </ul>
        </div>

    </div>

    <!-- For Org Chart Collapse -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script>
        $(function () {
            var toggleSwitch = document.getElementById('toggle');
            var genealogyTree = document.querySelector('.genealogy-tree');

            toggleSwitch.addEventListener('change', function () {
                if (this.checked) {
                    $(".genealogy-tree ul").hide();
                    $(".genealogy-tree>ul").show();
                    $(".genealogy-tree ul.active").show();
                    $(".genealogy-tree li").on("click", function (e) {
                        var children = $(this).find("> ul");
                        if (children.is(":visible"))
                            children.hide("fast").removeClass("active");
                        else children.show("fast").addClass("active");
                        e.stopPropagation();
                    });
                } else {
                    // Disable genealogy tree functionality
                    $(".genealogy-tree li").off("click");
                }
            });
        });
    </script>

    <script>
        document.getElementById('list-button').addEventListener('click', function () {
            const treeView = document.getElementById('tree-view');
            const listView = document.getElementById('list-view');

            if (treeView.style.display === 'none') {
                treeView.style.display = 'block';
                listView.style.display = 'none';
            } else {
                treeView.style.display = 'none';
                listView.style.display = 'block';
            }
        });
    </script>


    <script>
        function redirectToSchedule(rfid_no) {
            // Construct the URL with the rfid_no as a query parameter
            var url = 'kiosk-sched.php?rfid_no=' + encodeURIComponent(rfid_no);
            // Redirect to the new URL
            window.location.href = url;
        }
    </script>


    <footer>
        <p id="collaboration-text">In collaboration with Colegio de Sta. Teresa de Avila</p>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="../../assets/js/custom-javascript.js"></script>

    <script>
        $(document).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>

    <script>
        $(function () {
            // Variables for zoom and drag
            let scale = 1;
            let translateX = 0;
            let translateY = 0;
            let isDragging = false;
            let startX, startY;
            let isZoomEnabled = false; // For zoom toggle
            let isDragEnabled = false; // For drag toggle

            const container = document.querySelector('#org-chart-body-container');
            const genealogyBody = container.querySelector('.genealogy-body');

            // Function to reset transformations
            function resetTransformations() {
                scale = 1;
                translateX = 0;
                translateY = 0;
                genealogyBody.style.transform = `scale(${scale}) translate(${translateX}px, ${translateY}px)`;
            }

            // Zoom functionality
            function handleZoom(event) {
                if (!isZoomEnabled) return; // Exit if zoom is disabled
                event.preventDefault();
                const zoomIntensity = 0.1;
                scale += event.deltaY < 0 ? zoomIntensity : -zoomIntensity;
                scale = Math.min(Math.max(scale, 0.5), 3); // Limit zoom between 0.5x and 3x
                genealogyBody.style.transform = `scale(${scale}) translate(${translateX}px, ${translateY}px)`;
            }

            // Drag functionality
            function handleDragStart(event) {
                if (!isDragEnabled) return; // Exit if drag is disabled
                isDragging = true;
                startX = event.clientX - translateX;
                startY = event.clientY - translateY;
                container.style.cursor = 'grabbing';
            }

            function handleDragMove(event) {
                if (isDragging && isDragEnabled) {
                    translateX = event.clientX - startX;
                    translateY = event.clientY - startY;
                    genealogyBody.style.transform = `scale(${scale}) translate(${translateX}px, ${translateY}px)`;
                }
            }

            function handleDragEnd() {
                isDragging = false;
                container.style.cursor = 'grab';
            }

            // Event listeners for zoom and drag
            container.addEventListener('wheel', handleZoom);
            container.addEventListener('mousedown', handleDragStart);
            container.addEventListener('mousemove', handleDragMove);
            container.addEventListener('mouseup', handleDragEnd);
            container.addEventListener('mouseleave', handleDragEnd);

            // Toggle button logic
            const toggleZoom = document.getElementById('enable-zoom');
            const toggleDrag = document.getElementById('enable-drag');

            toggleZoom.addEventListener('change', () => {
                isZoomEnabled = toggleZoom.checked;
                if (!isZoomEnabled) {
                    resetTransformations(); // Reset zoom when disabled
                }
            });

            toggleDrag.addEventListener('change', () => {
                isDragEnabled = toggleDrag.checked;
                container.style.cursor = isDragEnabled ? 'grab' : 'default'; // Update cursor style
            });
        });
    </script>

    <script>
        function refreshPage() {
            window.location.reload(); // Reloads the current page
        }
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