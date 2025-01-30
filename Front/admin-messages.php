<?php
// admin-messages.php

session_start(); // Start the session here
            
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}
// ADD
$user_id = intval($_SESSION['admin_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Messages</title>
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
            <li class="active">
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
            <h1>Messages</h1>
            <ul class="breadcrumb">
                <li>
                    <a href="#">Dashboard</a>
                </li>
                <li>
                    <i class='bx bx-chevron-right'></i>
                </li>
                <li>
                    <a class="active" href="#">Inbox</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="cont-inbox fade-up-animation">
        <form id="delete-all-form" action="delete_all_messages_admin.php" method="POST">
            <div class="head">
                <a href="admin-messages-create.php">Create Messages
                    <i class='bx bxs-message-square-add'></i>
                </a>
                <button type="button" onclick="confirmDeleteAll()">
                    Delete all Messages
                    <i class='bx bxs-trash-alt'></i>
                </button>
            </div>
        </form>

        <?php
        // Define the SQL query to fetch messages with sender username and user ID
        $sql = "SELECT m.msg_id, m.sender_uid, u.username AS sender_username, u.uid, m.recipient_aid, m.message_cont, m.sent_at1 
                FROM messages1 m
                JOIN users u ON m.sender_uid = u.uid
                WHERE m.sender_uid = ? OR m.recipient_aid = ?";

        // Prepare the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $user_id, $user_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $messageCount = $result->num_rows;

            if ($messageCount > 0) {
                // Mark messages as read
                $updateSql = "UPDATE messages1 SET read_status1 = 1 WHERE recipient_aid = ? AND read_status1 = 0";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param('i', $user_id);
                $updateStmt->execute();
                $updateStmt->close();

                // Display messages
                while ($row = $result->fetch_assoc()) {
                    $messageId = htmlspecialchars($row['msg_id']);
                    $senderUsername = htmlspecialchars($row['sender_username']);
                    $recipientId = htmlspecialchars($row['recipient_aid']);
                    $messageContent = htmlspecialchars($row['message_cont']);
                    $sentAt = date("Y-m-d h:i A", strtotime($row['sent_at1']));
                    $senderUid = htmlspecialchars($row['uid']);

                    echo "<div>";
                    echo "<table>";
                    echo "<tbody>";
                    echo "<tr>";
                    echo "<td style='width:11%; cursor:pointer;' onclick=\"window.location.href='admin-parishioner-profile.php?uid=" . $senderUid . "'\"><b>{$senderUsername}</b></td>";
                    echo "<td style='width:70%;'><a href='admin-view-msg.php?msg_id={$messageId}'><p>{$messageContent}</p></a></td>";
                    echo "<td><a href='#' style='color:#000;'>{$sentAt}</a></td>";
                    echo "<td>
                            <form action='delete_message_admin.php' method='POST' style='display: inline;'>
                                <input type='hidden' name='message_id' value='{$messageId}'>
                                <button type='submit' id='btn-trash' title='Delete message'><i class='bx bxs-trash'></i></button>
                            </form>
                          </td>";
                    echo "<td>
                            <form action='admin-messages-reply.php' method='GET' style='display: inline;'>
                                <input type='hidden' name='username' value='" . urlencode($senderUsername) . "'>
                                <button type='submit' title='Reply' class='reply-to'>Reply</button>
                            </form>
                          </td>";
                    echo "</tr>";
                    echo "</tbody>";
                    echo "</table>";
                }
            } else {
                echo "<p id='alert'>No messages found.</p>";
            }
        } else {
            echo "Error: " . htmlspecialchars($stmt->error);
        }
        ?>
    </div>

    <!-- Overlay for Success Message -->
    <div class="overlay-delete" id="success-overlay" style="display: none;">
        <div class="overlay-content">All messages have been successfully deleted.</div>
    </div>

    <!-- Styles for Overlay -->
    <style>
        .overlay-delete {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }

        .overlay-content {
            background: white;
            color: black;
            padding: 20px 30px;
            border-radius: 8px;
            font-size: 18px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>

    <!-- JavaScript to Handle Overlay -->
    <script>
        function confirmDeleteAll() {
            if (confirm('Are you sure you want to delete all messages?')) {
                const overlay = document.getElementById('success-overlay');
                overlay.style.display = 'flex'; // Show the overlay
                setTimeout(() => {
                    overlay.style.animation = 'fadeOut 0.5s forwards';
                    setTimeout(() => overlay.style.display = 'none', 500); // Hide overlay after fade-out
                }, 3000);

                // Submit the form to delete all messages
                document.getElementById('delete-all-form').submit();
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

//confirmation delete 
function confirmDeleteAll() {
    if (confirm("Are you sure you want to delete all messages? This action cannot be undone.")) {
        document.getElementById('delete-all-form').submit();
    }
}

// Handle the click event on the Messages link
document.getElementById('messages-link').addEventListener('click', function(event) {
    // Prevent default link behavior (optional, depending on your use case)
    // event.preventDefault();

    // Update the message count span to 0
    document.getElementById('message-count').innerText = '0';

    // Optional: Send AJAX request to mark messages as read in the database
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "mark_messages_read.php", true); // Assuming you have a file to handle marking messages as read
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("user_id=<?php echo $user_id; ?>"); // Send user_id to identify the admin

    // Proceed with the link navigation
});

</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>