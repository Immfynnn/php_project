<?php
session_start();
require_once "../config.php";

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
header("Pragma: no-cache");

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

// Get the user ID from the session
$user_id = intval($_SESSION['uid']);

// Check if session exists, if not, try to auto-login using remember-me cookie
if (!isset($_SESSION['uid'])) {
    if (isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];
        $sql = "SELECT * FROM users WHERE remember_token = ?";
        $stmt = mysqli_stmt_init($conn);

        if (mysqli_stmt_prepare($stmt, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $token);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $users = mysqli_fetch_assoc($result); // Fetch user data

            if ($users) {
                // Set session variables with user information
                $_SESSION['uid'] = $users['uid'];
                $_SESSION['username'] = $users['username'];
                $_SESSION['firstname'] = $users['firstname'];
            } else {
                // Invalid token, delete the cookie and redirect to sign-in page
                setcookie("remember_me", "", time() - 3600, "/", "", false, true); // Delete cookie
                header("Location: signin.php");
                exit();
            }
        } else {
            echo "Database error: " . mysqli_error($conn);
            exit();
        }
    } else {
        // No session or cookie, redirect to login
        header("Location: signin.php");
        exit();
    }
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
?>

<!-- The rest of the HTML (unchanged) -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet"  href="css/css-bgcolorx.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">

    <style>
        .div-head-msg {
            margin-top:30px;
            padding:5px;
            display:flex;
            flex-direction:row;
            justify-content:space-between;
            background:#1A6B96;
        }
        #row-msg { 
            background:#1A6B96;
        }#row-msg:hover { 
            background:rgba(0,0,0,.2);
        }
        #suggestions-list {
            transition:all ease .3s;
            background:#342E37;
            color:#f9f9f9;
        }
        #suggestions-list:hover {
            transition:all ease .3s;
            background:#f9f9f9;
            color:#342E37;
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
/* Fade-in animation */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Fade-out animation */
@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}

#successOverlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    animation: fadeIn 1s ease forwards; /* Apply fade-in animation */
}

#successMessageContainer {
    background: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
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
            <li>
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

            
            <li class="active">
                <a href="messages.php">
                   <i class='bx bxs-message-rounded'></i>
                    <span class="text">Messages</span>
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


        <div class="cont-inbox fade-up-animation" style="background:#1A6B96; color:#fff; padding:30px; border-radius:10px; box-shadow:0 5px 10px rgba(0,0,0,.5);">
              <form id="delete-all-form" action="delete_all_messages.php" method="POST">
                 <h2 style="margin-bottom:15px;">Messages </h2>
                  <div class="head" style="display:flex; flex-direction:row; justify-content:space-between; align-items:center;">
                  <a id="create-message-btn" style="padding:10px; cursor:pointer; background:#3C91E6; color:#fff; border-radius:5px;">
                      Create Messages
                      <i class='bx bxs-message-square-add'></i>
                  </a>
                  <!-- JavaScript to show overlay and handle form interactions -->
<script>
    // Show the message creation overlay when the button is clicked
    document.getElementById('create-message-btn').addEventListener('click', function() {
        document.getElementById('overlay-create-msg').style.display = 'flex';
    });

    // Close the overlay when the 'Back' button is clicked
    document.getElementById('back-btn').addEventListener('click', function() {
        document.getElementById('overlay-create-msg').style.display = 'none';
    });
</script>

<!-- Animation for the overlay -->
<style>
    @keyframes slideFadeIn {
        0% {
            transform: translateY(50px);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

                      <button type="button" id="delete-all-btn" style="padding:10px; border-radius:5px; background:#DB504A; color:#fff; border:none; cursor:pointer;">
                          Delete all Messages
                          <i class='bx bxs-trash-alt'></i>
                      </button>
                  </div>

                  <div class="div-head-msg">
                    <h5 style="margin-left:20px;">Name</h5>
                    <h5 style="margin-right:300px;">Content</h5>
                    <h5 style="margin-right:100px;">Date & Time</h5>
                  </div>
                  <hr>
              </form>
              <?php
    // Check user status
    $statusSql = "SELECT user_status FROM users WHERE uid = ?";
    $statusStmt = $conn->prepare($statusSql);
    $statusStmt->bind_param('i', $user_id);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();

    if ($statusResult->num_rows > 0) {
        $userStatus = $statusResult->fetch_assoc()['user_status'];
        if ($userStatus === 'Offline') {
            header("Location: signin.php");
            exit();
        }
    } else {
        // If user status is not found, redirect to sign in
        header("Location: signin.php");
        exit();
    }

    // Fetch messages where the user is either the sender or the recipient
    $sql = "SELECT m.message_id, 
                   CASE WHEN m.sender_id = a.admin_id THEN a.admin_username ELSE u.username END AS sender_username,
                   CASE WHEN a.admin_image IS NOT NULL AND a.admin_image != '' THEN a.admin_image ELSE 'css/img/default-profile.png' END AS sender_image,
                   m.message_content, 
                   m.sent_at 
            FROM messages m
            LEFT JOIN admins a ON m.sender_id = a.admin_id
            LEFT JOIN users u ON m.recipient_id = u.uid
            WHERE m.sender_id = ? OR m.recipient_id = ?
            ORDER BY m.sent_at DESC";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $user_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $messageCount = $result->num_rows;

        if ($messageCount > 0) {
            // Mark all unread messages as read
            $updateSql = "UPDATE messages SET read_status = 1 WHERE recipient_id = ? AND read_status = 0";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('i', $user_id);
            $updateStmt->execute();
            $updateStmt->close();

            // Display each message
            while ($row = $result->fetch_assoc()) {
                $messageId = htmlspecialchars($row['message_id']);
                $senderUsername = htmlspecialchars($row['sender_username']);
                $senderImage = htmlspecialchars($row['sender_image']);
                $messageContent = htmlspecialchars($row['message_content']);

                // Truncate message content to 33 characters
                if (mb_strlen($messageContent) > 33) {
                    $messageContent = mb_substr($messageContent, 0, 33) . "......";
                }
                $sentAt = htmlspecialchars($row['sent_at']);
                $sentAtFormatted = date('F d, Y, g:i A', strtotime($sentAt));

                echo "<div class='msg-cont'>";
                echo "<div style='display:flex; flex-direction:row; outline:solid 1px rgba(0,0,0,.1); justify-content:space-between; align-items:center;'>";
                echo "<div id='row-msg-$messageId' onclick='redirectToMessage($messageId)' style='cursor:pointer; margin-top:10px; max-width:400px; border-radius:5px; padding:10px; display:flex; flex-direction:row; justify-content:space-between; align-items:center;'>";
                echo "<img src='{$senderImage}' alt='Profile Image' style='width:40px; height:40px; margin-left:15px; border-radius:50%; margin-right:10px;'>";
                echo "<h3><strong>{$senderUsername}: </strong></h3>";
                echo "</div>";
                echo "<div id='message-content-$messageId' onclick='redirectToMessage($messageId)' style='cursor:pointer;'><p style='margin-left:20px;'>". nl2br($messageContent) . "</p></div>";
                echo "<div style='display:flex; flex-direction:row; align-items:center;'>";
                echo "<p style='margin-right:20px;'> {$sentAtFormatted}</p>";
            
                // Adjust the delete button with correct messageId
                echo "<button type='button' class='delete-btn' data-message-id='$messageId' style='padding:5px; margin-right:20px; background:#DB504A; cursor:pointer; border-radius:10px; outline:none; border:none;'>
                    <i class='bx bxs-trash-alt' style='font-size:1.2rem; padding:5px; color:#fff;'></i>
                </button>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            // If no messages are found
            echo "<p style='text-align:center; padding:100px;'>No messages found.</p>";
        }
    } else {
        // Handle SQL execution errors
        echo "Error: " . htmlspecialchars($stmt->error);
    }
?>

<script>
function redirectToMessage(messageId) {
    window.location.href = `messages-content.php?message_id=${messageId}`;
}
</script>




<!-- Overlay for delete confirmation -->
<div id="overlay-msg-warning" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; justify-content:center; align-items:center; ">
    <div style="background:#1A6B96; padding:20px; border-radius:10px; text-align:center; width:300px; animation: slideFadeIn 0.5s ease-in-out;">
    <i class='bx bx-error-circle' style="font-size:3.5rem; color:red; margin:20px;"></i>
        <p style="color:#fff">Are you sure you want to delete this message?</p>
        <form id="delete-form" action="delete_message.php" method="POST" style="display:dlex; margin-top:20px; flex-direction:row; justify-content:center; align-items:center;">
            <input type="hidden" name="message_id" id="message-id-input">
            <button type="submit" style="padding:10px; cursor:pointer; padding-left:30px; margin-right:10px; padding-right:30px; background:red; color:#fff; border:none; border-radius:5px;">Yes</button>
            <button type="button" id="cancel-btn" style="padding:10px;cursor:pointer; padding-left:30px; padding-right:30px; background:gray; color:#fff; border:none; border-radius:5px;">No</button>
        </form>
    </div>
</div>


<!-- Overlay for deleting all messages -->
<div id="overlay-delete-all-msg" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; justify-content:center; align-items:center; ">
    <div style="background:#1A6B96; padding:20px; border-radius:10px; text-align:center; width:300px; animation: slideUpFadeIn 0.5s ease-in-out;">
        <i class='bx bx-error-circle' style="font-size:3.5rem; color:red; margin:20px;"></i>
        <p style="color:#fff;">Are you sure you want to delete all messages?</p>
        <form id="delete-all-form-confirm" action="delete_all_messages.php" method="POST" style="display:flex; margin-top:20px; flex-direction:row; justify-content:center; align-items:center;">
            <button type="submit" style="padding:10px; cursor:pointer; padding-left:30px; margin-right:10px; padding-right:30px; background:red; color:#fff; border:none; border-radius:5px;">Yes</button>
            <button type="button" id="cancel-all-btn" style="padding:10px; cursor:pointer; padding-left:30px; padding-right:30px; background:gray; color:#fff; border:none; border-radius:5px;">No</button>
        </form>
    </div>
</div>
<?php
// Fetch registered admins from the database
include '../config.php';

// Query to get admin usernames and IDs
$sql = "SELECT admin_id, admin_username FROM admins WHERE admin_active_status = 'Online'"; // You can adjust the query to include active admins only
$result = $conn->query($sql);
?>

<!-- Overlay for Creating a New Message -->
<div id="overlay-create-msg" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:#1A6B96; padding:30px; border-radius:10px; text-align:center; width:400px; animation: slideFadeIn 0.5s ease-in-out;">
        <h3>Create a New Message</h3>
        <form id="create-message-form" action="send_message.php" method="POST" enctype="multipart/form-data">
  <!-- Dropdown for selecting Admin Username -->
  <label for="recipient_admin_id_or_username" style="margin-top:20px;">Select Admin Username</label>
  <select id="recipient_admin_id_or_username" name="recipient_admin_id_or_username" style="margin-top:20px;" required>
    <option value="" disabled selected>Select an Admin</option>
    <?php
    // Fetch all admins
    $query = "SELECT admin_id, admin_username FROM admins";
    $result = $conn->query($query);

    // Check if any admins are fetched
    if ($result && $result->num_rows > 0) {
        // Loop through and display each admin as an option
        while ($row = $result->fetch_assoc()) {
            $admin_id = htmlspecialchars($row['admin_id']);
            $admin_username = htmlspecialchars($row['admin_username']);
            echo "<option value='{$admin_id}'>{$admin_username}</option>";
        }
    } else {
        echo "<option value='' disabled>No admins available</option>";
    }
    ?>
</select>
<br><br>
            <textarea id="message_cont" name="message_cont" rows="4" placeholder="Write your message..." style="width:100%; padding:10px;"></textarea><br><br>
            <label for="image_upload">Attach Image:</label>
            <input type="file" name="image_upload" id="image_upload">
            <button type="submit" style="padding:15px; cursor:pointer; background:#3C91E6; color:#fff; border:none; border-radius:5px;">Send Message</button>
            <button type="button" id="close-btn" style="padding:15px; margin-top:5px; cursor:pointer; background:#DB504A; color:#fff; border:none; border-radius:5px;">Close</button>
        </form>
    </div>
</div>
<!-- Success Overlay for Message Sent -->
<div id="successOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; justify-content:center; align-items:center;">
    <div id="successMessageContainer" style="background:transparent; padding:20px; border-radius:10px; text-align:center;">
        <h2 id="successMessage" style="margin:0; color:#fff;">Message Sent Successfully!</h2>
    </div>
</div>
<script>
// JavaScript to handle form submission and show success overlay
document.querySelector('#create-message-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    const formData = new FormData(this); // Gather form data

    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Parse JSON response
    .then(data => {
        if (data.status === 'success') {
            // Show the success overlay with fade-in animation
            const successOverlay = document.getElementById('successOverlay');
            successOverlay.style.display = 'flex'; // Make overlay visible

            // Apply fade-out animation after 2 seconds and redirect after fade-out is complete
            setTimeout(() => {
                successOverlay.style.animation = 'fadeOut 1s ease forwards'; // Apply fade-out animation
                setTimeout(() => {
                    successOverlay.style.display = 'none'; // Hide overlay after fade-out
                    window.location.href = 'messages.php'; // Redirect to messages.php
                }, 1000); // Wait for the fade-out to finish
            }, 2000); // Keep the overlay visible for 2 seconds
        } else {
            // Show error message (you can add an error overlay here if needed)
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error); // Handle errors
        alert('An error occurred while sending the message.');
    });
});
</script>



<!-- JavaScript to handle AJAX autocomplete -->
<script>
    // Get the input field and suggestions list
    const inputField = document.getElementById('recipient_admin_id_or_username');
    const suggestionsList = document.getElementById('suggestions-list');

    // Add an event listener for the input field
    inputField.addEventListener('input', function() {
        const query = inputField.value;

        if (query.length >= 3) {  // Start searching after 3 characters
            // Create a new AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_admin_usernames.php?term=' + query, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Parse the returned JSON data
                    const usernames = JSON.parse(xhr.responseText);
                    // Clear the suggestions list
                    suggestionsList.innerHTML = '';
                    // If there are results, show them
                    if (usernames.length > 0) {
                        suggestionsList.style.display = 'block';
                        usernames.forEach(function(username) {
                            const li = document.createElement('li');
                            li.textContent = username;
                            li.style.padding = '8px';
                            li.style.cursor = 'pointer';
                            li.addEventListener('click', function() {
                                inputField.value = username;  // Set input value to clicked suggestion
                                suggestionsList.innerHTML = '';  // Clear the suggestions list
                                suggestionsList.style.display = 'none';  // Hide the suggestions list
                            });
                            suggestionsList.appendChild(li);
                        });
                    } else {
                        suggestionsList.style.display = 'none';  // Hide if no results
                    }
                }
            };
            xhr.send();
        } else {
            // Hide suggestions list if query is too short
            suggestionsList.style.display = 'none';
        }
    });

    // Close the message creation overlay when the 'Close' button is clicked
    document.getElementById('close-btn').addEventListener('click', function() {
        document.getElementById('overlay-create-msg').style.display = 'none';
    });
</script>

<!-- Animation for the overlay -->
<style>
    @keyframes slideFadeIn {
        0% {
            transform: translateY(50px);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<style>
    @keyframes slideFadeIn {
        0% {
            transform: translateY(50px);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes slideUpFadeIn {
        0% {
            transform: translateY(50px);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<script>
    // Handling individual message delete confirmation
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const messageId = this.getAttribute('data-message-id');
            document.getElementById('message-id-input').value = messageId;
            document.getElementById('overlay-msg-warning').style.display = 'flex';
        });
    });

    document.getElementById('cancel-btn').addEventListener('click', function() {
        document.getElementById('overlay-msg-warning').style.display = 'none';
    });

    // Handling the "Delete all Messages" button
    document.getElementById('delete-all-btn').addEventListener('click', function() {
        document.getElementById('overlay-delete-all-msg').style.display = 'flex';
    });

    document.getElementById('cancel-all-btn').addEventListener('click', function() {
        document.getElementById('overlay-delete-all-msg').style.display = 'none';
    });
</script>
 

        </main>
    </section>


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
