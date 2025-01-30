<?php
// admin-messages-reply.php

session_start(); // Start the session here
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);
// Get the user ID from the query parameter
if (isset($_GET['uid'])) {
    $uid = intval($_GET['uid']);
} else {
    header("Location: admin-parishioner.php");
    exit();
}

// Query to fetch the user details based on the uid
$sql = "SELECT * FROM users WHERE uid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style29.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
</head>
<body>
    <section id="sidebar">
        <div class="center-a">
            <a href="" class="logs">
                <img src="css/img/LOGO.png" alt="Logo" style="width: 50px; border-radius:50px;" id="logs">
                <span class="text" id="title-txt">AdminHub</span>
            </a>
        </div>
        <ul class="side-menu top">
            <li><a href="admin_dashboard.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="admin-post.php"><i class='bx bx-news'></i>Post</a></li>
            
            <!--Messages Notification Count -->
           <?php
             // Example: Query to fetch the number of unread messages
               $unreadCount = 0; // Default value
               $sqlUnreadMessages = "SELECT COUNT(*) AS unread_count FROM messages1 WHERE recipient_aid = ? AND read_status1 = 0";
               $stmtUnread = $conn->prepare($sqlUnreadMessages);
               $stmtUnread->bind_param('i', $user_id);
               if ($stmtUnread->execute()) {
                   $resultUnread = $stmtUnread->get_result();
                   $unreadRow = $resultUnread->fetch_assoc();
                   $unreadCount = $unreadRow['unread_count'];
               }
              $stmtUnread->close();
            ?>
            <li>
                <a href="admin-messages.php">
                    <i class='bx bxs-chat'></i>
                    <span class="text">Messages</span>
                    <?php if ($unreadCount > 0): ?>
                        <span id="count"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const messagesLink = document.querySelector('a[href="admin-messages.php"]');
                messagesLink.addEventListener('click', function() {
                    const countSpan = document.getElementById('count');
                    if (countSpan) {
                        countSpan.textContent = ''; // Clear the count
                    }
                });
            });
            </script>
            
            <li><a href="admin-announcement.php"><i class='bx bxs-megaphone'></i>Announcement</a></li>
            <li><a href="admin-schedule.php"><i class='bx bxs-calendar'></i>Schedule</a></li>
            <li><a href="admin-reservation.php"><i class='bx bxs-briefcase-alt-2'></i>Reservation</a></li>
            <li class="active"><a href="admin-parishioner.php"><i class='bx bxs-user'></i>Parishioner</a></li>
        </ul>
        <ul class="side-menu">
            <li><a href="admin-settings.php"><i class='bx bxs-cog'></i>Settings</a></li>
            <li>
               <a href="#" class="logout" id="logout-link" onclick="showLogoutConfirmation(event)">
                   <i class='bx bx-log-out'></i>
                   <span class="text">Logout</span>
               </a>
           </li>

           <script>
            // Logout alert
            document.addEventListener('DOMContentLoaded', () => {
                const logoutLink = document.getElementById('logout-link');
                const confirmationDialog = document.getElementById('confirmation-dialog');
                const confirmLogout = document.getElementById('confirm-logout');
                const cancelLogout = document.getElementById('cancel-logout');
            
                // Show the confirmation dialog
                logoutLink.addEventListener('click', (event) => {
                    event.preventDefault(); // Prevent the default link action
                    confirmationDialog.classList.add('show'); // Show the dialog with transition
                });
            
                // Confirm logout
                confirmLogout.addEventListener('click', () => {
                    // Make an AJAX request to logout.php
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "admin_logout.php", true); // Assuming logout.php will handle the logout
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    window.location.href = 'admin.php'; // Redirect to sign-in page on success
                                } else {
                                    alert("Logout failed. Please try again."); // Handle error response
                                }
                            }
                        }
                    };
                    xhr.send("action=logout"); // Send a request to logout.php
                });
            
                // Cancel logout
                cancelLogout.addEventListener('click', () => {
                    confirmationDialog.classList.remove('show'); // Hide the dialog with transition
                });
            
                // Optionally, close dialog if overlay is clicked
                confirmationDialog.addEventListener('click', (event) => {
                    if (event.target === confirmationDialog) {
                        confirmationDialog.classList.remove('show'); // Hide the dialog with transition
                    }
                });
            });

           </script>
        </ul>
    </section>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#" style="opacity:0;">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <div class="clock"><h4 id="date-time"></h4></div>
            <a href="admin-notification.php" class="notification">
    <i class='bx bxs-bell'></i>
    <span class="num" id="notification-count">0</span>
</a>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function updateNotificationCount() {
        $.ajax({
            url: 'fetch-notification-count.php', // Path to the backend script
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#notification-count').text(response.unread_count); // Update the count
                } else {
                    console.error(response.message);
                }
            },
            error: function(error) {
                console.error('Error fetching notification count:', error);
            }
        });
    }

    // Fetch notification count every 10 seconds
    setInterval(updateNotificationCount, 10000);
    // Fetch immediately when the page loads
    updateNotificationCount();
</script>
           
            <?php
            // Fetch admin details
            $adminDetails = null;
            $sqlAdminDetails = "SELECT admin_image, admin_name, admin_username, admin_contact_no, admin_email, admin_gender FROM admins WHERE admin_id = ?";
            $stmtAdmin = $conn->prepare($sqlAdminDetails);
            $stmtAdmin->bind_param('i', $user_id);
            if ($stmtAdmin->execute()) {
                $resultAdmin = $stmtAdmin->get_result();
                $adminDetails = $resultAdmin->fetch_assoc();
            }
            $stmtAdmin->close();
            
            // Set default profile image if none exists
            $profileImage = !empty($adminDetails['admin_image']) ? $adminDetails['admin_image'] : 'css/img/default-profile.png';
            
            // Display the profile image in the nav section
            ?>
            <a href="#" class="profile" id="profile-link">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" style="outline:solid 1px #000;">
            </a>

        </nav>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Parishioner</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a href="#">Users</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">View Profile</a></li>
                    </ul>
                </div>
            </div>

            <div class="table-data" style=" outline: rgba(0, 0, 0, 0.1) solid 1px; border-radius:20px;">
                <div class="order">
                    <div class="header">
                        <h3>Parishioner</h3>
                    </div>
                    <br>

                    <div class="profile-info">
                        <div class="left-img">
                            <?php
                            $userImg = empty($user['userimg']) ? 'css/img/default-profile.png' : $user['userimg'];
                            ?>
                            <img src="<?php echo $userImg; ?>" alt="Profile Picture" style="width: 250px; height: 250px; border-radius: 50%;">
                            <p class="username"><h1><?php echo $user['username']; ?> </h1></p>
                        </div>
                        <div class="mid-txt">
                            <p><strong>ID:</strong> <?php echo $user['uid']; ?></p>
                            <h2><p>Name: <?php echo $user['firstname'] . ' ' . $user['lastname']; ?></p></h2>                            
                            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                            <p><strong>Gender:</strong> <?php echo $user['gender']; ?></p>
                         </div>
                         <div class="right-txt">
                            <p><strong>Phone:</strong> <?php echo $user['contactnum']; ?></p>
                            <p><strong>Address:</strong> <?php echo $user['address']; ?></p>
                            <p><strong>Active Status:</strong> <span style="color: <?php echo $user['user_status'] == 'Online' ? 'green' : 'red'; ?>"><?php echo $user['user_status']; ?></span></p>
                            <form action="admin_send_messages.php" method="GET" style="display: inline;">
                                <input type="hidden" name="username" value="<?php echo urlencode($user['username']); ?>">
                                <button type="submit" title="Reply" class="btn-button">Send Message</button><br>
                                <br>
                                <a href="admin-parishioner.php" class="btn-back">Back</a>
                           </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </section>

    <div class="overlay" id="confirmation-dialog">
        <div class="dialog">
            <p>Are you sure you want to log out?</p>
            <br>
            <button id="confirm-logout">Yes</button>
            <button id="cancel-logout">No</button>
        </div>
    </div>

    <script src="javascript/script.js"></script>

    <?php
// Fetch admin details
$adminDetails = null;
$sqlAdminDetails = "SELECT admin_image, admin_name, admin_username, admin_contact_no, admin_email, admin_gender FROM admins WHERE admin_id = ?";
$stmtAdmin = $conn->prepare($sqlAdminDetails);
$stmtAdmin->bind_param('i', $user_id);
if ($stmtAdmin->execute()) {
    $resultAdmin = $stmtAdmin->get_result();
    $adminDetails = $resultAdmin->fetch_assoc();
}
$stmtAdmin->close();

// Set default profile image if none exists
$profileImage = !empty($adminDetails['admin_image']) ? $adminDetails['admin_image'] : 'css/img/default-profile.png';
?>

<div class="overlay1" id="MyProfile">
    <br>
    <div class="dialog1">
        <h1>My Profile</h1>
        <br>
        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" style="width: 100px; height: 100px; border-radius: 50%;">
       <div class="div-pg">
        <div class="div-ls">
        <p>Name:</p>
        <p>Username:</p> 
        <p>Contact #:</p>
        <p>Email:</p>
        <p>Gender:</p>
        </div>
        <div class="div-ls1">
        <p><?php echo htmlspecialchars($adminDetails['admin_name']); ?></p>
        <p><?php echo htmlspecialchars($adminDetails['admin_username']); ?></p>
        <p><?php echo htmlspecialchars($adminDetails['admin_contact_no']); ?></p>
        <p><?php echo htmlspecialchars($adminDetails['admin_email']); ?></p>
        <p><?php echo htmlspecialchars($adminDetails['admin_gender']); ?></p>
        </div>
        </div>
        <a href="admin-profile-update.php">Edit Profile</a>
        <button id="close-profile">Close</button>
    </div>
</div>
<script src="javascript/admin-profile.js"></script>
    <!-- Clock -->
    <script>
        window.addEventListener("load", () => {
            clock();
            function clock() {
                const today = new Date();
                const hours = today.getHours();
                const minutes = today.getMinutes();
                const seconds = today.getSeconds();
                const hour = hours < 10 ? "0" + hours : hours;
                const minute = minutes < 10 ? "0" + minutes : minutes;
                const second = seconds < 10 ? "0" + seconds : seconds;
                const hourTime = hour > 12 ? hour - 12 : hour;
                const ampm = hour < 12 ? "AM" : "PM";
                const month = today.getMonth();
                const year = today.getFullYear();
                const day = today.getDate();
                const monthList = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                const time = hourTime + ":" + minute + ":" + second + ampm;
                const dateTime = time;
                document.getElementById("date-time").innerHTML = dateTime;
                setTimeout(clock, 1000);
            }
        });
    </script>
</body>
</html>
