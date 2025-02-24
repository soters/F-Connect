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
        <form id="uploadForm" action="../functions/insert-student.php" method="POST" enctype="multipart/form-data">
            <div class="action-widgets-2">
                <div class="widget-button">
                    <h1 class="sub-title">Student / New </h1>
                    <div class="buttons">
                        <button class="create-btn" type="submit">Save</button>
                        <a href="admin-student.php" class="discard-btn">Discard</a>
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
                    <div class="picture-container">
                        <div id="image-preview">
                            <img id="preview" src="../../assets/images/add-image.jpg" alt="Image Preview">
                        </div>
                        <label for="picture_path" class="custom-file-upload">Choose Image</label>
                        <input type="file" id="picture_path" name="picture_path" accept="image/*"
                            onchange="previewImage(event)">
                    </div>

                    <br>
                    <div class="faculty-name-container">
                        <div>
                            <label for="rfid_no">RFID No.</label>
                            <input class="name-input" type="tel" id="rfid_no" name="rfid_no" maxlength="10"
                                pattern="[0-9]{1,10}" required>
                        </div>
                        <div>
                            <label for="student_number">Student No.</label>
                            <input class="name-input" type="tel" id="student_number" name="student_number" maxlength="8"
                                required>
                        </div>
                    </div>
                    <hr>

                    <h1 class="info-title">Student Name</h1>
                    <hr>

                    <div class="faculty-name-container">
                        <div>
                            <label for="fname">First Name</label>
                            <input class="name-input" type="text" id="fname" name="fname" required>
                        </div>
                        <div>
                            <label for="mname">Middle Name</label>
                            <input class="name-input" type="text" id="mname" name="mname">
                        </div>
                        <div>
                            <label for="lname">Last Name</label>
                            <input class="name-input" type="text" id="lname" name="lname" required>
                        </div>
                        <div>
                            <div class="faculty-name-box">
                                <label for="suffix">Suffix</label>
                                <select id="suffix" name="suffix" class="name-input">
                                    <option value="">None</option>
                                    <option value="Sr.">Sr.</option>
                                    <option value="Jr.">Jr.</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                    <option value="V">V</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h1 class="info-title">Other Information</h1>
                    <hr>
                    <div class="faculty-name-container">
                        <div>
                            <div class="faculty-name-box">
                                <label for="gender">Gender</label>
                                <select id="sex" name="sex" class="name-input">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="phone_no">Contact Number</label>
                            <input class="name-input" type="tel" id="phone_no" name="phone_no" pattern="[0-9]{10}"
                                required>
                        </div>
                        <div>
                            <label for="birth-date">Birth Date</label>
                            <input class="name-input" type="date" id="dob" name="dob">
                        </div>
                        <div>
                            <label for="email">Email</label>
                            <input class="name-input" type="email" id="email" name="email" required>
                        </div>
                        <div>
                            <div class="faculty-name-box">
                                <label for="department">Section</label>
                                <select name="section_id" id="section_id" class="name-input-2">
                                    <option value="" disabled selected>Select a Section</option>
                                    <?php include '../functions/fetch-section.php'; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h1 class="info-title">Address</h1>
                    <hr>
                    <div class="faculty-name-container">

                        <div class="faculty-name-box">
                            <label for="region">Region</label>
                            <select id="region" name="region" class="name-input" required>
                                <option value="NCR">NCR</option>
                                <option value="CAR">CAR</option>
                                <option value="Region I">Region I</option>
                                <option value="Region II">Region II</option>
                                <option value="Region III">Region III</option>
                                <option value="Region IV-A">Region IV-A</option>
                                <option value="Region IV-B">Region IV-B</option>
                                <option value="Region V">Region V</option>
                                <option value="Region VI">Region VI</option>
                                <option value="Region VII">Region VII</option>
                                <option value="Region VIII">Region VIII</option>
                            </select>
                        </div>
                        <div class="faculty-name-box">
                            <label for="city">City</label>
                            <select id="city" name="city" class="name-input" required>
                                <option value="Caloocan City">Caloocan City</option>
                                <option value="Quezon City">Quezon City</option>
                                <option value="Manila">Manila</option>
                            </select>
                        </div>
                        <div class="faculty-name-box">
                            <label for="province">Province</label>
                            <select id="province" name="province" class="name-input" required>
                                <option value="None">None</option>
                                <option value="Abra">Abra</option>
                            </select>
                        </div>
                        <div>
                            <label for="zip_code">Zipcode</label>
                            <input class="name-input" type="tel" id="zip_code" name="zip_code" maxlength="4"
                                pattern="[0-9]{1,10}" required>
                        </div>
                        <div>
                            <label for="address_dtl">Address Detail</label>
                            <input class="name-input-2" type="text" id="address_dtl" name="address_dtl" required>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById("preview");

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            // Reset to default image if no file is selected
            preview.src = "../../assets/images/add-image.jpg";
        }
    }
</script>
<script>
    function uploadImage() {
        var formData = new FormData();
        var fileInput = document.getElementById("picture_path");

        if (fileInput.files.length === 0) {
            alert("Please select an image first.");
            return;
        }

        formData.append("picture_path", fileInput.files[0]);

        fetch("upload-image.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.text())
            .then(data => alert(data)) // Display success or error message
            .catch(error => console.error("Error:", error));
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