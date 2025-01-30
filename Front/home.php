<?php
session_start();
require_once "../config.php";

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
header("Pragma: no-cache");

// Check if session exists; if not, redirect to sign-in
if (!isset($_SESSION['uid'])) {
    if (isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];
        $sql = "SELECT * FROM users WHERE remember_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Set session variables
            $_SESSION['uid'] = $user['uid'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['firstname'] = $user['firstname'];
        } else {
            setcookie("remember_me", "", time() - 3600, "/", "", false, true);
            header("Location: signin.php");
            exit();
        }
    } else {
        header("Location: signin.php");
        exit();
    }
}

// Fetch user details
$uid = $_SESSION['uid'];
$sql = "SELECT firstname, profile_completed, user_status FROM users WHERE uid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $_SESSION['firstname'] = htmlspecialchars($row['firstname']);
    $_SESSION['profile_completed'] = $row['profile_completed'];

    // Check user status
    if ($row['user_status'] === 'Offline') {
        header("Location: signin.php");
        exit();
    }
} else {
    header("Location: signin.php");
    exit();
}

$displayName = !empty($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['username'];

// Fetch user profile image and details
$sqlUserDetails = "SELECT userimg FROM users WHERE uid = ?";
$stmt = $conn->prepare($sqlUserDetails);
$stmt->bind_param("i", $uid);
$stmt->execute();
$resultUser = $stmt->get_result();
$userDetails = $resultUser->fetch_assoc();
$profileImage = !empty($userDetails['userimg']) ? $userDetails['userimg'] : 'css/img/default-profile.png';
$stmt->close();

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
    <title>Home</title>
    <link rel="stylesheet" href="css/css-bgcolorx.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
    <style>
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
.text-alert {
    height:45vh;
    display:flex;
    justify-content:center;
    align-items:center;
    color:red;
    background:#f4f4f4;
}
/* Optional: Ensure smooth transition on transform and opacity */
.header-cont, .content-post, .post-content {
    transition: opacity 0.5s ease-out, transform 0.5s ease-out;
    opacity: 0;
    transform: translateY(30px);
}



    </style>
</head>
<body>

<!-- HTML Code -->
<section id="sidebar">
    <div class="center-a">
        <a href="index.php" target="_blank" class="logs">
            <img src="css/img/LOGO.png" alt="Logo" style="width: 50px; border-radius:50px;" id="logs">
            <span class="text" id="title-txt">Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan</span>
        </a>
    </div>
    <ul class="side-menu top">
        <li class="active">
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
        <li>
            <a href="messages.php">
                <i class='bx bxs-message-rounded'></i>
                <span class="text">Messages</span>
                <span id="count" style="left:100px; color:white; text-shadow:3px 3px 5px red;">
                    <?php echo $unreadCount > 0 ? $unreadCount : ''; ?>
                </span>
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

<!-- CSS for Transition -->
<style>
    /* Sidebar List Item Styles */
    #sidebar ul.side-menu li {
        opacity: 0;
        transform: translateX(-20px); /* Start position */
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    /* Animation on active state */
    #sidebar ul.side-menu li.active {
        opacity: 1 !important;
        transform: translateX(0) !important;
    }
</style>

<!-- JavaScript Animation -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const menuItems = document.querySelectorAll("#sidebar li");

        menuItems.forEach((item, index) => {
            if (!item.classList.contains("active")) {
                // Add delay for each item based on its index
                setTimeout(() => {
                    item.style.opacity = "1"; // Fade in
                    item.style.transform = "translateX(0)"; // Slide into position
                }, 100 * index); // Delay each item
            }
        });
    });
</script>


    <section id="content">
        <nav>
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
        <div class="header-cont" style="background:url('css/img/header-background1.png'); padding:50px; border-radius:30px 0 30px 0; background-position:center; background-size:cover; background-repeat:no-repeat; box-shadow:0 8px 5px rgba(0,0,0,.5);">
    <?php
    // Assuming the necessary session and database setup is already done
    $profileCompleted = $_SESSION['profile_completed']; // Get the profile status from session

    // Decide which div to display based on profile completion status
    if ($profileCompleted == 0) {
        // User has not completed the profile, show the setup div
        echo '     
                  <div class="div-setup">
                    <i style="text-align:center; color:#fff;"><h1>Hello!</h1></i>
                    <p style="color:#fff;">Set up your Profile now to get started on your journey with us</p>
                    <div class="form-btn-a">
                        <a href="user-profile-setup.php" style="color:;">Set Up</a> <!-- Link to the profile setup page -->
                    </div>
                </div>
                 </div>
              ';
    } else {
        // User has completed the profile, show the reservation div
        echo '
                <div class="div-setup">
                    <i><h1 style="color:#fff;">Start Your Reservation Now!</h1></i>
                    <div class="form-btn-a">
                        <a href="services.php">Reservation</a>
                    </div>
                </div>
              </div>
              ';
    }
    ?>
    <br>
    <br>
    <hr>

    <div class="content-post">
        <div class="header">
            <h1>Post</h1>
        </div>
        
        <div class="post-content">
        <?php
        // Fetch and display posts
        $sql = "
            SELECT posts.*, admins.admin_name, admins.admin_image 
            FROM posts 
            JOIN admins ON posts.admin_id = admins.admin_id 
            ORDER BY posts.post_date DESC
        ";
        $result = $conn->query($sql);

        // Check for SQL errors
        if ($result === false) {
            echo "SQL Error: " . $conn->error;
        } else if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='post-cont'>";
                echo "";
                // Display the admin's profile image and name
                echo "<p>";
                if (!empty($row["admin_image"]) && file_exists($row["admin_image"])) {
                } else {
                    echo "<br>";
                }
                echo "<div class='pad1'>";
                echo "<div><strong>Posted by:</strong> " . htmlspecialchars($row["admin_name"]) . "</div>";
                echo "</div>";                    
                echo "</p>";
                echo "<p style='font-size:13px;'>Date: " . htmlspecialchars($row["post_date"]) . "</p>";
                echo "<br>";

                // Display the post content
                echo "<div class='pad2' style='display:flex;justify-content:center;align-items:center;flex-direction:column;'>";
                echo "<p>" . htmlspecialchars($row["post_content"]) . "</p>";
                echo "<br>";
                // Check if post_image is not empty and exists
                if (!empty($row["post_image"]) && file_exists($row["post_image"])) {
                    echo "<img src='" . htmlspecialchars($row["post_image"]) . "' alt='Post Image' style='position:relative; height:auto;width:90%;'><br>";
                } else {
                    echo "<br>";
                }
                echo "</div>";
                echo "<br>";
                // Display likes and date
                echo "<hr style='opacity:.2;'>";
                echo "<br><p>" . htmlspecialchars($row["likes"]) . " <a href='like_post.php?post_id=" . $row["post_id"] . "'>Like</a></p>";
                echo "</div>";
            }
        } else {
            echo "<div class='text-alert'>No posts found.</div>";
        }
        ?>
        </div>
    </div>
</div>

        </main>
    </section>

    <script>
document.addEventListener("DOMContentLoaded", function() {
    // Function to apply animation (slide up with fade-in)
    const applyAnimation = (element, delay) => {
        if (element) {
            // Ensure the element starts hidden and slightly below
            element.style.opacity = "0";
            element.style.transform = "translateY(30px)"; // Start slightly below
            element.style.transition = `opacity 0.5s ease-out ${delay}s, transform 0.5s ease-out ${delay}s`; // Slide up and fade in

            // After delay, trigger animation
            setTimeout(() => {
                element.style.opacity = "1"; // Fade in
                element.style.transform = "translateY(0)"; // Slide up to the original position
            }, delay * 1000); // Convert delay to milliseconds
        }
    };

    // Apply animation to .header-cont, .content-post, and .post-content
    const elementsToAnimate = document.querySelectorAll(".header-cont, .content-post, .post-content");

    elementsToAnimate.forEach((element, index) => {
        applyAnimation(element, 0.3 + index * 0.2); // Staggered animation delay
    });
});

</script>

    <script>
       document.addEventListener('DOMContentLoaded', () => {
    // Select all the "remove-post" icons
    const removePostIcons = document.querySelectorAll('.remove-post');

    // Loop through each remove-post icon and add the click event listener
    removePostIcons.forEach(icon => {
        icon.addEventListener('click', function () {
            // Find the parent post container
            const postContainer = this.closest('.post-cont');

            // Fade out and remove the post from the DOM
            postContainer.style.transition = 'opacity 0.5s ease-out';
            postContainer.style.opacity = 0;

            // After the transition ends, remove the post from the DOM
            setTimeout(() => {
                postContainer.remove();
            }, 500);  // Wait for the fade-out transition to complete before removing the element
        });
    });
});

    </script>

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
