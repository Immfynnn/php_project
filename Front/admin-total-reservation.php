<?php
session_start();

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
    <title>Dashboard | Parishioner</title>
    <link rel="stylesheet" href="css/temp77.css">
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
                    <i class='bx bx-chevron-right'></i>
                </li>
                <li>
                    <a class="active" href="#">Total Reservation</a>
                </li>
            </ul>
        </div>
    </div>
    <div style="display:flex; justify-content:end;">
    <button id="downloadButton" 
            style="padding:10px; padding-left:20px; border:none; outline:none; padding-right:20px; margin-left:5px; background: blue; cursor:pointer; color:#fff; border-radius:5px;">
        Download
    </button>
    <a href="admin-payment-reservation.php" 
       style="padding:10px; padding-left:20px; padding-right:20px; margin-left:5px; background:#DB504A; color:#fff; border-radius:5px;">
       Back
    </a>
</div>

<div class="overlay-download" id="overlay-download" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000;">
    <div class="dialog-download" style="padding:20px; background:#fff; outline:solid 1px rgba(0,0,0,.5); display:flex;flex-direction:column; align-items:center; width:30%; border-radius:6px; margin: auto; position: relative; top: 0;">
        <p><b>Select reservation you want to download</b></p>
        <form id="downloadForm" method="POST" action="download.php">
            <select name="reservation_type" id="reservation_type" style="padding:10px; border-radius:10px; margin-top:10px; width:100%;">
            <option value="Overall">Overall</option>
                <option value="Baptism">Baptism</option>
                <option value="Annointing of the Sick">Annointing of the Sick</option>
                <option value="Burial">Burial</option>
                <option value="Confirmation">Confirmation</option>
                <option value="Mass Intention">Mass intention</option>
                <option value="Holy Eucharist">Holy Eucharist</option>
                <option value="Wedding">Wedding</option>
                <option value="Blessing">Blessing</option>
            </select>
            <div style="margin-top: 10px;">
                <button type="submit" id="confirmDownload" style="padding:10px; padding-left:30px; padding-right:30px; background:blue; cursor:pointer;  outline:none; border:none; border-radius:5px; color:#fff;">Download</button>
                <button type="button" id="closeOverlay" style="padding:10px; padding-left:30px; padding-right:30px; background:#DB504A; cursor:pointer; outline:none; border:none; border-radius:5px; color:#fff;">Close</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const downloadButton = document.getElementById('downloadButton');
        const overlayDownload = document.getElementById('overlay-download');
        const closeOverlayButton = document.getElementById('closeOverlay');

        // Show overlay when "Download" button is clicked
        downloadButton.addEventListener('click', function () {
            overlayDownload.style.display = 'flex';
        });

        // Hide overlay when "Close" button is clicked
        closeOverlayButton.addEventListener('click', function () {
            overlayDownload.style.display = 'none';
        });
    });
</script>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const downloadButton = document.getElementById('downloadButton');
    const overlayDownload = document.getElementById('overlay-download');
    const closeOverlayButton = document.getElementById('closeOverlay');

    // Show overlay when "Download" button is clicked
    downloadButton.addEventListener('click', function () {
        overlayDownload.style.display = 'flex'; // Show overlay
    });

    // Hide overlay when "Close" button is clicked
    closeOverlayButton.addEventListener('click', function () {
        overlayDownload.style.display = 'none'; // Hide overlay
    });
});



</script>

    <div class="table-data fade-up">
        <div class="order">
            <div class="head">
                <h3>Reservation</h3>
                <input type="text" id="searchInput" name="search" placeholder="Search by Username, Service Name, or Number"  
                       style="display: flex; padding:8px; width:320px; border:none; outline:solid 1px rgba(0,0,0,.4); border-radius:6px;" />
                <i class='bx bx-search' id="searchIcon"></i>
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
                <tbody id="reservationTableBody"> 
                    <?php
                    // Query to fetch reservations and corresponding user information, ordered by reservation date in descending order
                    $sqlReservations = "SELECT reservation.*, users.userimg, users.username, reservation.payment_type, reservation.s_status 
                                        FROM reservation 
                                        JOIN users ON reservation.uid = users.uid
                                        WHERE reservation.s_status IN ('Pending', 'Canceled', 'Processing', 'Approved', 'Ongoing', 'Completed')
                                        ORDER BY reservation.r_date DESC";

                    $stmtReservations = $conn->prepare($sqlReservations);
                    if ($stmtReservations->execute()) {
                        $resultReservations = $stmtReservations->get_result();
                        
                        // Check if there are any reservations to display
                        if ($resultReservations->num_rows > 0) {
                            while ($reservationRow = $resultReservations->fetch_assoc()) {
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
        </div>
    </div>

    <script>
     document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('reservationTableBody');

    // Add an event listener for input changes in the search bar
    searchInput.addEventListener('input', function () {
        const searchQuery = searchInput.value.trim().toLowerCase(); // Get the search query

        // Get all rows in the table
        const rows = tableBody.querySelectorAll('tr');

        // Loop through all rows and filter based on the search query
        rows.forEach(row => {
            const username = row.querySelector('td:nth-child(1) p')?.textContent.toLowerCase() || '';
            const serviceName = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const serviceId = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';

            // Check if any of the columns contain the search query
            if (
                username.includes(searchQuery) ||
                serviceName.includes(searchQuery) ||
                serviceId.includes(searchQuery)
            ) {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
        });
    });
});

    </script>
</main>


<script>
function goToDetails(row) {
    const serviceId = row.getAttribute('data-service-id');
    if (serviceId) {
        window.location.href = 'view_reservation_details.php?service_id=' + serviceId;
    }
}
</script>
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