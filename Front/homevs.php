<?php
session_start();
require_once "sql/config.php";

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
        echo "User not found";
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

// Fetch unread message count
$sqlUnread = "SELECT COUNT(*) as unread_count FROM messages WHERE recipient_id = ? AND read_status = 0";
$stmtUnread = $conn->prepare($sqlUnread);
$stmtUnread->bind_param("i", $_SESSION['uid']);
$stmtUnread->execute();
$resultUnread = $stmtUnread->get_result();
$rowUnread = $resultUnread->fetch_assoc();
$unreadCount = $rowUnread['unread_count'];

// Fetch notification count
$sqlNotifications = "SELECT COUNT(*) as notification_count FROM services WHERE uid = ? AND s_status IN ('To Pay', 'Processing', 'Approved')";
$stmtNotifications = $conn->prepare($sqlNotifications);
$stmtNotifications->bind_param("i", $_SESSION['uid']);
$stmtNotifications->execute();
$resultNotifications = $stmtNotifications->get_result();
$rowNotifications = $resultNotifications->fetch_assoc();
$notificationCount = $rowNotifications['notification_count'];

// Close statements
$stmtUnread->close();
$stmtNotifications->close();

// Prevent caching
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT"); // Date in the past
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="css/style99.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">

    <script>
    function greetUser() {
        const now = new Date();
        const hours = now.getHours();
        let greeting = "Good Evening"; // Default to Evening

        if (hours >= 5 && hours < 12) {
            greeting = "Good Morning";
        } else if (hours >= 12 && hours < 17) {
            greeting = "Good Afternoon";
        }

        // Update the greeting in the HTML
        document.getElementById('user-greeting').innerText = greeting + ', <?php echo $displayName; ?>';
    }

    window.onload = greetUser;
    </script>

    <script>
    function markMessagesAsRead() {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "mark_messages_read.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                // Successfully marked messages as read
                document.querySelector('.noti-cnt').innerText = ''; // Reset the count
            }
        };
        xhr.send("user_id=<?php echo $_SESSION['uid']; ?>");
    }
    </script>


</head>
<body>
<header class="fadeUp" id="header-confg" style="background:#fff;">
    <a href="home.php" id="logs"><img src="css/img/LOGO.png" alt="Logo" style="width: 100px; border-radius:50px;"></a>
    <input type="checkbox" id="check">
    <label for="check" class="icons">
        <i class='bx bx-menu' id="menu-icon"></i>
        <i class='bx bx-x' id="close-icon"></i>
    </label> 
    <navbar id="navbr-icon">
        <a href="home.php" title="Home"><i class='bx bxs-home' style="text-decoration:underline;"></i></a>
        
        <a href="user_messages_inbox.php" title="Messages"><i class='bx bxs-chat'></i>
        <span class="noti-cnt"><?php echo $unreadCount > 0 ? $unreadCount : ''; ?></span> 
        </a>

        
        <a href="notification.php" title="Notification"><i class='bx bxs-bell'></i>
            <span class="noti-cnt"><?php echo $notificationCount > 0 ? $notificationCount : ''; ?></span> 
        </a>
        
        <a href="anncmnt-display.php" title="Announcement"><i class='bx bxs-megaphone'></i>
         <span class="noti-cnt"></span>
        </a>
        
    </navbar>
    <div class="navbar-ext">
    <a href=""><i class='bx bxs-cog'></i></a>
    <a href="#" id="logout-link"><i class='bx bx-log-out' id="navbr-icon"></i></a>
    </div>
</header>

<section id="home" style="background-color: #fff;">
    <div class="div-container">

    <div class="gtr-user">
    <h1 id="user-greeting">Good Morning, <?php echo $displayName; ?></h1>
    <p>Start your Reservation Now!</p>
    <br>
    <a href="reservation-process.php" id="res-button">Reservation</a>
    
    <?php if ($profileCompleted == 0): ?>
        <br>
    <br>
    <br>
        <p class='text-remove'>Click the Button To Start Setup Your Profile</p>
        <br>
        <a href="setup-profile.php" id="res-button">Set Up Profile</a>
    <?php endif; ?>
</div>

        <div class="cont-post-t"><h1>Post</h1></div>

        <div class="cont-post">

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

                    // Display the admin's profile image and name
                    echo "<p>";
                    if (!empty($row["admin_image"]) && file_exists($row["admin_image"])) {
                    } else {
                        echo "<br>";
                    }
                    echo "<strong>Posted by:</strong> " . htmlspecialchars($row["admin_name"]);
                    echo "</p>";
                    echo "<p style='font-size:13px;'>Date: " . htmlspecialchars($row["post_date"]) . "</p>";
                    echo "<br>";

                    // Display the post content
                    echo "<p>" . htmlspecialchars($row["post_content"]) . "</p>";

                    // Check if post_image is not empty and exists
                    if (!empty($row["post_image"]) && file_exists($row["post_image"])) {
                        echo "<img src='" . htmlspecialchars($row["post_image"]) . "' alt='Post Image' style='position:relative; height:200px;width:500px;'><br>";
                    } else {
                        echo "<br>";
                    }

                    // Display likes and date
                    echo "<br><p>Likes: " . htmlspecialchars($row["likes"]) . " <a href='like_post.php?post_id=" . $row["post_id"] . "'>Like</a></p>";
                    echo "</div>";
                }
            } else {
                echo "No posts found.";
            }

            $conn->close();
            ?>
        </div>
    </div>
</section>

<!-- Confirmation dialog -->
<div class="overlay" id="confirmation-dialog">
    <div class="dialog">
        <p>Are you sure you want to log out?</p>
        <br>
        <button id="confirm-logout">Yes</button>
        <button id="cancel-logout">No</button>
    </div>
</div>
<script>

document.addEventListener('DOMContentLoaded', () => {
    const logoutLink = document.getElementById('logout-link');
    const confirmationDialog = document.getElementById('confirmation-dialog');
    const confirmLogout = document.getElementById('confirm-logout');
    const cancelLogout = document.getElementById('cancel-logout');

    // Show the confirmation dialog when the logout link is clicked
    logoutLink.addEventListener('click', (event) => {
        event.preventDefault(); // Prevent the default link action
        confirmationDialog.classList.add('show'); // Show the dialog with transition
    });

    // Redirect to the logout.php file to perform the logout and update status
    confirmLogout.addEventListener('click', () => {
        window.location.href = 'logout.php'; // Redirect to the logout URL to handle the logout process
    });

    // Close the confirmation dialog if "Cancel" is clicked
    cancelLogout.addEventListener('click', () => {
        confirmationDialog.classList.remove('show'); // Hide the dialog with transition
    });

    // Optionally, close dialog if the overlay area is clicked
    confirmationDialog.addEventListener('click', (event) => {
        if (event.target === confirmationDialog) {
            confirmationDialog.classList.remove('show'); // Hide the dialog with transition
        }
    });
});


</script>

<script>
function greetUser() {
    const now = new Date();
    const hours = now.getHours();
    let greeting = "Good Evening"; // Default to Evening

    if (hours >= 5 && hours < 12) {
        greeting = "Good Morning";
    } else if (hours >= 12 && hours < 17) {
        greeting = "Good Afternoon";
    }

    // Update the greeting in the HTML
    document.getElementById('user-greeting').innerText = greeting + ', <?php echo $displayName; ?>';
}

window.onload = greetUser;
</script>


</body>
</html>
