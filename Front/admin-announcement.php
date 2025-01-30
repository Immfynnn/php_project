<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);
$successMessage = '';
$error_message_ls = '';  
$errorMessage = '';    
$post_image1 = null;  

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_content1 = $_POST['post_content1'];
    $uploadOk = 1;
    $post_image1 = null; // Default if no image is uploaded

    // Check if an image has been uploaded
    if (isset($_FILES['post_image1']) && $_FILES['post_image1']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["post_image1"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate the uploaded image
        $check = getimagesize($_FILES["post_image1"]["tmp_name"]);
        if ($check === false) {
            $errorMessage = "File is not an image.";
            $uploadOk = 0;
        } elseif ($_FILES["post_image1"]["size"] > 10000000) {
            $errorMessage = "Sorry, your file is too large.";
            $uploadOk = 0;
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errorMessage = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // If image passes validation, move it to the target directory
        if ($uploadOk) {
            if (move_uploaded_file($_FILES["post_image1"]["tmp_name"], $target_file)) {
                $post_image1 = $target_file;
            } else {
                $errorMessage = "Sorry, there was an error uploading your file.";
                $uploadOk = 0;
            }
        }
    }

    // If no errors, insert the announcement and notification
    if ($uploadOk) {
        // Insert the announcement into the announcement table
        $stmt = $conn->prepare("INSERT INTO announcement (admin_id, post_image1, post_content1, post_date1) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $user_id, $post_image1, $post_content1);

        if ($stmt->execute()) {
            $post_aid = $stmt->insert_id; // Get the ID of the inserted announcement

            // Fetch admin username
            $sqlAdminUsername = "SELECT admin_username FROM admins WHERE admin_id = ?";
            $stmtAdmin = $conn->prepare($sqlAdminUsername);
            $stmtAdmin->bind_param("i", $user_id);
            $stmtAdmin->execute();
            $resultAdmin = $stmtAdmin->get_result();
            $adminRow = $resultAdmin->fetch_assoc();
            $admin_username = $adminRow['admin_username'];
            $stmtAdmin->close();

            // Prepare the notification message
            $notification_message = "$admin_username is posting an announcement. Click here to view now.";

            // Get all users (excluding the admin who posted the announcement) to send the notification to
            $sqlUsers = "SELECT uid FROM users WHERE uid != ?";
            $stmtUsers = $conn->prepare($sqlUsers);
            $stmtUsers->bind_param("i", $user_id);
            $stmtUsers->execute();
            $resultUsers = $stmtUsers->get_result();

            // Insert notifications for all users
            while ($userRow = $resultUsers->fetch_assoc()) {
                $uid = $userRow['uid'];

                // Insert the notification into the notifications table
                $notificationStmt = $conn->prepare("INSERT INTO notifications (uid, s_id, post_aid, message) VALUES (?, NULL, ?, ?)");
                $notificationStmt->bind_param("iis", $uid, $post_aid, $notification_message);
                $notificationStmt->execute();
            }

            $stmtUsers->close();
            $successMessage = "The announcement has been uploaded successfully and notifications sent!";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
    }
}


// Move mysqli close here to ensure all queries are done
// Messages query and other queries should execute before this
?>



<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Announcement</title>
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
            <li class="active">
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
                <a href="#">
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
                    <h1>Announcement</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li>
                            <i class='bx bx-chevron-right'>

                            </i></li>
                        <li>
                            <a class="active" href="#">Upload Announcement</a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php if (!empty($successMessage)): ?>
                <div class="alert-sent-success">
                    <h5><i class='bx bxs-check-circle'></i> <?php echo htmlspecialchars($successMessage); ?></h5>
                </div>
            <?php elseif (!empty($errorMessage)): ?>
                <div class="alert-sent-failed">
                    <h5><i class='bx bxs-error-circle'></i> <?php echo htmlspecialchars($errorMessage); ?></h5>
                </div>
                <?php elseif (!empty($error_message_ls)): ?>
              <div class="alert-sent-failed-orrange">
              <h5><i class='bx bxs-error-circle'></i> <?php echo htmlspecialchars($error_message_ls); ?></h5>
             </div>
            <?php endif; ?>
            <div class="container-post">
    <div class="top">
        <h3>Upload Announcement</h3>
    </div>
    <form action="admin-announcement.php" method="post" enctype="multipart/form-data">
        <textarea id="post_content1" name="post_content1" rows="6" cols="100" required placeholder="Write something..."></textarea><br><br>

        <label for="post_image1">Upload Image (Optional):</label><br>
        <input type="file" id="post_image1" name="post_image1" accept="image/*"><br><br>

        <div class="btn-button">
            <input type="submit" value="Upload" class="submit">
        </div>
    </form>
</div>

            
<div class="post-view">
    <div class="head">
        <h3>Announcement View</h3>
    </div>

    <?php
    include '../config.php'; // Database connection

    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $sql = "
        SELECT announcement.*, admins.admin_username, admins.admin_image 
        FROM announcement 
        JOIN admins ON announcement.admin_id = admins.admin_id 
        ORDER BY announcement.post_date1 DESC
    ";
    $result = $conn->query($sql);

    // Check for SQL errors
    if ($conn->error) {
        echo "SQL Error: " . $conn->error;
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='cont-post' data-id='" . $row['post_aid'] . "'>";

            // Display the admin's profile image and name
            echo "<p>";
            if (!empty($row["admin_image"]) && file_exists($row["admin_image"])) {
                echo "<img src='" . htmlspecialchars($row["admin_image"]) . "' alt='Admin Image' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;'>";
            }
            echo "<div class='top'>";
            echo "<strong>From: " . htmlspecialchars($row["admin_username"]) . "</strong>";
            // Corrected onclick attribute
            echo " <i class='bx bxs-trash' onclick='confirmDeletion(" . $row["post_aid"] . ")'></i>";
            echo "</div>";
            echo "</p>";
            echo "<p class='date'>Date: " . htmlspecialchars($row["post_date1"]) . "</p>";

            // Check for null or missing content before passing to htmlspecialchars()
            $postContent = isset($row['post_content1']) ? htmlspecialchars($row['post_content1']) : 'No content available';
            echo "<p class='text-p'>" . nl2br($postContent) . "</p>";

            // Display the post image if available
            if (!empty($row["post_image1"]) && file_exists($row["post_image1"])) {
                echo "<img src='" . htmlspecialchars($row["post_image1"]) . "' alt='Post Image' style='width: 100%; border-radius:5px;' class='text-p'><br>";
            }

            echo "</div>";
        }
    } else {
        echo "<h5 id='alert'>No Announcement Found.</h5>";
    }
    ?>
</div>

<!-- Overlay Confirmation -->
<div id="overlay" style="display: none;">
    <div class="confirmation-box">
        <p>Are you sure you want to delete this announcement?</p>
        <button onclick="deleteAnnouncement()">Yes</button>
        <button onclick="closeOverlay()">No</button>
    </div>
</div>

<script>
    let deleteId = null;

    // Function to open the overlay
    function confirmDeletion(postId) {
        deleteId = postId;
        document.getElementById('overlay').style.display = 'block';
    }

    // Function to close the overlay
    function closeOverlay() {
        deleteId = null;
        document.getElementById('overlay').style.display = 'none';
    }

    // Function to delete the announcement
    function deleteAnnouncement() {
        if (deleteId) {
            // Send an AJAX request to delete the announcement
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_announcement.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    if (xhr.responseText.trim() === "success") {
                        alert('Announcement deleted successfully');
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Error: ' + xhr.responseText); // Debugging message
                    }
                } else {
                    alert('Failed to delete the announcement. Status: ' + xhr.status);
                }
            };
            xhr.send('post_aid=' + deleteId);
            closeOverlay();
        }
    }
</script>

<style>
    #overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .confirmation-box {
        background: #fff;
        padding: 20px;
        width: 30%;
        margin-left: 550px;
        margin-top: 250px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }

    .confirmation-box button {
        margin: 5px;
        padding: 10px 20px;
        cursor: pointer;
    }
</style>

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
<?php
$stmtUnread->close();

$conn->close();
?>