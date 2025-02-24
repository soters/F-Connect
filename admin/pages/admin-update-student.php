<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');
$admin_rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);

$rfid_no = isset($_GET['rfid_no']) ? $_GET['rfid_no'] : null;

if (!$rfid_no) {
    $message = "No RFID provided!";
    $type = "error";
    header("Location: admin-student.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}

// Fetch student data
$sqlStudent = "SELECT s.*, a.region, a.province, a.city, a.zip_code, a.address_dtl, sec.section_name 
               FROM Students s
               LEFT JOIN StudentAddresses a ON s.rfid_no = a.rfid_no
               LEFT JOIN Sections sec ON s.section_id = sec.section_id
               WHERE s.rfid_no = ?";
$paramsStudent = [$rfid_no];
$stmtStudent = sqlsrv_query($conn, $sqlStudent, $paramsStudent);

if ($stmtStudent === false || !sqlsrv_has_rows($stmtStudent)) {
    $message = "Student data not found!";
    $type = "error";
    header("Location: admin-student.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}

$data = sqlsrv_fetch_array($stmtStudent, SQLSRV_FETCH_ASSOC);

// Fetch sections for dropdown
$sqlSections = "SELECT section_id, section_name FROM Sections";
$stmtSections = sqlsrv_query($conn, $sqlSections);

$sections = [];
if ($stmtSections !== false) {
    while ($row = sqlsrv_fetch_array($stmtSections, SQLSRV_FETCH_ASSOC)) {
        $sections[] = $row;
    }
}
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
        <form id="updateForm" action="../functions/update-student.php" method="POST" enctype="multipart/form-data">
            <div class="action-widgets-2">
                <div class="widget-button">
                    <h1 class="sub-title">Student / Update</h1>
                    <div class="buttons">
                        <button class="create-btn" type="submit">Save</button>
                        <a href="admin-student.php" class="discard-btn">Discard</a>
                    </div>
                </div>
            </div>
            <div id="messageBox" class="message-box"></div>
            <div class="faculty-container-1">
                <div class="buttons">
                    <a href="javascript:void(0);" class="pass-btn"
                        onclick="openResetModal(<?= isset($_GET['rfid_no']) ? htmlspecialchars($_GET['rfid_no']) : 'null'; ?>)">
                        Reset Password
                    </a>
                    <a href="javascript:void(0);" class="red-btn"
                        onclick="openDeleteModal(<?= isset($_GET['rfid_no']) ? htmlspecialchars($_GET['rfid_no']) : 'null'; ?>)">
                        Delete
                    </a>
                    <a href="javascript:void(0);" class="arc-btn"
                        onclick="openArchiveModal(<?= isset($_GET['rfid_no']) ? htmlspecialchars($_GET['rfid_no']) : 'null'; ?>)">
                        Archive
                    </a>
                    <button class="pass-btn" type="button">Account Information</button>
                </div>
                <div class="faculty-container-2">
                    <div class="picture-container">
                        <div id="image-preview">
                            <img id="preview"
                                src="<?= htmlspecialchars($data['picture_path']) ?: '../../assets/images/add-image.jpg' ?>"
                                alt="Image Preview">
                        </div>
                        <label for="picture_path" class="custom-file-upload">Choose Image</label>
                        <input type="file" id="picture_path" name="picture_path" accept="image/*"
                            onchange="previewImage(event)">
                    </div>

                    <br>
                    <div class="faculty-name-container">
                        <div>
                            <label for="rfid_no">RFID No.</label>
                            <input class="name-input" type="tel" id="rfid_no" name="rfid_no"
                                value="<?= htmlspecialchars($data['rfid_no']) ?>" readonly>
                        </div>
                        <div>
                            <label for="student_number">Student No.</label>
                            <input class="name-input" type="tel" id="student_number" name="student_number"
                                value="<?= htmlspecialchars($data['student_number']) ?>" required>
                        </div>
                    </div>
                    <hr>

                    <h1 class="info-title">Student Name</h1>
                    <hr>

                    <div class="faculty-name-container">
                        <div>
                            <label for="fname">First Name</label>
                            <input class="name-input" type="text" id="fname" name="fname"
                                value="<?= htmlspecialchars($data['fname']) ?>" required>
                        </div>
                        <div>
                            <label for="mname">Middle Name</label>
                            <input class="name-input" type="text" id="mname" name="mname"
                                value="<?= htmlspecialchars($data['mname']) ?>">
                        </div>
                        <div>
                            <label for="lname">Last Name</label>
                            <input class="name-input" type="text" id="lname" name="lname"
                                value="<?= htmlspecialchars($data['lname']) ?>" required>
                        </div>
                        <div>
                            <label for="suffix">Suffix</label>
                            <select id="suffix" name="suffix" class="name-input">
                                <option value="">None</option>
                                <option value="Sr." <?= $data['suffix'] == "Sr." ? 'selected' : '' ?>>Sr.</option>
                                <option value="Jr." <?= $data['suffix'] == "Jr." ? 'selected' : '' ?>>Jr.</option>
                                <option value="III" <?= $data['suffix'] == "III" ? 'selected' : '' ?>>III</option>
                            </select>
                        </div>
                    </div>

                    <h1 class="info-title">Other Information</h1>
                    <hr>
                    <div class="faculty-name-container">
                        <div>
                            <label for="sex">Gender</label>
                            <select id="sex" name="sex" class="name-input">
                                <option value="Male" <?= $data['sex'] == "Male" ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $data['sex'] == "Female" ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div>
                            <label for="phone_no">Contact Number</label>
                            <input class="name-input" type="tel" id="phone_no" name="phone_no"
                                value="<?= htmlspecialchars($data['phone_no']) ?>" required>
                        </div>
                        <div>
                            <label for="dob">Birth Date</label>
                            <input class="name-input" type="date" id="dob" name="dob"
                                value="<?= $data['dob']->format('Y-m-d') ?>">
                        </div>
                        <div>
                            <label for="email">Email</label>
                            <input class="name-input" type="email" id="email" name="email"
                                value="<?= htmlspecialchars($data['email']) ?>" required>
                        </div>
                        <div>
                            <label for="section_id">Section</label>
                            <select id="section_id" name="section_id" class="name-input-2" required>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?= htmlspecialchars($section['section_id']) ?>"
                                        <?= $data['section_id'] == $section['section_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($section['section_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <h1 class="info-title">Address</h1>
                    <hr>
                    <div class="faculty-name-container">
                        <div class="faculty-name-box">
                            <label for="region">Region</label>
                            <select id="region" name="region" class="name-input" required>
                                <option value="NCR" <?= $data['region'] == "NCR" ? 'selected' : '' ?>>NCR</option>
                                <option value="CAR" <?= $data['region'] == "CAR" ? 'selected' : '' ?>>CAR</option>
                                <!-- Add other regions as needed -->
                            </select>
                        </div>
                        <div class="faculty-name-box">
                            <label for="city">City</label>
                            <select id="city" name="city" class="name-input" required>
                                <option value="Caloocan City" <?= $data['city'] == "Caloocan City" ? 'selected' : '' ?>>
                                    Caloocan
                                    City</option>
                                <!-- Add other cities as needed -->
                            </select>
                        </div>
                        <div class="faculty-name-box">
                            <label for="province">Province</label>
                            <select id="province" name="province" class="name-input" required>
                                <option value="None" <?= $data['province'] == "None" ? 'selected' : '' ?>>None</option>
                                <option value="Abra" <?= $data['province'] == "Abra" ? 'selected' : '' ?>>Abra</option>
                                <!-- Add other provinces as needed -->
                            </select>
                        </div>
                        <div>
                            <label for="zip_code">Zip Code</label>
                            <input class="name-input" type="text" id="zip_code" name="zip_code"
                                value="<?= htmlspecialchars($data['zip_code']) ?>" required>
                        </div>
                        <div>
                            <label for="address_dtl">Address Detail</label>
                            <input class="name-input-2" type="text" id="address_dtl" name="address_dtl"
                                value="<?= htmlspecialchars($data['address_dtl']) ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="modalOverlay" class="modal-overlay"></div>

    <!-- Archive Confirmation Modal -->
    <div id="archiveModal" class="custom-modal">
        <div class="modal-content">
            <h2>Confirm Archive</h2>
            <p>Are you sure you want to archive this student?</p>
            <div class="modal-actions">
                <button id="confirmArchive" class="btn-confirm">Yes, Archive</button>
                <button onclick="closeArchiveModal()" class="btn-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="custom-modal">
        <div class="modal-content">
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete this student?</p>
            <div class="modal-actions">
                <button id="confirmDelete" class="btn-confirm">Yes, Delete</button>
                <button onclick="closeDeleteModal()" class="btn-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Reset Confirmation Modal -->
    <div id="resetModal" class="custom-modal">
        <div class="modal-content">
            <h2>Confirm Password Reset</h2>
            <p>Are you sure you want to reset the password of this student?</p>
            <div class="modal-actions">
                <button id="confirmReset" class="btn-confirm">Yes, Reset</button>
                <button onclick="closeResetModal()" class="btn-cancel">Cancel</button>
            </div>
        </div>
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
<script>
    function openArchiveModal(rfidNo) {
        document.getElementById("archiveModal").style.display = "block"; // Show modal
        document.getElementById("modalOverlay").style.display = "block"; // Show dark overlay
        document.getElementById("confirmArchive").setAttribute("data-rfid", rfidNo); // Store RFID
    }

    function closeArchiveModal() {
        document.getElementById("archiveModal").style.display = "none"; // Hide modal
        document.getElementById("modalOverlay").style.display = "none"; // Hide dark overlay
    }

    // When "Yes, Archive" button is clicked, redirect to archive_faculty.php
    document.getElementById("confirmArchive").addEventListener("click", function () {
        let rfidNo = this.getAttribute("data-rfid"); // Get stored RFID
        window.location.href = `../functions/archive-student.php?rfid_no=${rfidNo}`; // Redirect with RFID
    });

    function openDeleteModal(rfidNo) {
        document.getElementById("deleteModal").style.display = "block"; // Show modal
        document.getElementById("modalOverlay").style.display = "block"; // Show dark overlay
        document.getElementById("confirmDelete").setAttribute("data-rfid", rfidNo); // Store RFID
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").style.display = "none"; // Hide modal
        document.getElementById("modalOverlay").style.display = "none"; // Hide dark overlay
    }

    // When "Yes, Delete" button is clicked, redirect to delete_faculty.php
    document.getElementById("confirmDelete").addEventListener("click", function () {
        let rfidNo = this.getAttribute("data-rfid"); // Get stored RFID
        window.location.href = `../functions/delete-student.php?rfid_no=${rfidNo}`; // Redirect with RFID
    });

    function openResetModal(rfidNo) {
        document.getElementById("resetModal").style.display = "block"; // Show modal
        document.getElementById("modalOverlay").style.display = "block"; // Show dark overlay
        document.getElementById("confirmReset").setAttribute("data-rfid", rfidNo); // Store RFID
    }

    function closeResetModal() {
        document.getElementById("resetModal").style.display = "none"; // Hide modal
        document.getElementById("modalOverlay").style.display = "none"; // Hide dark overlay
    }

    // When "Yes, Delete" button is clicked, redirect to reset-faculty-pass.php
    document.getElementById("confirmReset").addEventListener("click", function () {
        let rfidNo = this.getAttribute("data-rfid"); // Get stored RFID
        window.location.href = `../functions/reset-student-pass.php?rfid_no=${rfidNo}`; // Redirect with RFID
    });
</script>

</html>