<?php
session_start(); // Start the session

include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);

// Fetch unread messages count
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

$sqlReservations = "SELECT reservation.*, users.userimg, users.username 
                    FROM reservation 
                    JOIN users ON reservation.uid = users.uid
                    WHERE reservation.s_status IN ('To Pay', 'Pending', 'Processing')"; // Removed 'Canceled'

$stmtReservations = $conn->prepare($sqlReservations);
$stmtReservations->execute();
$resultReservations = $stmtReservations->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Reservation</title>
    <link rel="stylesheet" href="css/temp05.css">
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
            <li class="active">
                <a href="admin-reservation.php">
                <i class='bx bxs-briefcase-alt-2'></i>
                 <span class="text">Reservation</span>
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
                            <a>Reservation</a>
                        </li>
                        <i class='bx bx-chevron-right'>

                            </i></li>
                        <li>
                        <li>
                            <a class="active" href="#">Pending Reservation</a>
                        </li>
                    </ul>
                </div>
            </div>
            <style>
                .btn-shortcut {
                    padding-top:20px;
                    width:100%;
                    text-align:center;
                    display:flex;
                    justify-content:space-between;
                }
                .btn-shortcut a {
                    text-decoration:none;
                    padding:10px;
                    padding-left:20px;
                    padding-right:20px;
                    text-align:center;
                    border-radius:5px;
                    transition:all ease .5s;
                }
                .btn-shortcut a:hover {
                    box-shadow:0 1px 10px #3C91E6;
                    transition:all ease .5s;
                }
                .payment {
                    background:#aef7bac7;
                    color:#000;
                    box-shadow:0 1px 2px rgba(0,0,0,.3);
                }
                .pending {
                    background:#ffe0d3;
                    color:#000;
                    box-shadow:0 1px 10px #3C91E6;
                }
                .approved {
                    background:#cfe8ff;
                    color:#000;
                    box-shadow:0 1px 2px rgba(0,0,0,.3);
                }
                .ongoing {
                    background:#fff2c6;
                    color:#000;
                    box-shadow:0 1px 2px rgba(0,0,0,.3);
                }
                .completed {
                    background:#aef7bac7;
                    color:#000;
                    box-shadow:0 1px 2px rgba(0,0,0,.3);
                }
            </style>
            <div class="btn-shortcut">
                <a href="admin-payment-reservation.php" class="payment">Payment</a>
                <a href="admin-pending-reservation.php" class="pending">Pending Reservation</a>
                <a href="admin-approved-reservation.php" class="approved">Approved Reservation</a>
                <a href="admin-ongoing-reservation.php" class="ongoing">Ongoing Reservation</a>
                <a href="admin-success-reservation.php" class="completed">Completed Reservation</a>
            </div>



            <div class="table-data fade-up">
                <div class="order">
                    <div class="head">
                        <h3>Pending Reservation</h3>
                        <i class='bx bx-search' ></i>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th style="text-align:center;">No.</th>
                                <th>Name of Reservation</th>
                                <th>Date</th>
                                <th>Payment Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($resultReservations->num_rows > 0) {
                                while ($reservationRow = $resultReservations->fetch_assoc()) {
                                    // Set user image
                                    $userImg = empty($reservationRow['userimg']) ? 'css/img/default-profile.png' : htmlspecialchars($reservationRow['userimg']);
                            ?>
                                    <tr style="cursor: pointer;" onclick="window.location.href='view_reservation_details.php?service_id=<?php echo $reservationRow['s_id']; ?>'">
                                        <td>
                                            <img src="<?php echo $userImg; ?>" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
                                            <p><?php echo htmlspecialchars($reservationRow['username']); ?></p>
                                        </td>
                                        <td style="padding:30px; font-weight:700;"><?php echo htmlspecialchars($reservationRow['s_id']); ?></td>
                                        <td><?php echo htmlspecialchars($reservationRow['service_type']); ?></td>
                                        <td><?php echo date('F j, Y, g:i A', strtotime($reservationRow['r_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($reservationRow['payment_type']); ?></td>
                                        <td><span class="status <?php echo strtolower($reservationRow['s_status']); ?>"><?php echo htmlspecialchars($reservationRow['s_status']); ?></span></td>
                                        <td>
                                            <?php if ($reservationRow['s_status'] == 'Pending') { ?>
                                                <form action="update_service_status.php" method="POST">
                                                    <input type="hidden" name="service_id" value="<?php echo $reservationRow['s_id']; ?>">
                                                    <button type="submit" id="update-btn">Update</button>
                                                </form>
                                            <?php } else { ?>
                                                <span style="color:#3C91E6;">Updated</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='7'>No reservations found</td></tr>";
                            }
                            $stmtReservations->close();
                            ?>
                        </tbody>

                    </table>


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

<script src="javascript/script.js"></script>

<!-- CLock  -->
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

    // Clock script
    window.addEventListener("load", () => {
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
            const time = hourTime + ":" + minute + ":" + second + ampm;
            document.getElementById("date-time").innerHTML = time;
            setTimeout(clock, 1000);
        }
        clock();
    });
    </script>


</body>
</html>