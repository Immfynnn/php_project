<?php
session_start(); // Start the session here
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);
$message = ''; // Initialize message variable
$error_message_ls = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_content = $_POST['post_content'];
    $post_image = null;
    $uploadOk = 1; // Flag for upload status

    // Check if a file was uploaded
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["post_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate the image
        $check = getimagesize($_FILES["post_image"]["tmp_name"]);
        if ($check === false) {
            $error_message_ls = "File is not an image.";
            $uploadOk = 0;
        } elseif ($_FILES["post_image"]["size"] > 10000000) {
            $error_message_ls = "Sorry, your file is too large.";
            $uploadOk = 0;
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Attempt to upload the file
        if ($uploadOk) {
            if (move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file)) {
                $post_image = $target_file; // Store the path of the uploaded file
            } else {
                $error_message = "Sorry, there was an error uploading your file.";
                $uploadOk = 0; // Prevent database insert on failure
            }
        }
    }

    // Insert into database only if upload was successful
    if ($uploadOk) {
        $stmt = $conn->prepare("INSERT INTO posts (admin_id, post_image, post_content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $post_image, $post_content);

        if ($stmt->execute()) {
            $message = "The post has been uploaded successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Continue with the rest of your code to fetch posts and handle other actions
// The connection will be closed after all queries are executed.

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

// Fetch posts for the dashboard
$sql = "SELECT posts.*, admins.admin_username, admins.admin_image FROM posts JOIN admins ON posts.admin_id = admins.admin_id ORDER BY posts.post_date DESC";
$result = $conn->query($sql);

if ($conn->error) {
    echo "SQL Error: " . $conn->error;
}

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Display post data
    }
} else {
}

// Now you can close the connection, since all queries have been executed

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Post</title>
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
            <li class="active">
                <a href="admin-post.php">
                <i class='bx bx-news'></i>
                 <span class="text">Post</span>
                </a>
            </li>
            
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
                    <h1>Post</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li>
                            <i class='bx bx-chevron-right'>

                            </i></li>
                        <li>
                            <a class="active" href="#">Post</a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php if (!empty($message)): ?>
              <div class="alert-sent-success">
              <h5><i class='bx bxs-check-circle'></i><?php echo htmlspecialchars($message); ?></h5>
             </div>
           <?php elseif (!empty($error_message_ls)): ?>
              <div class="alert-sent-failed-orrange">
              <h5><i class='bx bxs-error-circle'></i><?php echo htmlspecialchars($error_message_ls); ?></h5>
             </div>
           <?php elseif (!empty($error_message)): ?>
              <div class="alert-sent-failed">
              <h5><i class='bx bxs-error-circle'></i><?php echo htmlspecialchars($error_message); ?></h5>
             </div>
             <?php endif; ?>
            <div class="container-post">
                <div class="top">
                    <h3>Upload Post</h3>
                </div>
                <form action="admin-post.php" method="post" enctype="multipart/form-data">
               <textarea id="post_content" name="post_content" rows="6" cols="100" required placeholder="Write Something...."></textarea><br><br>
        
                <label for="post_image">Post Image (Optional):</label><br>
    
               <div class="btn-button">
               <input type="file" id="post_image" name="post_image" accept="image/*" class="btn-file"><br><br>
               <input type="submit" value="Upload" class="submit">
               </div>
               </form>
            </div>


            <div class="post-view">
    <div class="head">
        <h3>Post View</h3>
    </div>

    <?php
    include '../config.php'; // Database connection

    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $sql = "
        SELECT posts.*, admins.admin_username, admins.admin_image 
        FROM posts 
        JOIN admins ON posts.admin_id = admins.admin_id 
        ORDER BY posts.post_date DESC";
    $result = $conn->query($sql);

    // Check for SQL errors
    if ($conn->error) {
        echo "SQL Error: " . $conn->error;
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='cont-post'>";
            echo "<p>";
            if (!empty($row["admin_image"]) && file_exists($row["admin_image"])) {
                echo "<img src='" . htmlspecialchars($row["admin_image"]) . "' alt='Admin Image' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;'>";
            }
            echo "<div class='top'>";
            echo "<strong>Posted by: " . htmlspecialchars($row["admin_username"]) . "</strong>";
            echo " <i class='bx bxs-trash' onclick='confirmDelete(" . $row["post_id"] . ")'></i>";
            echo "</div>";
            echo "</p>";

            echo "<p class='date'>Date: " . htmlspecialchars($row["post_date"]) . "</p>";

            $postContent = isset($row['post_content']) ? htmlspecialchars($row['post_content']) : 'No content available';
            echo "<p class='text-p'>" . nl2br($postContent) . "</p>";

            if (!empty($row["post_image"]) && file_exists($row["post_image"])) {
                echo "<div class='img-cntr'><img src='" . htmlspecialchars($row["post_image"]) . "' alt='Post Image' style='width: 90%; height:400px; border-radius:5px;' class='text-p'></div><br>";
            }

            echo "<p class='text-p'><i class='bx bxs-like' style='font-size:30px; color:#3C91E6;'></i> " . htmlspecialchars($row["likes"]) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<h5 id='alert'>No posts found.</h5>";
    }
    ?>
</div>

<!-- Confirmation Overlay -->
<div id="deleteOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; padding:20px; justify-content:center; align-items:center;">
    <div style="background:white; padding:20px; border-radius:10px; text-align:center; margin:20px;">
        <p>Are you sure you want to delete this post?</p>
        <button onclick="proceedDelete()" style="margin-right:10px; padding:5px 10px;" class="btn-btn">Yes</button>
        <button onclick="closeOverlay()" style="padding:5px 10px;" class="btn-btn">No</button>
    </div>
</div>

<script>
    let deletePostId = null;

    function confirmDelete(postId) {
        deletePostId = postId;
        document.getElementById('deleteOverlay').style.display = 'flex';
    }

    function closeOverlay() {
        deletePostId = null;
        document.getElementById('deleteOverlay').style.display = 'none';
    }

    function proceedDelete() {
        if (deletePostId) {
            window.location.href = 'delete_post.php?post_id=' + deletePostId;
        }
    }
</script>

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

    //get current date and tim
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
$conn->close();
?>