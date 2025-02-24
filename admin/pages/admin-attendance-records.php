<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

$admin_rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);

$sql = "
    SELECT 
        ar.attd_ref,
        ar.rfid_no,
        f.fname AS prof_fname, 
        f.lname AS prof_lname, 
        ar.time_in, 
        ar.time_out, 
        ar.status, 
        ar.date_logged
    FROM AttendanceRecords ar
    JOIN Faculty f ON ar.rfid_no = f.rfid_no
    ORDER BY ar.date_logged DESC, ar.time_in ASC
";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Database error: " . print_r(sqlsrv_errors(), true));
}

// Fetch attendance status counts (without filtering by month and year)
$sqlAttendanceCount = "SELECT status, COUNT(*) as count 
                       FROM AttendanceRecords 
                       WHERE status IN ('Present', 'Absent', 'Late')
                       GROUP BY status";

$stmtAttendance = sqlsrv_query($conn, $sqlAttendanceCount);

$attendanceCounts = ['Present' => 0, 'Absent' => 0, 'Late' => 0];
while ($row = sqlsrv_fetch_array($stmtAttendance, SQLSRV_FETCH_ASSOC)) {
    $attendanceCounts[ucwords(strtolower(trim($row['status'])))] = $row['count'];
}

// Convert data to JSON for JavaScript
$attendanceData = json_encode($attendanceCounts);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
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
        <div id="header">
            <h1 class="title-text">Attendance</h1>

            <!-- Display Current Date and Time -->
            <div id="current-datetime" class="current-datetime">
                <p id="date-time"></p> <!-- Updated id here -->
            </div>
        </div>

        <div class="action-widgets">
            <div class="widget-button">
                <div class="buttons">
                    <button class="abtn" type="button" id="openModal">Generate Report</button>
                </div>
            </div>
            <div class="widget-search"></div>
        </div>
        <div id="messageBox" class="message-box"></div>
        <div class="apt-dashboard-widgets">
            <div class="widget apt-table-design">
                <h2 class="tbl-title">Attendance Records</h2>
                <table id="attendanceTable" class="display">
                    <thead class="tbl-header">
                        <tr>
                            <th>Professor</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Date</th>
                            <th>Action</th>
                            <th>Status</th> <!-- Moved to last column -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                            <?php
                            $statusClasses = [
                                'present' => 'status-present',
                                'late' => 'status-late',
                                'absent' => 'status-absent'
                            ];
                            $statusKey = strtolower(trim($row['status']));
                            $statusClass = $statusClasses[$statusKey] ?? 'status-default';

                            $professor = htmlspecialchars($row['prof_fname'] . " " . $row['prof_lname']);
                            $timeIn = $row['time_in'] ? $row['time_in']->format('h:i A') : 'N/A';
                            $timeOut = $row['time_out'] ? $row['time_out']->format('h:i A') : 'N/A';
                            $dateLogged = $row['date_logged'] ? $row['date_logged']->format('Y-m-d') : 'N/A';

                            $attdRef = htmlspecialchars($row['attd_ref']);
                            $rfidNo = htmlspecialchars($row['rfid_no']);
                            ?>
                            <tr class="tbl-row">
                                <td class="small-text"><?= $professor ?></td>
                                <td><?= $timeIn ?></td>
                                <td><?= $timeOut ?></td>
                                <td><?= $dateLogged ?></td>
                                <td>
                                    <!--<button class="archive-btn" data-attd-ref="<?= $attdRef ?>"
                                        data-rfid-no="<?= $rfidNo ?>">Archive</button>-->
                                    <button class="delete-btn action-btnn delete-btn" data-attd-ref="<?= $attdRef ?>"
                                        data-rfid-no="<?= $rfidNo ?>"><i class="fas fa-trash"></i></button>
                                </td>
                                <td class="attendance-status <?= $statusClass ?>"><?= ucwords($statusKey) ?></td>
                                <!-- Status at last column -->
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    // Only attach click event for rows with the 'clickable' class
                    const clickableRows = document.querySelectorAll("#appointmentTable tbody tr.clickable");
                    clickableRows.forEach(function (row) {
                        row.addEventListener("click", function () {
                            const appointmentCode = this.getAttribute("data-appointment-code");
                            if (appointmentCode) {
                                // Redirect to admin-update-appointment.php with the appointment_code as a GET parameter
                                window.location.href = "admin-update-appointment.php?appointment_code=" + encodeURIComponent(appointmentCode);
                            }
                        });
                    });
                });
            </script>


            <div class="widget widget-chart">
                <div class="widget-header">
                    <span class="widget-ttl">Attendance Trends</span>
                </div>
                <div class="widget-body">
                    <canvas id="attendanceChart"></canvas>
                </div>
                <br>
            </div>
        </div>

    </div>

    <!-- Dark background overlay -->
    <div id="modalOverlay" class="modal-overlay"></div>

    <!-- Archive Confirmation Modal -->
    <div id="archiveModal" class="custom-modal">
        <div class="modal-content">
            <h2>Confirm Archive</h2>
            <p>Are you sure you want to archive this attendance record?</p>
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
            <p>Are you sure you want to delete this attendance record?</p>
            <div class="modal-actions">
                <button id="confirmDelete" class="btn-confirm">Yes, Delete</button>
                <button onclick="closeDeleteModal()" class="btn-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <button id="scrollToTopBtn" onclick="scrollToTop()">↑</button>

    <div id="customModal1" class="modal-overlays">
        <div class="modal-container1">
            <div class="modal-header1">
                <h3>Generate Attendance Records</h3>
                <span class="close-modal1">&times;</span>
            </div>
            <div class="modal-body1">
                <label for="facultySelect">Select Faculty:</label>

                <select id="facultySelect" name="rfid_no" class="form-select">
                    <?php include '../functions/fetch-faculty.php'; ?>
                </select>

                <label for="reportType" class="mt-3">Select Report Type:</label>
                <select class="form-select" id="reportType">
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>

                <div id="extraFields" class="mt-3"></div> <!-- Dynamic Fields -->

                <button class="generate-btn mt-3" id="generatePdf">Generate Report</button>
            </div>
        </div>
    </div>

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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.6.0/jspdf.plugin.autotable.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#attendanceTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true
        });
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Bar Chart (Only 'Present', 'Absent', and 'Late')
    const attendanceCounts = <?= $attendanceData ?>;
    const attendanceLabels = Object.keys(attendanceCounts);
    const attendanceData = Object.values(attendanceCounts);
    const ctxAttendance = document.getElementById("attendanceChart").getContext("2d");

    if (attendanceData.length === 0 || attendanceData.every(val => val === 0)) {
        ctxAttendance.font = "13.3px Poppins";
        ctxAttendance.textAlign = "center";
        ctxAttendance.fillText("No data available for this month", ctxAttendance.canvas.width / 1.6, ctxAttendance.canvas.height / 2);
    } else {
        new Chart(ctxAttendance, {
            type: "bar",
            data: {
                labels: attendanceLabels,
                datasets: [{
                    label: "Attendance Status Count",
                    data: attendanceData,
                    backgroundColor: ["#4caf50", "#f44336", "#ff9800"], // Green, Red, Orange
                    barThickness: 80
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 2,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                family: "Poppins",
                                size: 12
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: "Poppins",
                                size: 12
                            }
                        }
                    }
                },
            }
        });
    }
</script>


<script>
    // Trigger fade-out effect before navigating
    window.addEventListener('beforeunload', function () {
        document.body.classList.add('fade-out');
    });
</script>

<script>
    function updateDateTime() {
        // Get current date and time
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const date = now.toLocaleDateString('en-US', options);
        const time = now.toLocaleTimeString('en-US');

        // Display the date and time
        document.getElementById('date-time').textContent = date + ' | ' + time;
    }

    // Update the time every second
    setInterval(updateDateTime, 1000);

    // Initial call to display the current date and time
    updateDateTime();

</script>
<script>
    let selectedAttdRef = null;
    let selectedRfidNo = null;

    document.querySelectorAll(".archive-btn").forEach(button => {
        button.addEventListener("click", function () {
            selectedAttdRef = this.getAttribute("data-attd-ref");
            selectedRfidNo = this.getAttribute("data-rfid-no");
            openArchiveModal();
        });
    });

    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function () {
            selectedAttdRef = this.getAttribute("data-attd-ref");
            selectedRfidNo = this.getAttribute("data-rfid-no");
            openDeleteModal();
        });
    });

    document.getElementById("confirmArchive").addEventListener("click", function () {
        window.location.href = "../functions/archive-attendance.php?attd_ref=" + selectedAttdRef + "&rfid_no=" + selectedRfidNo;
    });

    document.getElementById("confirmDelete").addEventListener("click", function () {
        window.location.href = "../functions/delete-attendance.php?attd_ref=" + selectedAttdRef + "&rfid_no=" + selectedRfidNo;
    });

    function openArchiveModal() {
        document.getElementById("modalOverlay").style.display = "block";
        document.getElementById("archiveModal").style.display = "block";
    }

    function closeArchiveModal() {
        document.getElementById("modalOverlay").style.display = "none";
        document.getElementById("archiveModal").style.display = "none";
    }

    function openDeleteModal() {
        document.getElementById("modalOverlay").style.display = "block";
        document.getElementById("deleteModal").style.display = "block";
    }

    function closeDeleteModal() {
        document.getElementById("modalOverlay").style.display = "none";
        document.getElementById("deleteModal").style.display = "none";
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
        }, 5000);

        // Remove message from URL without reloading
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("customModal1");
        const openModal = document.getElementById("openModal");
        const closeModal = document.querySelector(".close-modal1");
        const reportType = document.getElementById("reportType");
        const extraFields = document.getElementById("extraFields");
        const generatePdf = document.getElementById("generatePdf");

        // Open & Close Modal
        openModal.addEventListener("click", () => modal.style.display = "flex");
        closeModal.addEventListener("click", () => modal.style.display = "none");
        window.addEventListener("click", (event) => {
            if (event.target === modal) modal.style.display = "none";
        });

        // Handle report type selection
        reportType.addEventListener("change", function () {
            extraFields.innerHTML = "";
            let selectedType = this.value;

            if (selectedType === "weekly") {
                extraFields.innerHTML = `
            <label>Select Week:</label>
            <input type="week" id="reportWeek" class="form-control">
        `;
                generatePdf.setAttribute("data-type", "weekly");

            } else if (selectedType === "monthly") {
                extraFields.innerHTML = `
            <label>Select Month:</label>
            <input type="month" id="reportMonth" class="form-control">
        `;
                generatePdf.setAttribute("data-type", "monthly");

            } else if (selectedType === "yearly") {
                extraFields.innerHTML = `
            <label>Select Year:</label>
            <select id="reportYear" class="form-select">
                ${generateYearOptions()}
            </select>
        `;
                generatePdf.setAttribute("data-type", "yearly");
            }
        });


        // Function to generate year options dynamically
        function generateYearOptions() {
            let currentYear = new Date().getFullYear();
            let options = '<option value="" disabled selected>Select a Year</option>';
            for (let year = currentYear; year >= currentYear - 10; year--) { // Adjust range as needed
                options += `<option value="${year}">${year}</option>`;
            }
            return options;
        }


        // Call the correct function based on report type
        generatePdf.addEventListener("click", function () {
            let reportType = this.getAttribute("data-type");

            let facultyRFID = document.getElementById("facultySelect")?.value;
            console.log("Selected RFID:", facultyRFID);

            if (reportType === "weekly") {
                generateWeeklyReport();
            } else if (reportType === "monthly") {
                generateMonthlyReport();
            } else if (reportType === "yearly") {
                generateYearlyReport();
            } else {
                alert("Please select a valid report type.");
            }
        });


        // ------------------------ MONTHLY REPORT FUNCTION ------------------------
        function generateMonthlyReport() {
            const { jsPDF } = window.jspdf;
            let doc = new jsPDF();

            let selectedMonth = document.getElementById("reportMonth")?.value;
            let facultySelect = document.getElementById("facultySelect");
            let facultyRFID = facultySelect?.value;
            let facultyName = facultySelect?.options[facultySelect.selectedIndex]?.text || "Unknown Faculty";

            if (!selectedMonth) {
                alert("Please select a month.");
                return;
            }

            if (!facultyRFID) {
                alert("Please select a faculty member.");
                return;
            }

            fetch(`../functions/fetch-records-by-month.php?month=${selectedMonth}&rfid_no=${facultyRFID}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Fetched Data:", data);

                    if (!data.attendanceRecords || data.attendanceRecords.length === 0) {
                        alert("No attendance records found for this month.");
                        return;
                    }

                    // Add Report Header with Faculty Name
                    addReportHeader(doc, `Monthly Attendance Report - ${selectedMonth}`);
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text(`Faculty: ${facultyName}`, 14, 62);

                    let y = 65;

                    // Table Header
                    let tableData = [
                        ["Date", "Time In", "Time Out", "Total Hours", "Status"]
                    ];

                    // Add attendance records
                    data.attendanceRecords.forEach(record => {
                        tableData.push([
                            record.date_logged || "N/A",
                            formatTime(record.time_in),
                            formatTime(record.time_out),
                            record.total_hours || "0",
                            record.status || "N/A"
                        ]);
                    });

                    // Generate table
                    doc.autoTable({
                        startY: y,
                        head: [tableData[0]],
                        body: tableData.slice(1),
                        theme: "grid",
                        styles: { fontSize: 8 },
                        margin: { bottom: 10 }
                    });

                    y = doc.lastAutoTable.finalY + 10; // Position after table

                    // Attendance Summary Section (Still on the same page)
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text("Attendance Status Count", 14, y);
                    doc.setFont("helvetica", "normal");
                    y += 8;

                    Object.entries(data.statusCounts).forEach(([status, count]) => {
                        doc.text(`${status}: ${count}`, 20, y);
                        y += 6;
                    });

                    y += 8; // Space before Grand Total
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text(`Grand Total of Hours: ${data.grandTotalHours || 0} hrs`, 14, y);

                    addPageNumbers(doc);
                    doc.save(`Monthly_Attendance_Report_${selectedMonth}.pdf`);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    alert("Error generating monthly report.");
                });
        }


        // ------------------------ WEEKLY REPORT FUNCTION ------------------------
        function generateWeeklyReport() {
            const { jsPDF } = window.jspdf;
            let doc = new jsPDF();

            let weekInput = document.getElementById("reportWeek")?.value; // Get value from input
            let facultySelect = document.getElementById("facultySelect");
            let facultyRFID = facultySelect?.value;
            let facultyName = facultySelect?.options[facultySelect.selectedIndex]?.text || "Unknown Faculty";

            if (!weekInput) {
                alert("Please select a week.");
                return;
            }

            if (!facultyRFID) {
                alert("Please select a faculty member.");
                return;
            }

            // Extract year and week number from the input
            let [selectedYear, selectedWeek] = weekInput.split("-W");

            if (!selectedYear || !selectedWeek) {
                alert("Invalid week format.");
                return;
            }

            console.log(`Selected Year: ${selectedYear}, Selected Week: ${selectedWeek}`);

            // Ensure API request includes the 'year' parameter
            fetch(`../functions/fetch-records-by-week.php?week=${selectedWeek}&year=${selectedYear}&rfid_no=${facultyRFID}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Fetched Data:", data);

                    if (!data.attendanceRecords || data.attendanceRecords.length === 0) {
                        alert("No attendance records found for this week.");
                        return;
                    }

                    // Add Report Header with Faculty Name
                    addReportHeader(doc, `Weekly Attendance Report - Week ${selectedWeek}, ${selectedYear}`);
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text(`Faculty: ${facultyName}`, 14, 62);

                    let y = 65;

                    // Table Header
                    let tableData = [
                        ["Date", "Time In", "Time Out", "Total Hours", "Status"]
                    ];

                    // Add attendance records
                    data.attendanceRecords.forEach(record => {
                        tableData.push([
                            record.date_logged || "N/A",
                            formatTime(record.time_in),
                            formatTime(record.time_out),
                            record.total_hours || "0",
                            record.status || "N/A"
                        ]);
                    });

                    // Generate table
                    doc.autoTable({
                        startY: y,
                        head: [tableData[0]],
                        body: tableData.slice(1),
                        theme: "grid",
                        styles: { fontSize: 8 },
                        margin: { bottom: 10 }
                    });

                    y = doc.lastAutoTable.finalY + 10; // Position after table

                    // Attendance Summary Section
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text("Attendance Status Count", 14, y);
                    doc.setFont("helvetica", "normal");
                    y += 8;

                    Object.entries(data.statusCounts).forEach(([status, count]) => {
                        doc.text(`${status}: ${count}`, 20, y);
                        y += 6;
                    });

                    y += 8; // Space before Grand Total
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text(`Grand Total of Hours: ${data.grandTotalHours || 0} hrs`, 14, y);

                    addPageNumbers(doc);
                    doc.save(`Weekly_Attendance_Report_Week_${selectedWeek}_${selectedYear}.pdf`);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    alert("Error generating weekly report.");
                });
        }


        // ------------------------ YEARLY REPORT FUNCTION ------------------------
        function generateYearlyReport() {
            const { jsPDF } = window.jspdf;
            let doc = new jsPDF();

            let selectedYear = document.getElementById("reportYear")?.value;
            let facultySelect = document.getElementById("facultySelect");
            let facultyRFID = facultySelect?.value;
            let facultyName = facultySelect?.options[facultySelect.selectedIndex]?.text || "Unknown Faculty";

            if (!selectedYear) {
                alert("Please select a year.");
                return;
            }

            if (!facultyRFID) {
                alert("Please select a faculty member.");
                return;
            }

            // Fetch attendance records for the selected year
            fetch(`../functions/fetch-records-by-year.php?year=${selectedYear}&rfid_no=${facultyRFID}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Fetched Data:", data);

                    if (!data.attendanceRecords || data.attendanceRecords.length === 0) {
                        alert("No attendance records found for this year.");
                        return;
                    }

                    // Add Report Header
                    addReportHeader(doc, `Yearly Attendance Report - ${selectedYear}`);
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text(`Faculty: ${facultyName}`, 14, 62);

                    let y = 70;
                    let recordsByMonth = {};

                    // Group records by month
                    data.attendanceRecords.forEach(record => {
                        let month = new Date(record.date_logged).toLocaleString('en-us', { month: 'long' });
                        if (!recordsByMonth[month]) {
                            recordsByMonth[month] = [];
                        }
                        recordsByMonth[month].push([
                            record.date_logged || "N/A",
                            formatTime(record.time_in),
                            formatTime(record.time_out),
                            record.total_hours || "0",
                            record.status || "N/A"
                        ]);
                    });

                    // Generate tables for each month
                    Object.keys(recordsByMonth).forEach((month, index, monthsArray) => {
                        if (y + 40 > doc.internal.pageSize.height) { // Check if there's space for the month header
                            doc.addPage();
                            y = 20;
                        }

                        doc.setFontSize(12);
                        doc.setFont("helvetica", "bold");
                        doc.text(month, 14, y);
                        y += 5;

                        let tableStartY = y;
                        doc.autoTable({
                            startY: y,
                            head: [["Date", "Time In", "Time Out", "Total Hours", "Status"]],
                            body: recordsByMonth[month],
                            theme: "grid",
                            styles: { fontSize: 8 },
                            margin: { bottom: 10 },
                            didDrawPage: (data) => {
                                y = data.cursor.y + 10; // Update y position after the table
                            }
                        });

                        // If the next table won't fit on this page, move to a new one
                        if (index < monthsArray.length - 1 && y + 40 > doc.internal.pageSize.height) {
                            doc.addPage();
                            y = 20;
                        }
                    });

                    // Attendance Summary Section
                    if (y + 40 > doc.internal.pageSize.height) {
                        doc.addPage();
                        y = 20;
                    }

                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text("Attendance Status Count", 14, y);
                    doc.setFont("helvetica", "normal");
                    y += 8;

                    Object.entries(data.statusCounts).forEach(([status, count]) => {
                        doc.text(`${status}: ${count}`, 20, y);
                        y += 6;
                    });

                    y += 8;
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text(`Grand Total of Hours: ${data.grandTotalHours || 0} hrs`, 14, y);

                    addPageNumbers(doc);
                    doc.save(`Yearly_Attendance_Report_${selectedYear}.pdf`);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    alert("Error generating yearly report.");
                });
        }


        // ------------------------ HELPER FUNCTIONS ------------------------
        function addReportHeader(doc, title) {
            let logoImage = "../../assets/images/csa_logo.png";
            doc.addImage(logoImage, "PNG", 15, 10, 26, 26);

            doc.setFont("times", "bold");
            doc.setFontSize(18);
            doc.text("Colegio de Sta. Teresa de Avila", 50, 18);

            doc.setFont("helvetica", "normal");
            doc.setFontSize(10);
            doc.text("Address: 6 Kingfisher corner Skylark Street, Zabarte Subdivision,", 50, 25);
            doc.text("Brgy. Kaligayahan, Novaliches, Quezon City, Philippines", 50, 30);
            doc.text("Contact: 282753916 | Email: officialregistrarcsta@gmail.com", 50, 35);

            doc.setLineWidth(0.5);
            doc.line(15, 40, 195, 40);

            doc.setFont("helvetica", "bold");
            doc.setFontSize(14);
            doc.text(title, 15, 50);

            let timestamp = new Date().toLocaleString();
            doc.setFont("helvetica", "italic");
            doc.setFontSize(9);
            doc.text(`Generated on: ${timestamp}`, 15, 55);
        }

        function addPageNumbers(doc) {
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(9);
                doc.text(`Page ${i} of ${pageCount}`, 180, 285);
            }
        }

        function formatTime(time) {
            if (!time) return "N/A";

            // Handle case where time is an object
            if (typeof time === "object" && time.date) {
                let dateObj = new Date(time.date);
                return dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
            }

            // Handle case where time is a string (e.g., "08:40:00")
            if (typeof time === "string") {
                let date = new Date(`1970-01-01T${time}`);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
            }

            return time; // Return as-is if already formatted
        }

    });

</script>

</html>