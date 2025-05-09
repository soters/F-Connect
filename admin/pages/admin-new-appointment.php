<?php
session_start();
$admin_fname = $_SESSION['admin_fname'] ?? 'Unknown';
$acc_type = $_SESSION['acc_type'] ?? 'Unknown';
$picture_path = $_SESSION['picture_path'] ?? '../../assets/images/Prof.png';

include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');
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
            <!-- Appointment -->
            <div class="nav-button">
                <a href="admin-appointment.php">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointment</span>
                </a>
            </div>
            <!-- Announcement -->
            <div class="nav-button">
                <a href="admin-announcement.php">
                    <i class="fas fa-bullhorn"></i>
                    <span>Announcement</span>
                </a>
            </div>
            <!-- Faculty -->
            <div class="nav-button">
                <a href="admin-faculty.php">
                    <i class="fas fa-user"></i>
                    <span>Faculty Members</span>
                </a>
            </div>
            <!-- Schedule -->
            <div class="nav-button">
                <a href="admin-schedule.php">
                    <i class="fas fa-calendar"></i>
                    <span>Schedule</span>
                </a>
            </div>
            <!-- Sections -->
            <div class="nav-button">
                <a href="admin-sections.php">
                    <i class="fas fa-users"></i>
                    <span>Sections</span>
                </a>
            </div>
            <!-- Student -->
            <div class="nav-button">
                <a href="admin-student.php">
                    <i class="fas fa-users"></i>
                    <span>Student</span>
                </a>
            </div>
            <!-- Subjects -->
            <div class="nav-button">
                <a href="admin-subjects.php">
                    <i class="fas fa-book"></i>
                    <span>Subjects</span>
                </a>
            </div>
            <!-- Admin Panel -->
            <div class="nav-button">
                <a href="admin-manage.php">
                    <i class="fas fa-user-tie"></i>
                    <span>Admin Panel</span>
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
    </div>

    <!-- Main Content -->
    <div id="main-content" class="main-content">
        <form id="uploadForm" action="../functions/insert-appointment.php" method="POST" enctype="multipart/form-data">
            <div class="action-widgets-2">
                <div class="widget-button">
                    <h1 class="sub-title">Appointment / New</h1>
                    <div class="buttons">
                        <button class="create-btn-3" type="submit">Save</button>
                        <a href="admin-appointment.php" class="discard-btn">Discard</a>
                    </div>
                </div>
                <div class="widget-search"></div>
            </div>

            <div id="messageBox" class="message-box"></div>

            <div class="faculty-container-1">
                <div class="faculty-container-2">
                    <h1 class="info-title">Appointment Details</h1>
                    <hr>

                    <div class="faculty-name-container">
                        <div class="faculty-name-box">
                            <label for="selected_rfid">Faculty Member</label>
                            <select name="prof_rfid_no" id="prof_rfid_no" class="name-input" required>
                                <option value="" disabled selected>Select a Faculty Member</option>
                                <?php include '../functions/fetch-avl-faculty.php'; ?>
                            </select>
                        </div>

                        <div class="faculty-name-box">
                            <label for="stud_rfid_no">Student</label>
                            <select name="stud_rfid_no" id="stud_rfid_no" class="name-input" required>
                                <option value="" disabled selected>Select a Student</option>
                                <?php include '../functions/fetch-student.php'; ?>
                            </select>
                        </div>
                    </div>

                    <h1 class="info-title">Choose Time Slot</h1>
                    <hr>
                    <div id="slot-container" class="radio-container">
                    </div>

                    <h1 class="info-title">Agenda</h1>
                    <hr>
                    <div class="faculty-name-container">
                        <div class="faculty-name-box">
                            <label for="agenda">Agenda</label>
                            <select id="agenda" name="agenda" class="name-input-2" required>
                                <option value="" disabled selected>Select Agenda</option>
                                <option value="Project Or Research Discussion">Project Or Research Discussion</option>
                                <option value="Mentorship">Mentorship</option>
                                <option value="Internship or Practical Experience Advice">Internship or Practical
                                    Experience Advice</option>
                                <option value="Personal Academic Concerns">Personal Academic Concerns</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden inputs for start and end time -->
            <input type="hidden" id="start-time" name="start_time" required>
            <input type="hidden" id="end-time" name="end_time" required>
        </form>

        <script>
            const facultySelect = document.getElementById('prof_rfid_no');
            const timeContainer = document.getElementById('slot-container');
            const startTimeInput = document.getElementById('start-time');
            const endTimeInput = document.getElementById('end-time');

            facultySelect.addEventListener('change', function () {
                const rfid = this.value;
                timeContainer.innerHTML = '<p>Loading available slots...</p>';

                fetch('../functions/get-available-slots.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'rfid=' + encodeURIComponent(rfid)
                })
                    .then(response => response.json())
                    .then(slots => {
                        if (slots.length === 0) {
                            timeContainer.innerHTML = '<p style="color:red; font-size:12px;">No available slots for today.</p>';
                            return;
                        }

                        let html = '';
                        slots.forEach((slot, index) => {
                            const [start, end] = slot.split('-');
                            const formattedStart = formatTime(start);
                            const formattedEnd = formatTime(end);
                            html += `
                    <input type="radio" id="time${index}" name="appointment-time" value="${formattedStart} - ${formattedEnd}" data-start="${start}" data-end="${end}">
                    <label for="time${index}">${formattedStart} - ${formattedEnd}</label>
                `;
                        });

                        timeContainer.innerHTML = html;

                        // Re-bind event listeners for newly added radios
                        document.querySelectorAll('input[name="appointment-time"]').forEach(radio => {
                            radio.addEventListener('change', function () {
                                startTimeInput.value = this.dataset.start;
                                endTimeInput.value = this.dataset.end;
                            });
                        });
                    })
                    .catch(err => {
                        console.error('Error fetching time slots:', err);
                        timeContainer.innerHTML = '<p style="color:red;">Failed to load slots.</p>';
                    });
            });

            // Format 24-hour to 12-hour time
            function formatTime(timeStr) {
                const [hour, minute] = timeStr.split(':');
                const date = new Date();
                date.setHours(parseInt(hour), parseInt(minute));
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            // Prevent submit if no time selected
            document.getElementById('uploadForm').addEventListener('submit', function (e) {
                const selectedRadio = document.querySelector('input[name="appointment-time"]:checked');
                if (!selectedRadio) {
                    e.preventDefault();
                    alert("Please select a time slot.");
                }
            });
        </script>

    </div>

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
        }, 7000);

        // Remove message from URL without reloading
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
</script>


</html>