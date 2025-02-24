<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

$admin_rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);

// Query to count today's appointments by status
$sqlStatusCount = "SELECT status, COUNT(*) as count 
                   FROM Appointments 
                   WHERE date_logged = CAST(GETDATE() AS DATE) 
                   GROUP BY status";

try {
    $stmtStatus = sqlsrv_query($conn, $sqlStatusCount);

    // Initialize status counts with default 0 values
    $statusCounts = [
        'Pending' => 0,
        'Accepted' => 0,
        'Completed' => 0,
        'Declined' => 0,
        'Cancelled' => 0
    ];

    // Fetch results and store in the array
    while ($row = sqlsrv_fetch_array($stmtStatus, SQLSRV_FETCH_ASSOC)) {
        $status = ucwords(strtolower(trim($row['status']))); // Ensure proper case formatting
        $statusCounts[$status] = $row['count']; // Store count in array
    }

} catch (Exception $e) {
    error_log("Query Error: " . $e->getMessage());
    die("An error occurred while fetching the status counts. Please contact the administrator.");
}

// Get today's date
$today = date('Y-m-d');

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
        a.status
    FROM Appointments a
    JOIN Faculty f ON a.prof_rfid_no = f.rfid_no
    JOIN Students s ON a.stud_rfid_no = s.rfid_no
    WHERE a.date_logged = ?
    ORDER BY a.start_time ASC
";

$params = array($today);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Database error: " . print_r(sqlsrv_errors(), true));
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
            <h1 class="title-text">Appointments</h1>

            <!-- Display Current Date and Time -->
            <div id="current-datetime" class="current-datetime">
                <p id="date-time"></p> <!-- Updated id here -->
            </div>
        </div>

        <div class="action-widgets">
            <div class="widget-button">
                <div class="buttons">
                    <a href="admin-new-appointment.php">
                        <button class="abtn" type="button">Create Appointment
                        </button>
                    </a>
                    <a href="admin-appointment-history.php">
                        <button class="habtn" type="button">Appointment History
                        </button>
                    </a>
                </div>
            </div>
            <div class="widget-search"></div>
        </div>
        <div class="apt-dashboard-widgets">
            <div class="widget apt-table-design">
                <h2 class="tbl-title">Today's Appointments</h2>
                <table id="appointmentTable" class="display">
                    <thead class="tbl-header">
                        <tr>
                            <th>Professor</th>
                            <th>Student</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Agenda</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                            <?php
                            // Define status classes
                            $statusClasses = [
                                'pending' => 'status-pending',
                                'accepted' => 'status-accepted',
                                'completed' => 'status-completed',
                                'declined' => 'status-declined',
                                'cancelled' => 'status-cancelled'
                            ];

                            // Get status key and class (fallback to 'status-default' if unknown)
                            $statusKey = strtolower(trim($row['status']));
                            $statusClass = $statusClasses[$statusKey] ?? 'status-default';

                            // Determine if this row should be clickable (only Pending or Accepted)
                            $isClickable = in_array($statusKey, ['pending', 'accepted']);

                            // Handle NULL values and sanitize output
                            $professor = htmlspecialchars($row['prof_fname'] . " " . $row['prof_lname']);
                            $student = htmlspecialchars($row['stud_fname'] . " " . $row['stud_lname']);
                            $agenda = !empty($row['agenda']) ? htmlspecialchars($row['agenda']) : 'No Agenda Provided';
                            ?>
                            <tr class="tbl-row <?= $isClickable ? 'clickable' : '' ?>" <?= $isClickable ? 'data-appointment-code="' . htmlspecialchars($row['appointment_code']) . '"' : '' ?>>
                                <td class="small-text"><?= $professor ?></td>
                                <td class="small-text"><?= $student ?></td>
                                <td><?= $row['start_time'] ? $row['start_time']->format('h:i A') : 'N/A' ?></td>
                                <td><?= $row['end_time'] ? $row['end_time']->format('h:i A') : 'N/A' ?></td>
                                <td><?= $agenda ?></td>
                                <td class="appointment-status <?= $statusClass ?>"><?= ucwords($statusKey) ?></td>
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
                    <span class="widget-ttl">Appointment Overview Today</span>
                </div>
                <div class="widget-body">
                    <canvas id="dataChart"></canvas>
                </div>
                <br>
                <?php
                // SQL Query to select upcoming appointments scheduled for today within the next 30 minutes
                $sql = "
    SELECT appointment_code, start_time, end_time, agenda, status 
    FROM Appointments 
    WHERE 
        status IN ('Pending', 'Accepted') 
        AND date_logged = CAST(GETDATE() AS DATE)  -- Ensure the appointment is for today
        AND start_time BETWEEN CAST(GETDATE() AS TIME) AND CAST(DATEADD(MINUTE, 30, GETDATE()) AS TIME)
    ORDER BY start_time ASC
";

                $stmt = sqlsrv_query($conn, $sql);

                // Check if query execution was successful
                if ($stmt === false) {
                    echo "Error fetching data: " . print_r(sqlsrv_errors(), true);
                } else {
                    $appointmentsFound = false; // Track if appointments exist
                    $appointmentHTML = ""; // Store appointment cards
                    $appointmentCount = 0; // Counter for limiting to 2 appointments
                
                    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        // Limit the number of appointments to 2
                        if ($appointmentCount >= 3)
                            break;

                        $appointmentsFound = true; // At least one appointment exists
                        $appointmentCount++; // Increment appointment count
                
                        // Convert time to readable format
                        $startTime = isset($row['start_time']) ? $row['start_time']->format('h:i A') : 'N/A';
                        $endTime = isset($row['end_time']) ? $row['end_time']->format('h:i A') : 'N/A';

                        // Assign CSS class based on status
                        $statusClass = strtolower(str_replace(' ', '-', $row['status']));

                        // Append appointment card to HTML
                        $appointmentHTML .= "
            <div class='appointment-card'>
                <p class='appt-code'>Appointment code: <strong>" . htmlspecialchars($row['appointment_code']) . "</strong></p>
                <p class='appt-time'>$startTime - $endTime</p>
                <p class='appt-agenda'>" . htmlspecialchars($row['agenda']) . "</p>
                <span class='status-2 $statusClass'>" . htmlspecialchars($row['status']) . "</span>
            </div>";
                    }

                    // Output appointments if found, otherwise show no appointments message
                    if ($appointmentsFound) {
                        echo "<div class='appointment-container'>";
                        echo "<span class='widget-ttl'>Upcoming Appointment</span>";
                        echo $appointmentHTML;
                        echo "</div>";
                    } else {
                        echo "<div class='no-appointments-container'>
                <img src='../../assets/images/no-apt.png' alt='No Appointments' class='no-appt-img'>
                <p class='no-appointments'>No upcoming appointments for now.</p>
              </div>";
                    }
                }
                ?>

            </div>
        </div>

        <!-- Second Widgets Lines For Different Counts -->
        <div class="dashboard-widgets-second">
            <div class="dashboard-widget-first">
            </div>
            <div class="dashboard-widget-second">
            </div>
            <div class="dashboard-widget-third">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass the PHP status count variables to JavaScript
    const statusCounts = {
        pending: <?= $statusCounts['Pending']; ?>,
        accepted: <?= $statusCounts['Accepted']; ?>,
        completed: <?= $statusCounts['Completed']; ?>,
        declined: <?= $statusCounts['Declined']; ?>,
        cancelled: <?= $statusCounts['Cancelled']; ?>
    };

    // Get the canvas element for the chart
    const ctx = document.getElementById('dataChart').getContext('2d');

    // Create the bar chart
    const dataChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Accepted', 'Completed', 'Declined', 'Cancelled'],
            datasets: [{
                label: 'Appointments Count',
                data: [
                    statusCounts.pending,
                    statusCounts.accepted,
                    statusCounts.completed,
                    statusCounts.declined,
                    statusCounts.cancelled
                ],
                backgroundColor: ['#ffc107', '#007bff', '#28a745', '#dc3545', '#6c757d'], // Bar colors
                borderColor: ['#e0a800', '#0056b3', '#1e7e34', '#a71d2a', '#545b62'], // Border colors
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allows custom sizing
            scales: {
                x: {
                    ticks: {
                        color: '#393242', // X-axis label color
                        font: {
                            size: 12,
                            family: 'Poppins' // Font family
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#393242', // Y-axis label color
                        font: {
                            size: 12,
                            family: 'Poppins'
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#393242', // Legend text color
                        font: {
                            size: 12,
                            family: 'Poppins'
                        }
                    }
                }
            }
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

</html>