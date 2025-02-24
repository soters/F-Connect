<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');
$admin_rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);
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
        <form id="uploadForm" action="../functions/insert-appointment.php" method="POST" enctype="multipart/form-data">
            <div class="action-widgets-2">
                <div class="widget-button">
                    <h1 class="sub-title">Appointment / New </h1>
                    <div class="buttons">
                        <button class="create-btn" type="submit">Save</button>
                        <a href="admin-appointment.php" class="discard-btn">Discard</a>
                    </div>
                </div>
                <div class="widget-search"></div>
            </div>
            <div id="messageBox" class="message-box"></div>
            <div class="faculty-container-1">
                <div class="buttons">
                    <!--<button class="pass-btn" type="button">Sent Password Reset Instructions</button>-->
                </div>
                <div class="faculty-container-2">
                    <h1 class="info-title">Appointment Details</h1>
                    <hr>

                    <div class="faculty-name-container">
                        <div>
                            <div class="faculty-name-box">
                                <label for="prof_rfid_no">Faculty Member</label>
                                <select name="prof_rfid_no" id="prof_rfid_no" class="name-input">
                                    <option value="" disabled selected>Select a Faculty Member</option>
                                    <?php include '../functions/fetch-avl-faculty.php'; ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <div class="faculty-name-box">
                                <label for="stud_rfid_no">Student</label>
                                <select name="stud_rfid_no" id="stud_rfid_no" class="name-input">
                                    <option value="" disabled selected>Select a Student</option>
                                    <?php include '../functions/fetch-student.php'; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h1 class="info-title">Choose Time Slot</h1>
                    <hr>
                    <div class="radio-container">
                        <input type="radio" id="time1" name="appointment-time" value="07:00 AM - 08:00 AM"
                            data-start="07:00" data-end="08:00">
                        <label for="time1">07:00 AM - 08:00 AM</label>

                        <input type="radio" id="time2" name="appointment-time" value="08:00 AM - 09:00 AM"
                            data-start="08:00" data-end="09:00">
                        <label for="time2">08:00 AM - 09:00 AM</label>

                        <input type="radio" id="time3" name="appointment-time" value="09:00 AM - 10:00 AM"
                            data-start="09:00" data-end="10:00">
                        <label for="time3">09:00 AM - 10:00 AM</label>

                        <input type="radio" id="time4" name="appointment-time" value="10:00 AM - 11:00 AM"
                            data-start="10:00" data-end="11:00">
                        <label for="time4">10:00 AM - 11:00 AM</label>

                        <input type="radio" id="time5" name="appointment-time" value="11:00 AM - 12:00 PM"
                            data-start="11:00" data-end="12:00">
                        <label for="time5">11:00 AM - 12:00 PM</label>

                        <input type="radio" id="time6" name="appointment-time" value="01:00 PM - 02:00 PM"
                            data-start="13:00" data-end="14:00">
                        <label for="time6">01:00 PM - 02:00 PM</label>

                        <input type="radio" id="time7" name="appointment-time" value="02:00 PM - 03:00 PM"
                            data-start="14:00" data-end="15:00">
                        <label for="time7">02:00 PM - 03:00 PM</label>

                        <input type="radio" id="time8" name="appointment-time" value="03:00 PM - 04:00 PM"
                            data-start="15:00" data-end="16:00">
                        <label for="time8">03:00 PM - 04:00 PM</label>

                        <input type="radio" id="time9" name="appointment-time" value="04:00 PM - 05:00 PM"
                            data-start="16:00" data-end="17:00">
                        <label for="time9">04:00 PM - 05:00 PM</label>

                        <input type="radio" id="time10" name="appointment-time" value="05:00 PM - 06:00 PM"
                            data-start="17:00" data-end="18:00">
                        <label for="time10">05:00 PM - 06:00 PM</label>

                        <input type="radio" id="time11" name="appointment-time" value="06:00 PM - 07:00 PM"
                            data-start="18:00" data-end="19:00">
                        <label for="time11">06:00 PM - 07:00 PM</label>

                        <input type="radio" id="time12" name="appointment-time" value="07:00 PM - 08:00 PM"
                            data-start="19:00" data-end="20:00">
                        <label for="time12">07:00 PM - 08:00 PM</label>
                    </div>

                    <h1 class="info-title">Agenda</h1>
                    <hr>
                    <div class="faculty-name-container">
                        <div>
                            <div class="faculty-name-box">
                                <label for="agenda">Agenda</label>
                                <select id="agenda" name="agenda" class="name-input-2">
                                    <option value="Project/Research Discussion">Project/Research Discussion</option>
                                    <option value="Mentorship">Mentorship</option>
                                    <option value="Internship or Practical Experience Advice">Internship or Practical
                                        Experience Advice</option>
                                    <option value="Personal Academic Concerns">Personal Academic Concerns</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden inputs to store the start and end time values -->
            <input type="hidden" id="start-time" name="start_time">
            <input type="hidden" id="end-time" name="end_time">

        </form>

        <script>
            // Get all radio buttons for appointment time
            const timeRadios = document.querySelectorAll('input[name="appointment-time"]');

            // Listen for change events
            timeRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    // Set hidden form values for start_time and end_time
                    document.getElementById('start-time').value = this.getAttribute('data-start');
                    document.getElementById('end-time').value = this.getAttribute('data-end');

                    // Log the values to the console to verify they're set
                    console.log("Start Time: " + document.getElementById('start-time').value);
                    console.log("End Time: " + document.getElementById('end-time').value);
                });
            });

            // Ensure that hidden input values are set when the form is submitted
            document.getElementById('uploadForm').addEventListener('submit', function (event) {
                // Log the values before form submission
                console.log("Before submit - Start Time: " + document.getElementById('start-time').value);
                console.log("Before submit - End Time: " + document.getElementById('end-time').value);

                // Check if a radio button is selected
                const selectedRadio = document.querySelector('input[name="appointment-time"]:checked');

                if (!selectedRadio) {
                    event.preventDefault();  // Prevent form submission if no radio button is selected
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
        }, 10000);

        // Remove message from URL without reloading
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
</script>


</html>