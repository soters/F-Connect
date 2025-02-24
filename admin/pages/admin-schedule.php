<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Fetch faculty list
$sql_faculty = "
    SELECT 
        rfid_no, 
        CONCAT(fname, ' ', lname) AS full_name 
    FROM Faculty 
    WHERE archived = 0
";

$query_faculty = sqlsrv_query($conn, $sql_faculty);

if ($query_faculty === false) {
    die('Error fetching faculty data: ' . print_r(sqlsrv_errors(), true));
}

$sql_table = "
   SELECT 
    Schedules.sched_id,  
    CONCAT(Faculty.fname, ' ', Faculty.lname) AS fullname,  
    Schedules.type,  
    FORMAT(Schedules.start_date, 'yyyy-MM-dd') AS start_date,  
    Schedules.start_time,  
    Schedules.end_time,  
    COALESCE(Locations.room_name, 'N/A') AS room_name,  
    COALESCE(Schedules.subject_code, 'N/A') AS subject_code,  
    COALESCE(Sections.section_name, 'N/A') AS section_name  
FROM Faculty
JOIN Schedules ON Faculty.rfid_no = Schedules.rfid_no
LEFT JOIN Sections ON Schedules.section_id = Sections.section_id
LEFT JOIN Locations ON Schedules.room_id = Locations.room_id
WHERE Faculty.archived = 0
ORDER BY Faculty.lname ASC, Faculty.fname ASC;";

$stmt_table = sqlsrv_query($conn, $sql_table);

if ($stmt_table === false) {
    die('Error fetching schedule data: ' . print_r(sqlsrv_errors(), true));
}

// Define Philippine holidays
$philippineHolidays = [
    [
        'title' => 'New Year\'s Day',
        'start' => '2025-01-01T00:00:00',
        'end' => '2025-01-01T23:59:59',
    ],
    [
        'title' => 'Chinese New Year',
        'start' => '2025-01-29T00:00:00',
        'end' => '2025-01-29T23:59:59',
    ],
    [
        'title' => 'Araw ng Kagitingan',
        'start' => '2025-04-09T00:00:00',
        'end' => '2025-04-09T23:59:59',
    ],
    [
        'title' => 'Maundy Thursday',
        'start' => '2025-04-17T00:00:00',
        'end' => '2025-04-17T23:59:59',
    ],
    [
        'title' => 'Good Friday',
        'start' => '2025-04-18T00:00:00',
        'end' => '2025-04-18T23:59:59',
    ],
    [
        'title' => 'Black Saturday',
        'start' => '2025-04-19T00:00:00',
        'end' => '2025-04-19T23:59:59',
    ],
    [
        'title' => 'Labor Day',
        'start' => '2025-05-01T00:00:00',
        'end' => '2025-05-01T23:59:59',
    ],
    [
        'title' => 'Independence Day',
        'start' => '2025-06-12T00:00:00',
        'end' => '2025-06-12T23:59:59',
    ],
    [
        'title' => 'Ninoy Aquino Day',
        'start' => '2025-08-21T00:00:00',
        'end' => '2025-08-21T23:59:59',
    ],
    [
        'title' => 'EDSA People Power Revolution Anniversary',
        'start' => '2025-02-25T00:00:00',
        'end' => '2025-02-25T23:59:59',
    ],
    [
        'title' => 'National Heroes Day',
        'start' => '2025-08-25T00:00:00',
        'end' => '2025-08-25T23:59:59',
    ],
    [
        'title' => 'All Saints\' Day Eve',
        'start' => '2025-10-31T00:00:00',
        'end' => '2025-10-31T23:59:59',
    ],
    [
        'title' => 'All Saints\' Day',
        'start' => '2025-11-01T00:00:00',
        'end' => '2025-11-01T23:59:59',
    ],
    [
        'title' => 'Bonifacio Day',
        'start' => '2025-11-30T00:00:00',
        'end' => '2025-11-30T23:59:59',
    ],
    [
        'title' => 'Feast of the Immaculate Conception of Mary',
        'start' => '2025-12-08T00:00:00',
        'end' => '2025-12-08T23:59:59',
    ],
    [
        'title' => 'Christmas Eve',
        'start' => '2025-12-24T00:00:00',
        'end' => '2025-12-24T23:59:59',
    ],
    [
        'title' => 'Christmas Day',
        'start' => '2025-12-25T00:00:00',
        'end' => '2025-12-25T23:59:59',
    ],
    [
        'title' => 'Rizal Day',
        'start' => '2025-12-30T00:00:00',
        'end' => '2025-12-30T23:59:59',
    ],
    [
        'title' => 'Last Day of the Year',
        'start' => '2025-12-31T00:00:00',
        'end' => '2025-12-31T23:59:59',
    ],
];


function convertTo12HourFormat($time)
{
    // Ensure $time is a DateTime object, if not convert it
    if (!$time instanceof DateTime) {
        $time = DateTime::createFromFormat('H:i:s', $time);
    }

    // Convert to 12-hour format
    return $time->format('h:i A');
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../assets/css/admin-design.css">
    <script src="../../assets/fullcalendar-6.1.15/dist/index.global.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var facultyDropdown = document.getElementById('facultyDropdown');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                slotMinTime: '07:00',
                slotMaxTime: '21:00',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                initialView: 'dayGridMonth',
                events: [], // Start with no events
                dayMaxEvents: true, // Enable "+more" links
                moreLinkClick: 'popover',

                // Event click handler for showing modal
                eventClick: function (info) {
                    console.log("Event clicked:", info.event);

                    if (info.event.extendedProps.type === "Break") {
                        console.log("Opening Break Modal");

                        // Populate Break Modal
                        document.getElementById("breakStartDate").innerText = info.event.extendedProps.start_date || "N/A";
                        document.getElementById("breakStartTime").innerText = info.event.extendedProps.start_time || "N/A";
                        document.getElementById("breakEndTime").innerText = info.event.extendedProps.end_time || "N/A";

                        // Show Break Modal
                        var breakModal = new bootstrap.Modal(document.getElementById('custom-break-modal'), {});
                        breakModal.show();

                        return; // Exit function so it doesn't open the standard modal
                    }

                    console.log("Opening Regular Event Modal");

                    document.getElementById('eventType').innerText = info.event.extendedProps.type;
                    document.getElementById('eventStartDate').innerText = info.event.extendedProps.start_date || "N/A";
                    document.getElementById('eventStartTime').innerText = info.event.extendedProps.start_time || "N/A";
                    document.getElementById('eventEndTime').innerText = info.event.extendedProps.end_time || "N/A";
                    document.getElementById('eventRoom').innerText = info.event.extendedProps.room_name || "Not Assigned";
                    document.getElementById('eventSection').innerText = info.event.extendedProps.section_name || "Not Assigned";
                    document.getElementById('eventSubject').innerText = info.event.extendedProps.subject_code
                        ? `${info.event.extendedProps.subject_code} - ${info.event.extendedProps.subject_description}`
                        : "Not Assigned";

                    var eventModal = new bootstrap.Modal(document.getElementById('eventDetailsModal'), {});
                    eventModal.show();
                }
            });

            calendar.render();

            // Predefined holidays in the Philippines if no faculty is selected
            var holidays = <?php echo json_encode($philippineHolidays); ?>;

            // Add holidays to the calendar (these will be added as events if no faculty is selected)
            holidays.forEach(function (holiday) {
                calendar.addEvent({
                    title: holiday.title,
                    start: holiday.start,
                    end: holiday.end,
                    allDay: true,
                    color: '#B8B9FC', // Optional: Blue color for holidays
                });
            });

            // Dropdown change event
            facultyDropdown.addEventListener('change', function () {
                var selectedRFID = this.value;

                if (selectedRFID) {
                    // Fetch schedules for the selected faculty
                    fetch(`../functions/fetch-schedule.php?rfid_no=${selectedRFID}`)
                        .then(response => response.json())
                        .then(data => {
                            calendar.removeAllEvents(); // Clear existing events
                            calendar.addEventSource(data); // Add new events for the selected faculty
                        })
                        .catch(error => console.error('Error fetching schedules:', error));
                } else {
                    // If no faculty selected, show Philippine holidays
                    calendar.removeAllEvents(); // Clear existing events
                    holidays.forEach(function (holiday) {
                        calendar.addEvent({
                            title: holiday.title,
                            start: holiday.start,
                            end: holiday.end,
                            allDay: true,
                            color: '#B8B9FC',
                        });
                    });
                }
            });
        });

        // Toggle between Calendar View and Table View
        function toggleView() {
            const gridView = document.querySelector('.faculty-container-calendar');
            const tableView = document.getElementById('faculty-table');
            const toggleButton = document.getElementById('toggle-view-btn');

            if (gridView.style.display === 'none') {
                gridView.style.display = 'block';
                tableView.style.display = 'none';
                toggleButton.textContent = 'View as Table';
            } else {
                gridView.style.display = 'none';
                tableView.style.display = 'block';
                toggleButton.textContent = 'View as Calendar';
            }
        }
    </script>

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
            <h1 class="title-text">Schedules</h1>
            <!-- Display Current Date and Time -->
            <div id="current-datetime" class="current-datetime">
                <p id="date-time"></p> <!-- Updated id here -->
            </div>
        </div>

        <div class="action-widgets">
            <div class="widget-button">
                <h1 class="sub-title">Actions</h1>
                <div class="buttons">
                    <a href="admin-new-schedule.php">
                        <button class="create-btn-2" type="button">Create</button>
                    </a>
                    <div class="dropdown-sort">
                        <select id="facultyDropdown">
                            <option value="">-- Select Faculty --</option>
                            <?php while ($row = sqlsrv_fetch_array($query_faculty, SQLSRV_FETCH_ASSOC)): ?>
                                <option value="<?php echo $row['rfid_no']; ?>"><?php echo $row['full_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="widget-search">
                <div class="search-box-faculty">
                    <input type="text" id="search-input" placeholder="Search..." oninput="filterWidgets()">
                    <button class='bx bx-search'></button>
                </div>
                <div class="dropdown-sort">
                    <select id="sort-dropdown" onchange="sortWidgets()">
                        <option value="" disabled selected>Filters</option>
                        <option value="name-asc">Name (A-Z)</option>
                        <option value="name-desc">Name (Z-A)</option>
                    </select>
                </div>
                <button class="toggle-view-btn" id="toggle-view-btn" onclick="toggleView()">View as Table</button>
            </div>
        </div>

        <!-- 🔥 Message Box -->
        <div id="messageBox" class="message-box"></div>
        <div class="faculty-container-calendar">
            <div id='calendar'></div>
        </div>

        <div class="faculty-table-container" id="faculty-table" style="display: none;">
            <div class="sched-custom-table">
                <div class="sched-custom-table-header">
                    <div class="sched-custom-table-cell">Faculty Name</div>
                    <div class="sched-custom-table-cell">Type</div>
                    <div class="sched-custom-table-cell">Date</div>
                    <div class="sched-custom-table-cell">Start Time</div>
                    <div class="sched-custom-table-cell">End Time</div>
                    <div class="sched-custom-table-cell">Location</div>
                    <div class="sched-custom-table-cell">Subject</div>
                    <div class="sched-custom-table-cell">Actions</div>
                </div>
                <div class="sched-custom-table-body">
                    <?php while ($faculty = sqlsrv_fetch_array($stmt_table, SQLSRV_FETCH_ASSOC)): ?>
                        <?php if ($faculty['type'] === 'Break') { ?>
                            <script>console.log('Break schedule found:', <?= json_encode($faculty) ?>);</script>
                        <?php } ?>
                        <div class="sched-custom-table-row">
                            <div class="sched-custom-table-cell">
                                <?= htmlspecialchars($faculty['fullname']) ?>
                            </div>
                            <div class="sched-custom-table-cell">
                                <?= htmlspecialchars($faculty['type']) ?>
                            </div>
                            <div class="sched-custom-table-cell">
                                <?= htmlspecialchars($faculty['start_date']) ?>
                            </div>
                            <div class="sched-custom-table-cell">
                                <?php echo convertTo12HourFormat($faculty['start_time']); ?>
                            </div>
                            <div class="sched-custom-table-cell">
                                <?php echo convertTo12HourFormat($faculty['end_time']); ?>
                            </div>
                            <div class="sched-custom-table-cell">
                                <?= htmlspecialchars($faculty['room_name']) ?>
                            </div>
                            <div class="sched-custom-table-cell">
                                <?= htmlspecialchars($faculty['subject_code']) ?>
                            </div>
                            <div class="sched-custom-table-cell">
                                <button class="action-btn delete-btn"
                                    onclick="openDeleteModal(<?= htmlspecialchars($faculty['sched_id']) ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="action-btn edit-btn"
                                    onclick="updateSched(<?= htmlspecialchars($faculty['sched_id']) ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>

                </div>
            </div>

            <div id="no-data-message-2" style="display: none;">
                <img class="no-data-image" src="../../assets/images/data-not-found.png" alt="No Data Found">
                <h1 class="not-found-message">No Data found.</h1>
            </div>
        </div>
    </div>

    <!-- Dark background overlay -->
    <div id="modalOverlay" class="modal-overlay"></div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="custom-modal">
        <div class="modal-content">
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete this schedule?</p>
            <div class="modal-actions">
                <button id="confirmDelete" class="btn-confirm">Yes, Delete</button>
                <button onclick="closeDeleteModal()" class="btn-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div class="modal" tabindex="-1" id="eventDetailsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="sched-info-holder">
                        <div class="sched-type"><span id="eventType"></span></div>
                        <hr>
                        <div class="sched-section">Section: <span id="eventSection"></span></div>
                        <div class="sched-section">Date: <span id="eventStartDate"> </span></div>
                        <div class="sched-section">Start Time: <span id="eventStartTime"></span></div>
                        <div class="sched-section">End Time: <span id="eventEndTime"></span></div>
                        <div class="sched-section">Subject: <span id="eventSubject"></span></div>
                        <div class="sched-section">Location: <span id="eventRoom"></span></span></div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end w-100">
                    <button type="button" class="btn btn-primary btn-sm" id="editButton" style="text-transform: none;">
                        Schedule Info.
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Custom Break Modal -->
    <div class="modal" tabindex="-1" id="custom-break-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Break Time Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="sched-info-holder">
                        <div class="sched-type"><span id="breakType">Break</span></div>
                        <hr>
                        <div class="sched-section">Date: <span id="breakStartDate">N/A</span></div>
                        <div class="sched-section">Start Time: <span id="breakStartTime">N/A</span></div>
                        <div class="sched-section">End Time: <span id="breakEndTime">N/A</span></div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end w-100">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                        style="text-transform: none;">
                        Close
                    </button>
                </div>
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
    function filterWidgets() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();

        // Filter Table View
        const tableRows = document.querySelectorAll('.sched-custom-table-row');
        let tableFound = false;

        tableRows.forEach(row => {
            const facultyName = row.querySelector('.sched-custom-table-cell:nth-child(1)').textContent.toLowerCase();
            const type = row.querySelector('.sched-custom-table-cell:nth-child(2)').textContent.toLowerCase();
            const date = row.querySelector('.sched-custom-table-cell:nth-child(3)').textContent.toLowerCase();
            const location = row.querySelector('.sched-custom-table-cell:nth-child(6)').textContent.toLowerCase();
            const subject = row.querySelector('.sched-custom-table-cell:nth-child(7)').textContent.toLowerCase();

            if (
                facultyName.includes(searchTerm) ||
                type.includes(searchTerm) ||
                date.includes(searchTerm) ||
                location.includes(searchTerm) ||
                subject.includes(searchTerm)
            ) {
                row.style.display = ''; // Show the row
                tableFound = true;
            } else {
                row.style.display = 'none'; // Hide the row
            }
        });

        // Show or hide the "No Data" message based on the table contents
        const noDataMessage2 = document.getElementById('no-data-message-2');
        const customTable = document.querySelector('.sched-custom-table');

        if (!tableFound) {
            noDataMessage2.style.display = 'block'; // Show table view no data message
            customTable.style.display = 'none'; // Hide the table
        } else {
            noDataMessage2.style.display = 'none'; // Hide table view no data message
            customTable.style.display = 'block'; // Show the table
        }
    }

    function sortWidgets() {
        const sortOption = document.getElementById('sort-dropdown').value;

        // Sort Table View
        const tableRows = Array.from(document.querySelectorAll('.sched-custom-table-row'));

        tableRows.sort((a, b) => {
            const nameA = a.querySelector('.sched-custom-table-cell:nth-child(1)').textContent.toLowerCase();
            const nameB = b.querySelector('.sched-custom-table-cell:nth-child(1)').textContent.toLowerCase();

            switch (sortOption) {
                case 'name-asc':
                    return nameA.localeCompare(nameB); // Sort by name in ascending order
                case 'name-desc':
                    return nameB.localeCompare(nameA); // Sort by name in descending order
                default:
                    return 0; // No sorting applied
            }
        });

        // Re-append sorted rows to the table (Table View)
        const tableBody = document.querySelector('.sched-custom-table-body');
        tableRows.forEach(row => {
            tableBody.appendChild(row);
        });
    }

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
        window.location.href = `../functions/delete-schedule.php?sched_id=${rfidNo}`; // Redirect with RFID
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
<script>
    function updateSched(sched_id) {
        // Redirect to the update page with rfid_no as a query parameter
        window.location.href = `admin-update-schedule.php?sched_id=${sched_id}`;
    }
</script>

</html>