<?php
session_start();
require_once "../config.php"; // Database connection

// Check if the email exists in the session and is valid
if (!isset($_SESSION['email']) || !filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) {
    // If not, redirect to the forgot password page
    header("Location: forgot_pass.php");
    exit();
}
// Check if the user has passed the security question
if (!isset($_SESSION['qa_verified']) || $_SESSION['qa_verified'] !== true) {
    // If not verified, redirect to the forgot password page
    header("Location: fp_auth.php");
    exit();
}



$error = "";
$success = false;

if (isset($_POST['login'])) {
    $newPassword = trim($_POST['newPassword']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Validate the new password
    if (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        // Retrieve the email from the session
        $email = $_SESSION['email'];
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Prepare SQL query to update the password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $passwordHash, $email);

        if ($stmt->execute()) {
            // Successfully changed the password
            $success = true;
            $_SESSION['success'] = "Password successfully changed!";
        
            // Destroy session after password change
            session_unset();
            session_destroy();
        } else {
            $error = "Error updating password: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/forgot-pss.css">
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    
    <title>ASSRDLDMS | Forgot Password</title>
    <style>
        /* Loading screen styles */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading-content {
            text-align: center;
            color: white;
        }

        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #fff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 3s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success Overlay */
        #success-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            animation: fadeUp 1s ease-out forwards;
        }

        @keyframes fadeUp {
            0% { opacity: 0; transform: translateY(50px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .success-content {
            background: #28a745;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            color: white;
        }

        .success-content p {
            margin-bottom: 15px;
        }

        .success-content a {
            color: white;
            font-size: 18px;
            text-decoration: none;
        }

    </style>
</head>
<body>
<header class="stopanimate" id="header-confg">
        <style>
            header {
                opacity: 1;
                transform: translateY(0px);
            }
            header nav a {
                opacity: 1;
                transition: opacity 0.6s ease;
            }
        </style>
        <a href="admin.php"><img src="css/img/LOGO.png" alt="Logo" style="width: 80px; border-radius:50px;"></a>

        <input type="checkbox" id="check">
        <label for="check" class="icons">
            <i class='bx bx-menu' id="menu-icon"></i>
            <i class='bx bx-x' id="close-icon"></i>
        </label> 

        <nav>
            <a href="index.php#home" class="anim8">Home</a>
            <a href="index.php#about" class="anim8">About us</a>
            <a href="index.php#contact" class="anim8">Contact</a>
            <a href="signup.php" class="anim8">Sign Up</a>
            <a href="index.php#services" id="ser-txt" class="text5">Services</a>
        </nav>
    </header>

    <div class="wrapper">
    <div class="container main">
        <div class="row" style="height:490px;">
            <div class="col-md-6 side-image">
                <img src="mainbg.jpg" alt="" class="imgs-cnfg">
                <div class="text">
                    <h1>Hello Parishioner!</h1>
                    <p>Always remember Your Password or QA</p>
                </div>
            </div>
            <div class="col-md-6 right">
                <div class="input-box">
                    <h1 style="margin-bottom:45px;">Change Password</h1>

                    <form method="POST" action="new_pass.php">
                        <div class="div-alert" style="text-align:center;">
                            <?php 
                            if ($error) {
                                echo "<div style='color:red; font-size:14px;'>$error</div>";
                            }
                            ?>
                        </div>

                        <?php if ($success): ?>
                            <div id="success-overlay">
                                <div class="success-content">
                                <i class='bx bx-check-circle' style="font-size:3rem;"></i>
                                    <p>Password successfully changed!</p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="input-field">
                            <input type="password" class="input" name="newPassword" required>
                            <label for="newPassword">New Password</label>
                        </div>

                        <div class="input-field">
                            <input type="password" class="input" name="confirmPassword" required>
                            <label for="confirmPassword">Confirm New Password</label>
                        </div>

                        <div class="input-field">
                            <input type="submit" class="submit" value="Submit" name="login">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading screen (Initially hidden) -->
<div id="loading-screen">
    <div class="loading-content">
        <div class="spinner"></div>
        <p>Loading...</p>
    </div>
</div>

<script>
    // Show success overlay if password is successfully changed
    <?php if ($success): ?>
        document.getElementById('success-overlay').style.display = "flex";
    <?php endif; ?>

    // Close overlay and redirect to signin page
    document.getElementById('success-overlay').onclick = function() {
        window.location.href = 'signin.php';
    };
</script>

<footer>
    <p>&copy; 2024 Church Reservation Management System. All rights reserved.</p>
</footer>

<script>
    AOS.init({
        duration: 1000,  // Animation duration in ms
        once: true,      // Animation only happens once
    });
</script>




</body>
</html>
