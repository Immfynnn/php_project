    <?php
    session_start();
    require_once "../config.php";

    // Check if the email exists in the session and is valid
    if (!isset($_SESSION['email']) || !filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) {
        // If not, redirect to the forgot password page
        header("Location: forgot_pass.php");
        exit();
    }

    $error = ""; // Variable to hold error messages
    $question = ""; // Variable to hold the security question
    $attempts = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 0;
    $last_attempt_time = isset($_SESSION['last_attempt_time']) ? $_SESSION['last_attempt_time'] : 0;

    // Retrieve the email from the session
    $email = $_SESSION['email'];

    // Check if the user is locked out for too many failed attempts
    if ($attempts >= 8) {
        $time_diff = time() - $last_attempt_time;
        if ($time_diff < 600) {
            // Lock out for 10 minutes
            $error = "Oops! Too many attempts. Please try again after 10 minutes.";
        } else {
            // Reset attempts after 10 minutes
            $_SESSION['attempts'] = 0;
            $_SESSION['last_attempt_time'] = 0;
            $attempts = 0;
        }
    }

    // Fetch the security question (Q1) for the user
    $stmt = $conn->prepare("SELECT Q1 FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($question);
    $stmt->fetch();
    $stmt->close();

    if (empty($question)) {
        // If Q1 is blank, display an error message
        $error = "Oops! Your account is not set up. Please contact the administrator to recover your account.";
    } else {
        // Handle the form submission for verifying the answer
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $answer = trim($_POST['loginInput']);

            // Fetch the stored answer (A1) for the user
            $stmt = $conn->prepare("SELECT A1 FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($storedAnswer);
            $stmt->fetch();
            $stmt->close();
            // Verify the answer
            if (strtolower($answer) === strtolower($storedAnswer)) {
                // Correct answer, proceed to new_pass.php
                $_SESSION['attempts'] = 0; // Reset attempts on success
                $_SESSION['qa_verified'] = true; // Set verification flag
                header("Location: new_pass.php");
                exit();
            } else {
                // Incorrect answer, increment the attempt count
                $_SESSION['attempts'] = $attempts + 1;
                $_SESSION['last_attempt_time'] = time(); // Update the last attempt time
            
                if ($attempts >= 4) {
                    // After 5 incorrect attempts, show this message
                    $error = "If you forgot the answer, please contact the administrator to recover your account.";
                } else {
                    // For any other number of attempts below 5, just show a generic error
                    $error = "The answer you provided is incorrect. Please try again.";
                }
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
                    <form method="POST" action="fp_auth.php">
                        <div class="div-alert" style="text-align:center;">
                            <?php 
                            if (!empty($error)) {
                                echo "<div style='color:red; font-size:13px; margin-bottom:20px; display:flex; flex-direction:column; justify-content:center; align-items:center;'>";

                                echo "<i class='bx bx-error-circle' style='color:red; font-size:32px; margin-bottom:5px;'></i>";
                                echo "$error";
                                
                                // If the error is specific to account setup, show the back link
                                if ($error === "Oops! Your account is not set up. Please contact the administrator to recover your account.") {
                                    echo "<a href='signin.php' style='font-size:21px; margin-top:10px;'>Back</a>";
                                }

                                echo "</div>";
                            }
                            ?>
                        </div>

                        <!-- Only show question and input field if attempts are less than 8 -->
                        <?php if ($attempts < 8 && !empty($question)): ?>
                            <p><b>Question:</b> <?php echo htmlspecialchars($question); ?></p>
                            <div class="input-field">
                                <input type="text" class="input" name="loginInput" id="int-confg" 
                                       value="<?php echo isset($_POST['loginInput']) ? htmlspecialchars($_POST['loginInput']) : ''; ?>" required>
                                <label for="loginInput">Answer</label> 
                            </div>
                            <div class="input-field">
                                <input type="submit" class="submit" value="Submit" name="login">
                            </div> 
                        <?php endif; ?>

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
    // Show loading screen when form is submitted
    document.getElementById('forgot-password-form').onsubmit = function(event) {
        event.preventDefault(); // Prevent default form submission
        document.getElementById("loading-screen").style.display = "flex";  // Show loading screen
        setTimeout(function() {
            document.getElementById("forgot-password-form").submit(); // Submit the form after the delay
        }, 1000); // Simulate delay of 1 second (can be adjusted)
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
