<?php
session_start();
if (isset($_POST["submit"])) {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmpassword = $_POST["confirmpassword"];
    $gender = $_POST["gender"];
    $errors = '';

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Validation
    if (strlen($password) < 8) {
        $errors = "Password must be at least 8 characters long";
    }
    if ($password !== $confirmpassword) {
        $errors = "Passwords do not match";
    }
    if (strrpos($username, "@gmail.com") !== false) {
        $errors = "Username cannot contain @gmail.com";
    }
    if (!in_array($gender, ["Male", "Female", "Others"])) {
        $errors = "Please select a valid gender";
    }
    
    require_once "../config.php";

    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['username'] == $username) {
                    $errors = "Username already exists";
                }
                if ($row['email'] == $email) {
                    $errors = "Email is already in use";
                }
            }
        }
    } else {
        $errors[] = "Database error: " . mysqli_error($conn);
    }

    if (empty($errors)) {
        // Insert new user
        $sql = "INSERT INTO users (username, gender, email, password) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if (mysqli_stmt_prepare($stmt, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $username, $gender, $email, $passwordHash);
            if (mysqli_stmt_execute($stmt)) {
                // Show alert and redirect after alert is closed
                echo '<script>
                        alert("Account Has Been Successfully Created!");
                        window.location.href = "signin.php";
                      </script>';
                exit();
            } else {
                $errors = "Failed to insert user: " . mysqli_stmt_error($stmt);
            }
        } else {
            $errors = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASSRDLDMS | Sign up</title>

    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="website icon" type="png" href="css/img/LOGO.png" id="logo">
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
    <a href="#"><img src="css/img/LOGO.png" alt="Logo" style="width: 100px; border-radius:50px;"></a>
     
      <input type="checkbox" id="check">
      <label for="check" class="icons">
      <i class='bx bx-menu' id="menu-icon"></i>
      <i class='bx bx-x' id="close-icon"></i>
      </label> 

      <nav>
        <a href="index.php#home">Home</a>    
        <a href="index.php#about">About us</a>
        <a href="index.php#contact">Contact</a>
        <a href="signin.php">Sign In</a>
        <a href="index.php#services" id="ser-txt" class="text5">Services</a>
    </nav>

    </header>

    <section>
    <img src="css/img/mainbg.jpg" alt="background" style='opacity: .3;'>

    <div class="signup-cont">

        <div class="fadeUp" id="cont-signup" style="height:480px;">
            
            <div class="signup-title">
                <h1>Welcome Back</h1>
               <p>Enter your Personal Details to use all of the <br>Features</p>
                <div class="to-sign">
                    <a href="signin.php">Sign In</a>
                </div>
            </div>
            <div class="input-div">
                <div class="title-txt-s">
                    <h1>Sign Up</h1>
                </div>
                <form action="signup.php" method="post">
                <div class="input-lay2">
                <?php if (!empty($errors)): ?>
                   <p style="color:red; font-size:13px; margin-right:70px;"> <?php echo htmlspecialchars($errors); ?> </p>
                   <?php endif; ?>
                <input type="text" name="username" id="" value="<?php if(isset($_POST['username'])) echo $_POST['username']; ?>" placeholder="Username" required>
                
            <select id="gender" name="gender" required>
                <option value="">Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Female">Others</option>
            </select>

                <input type="email" name="email" id="" value="<?php if(isset($_POST['email'])) echo $_POST['email']; ?>" placeholder="Email Address" required>
              
                <input type="password" name="password" id="" placeholder="Password" required>
                <input type="password" name="confirmpassword" id="" placeholder="Confirmed Password" required>
                </div>

                <p class="agreement"><input type="checkbox" name="" id="agree" required> I agree the term and Conditions.</p>
                <div class="btn-div">
                    <input type="submit" value="SIGN UP" id="button" name="submit">
                    </form>
                </div>
            </div>

        </div>

    </div>
    <br>
    <br>
    </section>
    <footer>
        <h1>ALA WABALO</h1>
    </footer>
</body>
</html>