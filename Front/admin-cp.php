<?php
session_start(); // Start the session
require_once '../config.php'; // Include database connection

// Check if the admin session is set
if (!isset($_SESSION['admin_id'])) {
    // Redirect to admin-forgot-pass.php if session is not set
    header("Location: admin-forgot-pass.php");
    exit();
}

// Fetch the admin's details from the database using the session's admin_id
$admin_name = "";
$admin_id = $_SESSION['admin_id'];

$sql = "SELECT admin_name FROM admins WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $admin_name = htmlspecialchars($row['admin_name']); // Prevent XSS
} else {
    // If no admin is found, redirect back to admin-forgot-pass.php
    header("Location: admin-forgot-pass.php");
    exit();
}

// Initialize success variable
$password_updated = false;
$error_message = "";

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['admin_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update the password in the database
        $update_sql = "UPDATE admins SET admin_password = ? WHERE admin_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $admin_id);

        if ($update_stmt->execute()) {
            // Set the flag for successful password change
            $password_updated = true;
            session_destroy(); // End the session after password update (optional)
        }
    } else {
        $error_message = "Passwords do not match. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="css/style02.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="website icon" type="png" href="css/img/LOGO.png" id="logo">
</head>
<body>
<style>
    /* Overlay styles */
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        animation: fadeInOut 4s;
    }

    @keyframes fadeInOut {
        0% { opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { opacity: 0; }
    }

    .success-message {
        font-size: 24px;
        text-align: center;
    }
</style>
    
<header class="fadeUp" id="header-confg">
<a href="index.php" id="logs"><img src="css/img/LOGO.png" alt="Logo" style="width: 100px; border-radius:50px;" id="logs"></a>
     
<h1 style="color:#fff; letter-spacing:1px;">
    Administrator
</h1>
 <input type="checkbox" id="check">
 <label for="check" class="icons">
 <i class='bx bx-menu' id="menu-icon"></i>
 <i class='bx bx-x' id="close-icon"></i>
 </label> 

 <nav>
    <a href="index.php">Home</a> 
</nav>
</header>

<section id="admin-bg">
    <div class="admindiv">
        <div class="fadeUp" id="cont-admin">
            <div class="dv-left">
                <img src="css/img/icon-log.jpg" alt="logo" id="mainLog">
            </div>
            <div class="dv-right">
                <br>
                <div class="text-log">
                    <h4 style="color:#fff;">Change Password</h4>
                </div>
                <h4 style="margin-bottom:10px; color:green;"><?php echo $admin_name; ?></h4>

                <?php if (isset($error_message)): ?>
                    <p style="color: red;"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <form action="admin-cp.php" method="post">
                    <div class="input-log">
                        <input type="password" name="admin_password" placeholder="Enter New Password" required>
                    </div>
                    <div class="input-log">
                        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                    </div>
                    <div class="input-button">
                        <input type="submit" value="Submit" name="submit">
                    </div>
                </form>
                <br>
                <br>
            </div>
        </div>
    </div>
</section>

<?php if ($password_updated): ?>
    <div class="overlay" id="success-overlay">
        <div class="success-message">
            <p>Password changed successfully!</p>
            <p>Redirecting to login page...</p>
        </div>
    </div>
<?php endif; ?>

<script>
    // Show overlay and redirect after password change
    <?php if ($password_updated): ?>
        const overlay = document.getElementById('success-overlay');
        overlay.style.display = 'flex';
        setTimeout(() => {
            window.location.href = 'admin.php'; // Redirect to login page after 4 seconds
        }, 4000); // 4 seconds for fade-out effect
    <?php endif; ?>
</script>
<style>
    .admindiv {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        padding: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    #cont-admin {
        width: 50%;
        height: 370px;
        background-color: rgb(0, 0, 0);
        border-radius: 14px;
        display: flex;
        flex-direction: row;
        outline: #fff solid 1px;
        box-shadow: #fff 0 0 10px;
    }

    #cont-admin .dv-left {
        width: 50%;
        background-color: #fff;
        border-radius: 14px 0 0 14px;
    }

    #cont-admin .dv-right {
        width: 50%;
        border-radius: 0 14px 14px 0px;
        background: rgb(41, 31, 77);
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    #mainLog {
        position: relative;
        top: 35px;
        left: 45px;
        width: 280px;
        height: 280px;
    }

    .text-log {
        padding: 40px 0 30px;
        font-size: 25px;
    }

    .input-log input {
        width: 230px;
        height: 45px;
        padding: 20px;
        border-radius: 6px;
        margin: 5px;
        font-size: 14px;
    }

    .input-button input {
        width: 230px;
        height: 45px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 800;
        margin-left: 5px;
    }

    .input-button input:hover {
        background-color: rgba(255, 255, 255, 0.8);
    }

    /* Responsive Design */
    @media screen and (max-width: 768px) {
        #cont-admin {
            width: 80%;
            flex-direction: column;
            height: auto;
        }
        #cont-admin .dv-left, #cont-admin .dv-right {
            width: 100%;
            border-radius: 14px 14px 0 0;
        }
        #cont-admin .dv-right {
            border-radius: 0 0 14px 14px;
        }
        #mainLog {
            top: 20px;
            left: 0;
            width: 200px;
            height: 200px;
        }
        .text-log {
            padding: 20px 0 10px;
            font-size: 20px;
        }
        .input-log input, .input-button input {
            width: 90%;
        }
    }
</style>
</body>
</html>
