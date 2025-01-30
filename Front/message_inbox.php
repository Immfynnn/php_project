<?php
session_start();
require_once "../config.php";

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
?>

<!-- The rest of the HTML (unchanged) -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="css/reservation-css2.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
</head>
<body>


    <section id="sidebar">
        <div class="center-a">
        <a href="home" class="logs">
        <img src="css/img/LOGO.png" alt="Logo" style="width: 50px; border-radius:50px;" id="logs">
        <span class="text" id="title-txt">Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan</span>
        </a>
        </div>
        <ul class="side-menu top">
            <li>
                <a href="home.php">
                <i class='bx bxs-home-alt-2' style="color:#342E37;"></i>
                 <span class="text">Home</span>
                </a>
            </li>
            <li>
                <a href="my_reservation.php">
                <i class='bx bxs-calendar-event' style="color:#342E37;"></i>
                 <span class="text">Reservation</span>
                </a>
            </li>

            
            <li class="active">
                <a href="message_inbox.php">
                   <i class='bx bxs-message-rounded'style="color:#342E37;" ></i>
                    <span class="text">Messages</span>
                </a>
            </li>
        
            <li>
                <a href="announcement.php">
                <i class='bx bxs-megaphone' style="color:#342E37;"></i>
                 <span class="text">Announcement</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="settings.php">
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

            <div class="clock">
                <h4 id="date-time" style="color:green;"></h4>
            </div>
            
            <a href="admin-notification.php"  class="notification">
               <i class='bx bxs-bell'></i>
                <span class="num">0</span>
            </a>
            <a href="#" class="profile" id="profile-link">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Icon">
            </a>
        </nav>
        <main>


           
    
        </main>
    </section>


<!--User Profile Details-->

<?php
// Fetch User details
$UserDetails = null;
$sqlUserDetails = "SELECT uid, username, userimg, firstname, lastname, gender, age, contactnum, address, email FROM users WHERE uid = ?";
$stmtUser = $conn->prepare($sqlUserDetails);
$stmtUser->bind_param('i', $uid); // Use $uid instead of $user_id
if ($stmtUser->execute()) {
    $resultUser = $stmtUser->get_result();
    $UserDetails = $resultUser->fetch_assoc();
}
$stmtUser->close();

// Set default profile image if none exists
$profileImage = !empty($UserDetails['userimg']) ? $UserDetails['userimg'] : 'css/img/default-profile.png';
?>

<div class="overlay1" id="MyProfile">
    <br>
    <div class="dialog1">
        <h1>My Profile</h1>
        <br>
        <!-- Display Profile Image -->
        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" style="width: 100px; height: 100px; border-radius: 50%;"> 

        <div class="div-pg">
            <div class="div-ls">
                <p>ID:</p> <!-- Display User ID -->
                <p>Username:</p>
                <p>Gender:</p>
                <p>Age:</p>
                <p>Email:</p>
                <p>Contact #:</p>
                <p>Address:</p>
            </div>
            <div class="div-ls1">
                <!-- Display User Details -->
                <p><?php echo htmlspecialchars($UserDetails['uid']); ?></p> <!-- Display User ID -->
                <p><?php echo htmlspecialchars($UserDetails['firstname']); ?></p>
                <p><?php echo htmlspecialchars($UserDetails['lastname']); ?></p>
                <p><?php echo htmlspecialchars($UserDetails['username']); ?></p>
                <p><?php echo htmlspecialchars($UserDetails['gender']); ?></p>
                <p><?php echo htmlspecialchars($UserDetails['age']); ?></p>
                <p><?php echo htmlspecialchars($UserDetails['contactnum']); ?></p>
                <p><?php echo htmlspecialchars($UserDetails['email']); ?></p>
                <p><?php echo htmlspecialchars($UserDetails['address']); ?></p>
            </div>
        </div>

        <a href="admin-profile-update.php">Edit Profile</a>
        <button id="close-profile">Close</button>
    </div>
</div>
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
