<?php
session_start();
require_once "../config.php";

/*
// If the user is already logged in, redirect to the home page
if (isset($_SESSION['uid'])) {
    header("Location: home.php");
    exit();
}
*/
if (isset($_POST["login"])) {
    $loginInput = trim($_POST["loginInput"]); // Can be username or email
    $password = $_POST["password"];
    $remember = isset($_POST["remember"]); // Check if 'Remember Me' is selected

    // Check if input is email or username
    if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM users WHERE email = ?";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
    }

    $stmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $loginInput);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user["password"])) {
            // Update the user status to Online
            $updateStatusSql = "UPDATE users SET user_status = 'Online' WHERE uid = ?";
            $updateStatusStmt = mysqli_stmt_init($conn);
            if (mysqli_stmt_prepare($updateStatusStmt, $updateStatusSql)) {
                mysqli_stmt_bind_param($updateStatusStmt, "i", $user['uid']);
                mysqli_stmt_execute($updateStatusStmt);
            }

            // Set session variables
            $_SESSION['uid'] = $user['uid'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['firstname'] = $user['firstname'];

            if ($remember) {
                // Generate a secure token and store it in the database
                $token = bin2hex(random_bytes(16)); // Generate a secure token
                $updateTokenSql = "UPDATE users SET remember_token = ? WHERE uid = ?";
                $updateTokenStmt = mysqli_stmt_init($conn);
                if (mysqli_stmt_prepare($updateTokenStmt, $updateTokenSql)) {
                    mysqli_stmt_bind_param($updateTokenStmt, "si", $token, $user['uid']);
                    mysqli_stmt_execute($updateTokenStmt);
                }

                // Set the cookie to expire in 30 days
                setcookie("remember_me", $token, time() + (30 * 24 * 60 * 60), "/", "", isset($_SERVER['HTTPS']), true);
            } else {
                // Delete the "remember me" cookie if "remember" is not checked
                setcookie("remember_me", "", time() - 3600, "/", "", isset($_SERVER['HTTPS']), true);
            }

            // Redirect to home page
            header("Location: home.php");
            exit();
        } else {
            $error = 'Incorrect Username, Email, or Password';
        }
    } else {
        $error = "Database error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/design00.css">
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <title>ASSRDLDMS | Sign In</title>
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
<script>
        // Prevent back button navigation
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>

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
                        <h1>Welcome Back</h1>
                        <p>Log in to your account and join us for a fulfilling worship experience.</p>
                    </div>
                </div>
                <div class="col-md-6 right">
                    <div class="input-box">
                        <h1 style="margin-bottom:45px;">Sign In</h1>

                        <form method="POST" action="signin.php">
                        <div class="div-alert" style="text-align:center;">
                            <?php if (isset($error)) echo "<div style='color:red; font-size:13px; margin-bottom:20px;'>$error</div>"; ?>
                        </div>
                            <div class="input-field">
                            <input type="text" class="input" name="loginInput" id="int-confg" 
                                value="<?php echo isset($_POST['loginInput']) ? htmlspecialchars($_POST['loginInput']) : ''; ?>" required>
                                <label for="loginInput">Username or Email</label> 
                            </div>

                            <div class="input-field">
                                <input type="password" class="input" name="password" id="pass" required value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>">
                                <label for="pass">Password</label>
                            </div>

                            <div class="input-field">
                                <input type="submit" class="submit" value="Sign In" name="login">
                            </div> 
                        </form>

                        <div class="signin">
                            <a href="forgot_pass.php" class="text-forgotpwd">Forgot Password?</a><br><br>
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
