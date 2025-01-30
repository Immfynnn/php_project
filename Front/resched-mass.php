<?php
session_start();
require_once "../config.php";

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
header("Pragma: no-cache");

// Check if the s_id is passed in the URL
$s_id = $_GET['s_id'] ?? null; // Use $_GET to retrieve s_id from the URL

// Ensure s_id is provided
if (!$s_id) {
    echo "Reservation ID is missing.";
    exit();
}

// Fetch reservation details from the database
$stmt = $conn->prepare("SELECT * FROM reservation WHERE s_id = ?");
$stmt->bind_param("i", $s_id);
$stmt->execute();
$result = $stmt->get_result();
    
// Check if reservation exists
if ($result->num_rows > 0) {
    $reservation = $result->fetch_assoc(); // Fetch reservation data
} else {
    echo "Reservation not found.";
    exit();
}

// Fetch the latest user details using the `uid` stored in the session
$uid = $_SESSION['uid'];
$sql = "SELECT firstname, profile_completed, user_status FROM users WHERE uid = ?";
$stmt = mysqli_stmt_init($conn);

if (mysqli_stmt_prepare($stmt, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $firstname = htmlspecialchars($row['firstname']);
        $_SESSION['firstname'] = $firstname;
        $_SESSION['profile_completed'] = $row['profile_completed'];
        
        // Check user status
        if ($row['user_status'] === 'Offline') {
            // Redirect to signup.php if the user status is offline
            header("Location: signin.php");
            exit();
        }
    } else {
        echo "User not found";
        exit();
    }
} else {
    echo "Database error: " . mysqli_error($conn);
    exit();
}

// Display name logic
$displayName = !empty($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['username'];

// Get the profile completed status
$profileCompleted = $_SESSION['profile_completed'];

?>

<!-- HTML content (unchanged) -->

<?php
// Fetch admin details
$UserDetails = null;
$sqlUserDetails = "SELECT username, userimg, firstname, lastname, gender, age, contactnum, address FROM users WHERE uid = ?";
$stmtUser = $conn->prepare($sqlUserDetails);
$stmtUser->bind_param('i', $uid); // use $uid instead of $user_id
if ($stmtUser->execute()) {
    $resultUser = $stmtUser->get_result();
    $UserDetails = $resultUser->fetch_assoc();
}
$stmtUser->close();

// Set default profile image if none exists
$profileImage = !empty($UserDetails['userimg']) ? $UserDetails['userimg'] : 'css/img/default-profile.png';

// Get unread message count for the logged-in user
$sqlUnreadMessages = "SELECT COUNT(*) AS unread_count FROM messages WHERE recipient_id = ? AND read_status = 0";
$stmtUnread = $conn->prepare($sqlUnreadMessages);
$stmtUnread->bind_param("i", $uid);
$stmtUnread->execute();
$resultUnread = $stmtUnread->get_result();
$rowUnread = $resultUnread->fetch_assoc();
$unreadCount = $rowUnread['unread_count']; // This will store the count of unread messages
$stmtUnread->close();
?>

<!-- The rest of the HTML (unchanged) -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
    <link rel="stylesheet" href="css/update28.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
    <link rel="stylesheet" href="./fullcalendar/lib/main.min.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./fullcalendar/lib/main.min.js"></script>

    <style>
   .fc .fc-toolbar.fc-header-toolbar {
    margin-bottom: 1.5em;
}
.fc .fc-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.fc-direction-ltr {
    direction: ltr;
    text-align: left;
}
.fc, .fc *, .fc :after, .fc :before {
    box-sizing: border-box;
}
.fc {
    display: flex;
    flex-direction: column;
    font-size: 1em;
}
*, ::after, ::before {
    box-sizing: border-box;
}

.btn-group>.btn-group:not(:last-child)>.btn, .btn-group>.btn:not(:last-child):not(.dropdown-toggle) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
.btn-group>.btn-group:not(:first-child)>.btn, .btn-group>.btn:nth-child(n+3), .btn-group>:not(.btn-check)+.btn {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.btn-group-vertical>.btn-check:checked+.btn, .btn-group-vertical>.btn-check:focus+.btn, .btn-group-vertical>.btn.active, .btn-group-vertical>.btn:active, .btn-group-vertical>.btn:focus, .btn-group-vertical>.btn:hover, .btn-group>.btn-check:checked+.btn, .btn-group>.btn-check:focus+.btn, .btn-group>.btn.active, .btn-group>.btn:active, .btn-group>.btn:focus, .btn-group>.btn:hover {
    z-index: 1;
}
.btn-group-vertical>.btn, .btn-group>.btn {
    position: relative;
    flex: 1 1 auto;
}
.btn-check:active+.btn-primary, .btn-check:checked+.btn-primary, .btn-primary.active, .btn-primary:active, .show>.btn-primary.dropdown-toggle {
    color: #fff;
    background-color: #0a58ca;
    border-color: #0a53be;
}
[type=button]:not(:disabled), [type=reset]:not(:disabled), [type=submit]:not(:disabled), button:not(:disabled) {
    cursor: pointer;
}

.row {
    --bs-gutter-x: 1.5rem;
    --bs-gutter-y: 0;
    display: flex;
    flex-wrap: wrap;
    margin-top: calc(var(--bs-gutter-y) * -1);
    margin-right: calc(var(--bs-gutter-x) * -.5);
    margin-left: calc(var(--bs-gutter-x) * -.1);
    padding-right: calc(var(--bs-gutter-x) * .5);
}


.col-md-9 {
    flex: 0 0 auto;
    width: 100%;
}
:root {
    --bs-blue: #0d6efd;
    --bs-indigo: #6610f2;
    --bs-purple: #6f42c1;
    --bs-pink: #d63384;
    --bs-red: #dc3545;
    --bs-orange: #fd7e14;
    --bs-yellow: #ffc107;
    --bs-green: #198754;
    --bs-teal: #20c997;
    --bs-cyan: #0dcaf0;
    --bs-white: #fff;
    --bs-gray: #6c757d;
    --bs-gray-dark: #343a40;
    --bs-gray-100: #f8f9fa;
    --bs-gray-200: #e9ecef;
    --bs-gray-300: #dee2e6;
    --bs-gray-400: #ced4da;
    --bs-gray-500: #adb5bd;
    --bs-gray-600: #6c757d;
    --bs-gray-700: #495057;
    --bs-gray-800: #343a40;
    --bs-gray-900: #212529;
    --bs-primary: #0d6efd;
    --bs-secondary: #6c757d;
    --bs-success: #198754;
    --bs-info: #0dcaf0;
    --bs-warning: #ffc107;
    --bs-danger: #dc3545;
    --bs-light: #f8f9fa;
    --bs-dark: #212529;
    --bs-primary-rgb: 13,110,253;
    --bs-secondary-rgb: 108,117,125;
    --bs-success-rgb: 25,135,84;
    --bs-info-rgb: 13,202,240;
    --bs-warning-rgb: 255,193,7;
    --bs-danger-rgb: 220,53,69;
    --bs-light-rgb: 248,249,250;
    --bs-dark-rgb: 33,37,41;
    --bs-white-rgb: 255,255,255;
    --bs-black-rgb: 0,0,0;
    --bs-body-rgb: 33,37,41;
    --bs-font-sans-serif: system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
    --bs-font-monospace: SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
    --bs-gradient: linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0));
    --bs-body-font-family: var(--bs-font-sans-serif);
    --bs-body-font-size: 1rem;
    --bs-body-font-weight: 400;
    --bs-body-line-height: 1.5;
    --bs-body-color: #212529;
    --bs-body-bg: #fff;
}

:root {
    --bs-success-rgb: 71, 222, 152 !important;
}
#content main .schedule-cont {
    margin-left: 20px;
    padding: 20px;
    width: 100%;
    height: 100%;
    box-shadow: 5px 0 10px rgba(0, 0, 0, 0.1);
    outline: solid 1px rgba(0, 0, 0, 0.1);
    border-radius: 7px;
    font-family: Apple Chancery, cursive;

}

.btn-info.text-light:hover,
.btn-info.text-light:focus {
    background: #000;
}
table, tbody, td, tfoot, th, thead, tr {
    border-color: #ededed !important;
    border-style: solid;
    border-width: 1px !important;
}
.modal {
    display: none;
    background: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1055;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: auto;
    outline: 0;
}
.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border-radius: .3rem;
    outline: 0;
}
.modal .center {
    display: flex;
    width: 100%;
    height: 100%;
    justify-content: center;
    align-items: center;
    
}

.modal-dialog {
    position: relative;
    width: 30%;
    margin: .5rem;
    pointer-events: none;
}
.modal-body {
    position: relative;
    text-align: start;
    flex: 1 1 auto;
    padding: 1rem;
}
.col-md-3 {
    flex: 0 0 auto;
    width: 25%;
}
.card-header {
    padding: .5rem 1rem;
    margin-bottom: 0;
    background-color: rgba(0,0,0,.03);
    border-bottom: 1px solid rgba(0,0,0,.125);
}
h5 {
    display: block;
    font-size: 0.83em;
    margin-block-start: 1.67em;
    margin-block-end: 1.67em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
}
h5, h5 {
    font-size: 1.25rem;
    color: var(--light);
}
.bg-primary {
    --bs-bg-opacity: 1;
    background-color: rgba(var(--bs-primary-rgb),var(--bs-bg-opacity))!important;
}
.shadow {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.card-header:first-child {
    border-radius: calc(.25rem - 1px) calc(.25rem - 1px) 0 0;
}
.card-title {
    margin-bottom: .5rem;
}
.bg-gradient {
    background-image: var(--bs-gradient)!important;
}

.card-body {
    flex: 1 1 auto;
    padding: 1rem 1rem;
}
.card-footer:last-child {
    border-radius: 0 0 calc(.25rem - 1px) calc(.25rem - 1px);
}
.card-footer {
    padding: .5rem 1rem;
    background-color: rgba(0,0,0,.03);
    border-top: 1px solid rgba(0,0,0,.125);
}
.text-center {
    text-align: center!important;
}
.mb-2 {
    margin-bottom: .5rem!important;
}


.text-muted {
    --bs-text-opacity: 1;
    color: #6c757d!important;
}
.fw-bold {
    font-weight: 700!important;
}
.fs-4 {
    font-size: calc(1.275rem + .3vw)!important;
}
dd {
    margin-bottom: .5rem;
    margin-left: 0;
}
dt {
    font-weight: 700;
}
.modal .modal-header {
    padding: 20px;
    border-bottom: 1px solid #ccc;
}

.modal-title {
    font-size: 20px;
    font-weight: bold;
    color: #333;
}
.modal-footer {
    display: flex;
    flex-wrap: wrap;
    flex-shrink: 0;
    align-items: center;
    justify-content: flex-end;
    padding: .75rem;
    border-top: 1px solid #dee2e6;
    border-bottom-right-radius: calc(.3rem - 1px);
    border-bottom-left-radius: calc(.3rem - 1px);
}
.btn-group-sm>.btn, .btn-sm {
    padding: .25rem .5rem;
    font-size: .875rem;
    border-radius: .2rem;
}

.btn {
    display: inline-block;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: .375rem .75rem;
    font-size: 1rem;
    border-radius: .25rem;
    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
.btn-primary {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
}
.btn-danger {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}
.btn-secondary {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}
.btn-group, .btn-group-vertical {
    position: relative;
    display: inline-flex;
    vertical-align: middle;
}
.border {
    border: 1px solid #dee2e6!important;
}
.container, .container-fluid, .container-lg, .container-md, .container-sm, .container-xl, .container-xxl {
    margin-right: auto;
    margin-left: auto;
}
.fc-daygrid-dot-event {
    display: flex;
    align-items: center;
    padding: 2px 0;
    background: var(--light-red);
    color: var(--light);
}

.notification-container {
    position: relative;
    display: inline-block;
}

.notification-dropdown {
    position: absolute;
    top: 30px;
    right: 0;
    background-color: #1A6B96;
    outline:1px solid rgba(0,0,0,.2);
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.8);
    border-radius: 5px;
    width: 730px;
    z-index: 10;
    padding: 10px;
    color: #fff;
    visibility: hidden; /* Hidden by default */
    transform: translateY(-20px); /* Start slightly above */
    opacity: 0; /* Start invisible */
    transition: transform .5s ease, opacity .5s ease, visibility 0s .5s; /* Smooth slide and fade */
}

.notification-dropdown.active {
    visibility: visible; /* Make it visible */
    transform: translateY(0); /* Slide down to position */
    opacity: 1; /* Fully visible */
    transition: transform .5s ease, opacity .5s ease, visibility 0s; /* Instant visibility */
}

.notification-dropdown p {
    margin: 0;
    padding: 10px;
}

.notification-dropdown p:last-child {
    border-bottom: none;
}
.notification-dropdown li {
    padding:10px;
    background:rgba(0,0,0,.2);
    border-bottom:1px solid rgba(0,0,0,.2);
}
.notification-dropdown li:hover {
    background-color: rgba(255, 255, 255, 0.4);
    cursor: pointer;
}

.notification-dot {
    width: 10px;
    height: 10px;
    background-color: Yellow;
    border-radius: 50%;
    margin-right: 10px; /* Space between dot and message */
}

/* Style for View All link */
.view-all-link {
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    margin-top: 10px;
    padding: 10px;
    background-color: #0d4e73;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.view-all-link i {
    margin-left: 5px; /* Space between text and icon */
}

.view-all-link:hover {
    background-color: #093954;
    cursor: pointer;
}
    </style>
</head>
<body>


    <section id="sidebar">
        <div class="center-a">
        <a href="home" class="logs">
        <img src="css/img/LOGO.png" alt="Logo" style="width: 50px; border-radius:50px;" id="logs">
        <span class="text" id="title-txt">Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan</span>
        </a>
        </div>
        <ul class="side-menu top">
            <li>
                <a href="home.php">
                <i class='bx bxs-home-alt-2'></i>
                 <span class="text">Home</span>
                </a>
            </li>
            <li  class="active">
                <a href="my_reservation.php">
                <i class='bx bxs-calendar-event'></i>
                 <span class="text">Reservation</span>
                </a>
            </li>
            
            <li>
                <a href="my_calendar.php">
                <i class='bx bxs-calendar'></i>
                 <span class="text">Calendar</span>
                </a>
            </li>

            
            <li>
    <a href="messages.php">
        <i class='bx bxs-message-rounded'></i>
        <span class="text">Messages</span>
        <!-- Display unread message count if greater than 0 -->
        <span id="count" style="left:100px; color:white; text-shadow:3px 3px 5px red;">
            <?php echo $unreadCount > 0 ? $unreadCount : ''; ?>
        </span>
    </a>
</li>

        
<li>
    <a href="announcement.php">
        <i class='bx bxs-megaphone'></i>
        <span class="text">Announcement</span>
    </a>
</li>

        </ul>
        <ul class="side-menu">
            <li>
                <a href="settings.php">
                <i class='bx bxs-cog'></i>
                 <span class="text">Settings</span>
                </a>
            </li>
            <li>
                <a href="#" class="logout"  id="logout-link">
                <i class='bx bx-log-out'></i>
                 <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link" style="opacity:0; display:none">Categories</a>
            <form action="#">
                <div class="form-input" style="opacity:0; display:none;">
                    <input type="search" name="" id="" placeholder="Search...">
                    <button type="submit" class="search-btn">
                    <i class='bx bx-search'></i>
                    </button>
                </div>
            </form>
            <?php
             $username = $_SESSION['username']; // Get the username from the session
             ?>

          
<div class="clock" style="width:100%; justify-content:end;">
                <h4 id="date-time" style="color:lightgreen;"></h4>
            </div>
            <?php
// Fetch unread notifications for the logged-in user (limit 7 for display)
$sqlFetchNotifications = "SELECT * FROM notifications WHERE uid = ? ORDER BY created_at DESC LIMIT 7";
$stmtFetchNotifications = $conn->prepare($sqlFetchNotifications);
$stmtFetchNotifications->bind_param('i', $uid); // Use $uid from session
$stmtFetchNotifications->execute();
$notifications = $stmtFetchNotifications->get_result();

// Get the total unread count
$stmtUnreadCount = $conn->prepare("SELECT COUNT(*) AS unread FROM notifications WHERE uid = ? AND is_read = FALSE");
$stmtUnreadCount->bind_param('i', $uid);
$stmtUnreadCount->execute();
$unreadCountResult = $stmtUnreadCount->get_result();
$unreadCount = $unreadCountResult->fetch_assoc()['unread'];

// Count total notifications for "View All" check
$stmtTotalNotifications = $conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE uid = ?");
$stmtTotalNotifications->bind_param('i', $uid);
$stmtTotalNotifications->execute();
$totalNotificationsResult = $stmtTotalNotifications->get_result();
$totalNotifications = $totalNotificationsResult->fetch_assoc()['total'];
?>

<div class="notification-container">
    <a href="#" class="notification" id="notification-bell">
        <i class='bx bxs-bell'></i>
        <span class="num" id="notification-count"><?php echo $unreadCount; ?></span>
    </a>
    <div class="notification-dropdown" id="notification-dropdown">
        <h2 style="padding:10px; text-align:center;">Notification</h2>
        <?php if ($notifications->num_rows > 0): ?>
            <ul>
                <?php while ($notification = $notifications->fetch_assoc()): ?>
                    <li style="display:flex; flex-direction:row; align-items:center; justify-content:space-between;">
                        <?php 
                        // Determine the target URL based on s_id and post_aid
                        $targetUrl = '#';
                        if (!empty($notification['s_id']) && empty($notification['post_aid'])) {
                            $targetUrl = "my_reservation-details.php?s_id=" . urlencode($notification['s_id']);
                        } elseif (!empty($notification['post_aid']) && empty($notification['s_id'])) {
                            $targetUrl = "announcement.php?post_aid=" . urlencode($notification['post_aid']);
                        }
                        ?>
                        <a href="<?php echo htmlspecialchars($targetUrl); ?>" style="text-decoration:none; color:inherit;">
                            <p style="display:flex; justify-content:space-between; align-items:center;">
                                <?php if ($notification['is_read'] == false): ?>
                                    <span class="notification-dot"></span>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </p>
                        </a>
                        <small><?php echo date('m/d/Y h:i A', strtotime($notification['created_at'])); ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>
            <?php if ($totalNotifications > 7): // Show "View All" if more than 7 notifications ?>
                <a href="user-noti.php" class="view-all-link" style="color:#ffff;">
                    View All <i class='bx bxs-chevron-down'></i>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <p>No new notifications</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const notificationBell = document.getElementById('notification-bell');
    const notificationDropdown = document.getElementById('notification-dropdown');
    const notificationCount = document.getElementById('notification-count');

    // Toggle dropdown on bell click
    notificationBell.addEventListener('click', function (event) {
        event.preventDefault(); // Prevent default link behavior
        const isActive = notificationDropdown.classList.contains('active');

        // Toggle active class
        if (isActive) {
            notificationDropdown.classList.remove('active');
        } else {
            notificationDropdown.classList.add('active');

            // Mark notifications as read
            fetch('mark_notifications_read.php', {
                method: 'POST',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationCount.textContent = '0'; // Reset count to 0
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });

    // Hide dropdown if clicked outside
    document.addEventListener('click', function (event) {
        if (!notificationBell.contains(event.target) && !notificationDropdown.contains(event.target)) {
            notificationDropdown.classList.remove('active');
        }
    });
});
</script>



            <a href="#" class="profile" id="profile-link">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Icon">
            </a>
        </nav>
        <main>
        <div class="layout-page">


        <div class="cont-burial"  style="height:100%;">
        <div class="b-header">
            <h1>Mass Intention</h1>
        </div>
        <form action="update-detail-mass.php" method="POST" enctype="multipart/form-data">
        <div class="input-b">
        <input type="hidden" name="s_id" value="<?php echo htmlspecialchars($s_id); ?>">
    <input type="hidden" id="service_type" name="service_type" value="<?php echo htmlspecialchars($reservation['service_type']); ?>" style="width:100%; opacity:.5;" readonly>
</div>

        <div class="input-b">
        <label for="s_description">Description:</label>
        <p style="font-size:13px; margin-top:10px;">Type of Mass Intention</p>
        <input type="text" id="s_description" name="s_description" value="<?php echo htmlspecialchars($reservation['s_description']); ?>"style="width:100%; opacity:.5;" readonly>
    </div>  
    <div class="input-b">
        <label for="s_description">Name:</label>
        <input type="text" id="s_description" name="s_description1" value="<?php echo htmlspecialchars($reservation['s_description1']); ?>" style="width:100%; opacity:.5;" readonly>
    </div>  

    <div class="input-b">
    <label for="r_type">Schedule:</label>
        <select name="r_type" id="r_type">
            <option value="1st Mass">1st Mass</option>
            <option value="2nd Mass">2nd Mass</option>
            <option value="3rd Mass">3rd Mass</option>
        </select>
    </div>
        <!-- Valid ID upload field -->
        <div class="input-b">
            <label for="valid_id">Reserver's Valid ID:</label>
            <p style="font-size:12px;">Note: valid ID is required</p>
            <input type="file" id="valid_id" name="valid_id" accept="image/*,application/pdf,.docx,.doc" />
            <?php if ($reservation['valid_id']): ?>
                <div>
                    <label>Current Valid ID:</label>
                    <p href="uploads/<?php echo htmlspecialchars($reservation['valid_id']); ?>" target="_blank" style="color:blue;"><?php echo htmlspecialchars($reservation['valid_id']); ?></p>
                </div>
            <?php endif; ?>
        </div>




        <div class="input-b">
    <label for="ammount">Amount:</label>
    <input type="number" id="ammount" name="amount" value="<?php echo htmlspecialchars($reservation['amount']); ?>" placeholder="Any Amount" oninput="showPaymentType()" style="width:100%; opacity:.5;" readonly>
</div>


<div id="payment_type_container"></div>

        <div class="button-container">
            <a href="my_reservation-details.php?s_id=<?php echo $s_id; ?>">Back</a>
            <button type="submit" name="update_reservation" class="a-btn">Save Changes</button>
        </div>
    </form>


    </div>

    <div class="schedule-cont" style="background:#2E8BC0; color:#fff;">
    <div class="container py-5" id="page-container">
        <div class="div-sched-cont01">
            <h1 style="font-size:30px; margin-bottom:10px;">Schedule</h1>
        <div class="row">
            <div class="col-md-9" style="margin-right:40px;" >
                <div id="calendar"></div>
            </div>
          </div>
        </div>
        </div>

        
        <div class="content-detail-serv1" style="background:url('css/img/mass-intention.jpg'); background-size:cover;">
        <div class="bg-content-bapt">  
          <h1 style="font-size:40px; text-transform:uppercase; font-weight:800;">Mass Intention</h1>
          Mass Intention Services offer a meaningful way to remember loved ones, seek blessings, or offer prayers for special intentions during a holy Mass. This spiritual practice strengthens our connection with God and the Church.
        </div>
    </div>
         


        <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-details-modal">
        <div class="center">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header rounded-0">
                    <h5 class="modal-title">Schedule Details</h5>
                </div>
                <div class="modal-body rounded-0">
                    <div class="container-fluid">
                        <dl>
                            <dt class="text-muted">Title</dt>
                            <dd id="title" class="fw-bold fs-4"></dd>
                            <dt class="text-muted">Description</dt>
                            <dd id="description" class=""></dd>
                            <dt class="text-muted">Start</dt>
                            <dd id="start" class=""></dd>
                            <dt class="text-muted">End</dt>
                            <dd id="end" class=""></dd>
                        </dl>
                    </div>
                </div>
                <div class="modal-footer rounded-0">
                    <div class="text-end">
                        <button type="button" class="btn btn-primary btn-sm rounded-0" id="edit" data-id="">Edit</button>
                        <button type="button" class="btn btn-danger btn-sm rounded-0" id="delete" data-id="">Delete</button>
                        <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
          </div>
        </div>
    </div>
    </div>
    </div>
        </main>
    </section>
    <script>
function showPaymentType() {
    const amount = document.getElementById('ammount').value;
    const container = document.getElementById('payment_type_container');

    // Check if amount is not empty or zero
    if (amount.trim() !== '' && parseFloat(amount) > 0) {
        if (!container.innerHTML) {
            // Create the Payment Type content dynamically
            const inputB = document.createElement('div');
            inputB.classList.add('input-b');

            const label = document.createElement('label');
            label.setAttribute('for', 'payment_type');
            label.textContent = 'Payment Type:';

            const select = document.createElement('select');
            select.name = 'payment_type';
            select.id = 'payment_type';

            const option = document.createElement('option');
            option.value = 'Gcash (Scan / Send Money)';
            option.textContent = 'Gcash (Scan / Send Money)';

            select.appendChild(option);
            inputB.appendChild(label);
            inputB.appendChild(select);

            // Append the dynamically created content to the container
            container.appendChild(inputB);
        }
    } else {
        // Clear the container if amount is empty or invalid
        container.innerHTML = '';
    }
}
</script>
    <script>
    function overlayModal() {
  // Get the modal element
  const modal = document.getElementById("event-details-modal");

  // Create an overlay element
  const overlay = document.createElement("div");
  overlay.classList.add("modal-overlay");
  overlay.style.position = "absolute";
  overlay.style.top = "0";
  overlay.style.left = "0";
  overlay.style.width = "100%";
  overlay.style.height = "100%";
  overlay.style.backgroundColor = "rgba(0, 0, 0, 0.5)"; // Semi-transparent black

  // Append the overlay to the modal
  modal.appendChild(overlay);
}
</script>
    
<?php 
$schedules = $conn->query("SELECT * FROM `schedule_list`");
$sched_res = [];
foreach($schedules->fetch_all(MYSQLI_ASSOC) as $row){
    $row['sdate'] = date("F d, Y h:i A",strtotime($row['start_datetime']));
    $row['edate'] = date("F d, Y h:i A",strtotime($row['end_datetime']));
    $sched_res[$row['id']] = $row;
}
?>

<script>
    var scheds = $.parseJSON('<?= json_encode($sched_res) ?>')
</script>
<script src="js/script.js"></script>
   

<!--User Profile Details-->

<?php
// Fetch User details
$UserDetails = null;
$sqlUserDetails = "SELECT uid, username, userimg, firstname, lastname, gender, age, contactnum, address, email, profile_completed FROM users WHERE uid = ?";
$stmtUser = $conn->prepare($sqlUserDetails);
$stmtUser->bind_param('i', $uid); // Use $uid instead of $user_id
if ($stmtUser->execute()) {
    $resultUser = $stmtUser->get_result();
    $UserDetails = $resultUser->fetch_assoc();
}
$stmtUser->close();

// Set default profile image if none exists
$profileImage = !empty($UserDetails['userimg']) ? $UserDetails['userimg'] : 'css/img/default-profile.png';

// Determine profile completion status
$profileCompleted = $UserDetails['profile_completed'] ?? 0; // Default to 0 if not set
?>

<div class="overlay1" id="MyProfile">
    <br>
    <div class="dialog1">
        <h1 style="color:#fff;">My Profile</h1>
        <div class="div-main-prof">
            <div class="left-profile">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" style="width: 200px; height: 200px; border-radius: 10px; box-shadow:0px 5px 5px #000;">
            </div>
            <div class="mid-profile">
                <label>
                    <p>ID:</p>
                    <input type="text" value="<?php echo htmlspecialchars($UserDetails['uid']); ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Username:</p>
                    <input type="text" value="<?php echo htmlspecialchars($UserDetails['username']); ?>" readonly>
                </label>
                <br>
                <label>
                    <p>First Name:</p>
                    <input type="text" value="<?php echo htmlspecialchars($UserDetails['firstname']); ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Last Name:</p>
                    <input type="text" value="<?php echo htmlspecialchars($UserDetails['lastname']); ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Gender:</p>
                    <input type="text" value="<?php echo htmlspecialchars($UserDetails['gender']); ?>" readonly>
                </label>
            </div>
            <div class="right-profile">
                <label>
                    <p>Age:</p>
                    <input type="text" value="<?php echo htmlspecialchars($UserDetails['age']); ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Email:</p>
                    <input type="text" value="<?php echo htmlspecialchars($UserDetails['email']); ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Contact #:</p>
                    <input type="text" value="<?php echo htmlspecialchars($UserDetails['contactnum']); ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Address:</p>
                    <input type="text" value="<?php echo htmlspecialchars($UserDetails['address']); ?>" readonly>
                </label>
            </div>
        </div>
        <div class="divmainbtn-prof" style="display: flex; justify-content: end; margin-right: 30px;">
            <?php if ($profileCompleted): ?>
                <a href="my_profile.php" style="margin-right:10px;">Edit Profile</a>
            <?php else: ?>
                <a href="user-profile-setup.php" style="margin-right:10px;">Set up</a>
            <?php endif; ?>
            <button id="close-profile">Close</button>
        </div>
    </div>
</div>

<style>
.dialog1 {
    transform: translateY(-100%);
    opacity: 0;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.dialog1.slide-down {
    transform: translateY(0); /* Slide down into view */
    opacity: 1; /* Make it visible */
}

.dialog1.slide-up {
    transform: translateY(-100%); /* Slide up to hide */
    opacity: 0; /* Make it invisible */
}

.overlay1 {
    display: none; /* Initially hidden */
    justify-content: center;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    overflow: hidden;
}


</style>
<script src="javascript/prof-animate.js"></script>


<script src="javascript/user-profile.js"></script>

    <script src="javascript/script.js"></script>

<!-- Confirmation dialog -->
<div class="overlay" id="confirmation-dialog">
    <div class="dialog">
        <p>Are you sure you want to log out?</p>
        <br>
        <button id="confirm-logout">Yes</button>
        <button id="cancel-logout">No</button>
    </div>
</div>
<script src="javascript/user-logout.js"></script>

<!-- CLock  -->
<script>
    // Get the username from PHP (as passed in the script)
const username = "<?php echo htmlspecialchars($username); ?>"; // Get admin name from PHP

window.addEventListener("load", () => {
    clock();
    function clock() {
        const today = new Date();

        // Get time components
        const hours = today.getHours();  // Get the hours in 24-hour format
        const minutes = today.getMinutes();
        const seconds = today.getSeconds();

        // Add '0' to hour, minute & second when they are less than 10
        const hour = hours % 12 || 12;  // Convert to 12-hour format
        const minute = minutes < 10 ? "0" + minutes : minutes;
        const second = seconds < 10 ? "0" + seconds : seconds;

        // Determine AM or PM
        const ampm = hours < 12 ? "AM" : "PM";

        // Set the greeting based on the time of day
        let greeting = "";
        if (hours < 12) {
            greeting = `Good Morning, ${username}!`;
        } else if (hours < 18) {
            greeting = `Good Afternoon, ${username}!`;
        } else {
            greeting = `Good Evening, ${username}!`;
        }

        // Format the time string
        const time = `${hour}:${minute}:${second} ${ampm}`;

        // Update the greeting and time on the page
        document.getElementById("date-time").innerHTML = time;
        const greetingElement = document.querySelector(".greetings");
        greetingElement.innerHTML = greeting;

        // Add show class for slide-up and fade-in
        greetingElement.classList.add("show");

        // Remove the show class and add hide class after 7 seconds
        setTimeout(() => {
            greetingElement.classList.remove("show");
            greetingElement.classList.add("hide");
        }, 7000);

        // Update the clock every second
        setTimeout(clock, 1000);
    }
});
</script>


</body>
</html>


<?php
// Close the connection at the very end of the script
$conn->close();
?>
