<?php

session_start(); // Start the session here
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Reservation</title>
    <link rel="stylesheet" href="css/temp78.css">
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
            <li>
                <a href="admin_dashboard.php">
                 <i class='bx bxs-dashboard'></i>
                 <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="admin-post.php">
                <i class='bx bx-news'></i>
                 <span class="text">Post</span>
                </a>
            </li>
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
            <li>
                <a href="admin-announcement.php">
                <i class='bx bxs-megaphone'></i>
                 <span class="text">Announcement</span>
                </a>
            </li>
            <li>
                <a href="admin-schedule.php">
                <i class='bx bxs-calendar'></i>
                 <span class="text">Schedule</span>
                </a>
            </li>
            <li  class="active">
                <a href="admin-reservation.php">
                <i class='bx bxs-briefcase-alt-2'></i>
                 <span class="text">Reservation</span>
                 <span id="count" style="left:100px;"></span>
                </a>
            </li>
            <li>
                <a href="admin-parishioner.php">
                <i class='bx bxs-user'></i>
                 <span class="text">Parishioner</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="admin-settings.php">
                <i class='bx bxs-cog'></i>
                 <span class="text">Settings</span>
                </a>
            </li>
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
                    <input type="search" name="" id="" placeholder="Search...">
                    <button type="submit" class="search-btn">
                    <i class='bx bx-search'></i>
                    </button>
                </div>
            </form>
            <div class="clock">
              <h4 id="date-time"></h4>
             </div>
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
                    <h1>Reservation</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li>
                            <i class='bx bx-chevron-right'>

                            </i></li>
                        <li>
                            <a class="active" href="#">Reservation</a>
                        </li>
                    </ul>
                </div>
               
            </div>

            <div class="box-re">
               
                <ul class="box-info2">
                <li style="cursor:pointer;" onclick="window.location.href='admin-payment-reservation.php'">
                        <i class='bx bxs-dollar-circle' ></i>
                         <span class="head-title">
                            <h3>
                            <span class="noti-count"></span>
                                Payment 
                            </h3>
                        </span>
                    </li>
                <li style="cursor:pointer;" onclick="window.location.href='admin-pending-reservation.php'">
                        
                    <i class='bx bxs-hourglass-top'></i>
                        <span class="head-title">
                            <h3>
                            <span class="noti-count"></span>
                            Pending Reservation
                            </h3>
                        </span>
                    </li>
                    <li style="cursor:pointer;" onclick="window.location.href='admin-approved-reservation.php'">
                    <i class='bx bxs-check-circle'></i>
                        <span class="head-title">
                            <h3>
                            <span class="noti-count"></span>
                                Approved Reservation
                            </h3>
                        </span>
                    </li> 
                    <li style="cursor:pointer;" onclick="window.location.href='admin-ongoing-reservation.php'">
                    <i class='bx bxs-timer'></i>
                        <span class="head-title">
                            <h3>
                            <span class="noti-count"></span>
                                On Going
                            </h3>
                        </span>
                    </li>   
                    <li style="cursor:pointer;" onclick="window.location.href='admin-success-reservation.php'">
                    <i class='bx bxs-calendar-check'></i>
                        <span class="head-title">
                            <h3>
                            <span class="noti-count"></span>
                                Completed
                            </h3>
                        </span>
                    </li>   

                    
                </ul>
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

<!-- CLock  -->
<script>

window.addEventListener("load", () => {
  clock();
  function clock() {
    const today = new Date();

    // get time components
    const hours = today.getHours();
    const minutes = today.getMinutes();
    const seconds = today.getSeconds();

    //add '0' to hour, minute & second when they are less 10
    const hour = hours < 10 ? "0" + hours : hours;
    const minute = minutes < 10 ? "0" + minutes : minutes;
    const second = seconds < 10 ? "0" + seconds : seconds;

    //make clock a 12-hour time clock
    const hourTime = hour > 12 ? hour - 12 : hour;

    // if (hour === 0) {
    //   hour = 12;
    // }
    //assigning 'am' or 'pm' to indicate time of the day
    const ampm = hour < 12 ? "AM" : "PM";

    // get date components
    const month = today.getMonth();
    const year = today.getFullYear();
    const day = today.getDate();

    //declaring a list of all months in  a year
    const monthList = [
      "January",
      "February",
      "March",
      "April",
      "May",
      "June",
      "July",
      "August",
      "September",
      "October",
      "November",
      "December"
    ];

    //get current date and time
    const time = hourTime + ":" + minute + ":" + second + ampm;

    //combine current date and time
    const dateTime = time;

    //print current date and time to the DOM
    document.getElementById("date-time").innerHTML = dateTime;
    setTimeout(clock, 1000);
  }
});
</script>

</body>
</html>