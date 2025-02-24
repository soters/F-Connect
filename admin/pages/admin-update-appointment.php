<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');
$admin_rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);
$appointment_code = filter_input(INPUT_GET, 'appointment_code', FILTER_SANITIZE_STRING);

// Include your database connection file
include('../../connection/connection.php');

// 2. Connect to the database using SQLSRV
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the appointment record
$query = "SELECT * FROM Appointments WHERE appointment_code = ?";
$params = array($appointment_code);
$stmt = sqlsrv_query($conn, $query, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$appointment = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$appointment) {
    echo "Appointment not found.";
    exit();
}

// 3. Format the appointment's start and end times for comparison.
// (Assuming the time fields come as DateTime objects from SQLSRV.)
$apptStart = $appointment['start_time']->format('H:i');
$apptEnd = $appointment['end_time']->format('H:i');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../assets/css/admin-design.css">
</head>

<body>
    <!-- Sidebar/Navbar -->
    <div id="nav-bar">
        <input id="nav-toggle" type="checkbox" />
        <div id="nav-header">
            <img id="nav-logo" src="../../assets/images/F-Connect_L3.png" alt="F-CONNECT Logo" />
            <label for="nav-toggle"><span id="nav-toggle-burger"></span></label>
            <hr />
        </div>

        <div id="nav-content">
            <!-- Dashboard -->
            <div class="nav-button">
                <a href="admin-index.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- Attendance Records -->
            <div class="nav-button">
                <a href="admin-attendance-records.php">
                    <i class="fas fa-clipboard"></i>
                    <span>Attendance Records</span>
                </a>
            </div>
            <hr />

            <!-- Faculty -->
            <div class="nav-button">
                <a href="admin-faculty.php">
                    <i class="fas fa-user"></i>
                    <span>Faculty Members</span>
                </a>
            </div>

            <!-- Student -->
            <div class="nav-button">
                <a href="admin-student.php">
                    <i class="fas fa-users"></i>
                    <span>Student</span>
                </a>
            </div>
            <hr />

            <!-- Schedule -->
            <div class="nav-button">
                <a href="admin-schedule.php">
                    <i class="fas fa-calendar"></i>
                    <span>Schedule</span>
                </a>
            </div>

            <!-- Appointment -->
            <div class="nav-button">
                <a href="admin-appointment.php">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointment</span>
                </a>
            </div>

            <!-- Announcement (Newly Added) -->
            <div class="nav-button">
                <a href="admin-announcement.php">
                    <i class="fas fa-bullhorn"></i>
                    <span>Announcement</span>
                </a>
            </div>

            <hr />

            <!-- Sections -->
            <div class="nav-button">
                <a href="admin-sections.php">
                    <i class="fas fa-users"></i>
                    <span>Sections</span>
                </a>
            </div>

            <!-- Subjects -->
            <div class="nav-button">
                <a href="admin-subjects.php">
                    <i class="fas fa-book"></i>
                    <span>Subjects</span>
                </a>
            </div>
            <hr />

            <!-- Locations -->
            <div class="nav-button">
                <a href="admin-locations.php">
                    <i class="fas fa-location-arrow"></i>
                    <span>Locations</span>
                </a>
            </div>

            <!-- Admins -->
            <div class="nav-button">
                <a href="../authentication/admin-admins.php">
                    <i class="fas fa-user-tie"></i>
                    <span>Admins</span>
                </a>
            </div>

            <!-- Logout -->
            <div class="nav-button">
                <a href="../authentication/admin-logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>

            <div id="nav-content-highlight"></div>
        </div>

        <div id="nav-footer">
            <div id="nav-footer-heading">
                <div id="nav-footer-avatar"><img src="../../assets/images/Male_PF.jpg" />
                </div>
                <div id="nav-footer-titlebox">Benedict<span id="nav-footer-subtitle">Admin</span></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="main-content" class="main-content">
        <form id="uploadForm" action="../functions/update-appointment.php" method="POST" enctype="multipart/form-data">
            <div class="action-widgets-2">
                <div class="widget-button">
                    <h1 class="sub-title">Appointment / Edit </h1>
                    <div class="buttons">
                        <button class="create-btn" type="submit">Update</button>
                        <a href="admin-appointment.php" class="discard-btn">Discard</a>
                    </div>
                </div>
                <div class="widget-search"></div>
            </div>
            <div id="messageBox" class="message-box"></div>
            <div class="faculty-container-1">
                <div class="buttons">
                    <a href="javascript:void(0);" class="red-btn"
                        onclick="openDeleteModal(<?= isset($_GET['appointment_code']) ? htmlspecialchars($_GET['appointment_code']) : 'null'; ?>)">
                        Delete
                    </a>
                </div>
                <div class="faculty-container-2">

                    <h1 class="info-title">Appointment Information</h1>
                    <hr>
                    <div class="faculty-name-container">
                        <!-- In this example, we assume the first select is for the Agenda and the second for Status -->
                        <div class="faculty-name-box">
                            <label for="agenda">Agenda</label>
                            <select id="agenda" name="agenda" class="name-input">
                                <option value="" disabled <?= empty($appointment['agenda']) ? 'selected' : '' ?>>Select
                                    Agenda</option>
                                <option value="Project/Research Discussion"
                                    <?= ($appointment['agenda'] === 'Project/Research Discussion') ? 'selected' : '' ?>>
                                    Project/Research Discussion</option>
                                <option value="Mentorship" <?= ($appointment['agenda'] === 'Mentorship') ? 'selected' : '' ?>>Mentorship</option>
                                <option value="Internship or Practical Experience Advice"
                                    <?= ($appointment['agenda'] === 'Internship or Practical Experience Advice') ? 'selected' : '' ?>>Internship or Practical Experience Advice</option>
                                <option value="Personal Academic Concerns" <?= ($appointment['agenda'] === 'Personal Academic Concerns') ? 'selected' : '' ?>>Personal Academic Concerns</option>
                            </select>
                        </div>
                        <div class="faculty-name-box">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="name-input">
                                <option value="" disabled <?= empty($appointment['status']) ? 'selected' : '' ?>>Select
                                    Status</option>
                                <option value="Pending" <?= ($appointment['status'] === 'Pending') ? 'selected' : '' ?>>
                                    Pending</option>
                                <option value="Accepted" <?= ($appointment['status'] === 'Accepted') ? 'selected' : '' ?>>
                                    Accepted</option>
                                <option value="Completed" <?= ($appointment['status'] === 'Completed') ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancelled" <?= ($appointment['status'] === 'Cancelled') ? 'selected' : '' ?>>
                                    Canceled</option>
                                <option value="Declined" <?= ($appointment['status'] === 'Declined') ? 'selected' : '' ?>>
                                    Declined</option>
                            </select>
                        </div>
                        <input type="hidden" name="appointment_code"
                            value="<?= htmlspecialchars($appointment_code); ?>">
                    </div>
                </div>
            </div>
        </form>

    </div>

    <!-- Dark background overlay -->
    <div id="modalOverlay" class="modal-overlay"></div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="custom-modal">
        <div class="modal-content">
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete this appointment?</p>
            <div class="modal-actions">
                <button id="confirmDelete" class="btn-confirm">Yes, Delete</button>
                <button onclick="closeDeleteModal()" class="btn-cancel">Cancel</button>
            </div>
        </div>
    </div>
    <!-- Scroll to Top Button -->
    <button id="scrollToTopBtn" onclick="scrollToTop()">↑</button>


    <!-- Scroll to Top Button -->
    <button id="scrollToTopBtn" onclick="scrollToTop()">↑</button>
</body>
<script>
    const navToggle = document.getElementById('nav-toggle');
    const mainContent = document.getElementById('main-content');

    // Add event listener to toggle the margin of main-content based on the sidebar state
    navToggle.addEventListener('change', function () {
        if (navToggle.checked) {
            // Sidebar is toggled open, adjust margin to the larger size
            mainContent.style.marginLeft = '100px';  // Adjust this to your default sidebar width
        } else {
            // Sidebar is toggled closed, adjust margin to the smaller size
            mainContent.style.marginLeft = '280px';  // Adjust this to your collapsed sidebar width
        }
    });
</script>
<script>
    // Trigger fade-out effect before navigating
    window.addEventListener('beforeunload', function () {
        document.body.classList.add('fade-out');
    });
</script>
<script>
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');

    // Show/hide the button based on scroll position
    window.onscroll = function () {
        if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
            scrollToTopBtn.style.display = 'block';
        } else {
            scrollToTopBtn.style.display = 'none';
        }
    };

    // Scroll smoothly to the top
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    } 
</script>

<script>
    // Get message and type from URL
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get("message");
    const type = urlParams.get("type");

    if (message) {
        let messageBox = document.getElementById("messageBox");

        // Set message text and styling
        messageBox.innerText = message;
        messageBox.classList.add(type === "success" ? "message-success" : "message-error");
        messageBox.style.display = "block";

        // Hide message after 2 seconds
        setTimeout(function () {
            messageBox.style.display = "none";
        }, 10000);

        // Remove message from URL without reloading
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
</script>
<script>
    function openDeleteModal(appointmentCode) {
        document.getElementById("deleteModal").style.display = "block";
        document.getElementById("modalOverlay").style.display = "block";
        document.getElementById("confirmDelete").setAttribute("data-appointment-code", appointmentCode);
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").style.display = "none";
        document.getElementById("modalOverlay").style.display = "none";
    }

    document.getElementById("confirmDelete").addEventListener("click", function () {
        let appointmentCode = this.getAttribute("data-appointment-code");
        window.location.href = `../functions/delete-appointment.php?appointment_code=${appointmentCode}`;
    });
</script>


</html>