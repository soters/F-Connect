<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

$admin_rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);

// Get selected month and year from the request (default to current month and year)
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Fetch status counts
$sqlStatusCount = "SELECT status, COUNT(*) as count 
                   FROM Appointments 
                   WHERE MONTH(date_logged) = ? 
                   AND YEAR(date_logged) = ? 
                   AND status IN ('Completed', 'Declined', 'Cancelled')
                   GROUP BY status";

$params = array($selectedMonth, $selectedYear);
$stmtStatus = sqlsrv_query($conn, $sqlStatusCount, $params);

$statusCounts = ['Completed' => 0, 'Declined' => 0, 'Cancelled' => 0];
while ($row = sqlsrv_fetch_array($stmtStatus, SQLSRV_FETCH_ASSOC)) {
    $statusCounts[ucwords(strtolower(trim($row['status'])))] = $row['count'];
}

// Fetch agenda counts
$sqlAgendaCount = "SELECT agenda, COUNT(*) as count 
                   FROM Appointments 
                   WHERE MONTH(date_logged) = ? 
                   AND YEAR(date_logged) = ? 
                   GROUP BY agenda";

$stmtAgenda = sqlsrv_query($conn, $sqlAgendaCount, $params);
$agendaCounts = [];

while ($row = sqlsrv_fetch_array($stmtAgenda, SQLSRV_FETCH_ASSOC)) {
    $agendaCounts[ucwords(strtolower(trim($row['agenda'])))] = $row['count'];
}

// Query for top 3 professors with highest "Completed" appointments in selected month and year
$sqlTopProf = "
    SELECT TOP 3 
        f.rfid_no, 
        CONCAT(f.fname, ' ', COALESCE(f.mname + ' ', ''), f.lname) AS fullname, 
        COUNT(a.appointment_code) AS completed_count
    FROM Appointments a
    JOIN Faculty f ON a.prof_rfid_no = f.rfid_no
    WHERE a.status = 'Completed' 
    AND MONTH(a.date_logged) = ? 
    AND YEAR(a.date_logged) = ?
    GROUP BY f.rfid_no, f.fname, f.mname, f.lname
    ORDER BY completed_count DESC
";

$stmtTopProf = sqlsrv_query($conn, $sqlTopProf, $params);

$topProfs = [];
while ($row = sqlsrv_fetch_array($stmtTopProf, SQLSRV_FETCH_ASSOC)) {
    $topProfs[] = [
        'prof_rfid_no' => $row['rfid_no'],
        'fullname' => $row['fullname'],
        'completed_count' => $row['completed_count']
    ];
}


// Query to select all appointments (no date filter)
$sql = "
    SELECT 
        a.appointment_code, 
        f.fname AS prof_fname, 
        f.lname AS prof_lname, 
        s.fname AS stud_fname, 
        s.lname AS stud_lname, 
        a.start_time, 
        a.end_time, 
        a.agenda, 
        a.status,
        a.date_logged
    FROM Appointments a
    JOIN Faculty f ON a.prof_rfid_no = f.rfid_no
    JOIN Students s ON a.stud_rfid_no = s.rfid_no
    ORDER BY a.start_time ASC
";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die("Database error: " . print_r(sqlsrv_errors(), true));
}

// Convert agenda and status count data to JSON for Chart.js
$statusData = json_encode($statusCounts);
$agendaData = json_encode($agendaCounts);
$statusHasData = array_sum($statusCounts) > 0 ? 'true' : 'false';
$agendaHasData = count($agendaCounts) > 0 ? 'true' : 'false';

$appointmentQuery = "SELECT 
    appointment_code,
    prof_rfid_no,
    stud_rfid_no,
    CONVERT(NVARCHAR(8), start_time, 108) AS start_time,  -- Converts to HH:mm:ss
    CONVERT(NVARCHAR(8), end_time, 108) AS end_time,  
    ISNULL(agenda, 'No Agenda') AS agenda,
    status,
    FORMAT(date_logged, 'yyyy-MM-dd') AS date_logged
FROM Appointments
ORDER BY date_logged DESC;
";

$appointmentStmt = sqlsrv_query($conn, $appointmentQuery); // ✅ FIXED
if ($appointmentStmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch all records
$appointmentData = [];
while ($row = sqlsrv_fetch_array($appointmentStmt, SQLSRV_FETCH_ASSOC)) {
    $appointmentData[] = $row;
}


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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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
            <h1 class="title-text">Appointments History</h1>

            <!-- Display Current Date and Time -->
            <div id="current-datetime" class="current-datetime">
                <p id="date-time"></p> <!-- Updated id here -->
            </div>
        </div>

        <div class="action-widgets">
            <div class="widget-button">
                <div class="buttons">
                    <a href="admin-appointment.php">
                        <button class="habtn" type="button">Back
                        </button>
                    </a>
                    <form method="GET" id="filterForm">

                        <select class="selector" name="month" id="month">
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                $monthValue = str_pad($i, 2, "0", STR_PAD_LEFT);
                                echo "<option value='$monthValue' " . ($selectedMonth == $monthValue ? 'selected' : '') . ">" . date("F", mktime(0, 0, 0, $i, 1)) . "</option>";
                            }
                            ?>
                        </select>


                        <select class="selector" name="year" id="year">
                            <?php
                            $currentYear = date("Y");
                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                echo "<option value='$y' " . ($selectedYear == $y ? 'selected' : '') . ">$y</option>";
                            }
                            ?>
                        </select>
                        <button class="fabtn" type="submit">Filter</button>
                    </form>

                    <script>
                        document.getElementById('month').addEventListener('change', function () {
                            document.getElementById('filterForm').submit();
                        });

                        document.getElementById('year').addEventListener('change', function () {
                            document.getElementById('filterForm').submit();
                        });
                    </script>

                    <button class="gabtn-red" id="openModal">Generate Report</button>
                </div>
            </div>
            <div class="widget-search"></div>
        </div>
        <div class="dashboard-widgets-charts">
            <div class="chart-widget">
                <canvas id="agendaChart"></canvas>
            </div>
            <div class="chart-widget">
                <canvas id="statusChart"></canvas>
            </div>
            <?php if (!empty($topProfs)): ?>
                <div class="chart-widget">
                    <h2 class="tbl-title-2">Most Appointment / month</h2>
                    <?php foreach ($topProfs as $prof): ?>
                        <div class="most-card">
                            <h3 class="prof-full-name"><?= htmlspecialchars($prof['fullname']) ?></h3>
                            <p class="completed-lbl"><i>Completed Appointments:</i>
                                <span class="count"><?= $prof['completed_count'] ?></span>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="chart-widget">
                    <p class="no-data-message-4">No data available for this month</p>
                </div>
            <?php endif; ?>


        </div>
        <div id="messageBox" class="message-box"></div>
        <div class="apt-dashboard-widgets-3">
            <div class="widget apt-table-design">
                <h2 class="tbl-title">History</h2>
                <table id="appointmentTable" class="display">
                    <thead class="tbl-header">
                        <tr>
                            <th>Professor</th>
                            <th>Student</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Agenda</th>
                            <th>Date</th>
                            <th>Action</th>
                            <th>Status</th> <!-- Moved to the last column -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                            <?php
                            $statusClasses = [
                                'pending' => 'status-pending',
                                'accepted' => 'status-accepted',
                                'completed' => 'status-completed',
                                'declined' => 'status-declined',
                                'cancelled' => 'status-cancelled'
                            ];

                            $statusKey = strtolower(trim($row['status']));
                            $statusClass = $statusClasses[$statusKey] ?? 'status-default';

                            $isClickable = in_array($statusKey, ['pending', 'accepted']);

                            $professor = htmlspecialchars($row['prof_fname'] . " " . $row['prof_lname']);
                            $student = htmlspecialchars($row['stud_fname'] . " " . $row['stud_lname']);
                            $agenda = !empty($row['agenda']) ? htmlspecialchars($row['agenda']) : 'No Agenda Provided';
                            $dateLogged = $row['date_logged'] ? $row['date_logged']->format('Y-m-d') : 'N/A';
                            $appointmentCode = htmlspecialchars($row['appointment_code']);
                            ?>
                            <tr class="tbl-row <?= $isClickable ? 'clickable' : '' ?>" <?= $isClickable ? 'data-appointment-code="' . $appointmentCode . '"' : '' ?>>
                                <td class="small-text"><?= $professor ?></td>
                                <td class="small-text"><?= $student ?></td>
                                <td><?= $row['start_time'] ? $row['start_time']->format('h:i A') : 'N/A' ?></td>
                                <td><?= $row['end_time'] ? $row['end_time']->format('h:i A') : 'N/A' ?></td>
                                <td><?= $agenda ?></td>
                                <td><?= $dateLogged ?></td>
                                <td>
                                    <button class="btn-delete action-btnn delete-btn"
                                        onclick="openDeleteModal('<?= $appointmentCode ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                                <td class="appointment-status <?= $statusClass ?>"><?= ucwords($statusKey) ?></td>
                                <!-- Status now at the last column -->
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
        </div>
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

    <div id="customModal1" class="modal-overlays">
        <div class="modal-container1">
            <div class="modal-header1">
                <h3>Generate Appointment Report</h3>
                <span class="close-modal1">&times;</span>
            </div>
            <div class="modal-body1">
                <label for="reportType">Select Report Type:</label>
                <select class="form-select" id="reportType">
                    <option value="daily">Daily</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="faculty">By Faculty</option>
                </select>

                <div id="extraFields" class="mt-3"></div> <!-- Dynamic Fields -->

                <button class="generate-btn" id="generatePdf">Generate Report</button>
            </div>
        </div>
    </div>

</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.6.0/jspdf.plugin.autotable.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

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
    $(document).ready(function () {
        $('#appointmentTable').DataTable({
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

<!-- JavaScript for Actions -->
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
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var statusData = <?php echo $statusData; ?>;
    var agendaData = <?php echo $agendaData; ?>;
    var statusHasData = <?php echo $statusHasData; ?>;
    var agendaHasData = <?php echo $agendaHasData; ?>;
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Agenda Pie Chart
        const agendaCounts = <?= $agendaData ?>;
        const agendaLabels = Object.keys(agendaCounts);
        const agendaData = Object.values(agendaCounts);
        const ctxAgenda = document.getElementById("agendaChart").getContext("2d");

        if (agendaData.length === 0 || agendaData.every(val => val === 0)) {
            ctxAgenda.font = "13.3px Poppins";

            ctxAgenda.fillText("No data available for this month", ctxAgenda.canvas.width / 3.5, ctxAgenda.canvas.height / 2);
        } else {
            new Chart(ctxAgenda, {
                type: "pie",
                data: {
                    labels: agendaLabels,
                    datasets: [{
                        data: agendaData,
                        backgroundColor: ["#4CAF50", "#FF9800", "#F44336", "#2196F3"], // Green, Orange, Red, Blue
                        borderColor: "#ffffff",
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 1,
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                font: {
                                    family: "Poppins",
                                    size: 11
                                },
                                color: "#393242"
                            }
                        },
                        title: {
                            display: true,
                            text: "Agenda Overview / month",
                            font: {
                                family: "Poppins",
                                size: 16,
                                weight: "bold"
                            },
                            color: "#393242",
                        }
                    }
                }
            });
        }

        // Status Bar Chart (Only 'Completed', 'Declined', and 'Cancelled')
        const statusCounts = <?= $statusData ?>;
        const statusLabels = Object.keys(statusCounts);
        const statusData = Object.values(statusCounts);
        const ctxStatus = document.getElementById("statusChart").getContext("2d");

        if (statusData.length === 0 || statusData.every(val => val === 0)) {
            ctxStatus.font = "13.3px Poppins";
            ctxStatus.textAlign = "center";
            ctxStatus.fillText("No data available for this month", ctxStatus.canvas.width / 1.6, ctxStatus.canvas.height / 2);
        } else {
            new Chart(ctxStatus, {
                type: "bar",
                data: {
                    labels: statusLabels,
                    datasets: [{
                        label: "Appointment Status Count",
                        data: statusData,
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
                    plugins: {
                        title: {
                            display: true,
                            text: "Appointment Status Overview / month",
                            font: {
                                family: "Poppins",
                                size: 16,
                                weight: "bold"
                            },
                            color: "#393242",
                            padding: {
                                top: 10,
                                bottom: 20
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
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

            if (selectedType === "daily") {
                extraFields.innerHTML = '<label>Select Date:</label><input type="date" id="reportDate" class="form-control">';
                generatePdf.setAttribute("data-type", "daily");

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

            } else if (selectedType === "faculty") {
                extraFields.innerHTML = `
            <label>Select Faculty:</label>
            <select name="prof_rfid_no" id="prof_rfid_no" class="form-select">
                <option value="" disabled selected>Loading...</option>
            </select>`;
                generatePdf.setAttribute("data-type", "faculty");

                // Fetch faculty data
                fetch('../functions/fetch-faculty.php')
                    .then(response => response.text())
                    .then(options => {
                        document.getElementById("prof_rfid_no").innerHTML = '<option value="" disabled selected>Select a Faculty Member</option>' + options;
                    })
                    .catch(error => {
                        console.error("Error fetching faculty data:", error);
                        document.getElementById("prof_rfid_no").innerHTML = '<option value="" disabled selected>Error loading data</option>';
                    });
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
            if (reportType === "daily") {
                generateDailyReport();
            } else if (reportType === "faculty") {
                generateFacultyReport();
            } else if (reportType === "monthly") {
                generateMonthlyReport();
            } else if (reportType === "yearly") {
                generateYearlyReport();
            }
            else {
                alert("Please select a valid report type.");
            }
        });

        // ------------------------ DAILY REPORT FUNCTION ------------------------
        function generateDailyReport() {
            const { jsPDF } = window.jspdf;
            let doc = new jsPDF();
            let dateInput = document.getElementById("reportDate")?.value;

            if (!dateInput) {
                alert("Please select a valid date.");
                return;
            }

            fetch(`../functions/fetch-appointment-by-date.php?date=${dateInput}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.appointments || data.appointments.length === 0) {
                        alert("No appointments found for this date.");
                        return;
                    }

                    addReportHeader(doc, `Appointment Report - ${dateInput}`);

                    let tableData = [
                        ["Code", "Faculty", "Student", "Start", "End", "Agenda", "Status"]
                    ];

                    data.appointments.forEach(appointment => {
                        tableData.push([
                            appointment.appointment_code || "N/A",
                            `${appointment.fname || ""} ${appointment.lname || ""}`.trim(),
                            `${appointment.stud_fname || ""} ${appointment.stud_lname || ""}`.trim(),
                            formatTime(appointment.start_time),
                            formatTime(appointment.end_time),
                            appointment.agenda || "N/A",
                            appointment.status || "N/A"
                        ]);
                    });

                    doc.autoTable({
                        startY: 60,
                        head: [tableData[0]],
                        body: tableData.slice(1),
                        theme: "grid",
                        styles: { fontSize: 9 },
                        margin: { bottom: 20 }, // Ensure space for footer
                        didParseCell: function (data) {
                            if ([0, 3, 4, 5, 6].includes(data.column.index)) {
                                data.cell.styles.fontSize = 7;
                            }
                        }
                    });

                    // Status Summary
                    let statusY = doc.autoTable.previous.finalY + 10;
                    doc.setFont("helvetica", "bold");
                    doc.text("Status Summary", 15, statusY);
                    doc.setFont("helvetica", "normal");
                    let statusCounts = data.statusCounts;
                    let y = statusY + 5;
                    Object.keys(statusCounts).forEach(status => {
                        doc.text(`${status} - ${statusCounts[status]}`, 20, y);
                        y += 5;
                    });

                    // Agenda Summary
                    let agendaY = y + 10;
                    doc.setFont("helvetica", "bold");
                    doc.text("Agenda Summary", 15, agendaY);
                    doc.setFont("helvetica", "normal");
                    let agendaCounts = data.agendaCounts;
                    y = agendaY + 5;
                    Object.keys(agendaCounts).forEach(agenda => {
                        doc.text(`${agenda} - ${agendaCounts[agenda]}`, 20, y);
                        y += 5;
                    });

                    addPageNumbers(doc);
                    doc.save(`Appointment_Report_${dateInput}.pdf`);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    alert("Error generating report.");
                });
        }

        // ------------------------ MONTHLY REPORT FUNCTION ------------------------
        function generateMonthlyReport() {
            const { jsPDF } = window.jspdf;
            let doc = new jsPDF();
            let selectedMonth = document.getElementById("reportMonth")?.value;

            if (!selectedMonth) {
                alert("Please select a month.");
                return;
            }

            fetch(`../functions/fetch-appointment-by-month.php?month=${selectedMonth}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.appointments || data.appointments.length === 0) {
                        alert("No appointments found for this month.");
                        return;
                    }

                    // Group appointments by date
                    let groupedAppointments = {};
                    data.appointments.forEach(appointment => {
                        let date = appointment.date_logged;
                        if (!groupedAppointments[date]) {
                            groupedAppointments[date] = [];
                        }
                        groupedAppointments[date].push(appointment);
                    });

                    let y = 65; // Initial Y position for content

                    Object.keys(groupedAppointments).forEach((date, index) => {
                        let y = 20; // Reset Y position at the top

                        // Only add a new page for subsequent dates, not the first one
                        if (index > 0) {
                            doc.addPage();
                        }

                        // Add report header only on the first page
                        if (index === 0) {
                            addReportHeader(doc, `Monthly Appointment Report - ${selectedMonth}`);
                            y = 65; // Adjust y to avoid overlapping the header
                        }

                        // Title for the day's appointments
                        doc.setFontSize(12);
                        doc.setFont("helvetica", "bold");
                        doc.text(`Appointments for ${date}`, 14, y);
                        doc.setFontSize(10);
                        y += 10; // Space after title

                        let tableData = [
                            ["Code", "Faculty", "Student", "Start", "End", "Agenda", "Status"]
                        ];

                        groupedAppointments[date].forEach(appointment => {
                            tableData.push([
                                appointment.appointment_code || "N/A",
                                `${appointment.fname || ""} ${appointment.lname || ""}`.trim(),
                                `${appointment.stud_fname || ""} ${appointment.stud_lname || ""}`.trim(),
                                formatTime(appointment.start_time),
                                formatTime(appointment.end_time),
                                appointment.agenda || "N/A",
                                appointment.status || "N/A"
                            ]);
                        });

                        // Generate table
                        doc.autoTable({
                            startY: y,
                            head: [tableData[0]],
                            body: tableData.slice(1),
                            theme: "grid",
                            styles: { fontSize: 7 },
                            margin: { bottom: 10 }
                        });

                        y = doc.lastAutoTable.finalY + 10; // Ensure spacing after table
                    });

                    // Add Summary Section on a new page
                    doc.addPage();
                    y = 20;
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text("Appointment Status Count", 14, y);
                    doc.setFont("helvetica", "normal");
                    y += 8;
                    Object.entries(data.statusCounts).forEach(([status, count]) => {
                        doc.text(`${status}: ${count}`, 20, y);
                        y += 6;
                    });

                    y += 12; // Space before next section

                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text("Appointment Agenda Count", 14, y);
                    doc.setFont("helvetica", "normal");
                    y += 8;
                    Object.entries(data.agendaCounts).forEach(([agenda, count]) => {
                        doc.text(`${agenda}: ${count}`, 20, y);
                        y += 6;
                    });

                    addPageNumbers(doc);
                    doc.save(`Monthly_Appointment_Report_${selectedMonth}.pdf`);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    alert("Error generating monthly report.");
                });
        }

        // ------------------------ YEARLY REPORT FUNCTION ------------------------

        function generateYearlyReport() {
            const { jsPDF } = window.jspdf;
            let doc = new jsPDF();
            let selectedYear = document.getElementById("reportYear")?.value;

            if (!selectedYear) {
                alert("Please select a year.");
                return;
            }

            fetch(`../functions/fetch-appointment-by-year.php?year=${selectedYear}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.appointments || data.appointments.length === 0) {
                        alert("No appointments found for this year.");
                        return;
                    }

                    // Group appointments by month
                    let groupedAppointments = {};
                    data.appointments.forEach(appointment => {
                        let month = new Date(appointment.date_logged).toLocaleString('default', { month: 'long' });
                        if (!groupedAppointments[month]) {
                            groupedAppointments[month] = [];
                        }
                        groupedAppointments[month].push(appointment);
                    });

                    let y = 65;
                    addReportHeader(doc, `Yearly Appointment Report - ${selectedYear}`);

                    Object.keys(groupedAppointments).forEach((month, index) => {
                        let y = index === 0 ? 65 : 20; // ✅ First page: 65, Others: 20
                        if (index > 0) {
                            doc.addPage();
                        }

                        doc.setFontSize(12);
                        doc.setFont("helvetica", "bold");
                        doc.text(`Appointments for ${month}`, 14, y);
                        doc.setFontSize(10);
                        y += 10;

                        let tableData = [
                            ["Code", "Faculty", "Student", "Start", "End", "Agenda", "Status"]
                        ];

                        groupedAppointments[month].forEach(appointment => {
                            tableData.push([
                                appointment.appointment_code || "N/A",
                                `${appointment.prof_fname || ""} ${appointment.prof_lname || ""}`.trim(), // ✅ Updated faculty name
                                `${appointment.stud_fname || ""} ${appointment.stud_lname || ""}`.trim(),
                                formatTime(appointment.start_time),
                                formatTime(appointment.end_time),
                                appointment.agenda || "N/A",
                                appointment.status || "N/A"
                            ]);
                        });

                        doc.autoTable({
                            startY: y,
                            head: [tableData[0]],
                            body: tableData.slice(1),
                            theme: "grid",
                            styles: { fontSize: 7 },
                            margin: { bottom: 20 }, // Ensure space for footer
                            didParseCell: function (data) {
                                if ([0, 3, 4, 5, 6].includes(data.column.index)) {
                                    data.cell.styles.fontSize = 7;
                                }
                            }
                        });

                    });

                    // Add Summary Section
                    doc.addPage();
                    y = 20;
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text("Appointment Status Count", 14, y);
                    doc.setFont("helvetica", "normal");
                    y += 8;
                    Object.entries(data.statusCounts).forEach(([status, count]) => {
                        doc.text(`${status}: ${count}`, 20, y);
                        y += 6;
                    });

                    y += 12;
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text("Appointment Agenda Count", 14, y);
                    doc.setFont("helvetica", "normal");
                    y += 8;
                    Object.entries(data.agendaCounts).forEach(([agenda, count]) => {
                        doc.text(`${agenda}: ${count}`, 20, y);
                        y += 6;
                    });

                    addPageNumbers(doc);
                    doc.save(`Yearly_Appointment_Report_${selectedYear}.pdf`);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    alert("Error generating yearly report.");
                });
        }

        // ------------------------ FACULTY REPORT FUNCTION ------------------------
        function generateFacultyReport() {
            const { jsPDF } = window.jspdf;
            let doc = new jsPDF();
            let facultyId = document.getElementById("prof_rfid_no")?.value;

            if (!facultyId) {
                alert("Please select a faculty member.");
                return;
            }

            fetch(`../functions/fetch-appointment-by-faculty.php?faculty=${facultyId}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.appointments || data.appointments.length === 0) {
                        alert("No appointments found for this faculty.");
                        return;
                    }

                    let facultyName = `${data.faculty.fname} ${data.faculty.lname}`;
                    addReportHeader(doc, `Faculty Appointment Report - ${facultyName}`);

                    let tableData = [
                        ["Date Logged", "Code", "Student", "Start", "End", "Agenda", "Status"]
                    ];

                    data.appointments.forEach(appointment => {
                        tableData.push([
                            appointment.date_logged || "N/A",
                            appointment.appointment_code || "N/A",
                            `${appointment.stud_fname || ""} ${appointment.stud_lname || ""}`.trim(),
                            formatTime(appointment.start_time),
                            formatTime(appointment.end_time),
                            appointment.agenda || "N/A",
                            appointment.status || "N/A"
                        ]);
                    });

                    doc.autoTable({
                        startY: 60,
                        head: [tableData[0]],
                        body: tableData.slice(1),
                        theme: "grid",
                        styles: { fontSize: 9 },
                        margin: { bottom: 20 }, // Ensure space for footer
                        didParseCell: function (data) {
                            if ([1, 3, 4, 6].includes(data.column.index)) {
                                data.cell.styles.fontSize = 7;
                            }
                        }
                    });

                    // Status Summary
                    let statusY = doc.autoTable.previous.finalY + 10;
                    doc.setFont("helvetica", "bold");
                    doc.text("Status Summary", 15, statusY);
                    doc.setFont("helvetica", "normal");
                    let statusCounts = data.statusCounts;
                    let y = statusY + 5;
                    Object.keys(statusCounts).forEach(status => {
                        doc.text(`${status} - ${statusCounts[status]}`, 20, y);
                        y += 5;
                    });

                    // Agenda Summary
                    let agendaY = y + 10;
                    doc.setFont("helvetica", "bold");
                    doc.text("Agenda Summary", 15, agendaY);
                    doc.setFont("helvetica", "normal");
                    let agendaCounts = data.agendaCounts;
                    y = agendaY + 5;
                    Object.keys(agendaCounts).forEach(agenda => {
                        doc.text(`${agenda} - ${agendaCounts[agenda]}`, 20, y);
                        y += 5;
                    });

                    addPageNumbers(doc);
                    doc.save(`Faculty_Appointment_Report_${facultyName}.pdf`);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    alert("Error generating report.");
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

            if (typeof time === "object") {
                if (time.hours !== undefined && time.minutes !== undefined) {
                    return `${time.hours}:${time.minutes.toString().padStart(2, '0')} ${time.hours >= 12 ? 'PM' : 'AM'}`;
                } else if (time.toISOString) {
                    return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                }
            } else if (typeof time === "string") {
                let date = new Date(`1970-01-01T${time}`);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
            }

            return time; // Return as-is if it's already in a correct format
        }
    });

</script>

</html>