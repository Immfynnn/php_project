<?php
session_start();
require_once "../config.php";


// Fetch the latest user details using the `uid` stored in the session
if (isset($_SESSION['uid'])) {
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
                // Redirect to signin.php if the user status is offline
                header("Location: signin.php");
                exit();
            }
        } else {
            echo "User not found";
            exit();
        }
    } else {
        echo "Database error: " . mysqli_error($conn);
        exit();
    }
} else {
    echo "User ID not found in session";
    exit();
}

// Display name logic
$displayName = !empty($_SESSION['firstname']) ? $_SESSION['firstname'] : (isset($_SESSION['username']) ? $_SESSION['username'] : '');

// Get the profile completed status
$profileCompleted = $_SESSION['profile_completed'] ?? false;

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
    <link rel="stylesheet" href="css/update-pymnt.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
    <style>
       .cont-gateway1 {
    padding: 20px;
    background-color: #f5f5f5;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    outline: solid 1px rgba(0, 0, 0, .2);
    width: 80%;
    height: 620px;
    margin: auto;

    /* Initial state for fade-in */
    opacity: 0; /* Invisible */
    transition: opacity .5s ease-in; /* Smooth fade-in animation */
}

.cont-gateway1.fade-in {
    opacity: 1; /* Fully visible */
}

.div-row-detail {
    width: 100%;
    display:flex;
    flex-direction:row;
}

.cont-gateway1 .header h3 {
    color: #333;
    font-size: 1.5em;
    margin-bottom: 20px;
}

.cont-gateway1 .admin-details-row {
    margin-bottom: 15px;
}

.cont-gateway1 .admin-details-row p {
    font-size: 1em;
    color: #555;
}

.cont-gateway1 .admin-details-row input[type="text"] {
    width: 100%;
    padding: 10px;
    font-size: 1em;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    color: #333;
    background-color: #fafafa;
}

.cont-gateway1 .admin-details-row input[type="file"] {
    display: block;
    margin-top: 10px;
}

.cont-gateway1 button {
    padding: 10px 20px;
    font-size: 1em;
    color: #fff;
    background-color: #4CAF50;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.cont-gateway1 button:hover {
    background-color: #45a049;
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
                <a href="admin-announcement.php">
                <i class='bx bxs-megaphone' ></i>
                 <span class="text">Announcement</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="admin-settings.php">
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
                        <a href="my_reservation-details.php?s_id=<?php echo urlencode($notification['s_id']); ?>" style="text-decoration:none; color:inherit;">
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
        
        <div class="cont-gateway1" style="padding: 20px; background-color: #f5f5f5; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); margin: auto;">
    <div class="header">
    <h3>Gcash </h3>   
    </div>
    <div class="div-row-detail">

    <div class="div">
    <div class="admin-details-row">
        <p><i class='bx bx-info-circle'></i>Scan or Send money</p>
        <img src="css/img/qrcode1.jpg" alt="QR Code Gcash" style="width:100%; height:525px; border-radius:10px; cursor:pointer;"  onclick="zoomImage(this.src)">
    </div>
    </div>
    <div class="div" style="padding:20px;">

    <div class="admin-details-row">
        <p style="font-size:18px;">Name: <strong>Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan</strong></p>
        <p style="font-size:20px; margin-top:10px;">Gcash #: <strong>09123456789</strong></p>
    </div>


    <?php
   // Check if the s_id is passed in the URL
$s_id = $_GET['s_id'] ?? null; // Use $_GET to retrieve s_id from the URL

// Ensure s_id is provided
if (!$s_id) {
    echo "Reservation ID is missing.";
    exit();
}

// Fetch reservation details from the database
$stmt = $conn->prepare("SELECT * FROM reservation WHERE s_id = ?");
$stmt->bind_param("i", $s_id);
$stmt->execute();
$result = $stmt->get_result();
    
// Check if reservation exists
if ($result->num_rows > 0) {
    $reservation = $result->fetch_assoc(); // Fetch reservation data
} else {
    echo "Reservation not found.";
    exit();
}
    ?>
    
    <form action="payment-config1.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="s_id" value="<?php echo htmlspecialchars($s_id); ?>"> <!-- Pass s_id here -->
    <div class="input-b">
        <input type="hidden" name="service_type" value="<?php echo htmlspecialchars($reservation['service_type']); ?>" required>
    </div>
    <!-- Hidden field for total_amount -->
    <input type="hidden" name="total_amount" value="<?php echo htmlspecialchars($reservation['amount']); ?>">

    <div class="admin-details-row">
        <p>Services Name: <strong><?php echo htmlspecialchars($reservation['service_type']); ?></strong></p>
        <input type="text" name="total_amount_display" value="PHP <?php echo number_format($reservation['amount'], 2); ?>" style="padding:15px; outline:solid 1px rgba(0,0,0,.2); background:rgba(0,0,0,.1); cursor:default; width:100%; border-radius:5px;" readonly>
    </div>

 
    <div class="admin-details-row">
        <p><i class='bx bx-info-circle'></i>Please Screenshot the receipt of GCash and upload here (Required):</p>
        <img src="" id="image-preview" alt="Image Preview" style="width: 100px; margin-top:10px; margin-bottom:10px; height: 100px; border-radius: 5px; border: none; cursor:pointer; outline: none; display: none;" onclick="zoomImage(this.src)">
        <input type="number" name="ref_num" onchange="previewImage(event)" style="padding: 15px; outline: solid 1px rgba(0,0,0,.2);  width: 49%; margin-right:5px; border-radius: 5px;" placeholder="Ref No."><input type="date" name="pay_date" onchange="previewImage(event)" style="padding: 14px; outline: solid 1px rgba(0,0,0,.2);  width: 50%; border-radius: 5px;" placeholder="Payment Date">
        <input type="file" name="p_screenshot" id="file-input" onchange="previewImage(event)" style="padding: 15px; outline: solid 1px rgba(0,0,0,.2); margin-top:-10px; width: 100%; border-radius: 5px;">
    </div>
    <div class="admin-details-row">
        <button type="submit">Submit</button>
    </div>
</form>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const gatewayDiv = document.querySelector('.cont-gateway1');
        if (gatewayDiv) {
            // Add fade-in class to start the animation
            setTimeout(() => {
                gatewayDiv.classList.add('fade-in');
            }, 100); // Optional small delay for smooth transition
        } else {
            console.error("cont-gateway1 div not found.");
        }
    });
</script>



<script>
function previewImage(event) {
    const file = event.target.files[0];
    const imgPreview = document.getElementById('image-preview');

    if (file && file.name) { // Check if a file is selected and it has a name
        const reader = new FileReader();

        reader.onload = function(e) {
            imgPreview.src = e.target.result; // Set the image source to the file's data URL
            imgPreview.style.display = 'block'; // Display the preview image
        }

        reader.readAsDataURL(file); // Read the file as a data URL
    } else {
        imgPreview.style.display = 'none'; // Hide the preview if no valid file is selected
        imgPreview.src = ""; // Clear any existing preview image
    }
}
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


<?php
// Close the connection at the very end of the script
$conn->close();
?>
