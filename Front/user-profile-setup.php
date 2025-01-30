<?php
session_start(); // Start the session

include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['uid'];

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

// Fetch the user's email from the database to ensure it's available in the session
$sql = "SELECT email FROM users WHERE uid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

// Store email in session if it's not already set
if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = $email;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $age = (int) $_POST['age'];
    $contactnum = htmlspecialchars($_POST['contactnum']);
    $address = htmlspecialchars($_POST['address']);
    $Q1 = htmlspecialchars($_POST['Q1']); // Security question
    $A1 = htmlspecialchars($_POST['A1']); // Security answer

    // File upload logic
    if (isset($_FILES['userimg']) && $_FILES['userimg']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['userimg']['tmp_name'];
        $fileName = $_FILES['userimg']['name'];
        $fileSize = $_FILES['userimg']['size'];
        $fileType = $_FILES['userimg']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Directory where uploaded images will be saved
            $uploadFileDir = './uploads/';
            $dest_path = $uploadFileDir . md5(time() . $fileName) . '.' . $fileExtension;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // File successfully uploaded
                $userimg = $dest_path;
            } else {
                // Error uploading the file
                $userimg = null;
            }
        }
    } else {
        // Keep the old image if no new file is uploaded
        $userimg = isset($_SESSION['userimg']) ? $_SESSION['userimg'] : null;
    }

    // Update the user information in the database
    $uid = $_SESSION['uid'];
    $sql = "UPDATE users 
            SET firstname = ?, lastname = ?, age = ?, contactnum = ?, address = ?, userimg = ?, Q1 = ?, A1 = ?, profile_completed = 1 
            WHERE uid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssissssi', $firstname, $lastname, $age, $contactnum, $address, $userimg, $Q1, $A1, $uid);

    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname'] = $lastname;
        $_SESSION['age'] = $age;
        $_SESSION['contactnum'] = $contactnum;
        $_SESSION['address'] = $address;
        $_SESSION['userimg'] = $userimg;
        $_SESSION['Q1'] = $Q1;
        $_SESSION['A1'] = $A1;
        $_SESSION['profile_completed'] = 1; // Mark profile as completed

        // Display overlay with success message
        echo '<!DOCTYPE html>
              <html lang="en">
              <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Profile Setup Success</title>
                <style>
                  .overlay {
                      position: fixed;
                      top: 0;
                      left: 0;
                      width: 100%;
                      height: 100%;
                      background: rgba(0, 0, 0, 0.7);
                      display: flex;
                      justify-content: center;
                      align-items: center;
                      animation: fadeIn 0.5s;
                      z-index: 1000;
                      font-family:sans-serif;
                  }

                  @keyframes fadeIn {
                      from {
                          opacity: 0;
                      }
                      to {
                          opacity: 1;
                      }
                  }

                  .overlay-content {
                      background: white;
                      padding: 20px;
                      border-radius: 10px;
                      text-align: center;
                      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                  }

                  .overlay-content h2 {
                      margin: 0 0 10px;
                  }
                </style>
                <script>
                  function closeOverlay() {
                      window.location.href = "home.php";
                  }

                  document.addEventListener("click", closeOverlay);
                </script>
              </head>
              <body>
                <div class="overlay">
                  <div class="overlay-content">
                    <h2>Profile Setup Successfully</h2>
                    <p>Start Your Reservation Now!</p>
                  </div>
                </div>
              </body>
              </html>';
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Display name logic
$displayName = !empty($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['username'];

// Get the profile completed status
$profileCompleted = $_SESSION['profile_completed'];

// Default profile image if user image is not set
$profileImage = !empty($userimg) ? $userimg : 'css/img/default-profile.png';

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
    <link rel="stylesheet" href="css/css-bgcolorx.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
</head>

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
</style>

<body>

<section id="sidebar">
    <div class="center-a">
        <a href="" class="logs">
            <img src="css/img/LOGO.png" alt="Logo" style="width: 50px; border-radius:50px;" id="logs">
            <span class="text" id="title-txt">Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan</span>
        </a>
    </div>
    <ul class="side-menu top">
            <li>
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
                <a href="messages.php">
                   <i class='bx bxs-message-rounded'></i>
                    <span class="text">Messages</span>
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

<div class="clock" style="width:100%; justify-content:end;">
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

        <a href="#" class="profile" id="profile-link" style="cursor:default;">
            <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Icon">
        </a>
    </nav>
    <main>

        <div class="profile-cont" style="background:#1A6B96; color:#fff;">
            <div class="header">
                <h3>Set up Profile</h3>
            </div>

            <div class="cnt-input-a">
               <center>
                   <img id="profile-preview" src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" style="width: 100px; height: 100px; border-radius: 50%; outline: solid 1px #000;">
               </center>
           </div>

           <form action="user-profile-setup.php" method="POST" enctype="multipart/form-data">

           <div class="cnt-input-a">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" style="outline:rgba(0,0,0,.1) solid 1px; background:#ebebeb; cursor:default;" readonly >
           </div>

           <div class="cnt-input-a">
            <label for="firstname">First Name:</label>
            <input type="text" name="firstname" id="firstname" value="<?php echo htmlspecialchars($firstname ?? ''); ?>" required>
           </div>

           <div class="cnt-input-a">
            <label for="lastname">Last Name:</label>
            <input type="text" name="lastname" id="lastname" value="<?php echo htmlspecialchars($lastname ?? ''); ?>" required>
           </div>
           <div class="cnt-input-a">
            <label for="age">Age:</label>
            <input type="text" name="age" id="age" placeholder="(18+)" value="<?php echo htmlspecialchars($age ?? ''); ?>" required>
           </div>

           <div class="cnt-input-a">
            <label for="contactnum">Contact #:</label>
            <input type="text" name="contactnum" id="contactnum" placeholder="(63+)" value="<?php echo htmlspecialchars($contactnum ?? ''); ?>" required>
           </div>

           <div class="cnt-input-a">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" style="outline:rgba(0,0,0,.1) solid 1px; background:#ebebeb; cursor:default;" readonly>
                </div>

            <div class="cnt-input-a">
                <label for="address">Address:</label>
                <select name="address" id="address">
                    <option value="#">Select Address</option>
                    <option value="Brgy. Agujo">Brgy. Agujo</option>
                    <option value="Brgy. Bakhawan">Brgy. Bakhawan</option>
                    <option value="Brgy. Bateria">Brgy. Bateria</option>
                    <option value="Brgy. Bitoon">Brgy. Bitoon</option>
                    <option value="Brgy. Bagay">Brgy. Bagay</option>
                    <option value="Brgy. Calape">Brgy. Calape</option>
                    <option value="Brgy. Dalingding">Brgy. Dalingding</option>
                    <option value="Brgy. Lanao">Brgy. Lanao</option>
                    <option value="Brgy. Maya">Brgy. Maya</option>
                    <option value="Brgy. Malbago">Brgy. Malbago</option>
                    <option value="Brgy. Malingin">Brgy. Malingin</option>
                    <option value="Brgy. Poblacion">Brgy. Poblacion</option>
                    <option value="Brgy. Paypay">Brgy. Paypay</option>
                    <option value="Brgy. Pajo">Brgy. Pajo</option>
                    <option value="Brgy. Tominjao">Brgy. Tominjao</option>
                    <option value="Brgy. Tapilon">Brgy. Tapilon</option>
                    <option value="Brgy. Talisay">Brgy. Talisay</option>
                    <option value="Brgy. Tinubdan">Brgy. Tinubdan</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="cnt-input-a">
    <label for="security">Security Question:(Required)</label>
    <select name="Q1" id="Q1" onchange="showAnswerInput()" required>
        <option value="">Select Question</option>
        <option value="What was the name of your first pet?">What was the name of your first pet?</option>
        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
        <option value="What was the name of your first school?">What was the name of your first school?</option>
        <option value="What is your favorite childhood memory?">What is your favorite childhood memory?</option>
    </select>

    <!-- This input will be shown only if a question is selected -->
    <input type="text" name="A1" id="security-answer" placeholder="Answer" style="display:none;">
</div>

<script>
    function showAnswerInput() {
        var question = document.getElementById('Q1').value;
        var answerInput = document.getElementById('security-answer');

        if (question !== "") {
            // Show the answer input field if a question is selected
            answerInput.style.display = "block";
        } else {
            // Hide the answer input field if no question is selected
            answerInput.style.display = "none";
        }
    }
</script>
           <div class="cnt-input-a">
                <label for="userimg">Upload Profile Picture:</label>
                <input type="file" id="userimg" name="userimg" accept="image/*" onchange="previewImage(event)">
            </div>

           <button type="submit" class="submit-btn">Submit</button>
           <script>
function previewImage(event) {
    const file = event.target.files[0];
    const imgPreview = document.getElementById('profile-preview');

    if (file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            imgPreview.src = e.target.result; // Set the image source to the file's data URL
        }

        reader.readAsDataURL(file); // Read the file as a data URL
    }
}
</script>

           </form>
        </div>
    </main>
</section>

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

<script src="javascript/script.js"></script>
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
