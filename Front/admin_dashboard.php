<?php
session_start(); // Start the session

include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$adminId = $_SESSION['admin_id'];

// Query to check admin's active status
$adminActiveStatus = '';
if ($adminId) {
    $stmt = $conn->prepare("SELECT admin_active_status FROM admins WHERE admin_id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $stmt->bind_result($adminActiveStatus);
    $stmt->fetch();
    $stmt->close();
    
    // Check if admin is Online
    if ($adminActiveStatus !== 'Online') {
        header("Location: admin.php");
        exit();
    }
}

// Query to get admin's name
$adminName = '';
if ($adminId) {
    $stmt = $conn->prepare("SELECT admin_name FROM admins WHERE admin_id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $stmt->bind_result($adminName);
    $stmt->fetch();
    $stmt->close();
}

// Query to fetch the total number of users
$totalUsers = 0; // Default value
$sqlTotalUsers = "SELECT COUNT(*) AS total_users FROM users";
$stmtTotalUsers = $conn->prepare($sqlTotalUsers);
if ($stmtTotalUsers->execute()) {
    $resultTotalUsers = $stmtTotalUsers->get_result();
    $usersRow = $resultTotalUsers->fetch_assoc();
    $totalUsers = $usersRow['total_users'];
}
$stmtTotalUsers->close();

// Query to count total online users
$totalOnlineUsers = 0; // Default value
$sqlTotalOnlineUsers = "SELECT COUNT(*) AS total_online FROM users WHERE user_status = 'Online'";
$stmtOnlineUsers = $conn->prepare($sqlTotalOnlineUsers);
if ($stmtOnlineUsers->execute()) {
    $resultOnlineUsers = $stmtOnlineUsers->get_result();
    $onlineUsersRow = $resultOnlineUsers->fetch_assoc();
    $totalOnlineUsers = $onlineUsersRow['total_online'];
}
$stmtOnlineUsers->close();

// Example: Query to fetch the number of unread messages
$unreadCount = 0; // Default value
$sqlUnreadMessages = "SELECT COUNT(*) AS unread_count FROM messages1 WHERE recipient_aid = ? AND read_status1 = 0";
$stmtUnread = $conn->prepare($sqlUnreadMessages);
$stmtUnread->bind_param('i', $adminId);
if ($stmtUnread->execute()) {
    $resultUnread = $stmtUnread->get_result();
    $unreadRow = $resultUnread->fetch_assoc();
    $unreadCount = $unreadRow['unread_count'];
}
$stmtUnread->close();

// Query to count total Pending services
$totalPendingServices = 0; // Default value
$sqlTotalPendingServices = "SELECT COUNT(*) AS total_pending FROM reservation WHERE s_status = 'Pending'";
$stmtPendingServices = $conn->prepare($sqlTotalPendingServices);
if ($stmtPendingServices->execute()) {
    $resultPendingServices = $stmtPendingServices->get_result();
    $pendingServicesRow = $resultPendingServices->fetch_assoc();
    $totalPendingServices = $pendingServicesRow['total_pending'];
}
$stmtPendingServices->close();

// Query to count total Completed services
$totalCompletedServices = 0; // Default value
$sqlTotalCompletedServices = "SELECT COUNT(*) AS total_completed FROM reservation WHERE s_status = 'Completed'";
$stmtCompletedServices = $conn->prepare($sqlTotalCompletedServices);
if ($stmtCompletedServices->execute()) {
    $resultCompletedServices = $stmtCompletedServices->get_result();
    $CompletedServicesRow = $resultCompletedServices->fetch_assoc();
    $totalCompletedServices = $CompletedServicesRow['total_completed'];
}
$stmtCompletedServices->close();

// Query to count total services
$totalServices = 0; // Default value
$sqlTotalServices = "SELECT COUNT(*) AS total_services FROM reservation";
$stmtTotalServices = $conn->prepare($sqlTotalServices);
if ($stmtTotalServices->execute()) {
    $resultTotalServices = $stmtTotalServices->get_result();
    $servicesRow = $resultTotalServices->fetch_assoc();
    $totalServices = $servicesRow['total_services'];
}
$stmtTotalServices->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
            <li class="active">
                <a href="#">
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
            <li>
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
                <h4 class="greetings"></h4>
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
$stmtAdmin->bind_param('i', $adminId); // Use the correct variable for admin ID
if ($stmtAdmin->execute()) {
    $resultAdmin = $stmtAdmin->get_result();
    $adminDetails = $resultAdmin->fetch_assoc();
}
$stmtAdmin->close();

// Set default profile image if none exists
$profileImage = 'css/img/default-profile.png'; // Default image
if ($adminDetails) {
    // Only access fields if $adminDetails is not null
    $profileImage = !empty($adminDetails['admin_image']) ? $adminDetails['admin_image'] : $profileImage;
    $adminName = htmlspecialchars($adminDetails['admin_name']);
    $adminUsername = htmlspecialchars($adminDetails['admin_username']);
    $adminContactNo = htmlspecialchars($adminDetails['admin_contact_no']);
    $adminEmail = htmlspecialchars($adminDetails['admin_email']);
    $adminGender = htmlspecialchars($adminDetails['admin_gender']);
} else {
    // Optionally handle the case where no admin details are found
    $adminName = $adminUsername = $adminContactNo = $adminEmail = $adminGender = 'N/A'; // or any default message
}
?>
<a href="#" class="profile" id="profile-link">
    <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" style="outline:solid 1px #000;">
</a>


        </nav>


        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li>
                            <i class='bx bx-chevron-right'>

                            </i></li>
                        <li>
                            <a class="active" href="#">Home</a>
                        </li>
                    </ul>
                </div>

            </div>

            <ul class="box-info">
            <?php
// Assume you have a MySQL connection already established
// Example: $conn is your MySQL connection object

// Query to sum the total_amount where p_status is 'Paid'
$query = "SELECT SUM(CAST(total_amount AS DECIMAL(10,2))) AS total_payment 
          FROM payment 
          WHERE p_status = 'Paid'";

// Execute the query
$result = mysqli_query($conn, $query);

// Fetch the result
$row = mysqli_fetch_assoc($result);

// Check if there is a result
$total_payment = $row['total_payment'] ? $row['total_payment'] : 0;
?>

<!-- HTML -->
<li style="cursor:pointer;" onclick="window.location.href='admin-total-payment.php'">
    <i class="bx bxs-dollar-circle"></i>
    <span class="text">
        <h3>₱<?php echo number_format($total_payment, 2); ?></h3> <!-- Display total payment -->
        <p>Total Payment</p>
    </span>
</li>
                <li style="display:none;">
                <i class='bx bxs-church'></i>
                <span class="text">
                    <h3>7</h3>
                    <p>Services</p>
                </span>
                </li>

                <li style="cursor:pointer;" onclick="window.location.href='admin-parishioner.php'">
                   <i class='bx bxs-group'></i>
                   <span class="text">
                       <h3><?php echo $totalUsers; ?></h3> <!-- Total Users Count -->
                       <p>Parishioner</p>
                   </span>
               </li>  
               <li style="cursor:pointer;" onclick="window.location.href='admin-parishioner-online.php'">
                   <i class='bx bxs-user'></i>
                   <span class="text">
                       <h3><?php echo $totalOnlineUsers; ?></h3> <!-- Total Online Users Count -->
                       <p>Total Online</p>
                   </span>
               </li>

                
                
               
               <li style="cursor:pointer;" onclick="window.location.href='admin-pending-reservation.php'">
               <i class='bx bxs-time'></i>
                   <span class="text">
                       <h3><?php echo$totalPendingServices; ?></h3> <!-- Display total Pending services -->
                       <p>Pending Reservation</p>
                   </span>
               </li>
                 
               
               
               <li style="cursor:pointer;" onclick="window.location.href='admin-success-reservation.php'">
                <i class='bx bx-calendar-check'></i>
                <span class="text">
                <h3><?php echo$totalCompletedServices; ?></h3>
                    <p>Success Reservation</p>
                </span>
                </li>

                <li style="cursor:pointer;" onclick="window.location.href='admin-ongoing-reservation.php'">
    <i class='bx bxs-timer'></i>
    <span class="text">
        <h3>
            <?php
            // SQL query to count the number of ongoing reservations
            $sqlOngoingCount = "SELECT COUNT(*) AS ongoingCount FROM reservation WHERE s_status = 'Ongoing'";

            // Execute the query
            $resultOngoingCount = mysqli_query($conn, $sqlOngoingCount);

            // Fetch the count
            if ($resultOngoingCount) {
                $row = mysqli_fetch_assoc($resultOngoingCount);
                $ongoingCount = $row['ongoingCount']; // Get the count of ongoing reservations
            } else {
                $ongoingCount = 0; // Default to 0 if there's an error
            }

            // Display the total count
            echo htmlspecialchars($ongoingCount);
            ?>
        </h3>
        <p>Ongoing Reservation</p>
    </span>
</li>

                <li  style="cursor:pointer;" onclick="window.location.href='admin-total-reservation.php'">
                   <i class='bx bxs-calendar-check'></i>
                   <span class="text">
                       <h3><?php echo $totalServices; ?></h3> <!-- Display total services count -->
                       <p>Total Reservation</p>
                   </span>
               </li>
               <li style="cursor:pointer;" onclick="window.location.href='admin-canceled-reseravation.php'">
    <i class='bx bxs-calendar-exclamation'></i>
    <span class="text">
        <?php
        // SQL query to count the number of canceled reservations
        $sqlCountCanceled = "SELECT COUNT(*) AS canceledCount FROM reservation WHERE s_status = 'Canceled'";

        $resultCountCanceled = $conn->query($sqlCountCanceled);

        if ($resultCountCanceled) {
            $row = $resultCountCanceled->fetch_assoc();
            $canceledCount = $row['canceledCount']; // Get the count of canceled reservations
        } else {
            $canceledCount = 0; // Default to 0 if there's an error with the query
        }
        ?>
        <h3><?php echo $canceledCount; ?></h3> <!-- Display total Canceled reservations -->
        <p>Reservation Canceled</p>
    </span>
</li>


            </ul>
            <div class="table-data fade-up">
    <div class="order">
        <div class="head">
            <h3>Reservation</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>No.</th>
                    <th>Name of Reservation</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody> 
                <?php
                // Query to fetch reservations and corresponding user information, ordered by reservation date in descending order
                $sqlReservations = "SELECT reservation.*, users.userimg, users.username, reservation.payment_type, reservation.s_status 
                                    FROM reservation 
                                    JOIN users ON reservation.uid = users.uid
                                    WHERE reservation.s_status IN ('Pending', 'Canceled','Canceling', 'Processing', 'Approved', 'Ongoing', 'Completed')
                                    ORDER BY reservation.r_date DESC"; // Add ORDER BY to get the latest reservations at the top

                $stmtReservations = $conn->prepare($sqlReservations);
                if ($stmtReservations->execute()) {
                    $resultReservations = $stmtReservations->get_result();
                    $rowCount = $resultReservations->num_rows;  // Count the total number of rows
                    
                    $displayedRows = 0; // Counter to keep track of displayed rows

                    // Check if there are any reservations to display
                    if ($rowCount > 0) {
                        while ($reservationRow = $resultReservations->fetch_assoc()) {
                            if ($displayedRows < 6) { // Only display the first 6 rows
                                // Set user image inside the loop
                                $userImg = empty($reservationRow['userimg']) ? 'css/img/default-profile.png' : $reservationRow['userimg'];
                ?>
                                <tr data-service-id="<?php echo htmlspecialchars($reservationRow['s_id']); ?>" onclick="openDetailsInNewTab(this)" style="cursor: pointer;">
                                    <td>
                                        <img src="<?php echo htmlspecialchars($userImg); ?>" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
                                        <p><?php echo htmlspecialchars($reservationRow['username']); ?></p>
                                    </td>
                                    <td><?php echo htmlspecialchars($reservationRow['s_id']); ?></td>
                                    <td><?php echo htmlspecialchars($reservationRow['service_type']); ?></td>
                                    <td><?php echo date('F j, Y, g:i A', strtotime($reservationRow['r_date'])); ?></td>
                                    <td><span class="status <?php echo strtolower($reservationRow['s_status']); ?>"><?php echo htmlspecialchars($reservationRow['s_status']); ?></span></td>
                                </tr>
                <?php
                                $displayedRows++; // Increment the counter
                            }
                        }
                    } else {
                        // No reservations found
                        echo "<tr><td colspan='5'>No data</td></tr>";
                    }
                }
                $stmtReservations->close();
                ?>
            </tbody>
        </table>
        
        <?php if ($rowCount > 6) { ?>
            <!-- Display the "View All Reservations" link when there are more than 6 reservations -->
            <div style="text-align: center; padding: 10px;">
                <a href="admin-total-reservation.php" style="color: #007BFF; font-size: 16px;">View All Reservations</a>
            </div>
        <?php } ?>
    </div>
</div>

<script>
function openDetailsInNewTab(row) {
    const serviceId = row.getAttribute('data-service-id');
    if (serviceId) {
        window.open('view_reservation_details.php?service_id=' + serviceId, '_blank');
    }
}
</script>

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
                <p><?php echo $adminName; ?></p>
                <p><?php echo $adminUsername; ?></p>
                <p><?php echo $adminContactNo; ?></p>
                <p><?php echo $adminEmail; ?></p>
                <p><?php echo $adminGender; ?></p>
            </div>
        </div>
        <a href="admin-profile-update.php">Edit Profile</a>
        <button id="close-profile">Close</button>
    </div>
</div>



<script src="javascript/admin-profile.js"></script>


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

    const adminName = "<?php echo htmlspecialchars($adminName); ?>"; // Get admin name from PHP

    window.addEventListener("load", () => {
        clock();
        function clock() {
            const today = new Date();

            // Get time components
            const hours = today.getHours();
            const minutes = today.getMinutes();
            const seconds = today.getSeconds();

            // Add '0' to hour, minute & second when they are less than 10
            const hour = hours < 10 ? "0" + hours : hours;
            const minute = minutes < 10 ? "0" + minutes : minutes;
            const second = seconds < 10 ? "0" + seconds : seconds;

            // Make clock a 12-hour time format
            const hourTime = hours > 12 ? hours - 12 : hours;
            const ampm = hours < 12 ? "AM" : "PM";

            // Get the greeting message based on the time of day
            let greeting = "";
            if (hours < 12) {
                greeting = `Good Morning, ${adminName}!`;
            } else if (hours < 18) {
                greeting = `Good Afternoon, ${adminName}!`;
            } else {
                greeting = `Good Evening, ${adminName}!`;
            }

            // Get current date and time
            const time = hourTime + ":" + minute + ":" + second + " " + ampm;

            // Print current time and greeting to the DOM
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

            // Update the time every second
            setTimeout(clock, 1000);
        }
    });
});

</script>


</body>
</html>