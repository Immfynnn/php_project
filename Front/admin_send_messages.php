<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);
$message = '';
$error_message = '';
$user_status = ''; // Initialize the status variable

$adminId = $_SESSION['admin_id'];
// Query to check admin's active status
$adminActiveStatus = '';
if ($adminId) {
    $stmt = $conn->prepare("SELECT admin_active_status FROM admins WHERE admin_id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $stmt->bind_result($adminActiveStatus);
    $stmt->fetch();
    $stmt->close();
    
    // Check if admin is Online
    if ($adminActiveStatus !== 'Online') {
        header("Location: admin.php");
        exit();
    }
}

$recipient_username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';

// Fetch recipient's status
if ($recipient_username) {
    $statusSql = "SELECT user_status, uid FROM users WHERE username = ?";
    $statusStmt = $conn->prepare($statusSql);
    $statusStmt->bind_param('s', $recipient_username);
    
    if ($statusStmt->execute()) {
        $statusResult = $statusStmt->get_result();
        if ($statusRow = $statusResult->fetch_assoc()) {
            $user_status = htmlspecialchars($statusRow['user_status']);
            $recipient_id = intval($statusRow['uid']); // Get the recipient's ID
        } else {
            $error_message = "Recipient not found.";
        }
    } else {
        $error_message = "Error fetching status.";
    }
    $statusStmt->close();
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($recipient_id)) {
    $message_content = trim($_POST['message_content']);
    $image_upload_path = null; // Initialize as null for database

    // Handle file upload (if provided)
    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] == UPLOAD_ERR_OK) {
        // Sanitize and validate the file
        $image_name = basename($_FILES['image_upload']['name']);
        $image_tmp_name = $_FILES['image_upload']['tmp_name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Define allowed file types (e.g., jpg, png, gif)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_ext, $allowed_types)) {
            // Generate a unique file name and move the uploaded file to a folder
            $upload_dir = 'uploads/'; // Ensure this folder exists and is writable
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $unique_name = uniqid() . '.' . $image_ext;
            $image_upload_path = $upload_dir . $unique_name;

            if (!move_uploaded_file($image_tmp_name, $image_upload_path)) {
                $error_message = "Error uploading the image.";
                $image_upload_path = null; // Reset path on failure
            }
        } else {
            $error_message = "Invalid image type. Allowed types: jpg, jpeg, png, gif.";
        }
    }

    if (!empty($message_content)) {
        // Insert the message into the database
        $insertMessageSql = "INSERT INTO messages (sender_id, recipient_id, recipient_username, message_content, image_upload) 
                             VALUES (?, ?, ?, ?, ?)";
        $insertMessageStmt = $conn->prepare($insertMessageSql);
        $insertMessageStmt->bind_param('iisss', $user_id, $recipient_id, $recipient_username, $message_content, $image_upload_path);

        if ($insertMessageStmt->execute()) {
            $message = "Message sent successfully.";
        } else {
            $error_message = "Error sending message.";
        }

        $insertMessageStmt->close();
    } else {
        $error_message = "Message content cannot be empty.";
    }
}



// Unread message count
$unreadCount = 0;
$sqlUnreadMessages = "SELECT COUNT(*) AS unread_count FROM messages WHERE recipient_id = ? AND read_status = 0";
$stmtUnread = $conn->prepare($sqlUnreadMessages);
$stmtUnread->bind_param('i', $user_id);
if ($stmtUnread->execute()) {
    $resultUnread = $stmtUnread->get_result();
    $unreadRow = $resultUnread->fetch_assoc();
    $unreadCount = $unreadRow['unread_count'];
}
$stmtUnread->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Create Messages</title>
    <link rel="stylesheet" href="css/style29.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
</head>
<body>

    <section id="sidebar">
        <div class="center-a">
            <a href="#" class="logs">
                <img src="css/img/LOGO.png" alt="Logo" style="width: 50px; border-radius:50px;" id="logs">
                <span class="text" id="title-txt">AdminHub</span>
            </a>
        </div>
        <ul class="side-menu top">
            <li><a href="admin_dashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
            <li><a href="admin-post.php"><i class='bx bx-news'></i><span class="text">Post</span></a></li>
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
            <li><a href="admin-announcement.php"><i class='bx bxs-megaphone'></i><span class="text">Announcement</span></a></li>
            <li><a href="admin-schedule.php"><i class='bx bxs-calendar'></i><span class="text">Schedule</span></a></li>
            <li><a href="admin-reservation.php"><i class='bx bxs-briefcase-alt-2'></i><span class="text">Reservation</span></a></li>
            <li><a href="admin-parishioner.php"><i class='bx bxs-user'></i><span class="text">Parishioner</span></a></li>
        </ul>
        <ul class="side-menu">
            <li><a href="#"><i class='bx bxs-cog'></i><span class="text">Settings</span></a></li>
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
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <div class="clock"><h4 id="date-time"></h4></div>
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
            <h1>Parishioner</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="#">Users</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="#">View Profile</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Send Message</a></li>
            </ul>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert-sent-success">
            <h5><i class='bx bxs-check-circle'></i> <?php echo htmlspecialchars($message); ?></h5>
        </div>
    <?php elseif (!empty($error_message)): ?>
        <div class="alert-sent-failed">
            <h5><i class='bx bxs-error-circle'></i> <?php echo htmlspecialchars($error_message); ?></h5>
        </div>
    <?php endif; ?>

    <div class="cont-inbox fade-up-animation" style="padding:25px;">
        <!-- Note the enctype attribute added here -->
        <form action="admin_send_messages.php?username=<?php echo urlencode($recipient_username); ?>" 
              method="post" enctype="multipart/form-data">
            <label for="recipient_username_or_id">
                <p>Send Message To:
                <input type="text" id="recipient_username_or_id" name="recipient_username_or_id"  
                       class="idfill" value="<?php echo htmlspecialchars($recipient_username); ?>" 
                       style="outline:none; background:#f9f9f9; font-size:18px; cursor:default;" readonly>
                </p>
                <p>Status:
                <span style="color: 
                        <?php 
                            if ($user_status == 'Online') {
                                echo 'green';
                            } elseif ($user_status == 'Offline') {
                                echo 'red';
                            } else {
                                echo 'black';
                            }
                        ?>;">
                     <?php echo $user_status ? $user_status : 'Unknown'; ?>
                    </span>
                </p>
            </label>
            <br>
            <label for="" style="font-weight:300; font-size:14px; display:flex; flex-direction:column;">
                Attach Image(Optional):
                <input type="file" name="image_upload" id="image_upload" 
                       style="margin-top:10px; outline:none; border:none;">
            </label>
            <textarea id="message_content" name="message_content" rows="6" cols="100" 
                      placeholder="Write a message" required></textarea>
            <br>
            <div class="btn-button">
                <a href="admin-parishioner-profile.php" class="btn-back">
                    <i class='bx bx-arrow-back' style="margin-right:10px;"></i>Back
                </a>
                <input type="submit" class="submit" style="width:150px;" value="Send Message">
            </div>
        </form>
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
    <!-- Clock Script -->
    <script>
    window.addEventListener("load", () => {
        clock();
        function clock() {
            const today = new Date();
            const hours = today.getHours();
            const minutes = today.getMinutes();
            const seconds = today.getSeconds();
            const hour = hours < 10 ? "0" + hours : hours;
            const minute = minutes < 10 ? "0" + minutes : minutes;
            const second = seconds < 10 ? "0" + seconds : seconds;
            const hourTime = hour > 12 ? hour - 12 : hour;
            const ampm = hour < 12 ? "AM" : "PM";
            const dateTime = hourTime + ":" + minute + ":" + second + ampm;
            document.getElementById("date-time").innerHTML = dateTime;
            setTimeout(clock, 1000);
        }
    });
    </script>

    <!-- LOGOUT OVERLAY Script -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const logoutLink = document.getElementById('logout-link');
        const confirmationDialog = document.getElementById('confirmation-dialog');
        const confirmLogout = document.getElementById('confirm-logout');
        const cancelLogout = document.getElementById('cancel-logout');

        logoutLink.addEventListener('click', (event) => {
            event.preventDefault(); 
            confirmationDialog.classList.add('show'); 
        });

        confirmLogout.addEventListener('click', () => {
            window.location.href = 'admin.php'; 
        });

        cancelLogout.addEventListener('click', () => {
            confirmationDialog.classList.remove('show');
        });

        confirmationDialog.addEventListener('click', (event) => {
            if (event.target === confirmationDialog) {
                confirmationDialog.classList.remove('show');
            }
        });
    });
    </script>

</body>
</html>

<?php
$conn->close();
?>
