<?php

session_start();

include '../config.php';

// Redirect to admin login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);

// Fetch admin details
$adminDetails = null;
$sqlAdminDetails = "SELECT admin_image, admin_name, admin_username, admin_contact_no, admin_email,pass_code, admin_gender FROM admins WHERE admin_id = ?";
$stmtAdmin = $conn->prepare($sqlAdminDetails);
$stmtAdmin->bind_param('i', $user_id);
if ($stmtAdmin->execute()) {
    $resultAdmin = $stmtAdmin->get_result();
    $adminDetails = $resultAdmin->fetch_assoc();
}
$stmtAdmin->close();

// Set default profile image if none exists
$profileImage = !empty($adminDetails['admin_image']) ? $adminDetails['admin_image'] : 'css/img/default-profile.png';

// Initialize message variable
$message = '';
$error_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $admin_name = htmlspecialchars($_POST['admin_name']);
    $admin_username = htmlspecialchars($_POST['admin-username']);
    $admin_gender = htmlspecialchars($_POST['admin_gender']);
    $admin_contact_no = htmlspecialchars($_POST['admin_contact_no']);
    $admin_email = htmlspecialchars($_POST['admin_email']);
    $pass_code = htmlspecialchars($_POST['pass_code']);
    
    // Handle image upload
    $newProfileImage = $profileImage; // Default to the current image
    if (isset($_FILES['admin_image']) && $_FILES['admin_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Ensure this directory exists and is writable
        $uploadFile = $uploadDir . basename($_FILES['admin_image']['name']);
        // Validate and move the uploaded file
        if (move_uploaded_file($_FILES['admin_image']['tmp_name'], $uploadFile)) {
            $newProfileImage = $uploadFile; // Update profile image to the new one
        } else {
            // Handle upload error
            $error_message = "Error uploading file"; 
        }
    }

    // Update admin details in the database
    $sqlUpdate = "UPDATE admins SET admin_name = ?, admin_username = ?, admin_contact_no = ?, admin_email = ?,pass_code = ?, admin_gender = ?, admin_image = ? WHERE admin_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param('sssssssi', $admin_name, $admin_username, $admin_contact_no, $admin_email,$pass_code, $admin_gender, $newProfileImage, $user_id);
    
    if ($stmtUpdate->execute()) {
        $_SESSION['message'] = "Profile updated successfully!"; // Store success message in session
        header("Location: admin-profile-update.php"); // Redirect to the same page
        exit();
    } else {
        $error_message = "Error updating profile: " . $stmtUpdate->error; // Handle error
    }
    $stmtUpdate->close();
}

// Check for session messages and clear them
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Update Profile</title>
    <link rel="stylesheet" href="css/temp02.css">
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
            <li class="active">
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
                    <h1>Settings</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Setting</a>
                        </li>
                        <li>
                            <i class='bx bx-chevron-right'>

                            </i></li>
                        <li>
                        <li>
                            <a class="active" href="admin-profile-update.php">Profile</a>
                        </li>
                    </ul>
                </div>
               
            </div>
            <?php if (!empty($message)): ?>
            <div class="alert-sent-success">
                <h5><?php echo htmlspecialchars($message); ?></h5>
            </div>
        <?php elseif (!empty($error_message)): ?>
            <div class="alert-sent-failed">
                <h5><i class='bx bxs-error-circle'></i><?php echo htmlspecialchars($error_message); ?></h5>
            </div>
        <?php endif; ?>

            <center>

            <div class="a-profile-div">
                <div class="header" style="padding:20px;">
                    <h3 style="text-align:center;"> Profile</h3>
                </div>
               <form action="admin-profile-update.php" method="POST" enctype="multipart/form-data">
               
               <div class="cnt-input-a">
                   <center>
                       <img id="profile-preview" src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" style="width: 100px; height: 100px; border-radius: 50%; outline: solid 1px #000;">
                   </center>
               </div>
               
               
               <div class="cnt-input-a">
                <label for="admin_name">Name:</label>
                <input type="text" name="admin_name" id="admin_name" value="<?php echo htmlspecialchars($adminDetails['admin_name']); ?>" required>
               </div>

               <div class="cnt-input-a">
                <label for="admin-username">Username:</label>
                <input type="text" name="admin-username" id="admin-username" value="<?php echo htmlspecialchars($adminDetails['admin_username']); ?>" required>
               </div>

               <div class="cnt-input-a">
                <label for="admin_gender">Gender:</label>
                 <select id="admin_gender" name="admin_gender" required>
                     <option value="Male" >Male</option>
                     <option value="Female">Female</option>
                 </select>
               </div>

               
               <div class="cnt-input-a">
                <label for="admin_contact_no">Contact #:</label>
                <input type="text" name="admin_contact_no" id="admin_contact_no" palaceholder="(63+)" value="<?php echo htmlspecialchars($adminDetails['admin_contact_no']); ?>" required>
               </div>

               <div class="cnt-input-a">
                <label for="admin_email">Email:</label>
                <input type="text" name="admin_email" id="admin_email" value="<?php echo htmlspecialchars($adminDetails['admin_email']); ?>" required>
               </div>
               <div class="cnt-input-a">
                <label for="pass_code">Pass Code:</label>
                <input type="password" name="pass_code" value="<?php echo htmlspecialchars($adminDetails['pass_code']); ?>" id="pass_code">
               </div>
               
               <div class="cnt-input-a">
                    <label for="admin_image">Upload Profile Picture:</label>
                    <input type="file" id="admin_image" name="admin_image" accept="image/*" onchange="previewImage(event)">
                </div>

               <button type="submit" class="submit-btn">Update Profile</button>
                </form>
            </div>

            </center>

           
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
    const date = monthList[month] + " " + day + ", " + year;
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