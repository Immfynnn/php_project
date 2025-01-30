<?php
session_start();
require_once "../config.php";


// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
header("Pragma: no-cache");

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
        header("Location: signin.php");
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


?>

<!-- The rest of the HTML (unchanged) -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
    <link rel="stylesheet"  href="css/css-bgcolorx.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
    <style>
        /* Style for the buttons */
button, a.button-link {
    display: inline-block;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    border-radius: 5px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

/* Update Button */
#button-update {
    background-color: #4CAF50; /* Green background */
    color: white;
    border: none;
}

#button-update:hover {
    background-color: #45a049; /* Darker green on hover */
}

/* Cancel Reservation Button */
#button-delete {
    background-color: #f44336; /* Red background */
    color: white;
    margin-top:10px;
    margin-bottom:10px;
    border: none;
}

#button-delete:hover {
    background-color: #e53935; /* Darker red on hover */
}

/* Back Link */
a.button-link {
    background-color: #008CBA; /* Blue background */
    color: white;
    border: none;
    text-decoration: none; /* Remove underline */
}

a.button-link:hover {
    background-color: #007bb5; /* Darker blue on hover */
}

/* General Button Styling */
button:focus, a:focus {
    outline: none;
}

button:active, a:active {
    transform: scale(0.98); /* Button click effect */
}
/* Style for the warning overlay */
.overlay-warning {
    display: none;  /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

/* Warning dialog box */
.dialog-warning {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    width: 80%;
    max-width: 500px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.dialog-warning h2 {
    color: red;
}

.dialog-warning p {
    font-size: 16px;
    margin-bottom: 20px;
}

.dialog-warning button {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #f44336; /* Red background */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.dialog-warning button:hover {
    background-color: #e53935; /* Darker red on hover */
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
        <a href="home.php" class="logs">
        <img src="css/img/LOGO.png" alt="Logo" style="width: 50px; border-radius:50px;" id="logs">
        <span class="text" id="title-txt">Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan</span>
        </a>
        </div>
        <ul class="side-menu top">
            <li>
                <a href="home.php">
                <i class='bx bxs-home-alt-2' ></i>
                 <span class="text">Home</span>
                </a>
            </li>
            <li class="active">
                <a href="my_reservation.php">
                <i class='bx bxs-calendar-event' ></i>
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
                <i class='bx bxs-megaphone' ></i>
                 <span class="text">Announcement</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="settings.php">
                <i class='bx bxs-cog'></i>
                 <span class="text" style="color:#f9f9f9;">Settings</span>
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

            <div class="clock">
                <h4 class="greetings" style="margin-right:10px;color:#f9f9f9;"></h4>
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
        <?php

        
// Fetch reservation data for the logged-in user, joining with the payment table
$sqlReservations = "
    SELECT r.s_id, r.service_type, r.set_date, r.payment_type, r.s_status, r.r_date, p.pay_id, p.p_status 
    FROM reservation r
    LEFT JOIN payment p ON r.s_id = p.s_id 
    WHERE r.uid = ? 
    ORDER BY r.r_date DESC"; // Order by the reservation date

$stmtReservations = $conn->prepare($sqlReservations);
$stmtReservations->bind_param('i', $uid); // Use the session's uid
$stmtReservations->execute();
$resultReservations = $stmtReservations->get_result();

// Check if there are any reservations
$reservations = [];
if ($resultReservations->num_rows > 0) {
    while ($row = $resultReservations->fetch_assoc()) {
        $reservations[] = $row; // Add each reservation to the array
    }
}

$stmtReservations->close();
?>

<div class="table-data fade-up" style="flex-direction:column;">
<div style="boder:0; outline:none; box-shadow:none; padding:0; display:flex; justify-content:space-between;">
<h1 style="font-size: 31px;">Receipt</h1>
<div>
<a href="my_reservation.php" style="text-align:center; width:100px;  margin-right:30px;  padding:10px; padding-right:20px; padding-left:20px; background:#DB504A;
border-radius:5px; color:#fff;">Back</a>
</div>
</div>



    <div class="order" style=" background:#1A6B96; color:#fff;">
        <div class="head">
            <h3>My Receipt</h3>
            <input type="text" id="searchInput" name="search" placeholder="Search by Receipt No. or Name of Services" style="display: flex; padding:8px; background:rgba(255,255,255,.6); width:320px; border:none; outline:solid none; border-radius:6px;" />
            <i class='bx bx-search' id="searchIcon"></i>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('searchInput');

                // Add input event listener for search functionality
                searchInput.addEventListener('input', function () {
                    const filter = searchInput.value.toLowerCase();
                    const tableRows = document.querySelectorAll("tbody tr");

                    tableRows.forEach(row => {
                        // Select relevant cells from the row
                        const receiptNo = row.querySelector("td:nth-child(1)")?.textContent.toLowerCase() || '';
                        const serviceName = row.querySelector("td:nth-child(2)")?.textContent.toLowerCase() || '';

                        // Show or hide the row based on the search filter
                        if (
                            receiptNo.includes(filter) ||
                            serviceName.includes(filter)
                        ) {
                            row.style.display = ""; // Show the row
                        } else {
                            row.style.display = "none"; // Hide the row
                        }
                    });
                });
            });
        </script>


        <style>
            /* Style for the status with 'Pending' */
.setcolor-pending {
    color: white;
    background-color: #ffa500;
    border-radius: 25px;
    padding: 5px;
    font-size:14px;
    padding-left: 53px;
    padding-right:53px;
    text-align: center;
    font-weight:700;
}

            /* Style for the status with 'Pending' */
            .setcolor-canceled {
    color: white;
    background-color: #e60000;
    border-radius: 25px;
    padding: 5px;
    font-size:14px;
    padding-left: 53px;
    padding-right:53px;
    text-align: center;
    font-weight:700;
}


/* Style for the status with 'Approved' */
.setcolor-paid {
    color: white;
    background-color: #28a745;
    border-radius: 25px;
    font-size:14px;
    padding: 5px;
    padding-left: 65px;
    padding-right: 65px;
    text-align: center;
    font-weight:700;
}

/* Style for the status with 'Cancelled' */
.setcolor-refund {
    color: white;
    background-color:  #ff4d4d;
    border-radius: 25px;
    font-size:14px;
    padding: 5px;
    padding-left: 55px;
    padding-right: 55px;
    text-align: center;
    font-weight:700;
}

.setcolor-topay {
    color: white;
    background-color:  #e60000;
    border-radius: 25px;
    font-size:14px;
    padding: 5px;
    padding-left: 35px;
    padding-right: 35px;
    text-align: center;
    font-weight:700;
}
.setcolor-processing {
    color: white;
    background-color: #ffa500;
    border-radius: 25px;
    font-size:14px;
    padding: 5px;
    padding-left: 20px;
    padding-right: 20px;
    text-align: center;
    font-weight:700;
}
.setcolor-ongoing {
    color: white;
    background-color:  #004d00;
    border-radius: 25px;
    font-size:14px;
    padding: 5px;
    padding-left: 30px;
    padding-right: 30px;
    text-align: center;
    font-weight:700;
}
.setcolor-otc {
    color: white;
    background-color: #007bff;
    border-radius: 25px;
    font-size:14px;
    padding: 5px;
    padding-left: 23px;
    padding-right: 23px;
    text-align: center;
    font-weight:700;
}

        </style>

        <table>
            <thead>
                <tr>
                    <th>Receipt No.</th>
                    <th>Name of Services</th>
                    <th>Date</th>
                    <th>Payment Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($reservations)) {
                foreach ($reservations as $reservation) {
                    // Skip displaying this reservation if pay_id is null or blank
                    if (empty($reservation['pay_id'])) {
                        continue; // Skip this reservation if pay_id is empty or null
                    }
                    // Determine the class based on s_status
                     $statusClass = "";
                     if ($reservation['p_status'] == "Pending") {
                         $statusClass = "setcolor-pending";
                     } elseif ($reservation['p_status'] == "Approved") {
                         $statusClass = "setcolor-approved";
                     }
                     elseif ($reservation['p_status'] == "Paid") {
                        $statusClass = "setcolor-paid";
                    }
                
                elseif ($reservation['p_status'] == "Canceled") {
                   $statusClass = "setcolor-canceled";
               }
                elseif ($reservation['p_status'] == "Over the Counter") {
                    $statusClass = "setcolor-otc";
                }
                elseif ($reservation['p_status'] == "Refund") {
                    $statusClass = "setcolor-refund";
                }
                



                    // Create a link to view details of the reservation using pay_id
                    $reservationDetailsUrl = "my_receipt-1.php?pay_id=" . $reservation['pay_id'];

                    // Check the status and apply the appropriate style
                    $statusColor = ($reservation['p_status'] == "Pending") ? "style='color: orange;'" : "";

                    echo "<tr onclick=\"window.location.href='$reservationDetailsUrl';\">";
                    echo "<td>" . htmlspecialchars($reservation['pay_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($reservation['service_type']) . "</td>";
                    echo "<td>" . date("F j, Y, g A", strtotime($reservation['r_date'])) . "</td>";
                    echo "<td>" . htmlspecialchars($reservation['payment_type']) . "</td>";
                    // Apply the style to the status column
                    echo "<td><a class='$statusClass'>" . htmlspecialchars($reservation['p_status']) . "</a></td>";
                    echo "</tr>";
                }
            } else {
                // If no reservations, display a message
                echo "<tr><td colspan='5'>No Receipt found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

</main>


<!--User Profile Details-->

<?php
// Fetch admin details (optional, you may not need this if not displaying user info)
$UserDetails = null;
try {
    $sqlUserDetails = "SELECT uid, username, userimg, firstname, lastname, gender, age, email, contactnum, address FROM users WHERE uid = ?";
    $stmtUser = $conn->prepare($sqlUserDetails);

    if (!$stmtUser) {
        throw new Exception("Failed to prepare the statement: " . $conn->error);
    }

    $stmtUser->bind_param('i', $_SESSION['uid']); // Use session UID
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();

    if ($resultUser->num_rows > 0) {
        $UserDetails = $resultUser->fetch_assoc();
    } else {
        throw new Exception("No user details found.");
    }

    $stmtUser->close();
} catch (Exception $e) {
    error_log($e->getMessage());
}

// Default profile image in case it's missing
$profileImage = isset($UserDetails['userimg']) ? $UserDetails['userimg'] : 'css/img/default-profile.png'; // Change to your default image path
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
                    <input type="text" value="<?php echo isset($UserDetails['uid']) ? htmlspecialchars($UserDetails['uid']) : ''; ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Username:</p>
                    <input type="text" value="<?php echo isset($UserDetails['username']) ? htmlspecialchars($UserDetails['username']) : ''; ?>" readonly>
                </label>
                <br>
                <label>
                    <p>First Name:</p>
                    <input type="text" value="<?php echo isset($UserDetails['firstname']) ? htmlspecialchars($UserDetails['firstname']) : ''; ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Last Name:</p>
                    <input type="text" value="<?php echo isset($UserDetails['lastname']) ? htmlspecialchars($UserDetails['lastname']) : ''; ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Gender:</p>
                    <input type="text" value="<?php echo isset($UserDetails['gender']) ? htmlspecialchars($UserDetails['gender']) : ''; ?>" readonly>
                </label>
            </div>
            <div class="right-profile">
                <label>
                    <p>Age:</p>
                    <input type="text" value="<?php echo isset($UserDetails['age']) ? htmlspecialchars($UserDetails['age']) : ''; ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Email:</p>
                    <input type="text" value="<?php echo isset($UserDetails['email']) ? htmlspecialchars($UserDetails['email']) : ''; ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Contact #:</p>
                    <input type="text" value="<?php echo isset($UserDetails['contactnum']) ? htmlspecialchars($UserDetails['contactnum']) : ''; ?>" readonly>
                </label>
                <br>
                <label>
                    <p>Address:</p>
                    <input type="text" value="<?php echo isset($UserDetails['address']) ? htmlspecialchars($UserDetails['address']) : ''; ?>" readonly>
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
