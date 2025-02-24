<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Pagination settings
$limit = 16;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total number of announcements
$sql_count = "SELECT COUNT(*) AS total FROM Announcement";
$stmt_count = sqlsrv_query($conn, $sql_count);
$row_count = sqlsrv_fetch_array($stmt_count, SQLSRV_FETCH_ASSOC);
$total_announcements = (int) $row_count['total'];

// Calculate total pages
$total_pages = ceil($total_announcements / $limit);

// Query for Paginated Announcements Grid View
$sql_paginated = "SELECT 
                    announcement_id, 
                    title, 
                    content, 
                    date, 
                    picture_path 
                FROM Announcement
                ORDER BY date DESC
                OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

$params = array($offset, $limit);
$stmt_paginated = sqlsrv_query($conn, $sql_paginated, $params);

if ($stmt_paginated === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Query for Announcements Table View (Full List)
$sql_table = "SELECT 
                announcement_id, 
                title, 
                content, 
                date, 
                picture_path 
            FROM Announcement
            ORDER BY date DESC";

$stmt_table = sqlsrv_query($conn, $sql_table);
if ($stmt_table === false) {
    die(print_r(sqlsrv_errors(), true));
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
        <div id="header">
            <h1 class="title-text">Announcements</h1>
            <!-- Display Current Date and Time -->
            <div id="current-datetime" class="current-datetime">
                <p id="date-time"></p> <!-- Updated id here -->
            </div>
        </div>

        <div class="action-widgets">
            <div class="widget-button">
                <h1 class="sub-title">Actions</h1>
                <div class="buttons">
                    <a href="admin-new-announcement.php">
                        <button class="create-btn" type="button">Create
                        </button>
                    </a>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="prev">Previous</a>
                        <?php endif; ?>
                        <span class="page-number">Page <?= $page ?> of <?= $total_pages ?></span>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="next">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="widget-search">
                <div class="search-box-faculty">
                    <input type="text" id="search-input" placeholder="Search..." oninput="filterWidgets()">
                    <button class='bx bx-search'></button>
                </div>
                <div class="dropdown-sort">
                    <div class="dropdown-sort">
                        <select id="sort-dropdown" onchange="sortWidgets()">
                            <option value="" disabled selected>Filters</option>
                            <option value="title-asc">Title (A-Z)</option>
                            <option value="title-desc">Title (Z-A)</option>
                            <option value="date-recent">Date (Recent-Oldest)</option>
                            <option value="date-oldest">Date (Oldest-Recent)</option>
                        </select>
                    </div>
                </div>
                <button class="toggle-view-btn" id="toggle-view-btn" onclick="toggleView()">View as Table</button>
            </div>

        </div>

        <!-- 🔥 Message Box -->
        <div id="messageBox" class="message-box"></div>
        <div class="faculty-container">
            <div class="faculty-grid">
                <?php while ($announcement = sqlsrv_fetch_array($stmt_paginated, SQLSRV_FETCH_ASSOC)): ?>
                    <div class="announcement-card" data-id="<?= htmlspecialchars($announcement['announcement_id']) ?>">
                        <img src="<?= htmlspecialchars($announcement['picture_path']) ?>" alt="Announcement Image"
                            class="announcement-img">
                        <div class="announcement-content">
                            <h3 class="announcement-title"><?= htmlspecialchars($announcement['title']) ?></h3>
                            <p class="announcement-description">
                                <?= htmlspecialchars(substr($announcement['content'], 0, 100)) ?>... <a href="#">view
                                    more.</a>
                            </p>
                            <p class="announcement-date"><em>Date Posted:
                                    <?= htmlspecialchars($announcement['date'] instanceof DateTime ? $announcement['date']->format('Y/m/d') : 'No Date') ?>
                                </em></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div id="no-data-message" style="display: <?= sqlsrv_has_rows($stmt_paginated) ? 'none' : 'block' ?>;">
                <img class="no-data-image" src="../../assets/images/data-not-found.png" alt="No Data Found">
                <h1 class="not-found-message">No Data found.</h1>
            </div>
        </div>


        <div class="faculty-table-container" id="faculty-table" style="display: none;">
            <div class="custom-table">
                <div class="custom-table-header">
                    <div class="custom-table-cell">Title</div>
                    <div class="custom-table-cell">Date Posted</div>
                    <div class="custom-table-cell">Actions</div> <!-- Actions column -->
                </div>
                <div class="custom-table-body">
                    <?php while ($announcement = sqlsrv_fetch_array($stmt_table, SQLSRV_FETCH_ASSOC)): ?>
                        <div class="custom-table-row">
                            <div class="custom-table-cell">
                                <?= htmlspecialchars($announcement['title']) ?>
                            </div>
                            <div class="custom-table-cell">
                                <?= htmlspecialchars($announcement['date'] instanceof DateTime ? $announcement['date']->format('Y/m/d') : 'No Date') ?>
                            </div>
                            <div class="custom-table-cell">
                                <!-- Action buttons inline -->
                                <!-- Edit Button -->
                                <button class="action-btn update-btn"
                                    onclick="editAnnouncement('<?= htmlspecialchars($announcement['announcement_id']) ?>')">
                                    <i class="fas fa-edit"></i> <!-- Edit icon -->
                                </button>
                                <!-- Delete Button -->
                                <button class="action-btn delete-btn"
                                    onclick="openDeleteModal('<?= htmlspecialchars($announcement['announcement_id']) ?>')">
                                    <i class="fas fa-trash"></i> <!-- Trash icon -->
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
            <p>Are you sure you want to delete this announcement?</p>
            <div class="modal-actions">
                <button id="confirmDelete" class="btn-confirm">Yes, Delete</button>
                <button onclick="closeDeleteModal()" class="btn-cancel">Cancel</button>
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

        // Filter Grid View
        const announcementCards = document.querySelectorAll('.announcement-card');
        let foundGrid = false;

        announcementCards.forEach(card => {
            const title = card.querySelector('.announcement-title').textContent.toLowerCase();
            const datePosted = card.querySelector('.announcement-date').textContent.toLowerCase();

            if (title.includes(searchTerm) || datePosted.includes(searchTerm)) {
                card.style.display = ''; // Show the card
                foundGrid = true;
            } else {
                card.style.display = 'none'; // Hide the card
            }
        });

        // Filter Table View
        const tableRows = document.querySelectorAll('.custom-table-row');
        let foundTable = false;

        tableRows.forEach(row => {
            const title = row.querySelector('.custom-table-cell:nth-child(1)').textContent.toLowerCase();
            const datePosted = row.querySelector('.custom-table-cell:nth-child(2)').textContent.toLowerCase();

            if (title.includes(searchTerm) || datePosted.includes(searchTerm)) {
                row.style.display = ''; // Show the row
                foundTable = true;
            } else {
                row.style.display = 'none'; // Hide the row
            }
        });

        // Show or hide "No Data Found" messages
        document.getElementById('no-data-message').style.display = foundGrid ? 'none' : 'block';
        document.getElementById('no-data-message-2').style.display = foundTable ? 'none' : 'block';
        document.querySelector('.custom-table').style.display = foundTable ? 'block' : 'none';
    }

    function sortWidgets() {
        const sortOption = document.getElementById('sort-dropdown').value;

        // Sort Grid View
        const announcementCards = Array.from(document.querySelectorAll('.announcement-card'));

        announcementCards.sort((a, b) => {
            const titleA = a.querySelector('.announcement-title').textContent.toLowerCase();
            const titleB = b.querySelector('.announcement-title').textContent.toLowerCase();

            const dateA = new Date(a.querySelector('.announcement-date').textContent.replace('Date Posted: ', ''));
            const dateB = new Date(b.querySelector('.announcement-date').textContent.replace('Date Posted: ', ''));

            switch (sortOption) {
                case 'title-asc':
                    return titleA.localeCompare(titleB);
                case 'title-desc':
                    return titleB.localeCompare(titleA);
                case 'date-recent':
                    return dateB - dateA; // Recent first
                case 'date-oldest':
                    return dateA - dateB; // Oldest first
                default:
                    return 0;
            }
        });

        const announcementGrid = document.querySelector('.faculty-grid');
        announcementCards.forEach(card => announcementGrid.appendChild(card));

        // Sort Table View
        const tableRows = Array.from(document.querySelectorAll('.custom-table-row'));

        tableRows.sort((a, b) => {
            const titleA = a.querySelector('.custom-table-cell:nth-child(1)').textContent.toLowerCase();
            const titleB = b.querySelector('.custom-table-cell:nth-child(1)').textContent.toLowerCase();
            const dateA = new Date(a.querySelector('.custom-table-cell:nth-child(2)').textContent);
            const dateB = new Date(b.querySelector('.custom-table-cell:nth-child(2)').textContent);

            switch (sortOption) {
                case 'title-asc':
                    return titleA.localeCompare(titleB);
                case 'title-desc':
                    return titleB.localeCompare(titleA);
                case 'date-recent':
                    return dateB - dateA;
                case 'date-oldest':
                    return dateA - dateB;
                default:
                    return 0;
            }
        });

        const tableBody = document.querySelector('.custom-table-body');
        tableRows.forEach(row => tableBody.appendChild(row));
    }
</script>
<script>
    function toggleView() {
        const gridView = document.querySelector('.faculty-container');
        const tableView = document.getElementById('faculty-table');
        const toggleButton = document.getElementById('toggle-view-btn');

        if (gridView.style.display === 'none') {
            gridView.style.display = 'block';
            tableView.style.display = 'none';
            toggleButton.textContent = 'View as Table';
        } else {
            gridView.style.display = 'none';
            tableView.style.display = 'block';
            toggleButton.textContent = 'View as Grid';
        }
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
        document.getElementById("confirmDelete").setAttribute("data-id", rfidNo); // Store RFID
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").style.display = "none"; // Hide modal
        document.getElementById("modalOverlay").style.display = "none"; // Hide dark overlay
    }

    // When "Yes, Delete" button is clicked, redirect to delete_faculty.php
    document.getElementById("confirmDelete").addEventListener("click", function () {
        let rfidNo = this.getAttribute("data-id"); // Get stored RFID
        window.location.href = `../functions/delete-announcement.php?announcement_id=${rfidNo}`; // Redirect with RFID
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
    function editAnnouncement(announcement_id) {
        // Redirect to the update page with rfid_no as a query parameter
        window.location.href = `admin-update-announcement.php?announcement_id=${announcement_id}`;
    }
</script>
<script>
    document.querySelectorAll('.announcement-card').forEach(card => {
        card.addEventListener('click', () => {
            const announcementId = card.getAttribute('data-id');
            window.location.href = `admin-update-announcement.php?announcement_id=${announcementId}`;
        });
    });
</script>

</html>