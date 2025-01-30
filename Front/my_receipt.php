<?php
session_start();
require_once "../config.php";


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
    <title>Reservation</title>
    <link rel="stylesheet" href="css/reservation-css5.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
    <style>
        .cont-receipt {
            width: 80%;
            box-shadow:0 5px 10px rgba(0,0,0,.3);
            outline:solid 1px rgba(0,0,0,.2);
            padding:25px;
            margin:auto;
            border-radius:10px;
        }
        .cont-receipt .header {
            display:flex;
            flex-direction:row;
            justify-content:space-between;
            align-items:center;
            padding:5px 20px;
        }
        .cont-receipt .header .receipt-title h3{
            font-size:18px;
        }
        .cont-receipt .header .receipt-title .txt-title {
            text-transform:uppercase;
        }
        .cont-receipt .receipt-info {
            display:flex;
            flex-direction:row;
            margin-top:35px;
            justify-content:space-between; 
        }
        .cont-receipt .receipt-info .div-inpt {
            width:550px;
            margin:5px; 
            display:flex;
            flex-direction:row;
        }
        .cont-receipt .receipt-info .div-inpt label {
            text-transform:uppercase;
            font-weight:700;
            width: 260px;
        }
        .cont-receipt .receipt-info .div-inpt input {
            width: 100%;
            background: transparent;
            text-align:center;
            border: none;
            font-size:18px;
            border-bottom: 2px solid rgba(0, 0, 0, 1);
            outline: none;
            margin-bottom: 20px;
            color: #40414a;
        }
        .info-lay-row {
            margin-right:100px;
            text-transform:uppercase;
        }
        .table-row {
            padding:10px 40px;
        }
        .table-row table {
            width: 100%;
            outline:solid 2px #000;
            padding:5px;
        }
        .table-row table th{
            padding-bottom: 12px;
            font-size: 18px;
            text-transform:uppercase;
            text-align: center;
            border-bottom: 2px solid #000;
        }
        .table-row table td {
            padding:20px;
            font-size: 18px;

            text-align: center;
        }
        .receipt-footer {
            display:flex;
            flex-direction:row;
            justify-content: space-between;
            padding:1px 25px;
        }
        .receipt-row-9 {
            width: 400px;
            text-transform:uppercase;
        }
        .receipt-row-9 input {
            width: 100%;
            background: transparent;
            text-align:center;
            border: none;
            font-size:18px;
            border-bottom: 2px solid rgba(0, 0, 0, 1);
            outline: none;
            margin-bottom: 20px;
            color: #40414a;
            margin-top:20px;
        
        }
        .div-row-10 {
            display:flex;
            flex-direction:row;
            padding-right:15px;
        }
        .table-row-12 {

            outline:solid 2px #000;
            margin-top:-10px;
            height:45px;
            display:flex;
            justify-content:center;
            align-items:center;
            width: 200px;
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
                <i class='bx bxs-home-alt-2' style="color:#342E37;"></i>
                 <span class="text">Home</span>
                </a>
            </li>
            <li class="active">
                <a href="my_reservation.php">
                <i class='bx bxs-calendar-event' style="color:#342E37;"></i>
                 <span class="text">Reservation</span>
                </a>
            </li>

            
            <li>
                <a href="admin-messages.php">
                   <i class='bx bxs-message-rounded'style="color:#342E37;" ></i>
                    <span class="text">Messages</span>
                </a>
            </li>
        
            <li>
                <a href="admin-announcement.php">
                <i class='bx bxs-megaphone' style="color:#342E37;"></i>
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

            <div class="clock">
                <h4 class="greetings" style="margin-right:10px;color:#f9f9f9;"></h4>
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

        <?php
    $s_id = $_SESSION['s_id'] ?? null; // Using null coalescing operator for safety

    if ($s_id) {
        // Prepare and execute the query to retrieve the reservation and user details
        $stmt = $conn->prepare("SELECT r.*, u.firstname, u.lastname, u.username, p.pay_id, p.total_amount, p.p_status 
                                FROM reservation r
                                JOIN users u ON r.uid = u.uid 
                                LEFT JOIN payment p ON r.s_id = p.s_id 
                                WHERE r.s_id = ?");
        $stmt->bind_param("i", $s_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Check if reservation exists
        if ($result->num_rows > 0) {
            // Fetch reservation, user, and payment data
            $reservation = $result->fetch_assoc();
            
            // Check if firstname and lastname are set, otherwise use username
            if (!empty($reservation['firstname']) && !empty($reservation['lastname'])) {
                $full_name = $reservation['firstname'] . ' ' . $reservation['lastname'];
            } else {
                $full_name = $reservation['username']; // Use username as fallback
            }
            
            // Get pay_id for receipt number
            $receipt_no = $reservation['pay_id'] ?? 'N/A';
        } else {
            echo "Reservation not found.";
            exit();
        }
    } else {
        echo "Invalid reservation ID.";
        exit();
    }
?>


<div class="header" style="margin-bottom:20px; display:flex; flex-direction:row; justify-content:space-between;">
    <h1>My Receipt</h1>
    <a href="my_reservation.php"  style="margin-right:120px; padding:10px; padding-left:30px; padding-right:30px; background:blue; border-radius:5px; color:#fff;">Done</a>
</div>
        <div class="cont-receipt">
            <div class="header">
               <div class="receipt-title">
                <h3 class="txt-title">Archdiocesan Shrine of Santa Rosa de Lime </h3>
                <h3>Daanbantayan, Cebu</h3>
               </div>

                    <h2>OFFICIAL <br> RECEIPT</h2>
            </div>
            <hr>
            <div class="receipt-info">
                <div class="info-lay">

                <div class="div-inpt">
                <Label>Received from:</Label>
                <input type="text" name="received_from" id="received_from" value="<?php echo htmlspecialchars($full_name); ?>" style="cursor:default;" readonly>
                </div>

                <div class="div-inpt">
                <Label>The ammount of:</Label>
                <input type="text" name="" id="" value="<?php echo htmlspecialchars($reservation['amount']); ?>" style="cursor:default;" readonly>
                </div>
                <div class="div-inpt">

                <input type="text" name="" id="" style="cursor:default;" readonly>
                </div>
                <div class="div-inpt">
                <Label>Recieved through:</Label>
                <input type="text" name="payment_type" value="<?php echo htmlspecialchars($reservation['payment_type']); ?>" style="cursor:default;" readonly>
                </div>

                </div>

                <div class="info-lay-row">
                     <h4 style="color:red;">Receipt No: <?php echo htmlspecialchars($receipt_no); ?></h4>
                     <h4>Date: <?php echo date("Y/m/d"); ?></h4>
                </div>

            </div>

            <div class="table-row">
            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($reservation['service_type']); ?></td>
                        <td></td>
                        <td><?php echo htmlspecialchars($reservation['amount']); ?></td>
                    </tr>
                </tbody>
            </table>
            </div>
            <div class="receipt-footer">
                <div class="receipt-row-9">
                    <h3>Received by:</h3>
                    <input type="text" name="" id="" style="cursor:default;" readonly>
                    <p style="text-align:center;">Cashier / Treasurer</p>
                </div>

                <div class="div-row-10">
                    <div class="table-row-12">
                        <span>Total</span>
                    </div>
                    <div class="table-row-12">
                        <span><?php echo htmlspecialchars($reservation['amount']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        </main>


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
            <p>ID:</p>
                <p>First Name</p>
                <p>Last Name</p>
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
