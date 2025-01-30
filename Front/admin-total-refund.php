<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

// Fetch admin details
$adminDetails = null;
$admin_id = $_SESSION['admin_id'];
$sqlAdminDetails = "SELECT admin_image, admin_name, admin_username, admin_contact_no, admin_email, admin_gender FROM admins WHERE admin_id = ?";
$stmtAdmin = $conn->prepare($sqlAdminDetails);
$stmtAdmin->bind_param('i', $admin_id);
if ($stmtAdmin->execute()) {
    $resultAdmin = $stmtAdmin->get_result();
    $adminDetails = $resultAdmin->fetch_assoc();
}
$stmtAdmin->close();

// Set default profile image if none exists
$profileImage = !empty($adminDetails['admin_image']) ? $adminDetails['admin_image'] : 'css/img/default-profile.png';


// Fetch notifications
$query = $conn->prepare("SELECT n_id, message_noti, is_read1, created_at, s_id FROM notification_admin WHERE admin_id = ? ORDER BY created_at DESC");
$query->bind_param("i", $admin_id);
$query->execute();
$result = $query->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Notification</title>
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
        <li class="active">
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
            </a>
        </li>
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
                    <i class='bx bx-chevron-right'></i>
                </li>
                <li>
                    <a href="#">Home</a>
                </li>
                <li>
                    <i class='bx bx-chevron-right'></i>
                </li>
                <li>
                    <a class="active" href="#">Total Refund</a>
                </li>
            </ul>
        </div>
    </div>
    <?php
// Query to sum the total_amount where p_status is 'Refund'
$query = "SELECT SUM(CAST(total_amount AS DECIMAL(10,2))) AS total_payment 
FROM payment 
WHERE p_status = 'Refund'";

// Execute the query
$result = mysqli_query($conn, $query);

// Fetch the result
$row = mysqli_fetch_assoc($result);

// Check if there is a result
$total_payment = $row['total_payment'] ? $row['total_payment'] : 0;
?>


    <div style="width:100%; text-align:right; padding-top:10px;">
        <a href="admin-total-payment.php" style="padding:10px; padding-left:20px; padding-right:20px; border-radius:5px; background:#3C91E6; color:#fff; margin-right:5px;">Paid</a>
        <a href="admin_dashboard.php"  style="padding:10px; padding-left:20px; padding-right:20px; border-radius:5px; background:#DB504A; color:#fff; margin-right:10px;">Back</a>
    </div>

    <div class="table-data fade-up" style="outline: rgba(0, 0, 0, 0.1) solid 1px; border-radius:20px;">
        <div class="order">
            <div class="header" style="display:flex; justify-content:space-between; padding:20px;">
                <h3>Amount: <b style="color:#DB504A;">₱<?php echo number_format($total_payment, 2); ?></b></h3>
                <div class="divleft">
                    <input type="text" id="search-input" placeholder="Search by username" style="padding:10px; width:400px; border-radius:5px; border:none; outline:solid 1px rgba(0,0,0,.2);;">
                    <i class='bx bx-search' id="searchIcon"></i>
                </div>
            </div>
            <br>

            <table>
                <thead>
                    <tr>
                        <th>Profile Pic</th> <!-- userimg -->
                        <th>Name</th> <!-- firstname and lastname -->
                        <th>Receipt #.</th> <!-- pay_id -->
                        <th>Payment Type</th> <!-- payment_type -->
                        <th>Amount</th> <!-- Total_amount -->
                        <th>Payment Status</th> <!-- p_status -->
                    </tr>
                </thead>
                <tbody id="user-table-body">
                    <?php
                    // Include the PHP code to fetch and display refund records
                    include('fetch_refund_records.php'); // Assuming the PHP code is in this file
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>


<script>
    document.getElementById("search-input").addEventListener("input", function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#user-table-body tr");

    rows.forEach(function(row) {
        let name = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
        if (name.indexOf(filter) > -1) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});

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

<!-- Clock Script -->
<script>
window.addEventListener("load", () => {
    clock();
    function clock() {
        const today = new Date();

        let hours = today.getHours();
        const minutes = today.getMinutes();
        const seconds = today.getSeconds();

        // Make it 12-hour format
        const hourTime = hours % 12 || 12;
        const ampm = hours < 12 ? "AM" : "PM";

        const time = `${hourTime < 10 ? '0' : ''}${hourTime}:${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds} ${ampm}`;

        document.getElementById("date-time").innerHTML = time;
        setTimeout(clock, 1000);
    }
});
</script>

<script src="javascript/admin-profile.js"></script>
<script src="javascript/script.js"></script>
</body>
</html>
