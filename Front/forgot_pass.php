<?php
session_start();
require_once "../config.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['loginInput']); // Get the entered email
    $validDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com']; // Add more valid domains as needed

    // Validate email format and domain
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $domain = substr(strrchr($email, "@"), 1); // Extract the domain from the email
        if (!in_array($domain, $validDomains)) {
            $error = "Invalid email domain. Please use a valid provider (e.g., Gmail, Yahoo).";
        } else {
          // Check if the email exists in the database
           $stmt = $conn->prepare("SELECT email, uid FROM users WHERE email = ?");
           $stmt->bind_param("s", $email);
           $stmt->execute();
           $stmt->store_result();
           $stmt->bind_result($fetchedEmail, $uid);
           
           if ($stmt->num_rows > 0) {
               // Fetch the user data
               $stmt->fetch();
           
               // Email exists, proceed to fp_auth.php
               $_SESSION['email'] = $fetchedEmail; // Store email in session
               $_SESSION['uid'] = $uid; // Store user ID in session
               header("Location: fp_auth.php");
               exit();
           } else {
               // Email not registered
               $error = "The entered email address is not registered.";
           }
           $stmt->close();

        }
    }
}

$conn->close();
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
    
    <title>ASSRDLDMS | Sign up</title>
    <style>
        .back-link a {
    color: #007bff; /* Blue color */
    text-decoration: none; /* Removes underline */
    font-size: 16px;
    margin-top: 10px;
    display: inline-block;
    
}

.back-link a:hover {
    text-decoration: underline; /* Adds underline on hover */
}

<style>
        /* Loading screen styles */
#loading-screen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
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
            <div class="row" data-aos="fade-up" style="height:490px;">
                <div class="col-md-6 side-image">
                    <img src="mainbg.jpg" alt="" class="imgs-cnfg">
                    <div class="text">
                        <h1>Hello Parishioner!</h1>
                        <p>Log in to your account and join us for a fulfilling worship experience.</p>
                    </div>
                </div>
                <div class="col-md-6 right">
                    <div class="input-box">
                        <h1 style="margin-bottom:45px;">Forgot Password</h1>

                        <form method="POST" action="forgot_pass.php">
                        <div class="div-alert" style="text-align:center;">
                            <?php if (isset($error)) echo "<div style='color:red; font-size:13px; margin-bottom:20px;'>$error</div>"; ?>
                        </div>
                            <div class="input-field">
                            <input type="text" class="input" name="loginInput" id="int-confg" 
                                value="<?php echo isset($_POST['loginInput']) ? htmlspecialchars($_POST['loginInput']) : ''; ?>" required>
                                <label for="loginInput">Enter your registered email address</label> 
                            </div>
                            <div class="input-field">
                                <input type="submit" class="submit" value="Submit" name="login">
                            </div> 
                        </form>

                        <div class="signin">
                            <span>Don't have an account? <a href="signup.php">Register here</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Loading screen (Initially hidden) -->
<div id="loading-screen" style="display:none;">
    <div class="loading-content">
        <div class="spinner"></div>
        <p>Loading...</p>
    </div>
</div>
<script>
    // Show loading screen when form is submitted
    document.querySelector("form").onsubmit = function() {
        document.getElementById("loading-screen").style.display = "flex";  // Show loading screen
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