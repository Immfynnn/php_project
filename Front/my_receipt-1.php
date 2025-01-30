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
    <style>
        .cont-receipt {
            width: 80%;
            box-shadow:0 5px 10px rgba(0,0,0,.3);
            outline:solid 1px rgba(0,0,0,.2);
            padding:25px;
            margin:auto;
            border-radius:10px;
        }
        .cont-receipt .header {
            display:flex;
            flex-direction:row;
            justify-content:space-between;
            align-items:center;
            padding:5px 20px;
        }
        .cont-receipt .header .receipt-title h3{
            font-size:18px;
        }
        .cont-receipt .header .receipt-title .txt-title {
            text-transform:uppercase;
        }
        .cont-receipt .receipt-info {
            display:flex;
            flex-direction:row;
            margin-top:35px;
            justify-content:space-between; 
        }
        .cont-receipt .receipt-info .div-inpt {
            width:550px;
            margin:5px; 
            display:flex;
            flex-direction:row;
        }
        .cont-receipt .receipt-info .div-inpt label {
            text-transform:uppercase;
            font-weight:700;
            width: 260px;
        }
        .cont-receipt .receipt-info .div-inpt input {
            width: 100%;
            background: transparent;
            text-align:center;
            border: none;
            font-size:18px;
            border-bottom: 2px solid rgba(0, 0, 0, 1);
            outline: none;
            margin-bottom: 20px;
            color: #40414a;
        }
        .info-lay-row {
            margin-right:100px;
            text-transform:uppercase;
        }
        .table-row {
            padding:10px 40px;
        }
        .table-row table {
            width: 100%;
            outline:solid 2px #000;
            padding:5px;
        }
        .table-row table th{
            padding-bottom: 12px;
            font-size: 18px;
            text-transform:uppercase;
            text-align: center;
            border-bottom: 2px solid #000;
        }
        .table-row table td {
            padding:20px;
            font-size: 18px;

            text-align: center;
        }
        .receipt-footer {
            display:flex;
            flex-direction:row;
            justify-content: space-between;
            padding:1px 25px;
        }
        .receipt-row-9 {
            width: 400px;
            text-transform:uppercase;
        }
        .receipt-row-9 input {
            width: 100%;
            background: transparent;
            text-align:center;
            border: none;
            font-size:18px;
            border-bottom: 2px solid rgba(0, 0, 0, 1);
            outline: none;
            margin-bottom: 20px;
            color: #40414a;
            margin-top:20px;
        
        }
        .div-row-10 {
            display:flex;
            flex-direction:row;
            padding-right:15px;
        }
        .table-row-12 {

            outline:solid 2px #000;
            margin-top:-10px;
            height:45px;
            display:flex;
            justify-content:center;
            align-items:center;
            width: 200px;
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
$pay_id = $_GET['pay_id'] ?? null; // Get `pay_id` from the query parameter

if ($pay_id) {
    // Prepare and execute the query to retrieve the reservation and user details
    $stmt = $conn->prepare("SELECT r.*, u.firstname, u.lastname, u.username, p.pay_id, p.total_amount, p.p_status, a.admin_name 
                            FROM reservation r
                            JOIN users u ON r.uid = u.uid 
                            LEFT JOIN payment p ON r.s_id = p.s_id
                            LEFT JOIN admins a ON r.admin_id = a.admin_id
                            WHERE p.pay_id = ?");
    $stmt->bind_param("i", $pay_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if reservation exists
    if ($result->num_rows > 0) {
        // Fetch reservation, user, and payment data
        $reservation = $result->fetch_assoc();

        // Check if firstname and lastname are set, otherwise use username
        if (!empty($reservation['firstname']) && !empty($reservation['lastname'])) {
            $full_name = $reservation['firstname'] . ' ' . $reservation['lastname'];
        } else {
            $full_name = $reservation['username']; // Use username as fallback
        }

        // Get pay_id for receipt number
        $receipt_no = $reservation['pay_id'] ?? 'N/A';

        // Handle case where admin_name is missing or NULL
        $admin_name = $reservation['admin_name'] ?? ''; // Fallback to 'N/A' if admin_name is not available
    } else {
        echo "Reservation not found.";
        exit();
    }
} else {
    echo "Invalid payment ID.";
    exit();
}
?>

<div class="header" style="margin-bottom:20px; display:flex; flex-direction:row; justify-content:space-between;">
    <h1>My Receipt</h1>
    <div>
    <?php if (!empty($admin_name) && strtolower($reservation['p_status']) !== 'canceled' && strtolower($reservation['p_status']) !== 'refund') : ?>
    <a href="generate-receipt-pdf.php?pay_id=<?php echo htmlspecialchars($pay_id); ?>" 
       style="padding:10px; padding-left:30px; margin-right:5px; padding-right:30px; background:#007BFF; border-radius:5px; color:#fff;">
        Download PDF
    </a>
<?php endif; ?>

        <a href="my_reservation-details.php?s_id=<?php echo htmlspecialchars($reservation['s_id']); ?>" 
           style="padding:10px; padding-left:30px; margin-right:5px; padding-right:30px; background:#007BFF; border-radius:5px; color:#fff;">
            View My Reservation
        </a>

        <a href="user-receipt.php" style="margin-right:120px; padding:10px; padding-left:30px; padding-right:30px; background:#FF4136; border-radius:5px; color:#fff;">
            Back
        </a>
    </div>
</div>
<div class="cont-receipt">
    <div class="header">
        <div class="receipt-title">
            <h3 class="txt-title">Archdiocesan Shrine of Santa Rosa de Lima</h3>
            <h3>Daanbantayan, Cebu</h3>
        </div>
        <h2>OFFICIAL <br> RECEIPT</h2>
    </div>
    <hr>
    <div class="receipt-info">
        <div class="info-lay">
            <div class="div-inpt">
                <label>Received from:</label>
                <input type="text" name="received_from" id="received_from" value="<?php echo htmlspecialchars($full_name); ?>" style="cursor:default;" readonly>
            </div>
            <div class="div-inpt">
                <label>The amount of:</label>
                <input type="text" name="" id="" value="PHP <?php echo htmlspecialchars($reservation['amount']); ?>" style="cursor:default;" readonly>
            </div>
            <div class="div-inpt">
    <?php if (strtolower($reservation['s_status']) === 'canceled' || strtolower($reservation['s_status']) === 'refund'): ?>
        <input type="text" name="" id="" value="<?php echo htmlspecialchars($reservation['p_status']); ?>" style="cursor:default; color:red;" readonly>
    <?php else: ?>
        <!-- Leave this section blank -->
    <?php endif; ?>
</div>

            <div class="div-inpt">
                <label>Received through:</label>
                <input type="text" name="payment_type" value="<?php echo htmlspecialchars($reservation['payment_type']); ?>" style="cursor:default;" readonly>
            </div>
        </div>
        <div class="info-lay-row">
            <h4 style="color:red;">Receipt No: <?php echo htmlspecialchars($receipt_no); ?></h4>
            <h4>Date: <?php echo date("Y/m/d"); ?></h4>
        </div>
    </div>
    <div class="table-row">
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Type</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($reservation['service_type']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['r_type']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['amount']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="receipt-footer">
        <div class="receipt-row-9">
            <h3>Received by:</h3>
            <input type="text" name="" id="" value="<?php echo htmlspecialchars($admin_name); ?>" style="cursor:default;" readonly>
            <p style="text-align:center;">Cashier / Treasurer</p>
        </div>
        <div class="div-row-10">
            <div class="table-row-12">
                <span>Total</span>
            </div>
            <div class="table-row-12">
                <span><?php echo htmlspecialchars($reservation['amount']); ?></span>
            </div>
        </div>
    </div>
</div>

</main>

<!--User Profile Details-->

<?php
// Fetch User details
$UserDetails = null;
$sqlUserDetails = "SELECT uid, username, userimg, firstname, lastname, gender, age, contactnum, address, email FROM users WHERE uid = ?";
$stmtUser = $conn->prepare($sqlUserDetails);
$stmtUser->bind_param('i', $uid); // Use $uid instead of $user_id
if ($stmtUser->execute()) {
    $resultUser = $stmtUser->get_result();
    $UserDetails = $resultUser->fetch_assoc();
}
$stmtUser->close();

// Set default profile image if none exists
$profileImage = !empty($UserDetails['userimg']) ? $UserDetails['userimg'] : 'css/img/default-profile.png';
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
            <a href="my_profile.php" style="margin-right:10px;">Edit Profile</a>
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
