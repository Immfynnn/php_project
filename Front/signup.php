<?php
session_start();

$errors = [
    'username' => '',
    'email' => '',
    'password' => ''
];

$success = false; // Add this variable to track success

if (isset($_POST["submit"])) {
    $username = htmlspecialchars($_POST["username"]);
    $email = htmlspecialchars($_POST["email"]);
    $password = htmlspecialchars($_POST["password"]);
    $confirmpassword = htmlspecialchars($_POST["confirmpassword"]);
    $gender = htmlspecialchars($_POST["gender"]);

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Validation
if (!preg_match('/^\S+$/', $username)) {
    $errors['username'] = "Invalid Username";
}
if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    $errors['username'] = "Username can only contain letters and numbers, without special characters.";
}
if (strlen($password) < 8) {
    $errors['password'] = "Password must be at least 8 characters long";
}
if ($password !== $confirmpassword) {
    $errors['password'] = "Passwords do not match";
}
if (strrpos($username, "@gmail.com") !== false) {
    $errors['username'] = "Username cannot contain @gmail.com";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || 
    !preg_match('/@(gmail\.com|yahoo\.com|yourcompanydomain\.com)$/', $email)) {
    $errors['email'] = "Email must be a valid @gmail.com, @yahoo.com, or @yourcompanydomain.com address.";
}

    require_once "../config.php";

    // Check if username or email already exists in the database
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['username'] == $username) {
                    $errors['username'] = "Username already exists";
                }
                if ($row['email'] == $email) {
                    $errors['email'] = "Email is already in use";
                }
            }
        }
    } else {
        $errors['username'] = "Database error: " . mysqli_error($conn);
    }

    // If no errors, insert the new user into the database
    if (empty($errors['username']) && empty($errors['email']) && empty($errors['password'])) {
        $sql = "INSERT INTO users (username, gender, email, password) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if (mysqli_stmt_prepare($stmt, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $username, $gender, $email, $passwordHash);
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                $_SESSION['success'] = true;
            } else {
                $errors['username'] = "Failed to insert user: " . mysqli_stmt_error($stmt);
            }
        } else {
            $errors['username'] = "Database error: " . mysqli_error($conn);
        }
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
    <title>ASSRDLDMS | Sign up</title>

    <style>
        /* Overlay Styles */
        .overlay {
            display: flex; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            opacity: 1;
            animation: fadeIn 0.5s forwards;
        }

        .overlay-content {
            background-color: #0e0d1f;
            color:#fff;
            display:flex;
            justify-content:center;
            align-items:center;
            flex-direction:column;
            padding: 20px;
            height:380px;
            border-radius: 10px;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
            box-shadow:0 0 50px rgba(255,255,255,.2);
        }

        .overlay h4 {
            margin-bottom: 20px;
            color:  #28a745;
            font-weight:700;
            font-size: 24px;
        }
        .overlay p {
            margin:50px;
        }

        .overlay i {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
            text-shadow:0 0 25px  #28a745;
        }

        .overlay button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .overlay button:hover {
            background-color: #218838;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
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
        <a href="signin.php" class="anim8">Sign In</a>
        <a href="index.php#services" id="ser-txt" class="text5">Services</a>
    </nav>
</header>

    <!-- Overlay for success message -->
    <?php if ($success || isset($_SESSION['success'])): ?>
    <div id="overlay" class="overlay">
        <div class="overlay-content">
        <i class="bx bx-check-circle"></i> <!-- Success icon -->
            <h4>Registration Successful!</h4>
            <p>Your account has been created and you can now log in.</p>
            <button id="close-overlay">Close</button>
        </div>
    </div>
    <?php endif; ?>

<script>
    // JavaScript to handle closing of the overlay
    document.getElementById("close-overlay").addEventListener("click", function() {
        window.location.href = "signin.php"; // Redirect to signin.php
    });

    // Automatically show the overlay if the session variable is set
    <?php if (isset($_SESSION['success']) && $_SESSION['success']) : ?>
        document.getElementById("overlay").style.display = "flex";
        <?php unset($_SESSION['success']); // Clear the session variable after use ?>
    <?php endif; ?>
</script>

<div class="wrapper">
    <div class="container main">
        <div class="row" data-aos="fade-up">
            <div class="col-md-6 side-image">
                <img src="mainbg.jpg" alt="" class="imgs-cnfg">
                <div class="text">
                    <h1>Hello!</h1>
                    <p>We’re excited to welcome you! Register now and be part of our community.</p>
                </div>
            </div>
            <div class="col-md-6 right">
                <div class="input-box">
                    <form action="signup.php" method="post">
                        <h1>Create Account</h1>
                        
                        <?php if ($errors['username']): ?>
                            <p style="color:red; font-size:13px; text-align:center;"> <?php echo htmlspecialchars($errors['username']); ?> </p>
                        <?php endif; ?>

                        <div class="input-field">
                            <input type="text" class="input" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required autocomplete="off">
                            <label for="username">Username</label> 
                        </div> 

                        <div class="input-field">
                            <select name="gender" id="gender" required>
                                <option value="">Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            <i class='bx bxs-chevron-down'></i>
                        </div>

                        <?php if ($errors['email']): ?>
                            <p style="color:red; font-size:13px; margin-right:70px;"> <?php echo htmlspecialchars($errors['email']); ?> </p>
                        <?php endif; ?>

                        <div class="input-field">
                            <input type="email" class="input" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required autocomplete="off">
                            <label for="email">Email</label> 
                        </div> 

                        <?php if ($errors['password']): ?>
                            <p style="color:red; font-size:13px; margin-right:70px;"> <?php echo htmlspecialchars($errors['password']); ?> </p>
                        <?php endif; ?>

                        <div class="input-field">
                            <input type="password" class="input" name="password"  value="<?php echo htmlspecialchars($password ?? ''); ?>" id="pass" required>
                            <label for="pass">Password</label>
                        </div>
                        <div class="input-field">
                            <input type="password" class="input" name="confirmpassword"  value="<?php echo htmlspecialchars($confirmpassword ?? ''); ?>" id="pass" required>
                            <label for="pass">Confirm Password</label>
                        </div>

                        <div class="terms">
                            <p>
                                By signing up, you agree to our 
                                <a href="terms&conditions.html" target="_blank">Terms and Conditions</a>.
                            </p>
                        </div>

                        <div class="input-field">
                            <input type="submit" class="submit" name="submit" value="Sign Up">
                        </div>

                        <div class="signin">
                            <span>Already have an account? <a href="signin.php">Signin here</a></span>
                        </div>
                    </form>
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
        duration: 1000,  
        once: true,     
    });
</script>

</body>
</html>
