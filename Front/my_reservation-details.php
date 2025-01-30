<?php
session_start();
require_once "../config.php";


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

// Prevent caching
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT"); // Date in the past
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
    <link rel="stylesheet" href="css/update29.css">
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
    padding:20px;
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
.setcolor-canceling {
    color: red;
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
// Get the reservation ID from the URL
if (isset($_GET['s_id'])) {
    $s_id = $_GET['s_id'];

    // Fetch the reservation details from the database
    $sqlDetails = "SELECT r.*, p    .pay_id 
                   FROM reservation r 
                   LEFT JOIN payment p ON r.s_id = p.s_id 
                   WHERE r.s_id = ?";
    $stmtDetails = $conn->prepare($sqlDetails);
    $stmtDetails->bind_param('i', $s_id);
    $stmtDetails->execute();
    $resultDetails = $stmtDetails->get_result();

    // Check if the reservation exists
    if ($resultDetails->num_rows > 0) {
        $reservation = $resultDetails->fetch_assoc();
    } else {
        echo "Reservation not found.";
        exit;
    }

    $stmtDetails->close();
} else {
    echo "No reservation ID provided.";
    exit;
}
?>
<style>
    .input-conf-1 {
        width: 320px;
        cursor: default;
        background: #f4f4f4;
    }

    .input-conf-2 {
        width: 100%;
        cursor: default;
        background: #f4f4f4;
    }
    /* Status Colors */
    .setcolor-topay {
        color: #DB504A; /* Orange */
        animation: pulse 1.5s infinite; /* Add pulse animation */
    }

    /* Status Colors */
    .setcolor-pending {
        color: #fd7238; /* Orange */
        animation: pulse 1.5s infinite; /* Add pulse animation */
    }
     /* Status Colors */
     .setcolor-processing {
        color: #fd7238; /* Orange */
        animation: pulse 1.5s infinite; /* Add pulse animation */
    }

    .setcolor-approved,
    .setcolor-ongoing {
        color: #28a745; /* Green */
        animation: pulse 1.5s infinite; /* Add pulse animation */
    }

    .setcolor-cancelled {
        color: #dc3545; /* Red */
    }

    .setcolor-completed {
        color: #007bff; /* Blue */
    }
    .setcolor-cancel {
        color:red;
    } .setcolor-reschedule {
        color:orange;
    }

    /* Pulse Animation */
    @keyframes pulse {
        0% {n
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
        100% {
            opacity: 1;
        }
    }
</style>

<div class="my_reservation-d" style="background:#1A6B96;">
    <div class="header">
        <h3 style="color:#fff; margin-top:20px;">My Reservation Details</h3>
    </div>
    <form action="reservation-cancel.php" method="POST" id="cancel-reservation-form">
        <!-- Hidden input for the reservation ID -->
        <input type="hidden" name="s_id" value="<?php echo htmlspecialchars($reservation['s_id']); ?>" />

        <!-- Hidden field to indicate the deletion action -->
        <input type="hidden" name="delete_reservation" value="1" />
        
        <div class="input-d-1" style="display: flex; flex-direction: row;">

            <div class="div-row-02" style="width: 100%; display: flex; flex-direction: column; padding: 20px; border-radius: 10px 0 0 10px;">
            <label for="" style="font-size: 18px;">
                    Reservation No: <br>
                    <input type="text" value="<?php echo htmlspecialchars($reservation['s_id']); ?>" class="input-conf-1" readonly>
                </label>
                <label for="" style="font-size: 18px;">
                    Service: <br>
                    <input type="text" value="<?php echo htmlspecialchars($reservation['service_type']); ?>" class="input-conf-1" readonly>
                </label>
                <?php if ($reservation['service_type'] == 'Wedding'): ?>
    <label for="" style="font-size: 18px;">
        Groom: <br>
        <input type="text" value="<?php echo htmlspecialchars($reservation['s_description']); ?>" class="input-conf-1" readonly>
    </label>
    <label for="" style="font-size: 18px;">
        Bride: <br>
        <input type="text" value="<?php echo htmlspecialchars($reservation['s_description1']); ?>" class="input-conf-1" readonly>
    </label>
<?php else: ?>
    <label for="" style="font-size: 18px;">
        Description: <br>
        <input type="text" value="<?php echo htmlspecialchars($reservation['s_description']); ?>" class="input-conf-1" readonly>
    </label>
<?php endif; ?>

<?php if ($reservation['service_type'] == 'Mass Intention'): ?>
    <label for="" style="font-size: 18px;">
          Name: <br>
        <input type="text" value="<?php echo htmlspecialchars($reservation['s_description1']); ?>" class="input-conf-1" readonly>
    </label>
    <?php endif; ?>

<?php if ($reservation['service_type'] == 'Wedding' || $reservation['service_type'] == 'Confirmation'): ?>
    <label for="" style="font-size: 18px;">
        Type: <br>
        <input type="text" value="<?php echo htmlspecialchars($reservation['r_type']); ?>" class="input-conf-1" readonly>
    </label>
<?php endif; ?>

    <?php if($reservation['service_type'] == 'Blessing'): ?>
        <label for="" style="font-size: 18px;">
                    Type of blessing: <br>
                    <input type="text" value="<?php echo htmlspecialchars($reservation['r_type']); ?>" class="input-conf-1" readonly>
                </label>
                <?php endif; ?>
    <?php if ($reservation['service_type'] == 'Mass Intention'): ?>
   
        <label for="" style="font-size: 18px;">
                    Schedule: <br>
                    <input type="text" value="<?php echo htmlspecialchars($reservation['r_type']); ?>" class="input-conf-1" readonly>
                </label>
                
<?php else: ?>
    <label for="" style="font-size: 18px;">
                    Date: <br>
                    <input type="text" value="<?php echo htmlspecialchars($reservation['set_date']); ?>" class="input-conf-1" readonly>
                </label>
                
<?php endif; ?>
               
<?php if ($reservation['service_type'] !== 'Mass Intention'): ?>
    <label for="" style="font-size: 18px;">
                    Priest: <br>
                    <input type="text" value="<?php echo htmlspecialchars($reservation['priest']); ?>" class="input-conf-1" style="color:green;" readonly>
                </label>
<?php endif; ?> 
<?php if ($reservation['service_type'] !== 'Annointing of the Sick' && $reservation['service_type'] !== 'Holy Eucharist' && $reservation['service_type'] !== 'Burial' && $reservation['service_type'] !== 'Blessing' && $reservation['service_type'] !== 'Baptism' && $reservation['service_type'] !== 'Confirmation' && $reservation['service_type'] !== 'Wedding'): ?>
    <label for="" style="font-size: 18px;">
                    Priest: <br>
                    <input type="text" value="<?php echo htmlspecialchars($reservation['priest']); ?>" class="input-conf-1" style="color:green;" readonly>
                </label>
<?php endif; ?> 

        
                
    
            </div>

            <div class="div-row-03" style="width: 100%; display: flex; flex-direction: column; padding: 20px; border-radius: 0 10px 10px 0;">

            <?php if ($reservation['service_type'] !== 'Confirmation'  && $reservation['service_type'] !== 'Mass Intention'): ?>
                    <label for="" style="font-size: 18px;">
                    Time Slot: <br>
                    <input type="text" value="<?php echo htmlspecialchars($reservation['time_slot']); ?>" class="input-conf-2" readonly>
                </label>
                
<?php endif; ?> 
<?php if (strtolower($reservation['service_type']) !== 'burial' && strtolower($reservation['service_type']) !== 'confirmation'): ?>
    <?php if (isset($reservation['per_head']) && $reservation['per_head'] > 0): ?>
        <label for="" style="font-size: 18px;">
            Additional Godparents per head: <br>
            <input type="text" value="<?php echo htmlspecialchars($reservation['per_head']); ?>" class="input-conf-2" readonly>
        </label>
    <?php else: ?>
        <?php 
        // Define the types that should show "N/A"
        $no_godparents_types = ['Confirmation'];

        // Check if service type matches any of the above
        if (in_array($reservation['service_type'], $no_godparents_types)): ?>
            <label for="" style="font-size: 18px;">
                Additional Godparents per head: <br>
                <input type="text" value="N/A" class="input-conf-2" readonly>
            </label>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>



               
<?php if ($reservation['service_type'] == 'Holy Eucharist'): ?>
    <label for="" style="font-size: 18px;">
    No. of attendences: <br>
        <input type="text" value="<?php echo htmlspecialchars($reservation['s_description1']); ?>" class="input-conf-2" readonly>
    </label>
<?php endif; ?> 


                <label for="" style="font-size: 18px;">
                    Reserver's Valid ID: <br>
                    <input type="text" value="<?php echo htmlspecialchars($reservation['valid_id']); ?>" class="input-conf-2" readonly>
                </label>

                <?php if ($reservation['service_type'] !== 'Confirmation' && $reservation['service_type'] !== 'Baptism' && $reservation['service_type'] !== 'Wedding'  && $reservation['service_type'] !== 'Mass Intention' ): ?>
    <label for="" style="font-size: 18px;">
        Address: <br>
        <input type="text" value="<?php echo htmlspecialchars($reservation['s_address']); ?>" class="input-conf-2" readonly>
    </label>
<?php endif; ?>

<?php 
if (
    ($reservation['service_type'] !== "Mass Intention" && !empty($reservation['payment_type'])) || 
    ($reservation['service_type'] !== "Blessing" && !empty($reservation['payment_type']))
): ?>
    <label for="" style="font-size: 18px;">
        Payment Types: <br>
        <input type="text" value="<?php echo htmlspecialchars($reservation['payment_type']); ?>" class="input-conf-2" readonly>
    </label>
<?php endif; ?>


                <!-- Status Field with Dynamic Class -->
                <label for="" style="font-size: 18px;">
                    Status: <br>
                    <?php
                    // Assign a CSS class based on s_status
                    $statusClass = "";
                    if ($reservation['s_status'] == "Pending") {
                        $statusClass = "setcolor-pending";
                    } if ($reservation['s_status'] == "Processing") {
                        $statusClass = "setcolor-processing";
                    }
                      if ($reservation['s_status'] == "To Pay") {
                        $statusClass = "setcolor-topay";
                    }
                    if ($reservation['s_status'] == "Canceling") {
                        $statusClass = "setcolor-cancel";
                    }
                     elseif ($reservation['s_status'] == "Approved" || $reservation['s_status'] == "Ongoing") {
                        $statusClass = "setcolor-approved";
                    } elseif ($reservation['s_status'] == "Canceled") {
                        $statusClass = "setcolor-cancelled";
                    } elseif ($reservation['s_status'] == "Completed") {
                        $statusClass = "setcolor-completed";
                    } elseif ($reservation['s_status'] == "Reschedule") {
                        $statusClass = "setcolor-reschedule";
                    }
                    ?>
                    <input type="text" value="<?php echo htmlspecialchars($reservation['s_status']); ?>" class="<?php echo htmlspecialchars($statusClass); ?> input-conf-2" readonly>
                </label>

                <?php 
if (
    ($reservation['service_type'] !== "Mass Intention" && !empty($reservation['payment_type'])) || 
    ($reservation['service_type'] !== "Blessing" && !empty($reservation['payment_type']))
): ?>
    <label for="" style="font-size: 18px;">
        Amount: <br>
        <input type="text" value="PHP <?php echo htmlspecialchars($reservation['amount']); ?>" class="input-conf-2" readonly>
    </label>
<?php endif; ?>

            </div>
    
        </div>
        <div>
        <div class="div" style="display:flex; justify-content:space-between;">
            <div class="div">
            <?php 
// Display Cancel Reservation button if payment type is "Over the Counter" and status is not "Completed" or "Canceling"
if (
    (strtolower($reservation['payment_type']) === 'over the counter') && 
    (strtolower($reservation['s_status']) !== 'completed') &&
    (strtolower($reservation['s_status']) !== 'canceling') &&
    (strtolower($reservation['s_status']) !== 'canceled') 
): ?>
    <button type="button" id="button-delete" class="button-link" style="padding:20px;">Cancel Reservation</button>
<?php endif; ?>

<?php 
// Display Cancel Reservation button if payment type is "Gcash (Scan / Send Money)" and status is "To Pay"
if (
    (strtolower($reservation['payment_type']) === 'gcash (scan / send money)') && 
    (strtolower($reservation['s_status']) === 'to pay')
): ?>
    <button type="button" id="button-delete" class="button-link" style="padding:20px;">Cancel Reservation</button>
<?php endif; ?>


            </div>

            <div class="div">

            <!-- Conditionally display Payment button -->
            <?php if (strtolower($reservation['s_status']) === 'to pay'): ?>
                 <?php if (strtolower($reservation['payment_type']) === 'over the counter'): ?>       
                     <!-- Redirect to Gateway 2 (GCash) -->
                     <a href="gateway2-payment2.php?s_id=<?php echo htmlspecialchars($reservation['s_id']); ?>" 
                        style="margin-top:10px; background:#4fbd34; padding:20px; margin-right:5px; font-weight:700; color:#fff; border-radius:5px;">
                        Payment
                     </a>
                 <?php elseif (strtolower($reservation['payment_type']) === 'gcash (scan / send money)'): ?>
                      <!-- Redirect to Gateway 1 (Over the Counter) -->
                      <a href="gateway1-payment1.php?s_id=<?php echo htmlspecialchars($reservation['s_id']); ?>" 
                        style="margin-top:10px; background:#4fbd34; padding:20px; margin-right:5px; font-weight:700; color:#fff; border-radius:5px;">
                        Payment
                     </a>
                 <?php endif; ?>
             <?php endif; ?>


                <!-- Conditionally display View Receipt button -->
                <?php if (!empty($reservation['pay_id'])): ?>
                    <a href="my_receipt-1.php?pay_id=<?php echo htmlspecialchars($reservation['pay_id']); ?>" 
                       class="button-link" 
                       style="margin-top:10px; background:#4fbd34; padding:20px;">
                        View Receipt
                    </a>
                <?php endif; ?>

                <!-- Update button conditions -->
                  <!-- Update button conditions -->
                  <?php 
$restricted_statuses = ['approved', 'canceled', 'completed', 'ongoing'];

// Check if the status is "reschedule"
if (strtolower($reservation['s_status']) === 'reschedule') {
    if (strtolower($reservation['service_type']) === 'mass intention') {
        echo '<a href="resched-mass.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-reschedule" style="background:orange;  padding:20px;" " class="button-link">Reschedule</a>';
    } elseif (strtolower($reservation['service_type']) === 'burial') {
        echo '<a href="resched-burial.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-reschedule" style="background:orange;  padding:20px;" "  class="button-link">Reschedule</a>';
    } elseif (strtolower($reservation['service_type']) === 'baptism') {
        echo '<a href="resched-baptism.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-reschedule" style="background:orange; padding:20px;"  class="button-link">Reschedule</a>';
    } elseif (strtolower($reservation['service_type']) === 'blessing') {
        echo '<a href="resched-blessing.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-reschedule" style="background:orange; padding:20px;"  class="button-link">Reschedule</a>';
    } elseif (strtolower($reservation['service_type']) === 'confirmation') {
        echo '<a href="resched-confirmation.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-reschedule" style="background:orange; padding:20px;"  class="button-link">Reschedule</a>';
    }
} 
// Otherwise, display the update button only if not in restricted statuses
elseif (!in_array(strtolower($reservation['s_status']), $restricted_statuses)) {
    if (strtolower($reservation['service_type']) === 'baptism') {
        echo '<a href="my_reservation-update-baptism.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-update" style="background:darkblue;" class="button-link">Update</a>';
    } elseif (strtolower($reservation['service_type']) === 'burial') {
        echo '<a href="my_reservation-update.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-update" style="background:blue;" class="button-link">Update</a>';
    } elseif (strtolower($reservation['service_type']) === 'confirmation') {
        echo '<a href="update-confirmation.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-update" style="background:blue;" class="button-link">Update</a>';
    } elseif (strtolower($reservation['service_type']) === 'wedding') {
        echo '<a href="update-wedding.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-update" style="background:blue;" class="button-link">Update</a>';
    } elseif (strtolower($reservation['service_type']) === 'mass intention') {
        echo '<a href="update-mass.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-update" style="background:blue;" class="button-link">Update</a>';
    } elseif (strtolower($reservation['service_type']) === 'blessing') {
        echo '<a href="update-blessing.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-update" style="background:blue;" class="button-link">Update</a>';
    } elseif (strtolower($reservation['service_type']) === 'annointing of the sick') {
        echo '<a href="update-annoint.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-update" style="background:blue;" class="button-link">Update</a>';
    } elseif (strtolower($reservation['service_type']) === 'holy eucharist') {
        echo '<a href="update-eucharist.php?s_id=' . htmlspecialchars($reservation['s_id']) . '" id="button-update" style="background:blue;" class="button-link">Update</a>';
    }
}
?>


                <!-- Back Button -->
                <a href="my_reservation.php" id="button-back" class="button-link" style="margin-top:10px; padding:20px;">Back</a>
            </div>
            </div>
        </div>
    </form>
</div>

            </main> 

            <script>
                document.addEventListener("DOMContentLoaded", function () {
    const reservationDiv = document.querySelector(".my_reservation-d");
    reservationDiv.style.position = "relative"; // Ensure position is relative or absolute for animation
    reservationDiv.style.left = "100%"; // Start off-screen to the right
    reservationDiv.style.transition = "left 1s ease-in-out"; // Smooth animation

    // Trigger the slide-in animation
    setTimeout(() => {
        reservationDiv.style.left = "0"; // Move to its original position
    }, 100); // Delay for better visual effect
});

            </script>

<!-- Warning Overlay for Cancel Reservation -->
<div class="overlay-warning" id="overlay-warning">
    <div class="dialog-warning">
        <i class='bx bx-error-circle' style="margin-right:5px; color:red; font-size:40px;"></i>
        <h2>Warning!</h2>
        <p>If you cancel your reservation, it will be automatically deleted and the receipt you received will no longer be valid.</p>
        <button id="confirm-cancel" class="button-link">Confirm Cancel</button>
        <button id="cancel-cancel" class="button-link" style="background:blue;">Go Back</button>
    </div>
</div>

<script>
// Get the cancel reservation button and overlay elements
const cancelButton = document.querySelector("#button-delete");
const overlayWarning = document.getElementById("overlay-warning");
const confirmCancelButton = document.getElementById("confirm-cancel");
const cancelCancelButton = document.getElementById("cancel-cancel");

// When the cancel button is clicked, show the warning overlay
cancelButton.addEventListener("click", (e) => {
    e.preventDefault();  // Prevent the form from being submitted immediately
    overlayWarning.style.display = "flex";  // Show the warning overlay
});

// If the user confirms the cancel, submit the form to cancel the reservation
confirmCancelButton.addEventListener("click", () => {
    // Submit the form programmatically
    document.getElementById("cancel-reservation-form").submit();
});

// If the user cancels, hide the warning overlay
cancelCancelButton.addEventListener("click", () => {
    overlayWarning.style.display = "none";  // Hide the overlay
});
</script>



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
