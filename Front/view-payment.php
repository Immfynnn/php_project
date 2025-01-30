<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);

// Ensure `pay_id` is provided in the URL
if (!isset($_GET['pay_id'])) {
    echo "Payment ID not specified.";
    exit();
}

$pay_id = intval($_GET['pay_id']);

// Fetch payment and reservation details, along with the user's name (firstname, lastname, or username as fallback)
$paymentDetails = null;
$reservationDetails = null;
$sqlPaymentDetails = "
    SELECT 
        payment.pay_id, 
        payment.p_screenshot, 
        payment.total_amount,
        payment.ref_num,
        payment.pay_date,
        payment.p_status, 
        payment.p_date,
        reservation.service_type,
        reservation.s_status AS reservation_status,
        reservation.s_id,
        reservation.s_description,
        reservation.set_date,
        reservation.time_slot,
        reservation.s_address,
        reservation.fee,
        reservation.payment_type,
        users.firstname,
        users.lastname,
        users.username
    FROM 
        payment
    JOIN 
        reservation ON payment.s_id = reservation.s_id
    JOIN
        users ON reservation.uid = users.uid  -- Join with the users table to get the name
    WHERE 
        payment.pay_id = ?
";

$stmtPayment = $conn->prepare($sqlPaymentDetails);
$stmtPayment->bind_param('i', $pay_id);

if ($stmtPayment->execute()) {
    $resultPayment = $stmtPayment->get_result();
    $paymentDetails = $resultPayment->fetch_assoc();
}
$stmtPayment->close();

if (!$paymentDetails) {
    echo "Payment details not found.";
    exit();
}
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
                <a href="#" class="logout" id="logout-link">
                <i class='bx bx-log-out'></i>
                 <span class="text">Logout</span>
                </a>
            </li>
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
                <p class="greetings"></p>
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
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a>Reservation</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Payment Details</a></li>
            </ul>
        </div>
    </div>

    <div class="view-details" style="width:95%;">
        <div class="header" style="padding:20px; padding-bottom:30px; display:flex; flex-direction:row; justify-content:space-between;">
            <h3>Payment Details</h3>
            <div>
            <a href="generate-receipt-pdf.php?pay_id=<?php echo htmlspecialchars($pay_id); ?>" 
       style="padding:10px; padding-left:30px; margin-right:5px; padding-right:30px; background:#007BFF; border-radius:5px; color:#fff;">
        Download PDF
    </a>
                <a href="view_reservation_details.php?service_id=<?php echo htmlspecialchars($paymentDetails['s_id']); ?>" 
                   style="padding:10px; padding-left:20px; padding-right:20px; background:#3C91E6; color:#fff; border-radius:5px;">
                    View Reservation
                </a>
                <a href="admin-payment-reservation.php" 
                   style="padding:10px; padding-left:20px; padding-right:20px; margin-left:5px; background:#DB504A; color:#fff; border-radius:5px;">
                   Back
                </a>
            </div>
        </div>

        <div class="detail-row">
            <label><strong>Name:</strong></label>
            <p class="input-like">
                <?php 
                    if (!empty($paymentDetails['firstname']) && !empty($paymentDetails['lastname'])) {
                        echo htmlspecialchars($paymentDetails['firstname'] . ' ' . $paymentDetails['lastname']);
                    } else {
                        echo htmlspecialchars($paymentDetails['username']);
                    }
                ?>
            </p>
        </div>

        <div class="detail-row"><label><strong>Receipt No:</strong></label>
            <p class="input-like"><?php echo htmlspecialchars($paymentDetails['pay_id']); ?></p>
        </div>
        <div class="detail-row"><label><strong>Reservation Type:</strong></label>
            <p class="input-like"><?php echo htmlspecialchars($paymentDetails['service_type']); ?></p>
        </div>
        <div class="detail-row"><label><strong>Date & Time:</strong></label>
            <?php
                $date = new DateTime($paymentDetails['p_date']);
                echo '<p class="input-like">' . $date->format('F j, Y, h:i A') . '</p>';
            ?>
        </div>
        <div class="detail-row" style="display:flex;flex-direction:row; justify-content:space-between; background: rgba(0,0,0,.1); padding:20px;">
         <div style="display:flex;flex-direction:column;" >   
            <?php if (!empty($paymentDetails['p_screenshot'])): ?>
                <label><strong>Gcash Receipt Screenshot:</strong></label>
                <img src="<?php echo htmlspecialchars($paymentDetails['p_screenshot']); ?>" 
                     alt="Gcash Screenshot" class="requirement-image" 
                     style="max-width: 200px; max-height:200px; margin-right: 10px; border-radius:5px; cursor: pointer;" 
                     onclick="zoomImage(this.src)">
            <?php else: ?>
                <p>No screenshot available</p>
            <?php endif; ?>
            </div>

            <div style="display:flex;flex-direction:column; align-items:center;" > 
            <label><strong>Ref No.</strong></label>
            <input type="text" value="<?php echo htmlspecialchars($paymentDetails['ref_num']); ?>" style="padding:20px; font-size:18px; border-radius:5px; border:none; outline:none; margin-top:15px;" readonly>
            </div>

            <div style="display:flex;flex-direction:column; align-items:center;" > 
            <label><strong>Payment Date</strong></label>
            <input type="text" value="<?php echo date('F d, Y', strtotime($paymentDetails['pay_date'])); ?>" style="padding:20px; font-size:18px; border-radius:5px; border:none; outline:none; margin-top:15px;" readonly>

            </div>

        </div>

        <div class="detail-row"><label><strong>Total Amount:</strong></label>
            <p class="input-like"><?php echo htmlspecialchars($paymentDetails['total_amount']); ?></p>
        </div>

        <div class="detail-row"><label><strong>Payment Type:</strong></label>
            <p class="input-like"><?php echo htmlspecialchars($paymentDetails['payment_type']); ?></p>
        </div>

        <div class="detail-row"><label><strong>Reservation Status:</strong></label>
            <p class="input-like"><?php echo htmlspecialchars($paymentDetails['reservation_status']); ?></p>
        </div>

        <!-- Render the Update Reservation Status form only if p_status is NOT Refund -->
        <?php if ($paymentDetails['p_status'] !== 'Refund'): ?>
            <form method="POST" action="payment_update_config.php">
                <select name="s_status" id="s_status" class="input-like" style="width:100%;">
                    <option value="Paid" <?php echo ($paymentDetails['reservation_status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                    <?php if (strcasecmp(trim($paymentDetails['payment_type']), 'Over the Counter') !== 0): ?>
                        <option value="Refund" <?php echo ($paymentDetails['reservation_status'] == 'Refund') ? 'selected' : ''; ?>>Refund</option>
                    <?php endif; ?>
                    <option value="Canceled" <?php echo ($paymentDetails['reservation_status'] == 'Canceled') ? 'selected' : ''; ?>>Canceled</option>
                </select>
                <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($paymentDetails['s_id']); ?>">
                <button type="submit" class="btn-update-stat">Update</button>
            </form>
        <?php endif; ?>
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

<script>
// Function to display the zoomed image
function zoomImage(src) {
    const zoomedContainer = document.createElement('div');
    zoomedContainer.classList.add('zoomed');

    zoomedContainer.innerHTML = `<img src="${src}" alt="Zoomed Image">`;
    
    // Append the zoomed container to the body
    document.body.appendChild(zoomedContainer);
    
    // Trigger a reflow to apply the transition
    requestAnimationFrame(() => {
        zoomedContainer.classList.add('show');
    });

    // Add event listener to close the zoomed image when clicked
    zoomedContainer.addEventListener('click', function() {
        zoomedContainer.classList.remove('show'); // Start fade-out
        setTimeout(() => {
            document.body.removeChild(zoomedContainer); // Remove after fade-out
        }, 300); // Match this with your CSS transition duration
    });
}
</script>

</body>
</html>