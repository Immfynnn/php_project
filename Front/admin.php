<?php
// admin.php

session_start(); // Start the session to manage user login state
require_once '../config.php'; // Include your database connection file

$error_message = ''; // Initialize an error message variable

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sanitize input
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // Hash the password using SHA-256 to match the hash stored in the database
    $hashed_password = hash('sha256', $password);

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT admin_id FROM admins WHERE admin_username = ? AND admin_password = ?");
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Fetch admin ID and set session variables
        $row = $result->fetch_assoc();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $row['admin_id']; // Store admin ID in session

        // Update the admin's status to 'Online'
        $admin_id = $row['admin_id'];
        $update_stmt = $conn->prepare("UPDATE admins SET admin_active_status = 'Online' WHERE admin_id = ?");
        $update_stmt->bind_param("i", $admin_id);
        $update_stmt->execute();
        $update_stmt->close();

        header("Location: admin_dashboard.php"); // Redirect to admin dashboard
        exit();
    } else {
        // Login failed, set error message
        $error_message = "Invalid Username or Password";
    }

    $stmt->close();
    $conn->close();
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
  <div class="fadeUp" id="cont-admin" style="height:400px;">
    <div class="dv-left">
        <img src="css/img/icon-log.jpg" alt="logo" id="mainLog">
    </div>
    <div class="dv-right">
        <div class="text-log">
            <h1>Admin</h1>
        </div>
        <?php if ($error_message): ?>
            <p style="color: red; font-size:13px;font-weight: bold;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="admin.php" method="post">
        <div class="input-log">
            <input type="text" name="username" placeholder="Username" required>
        </div>

        <div class="input-log">
            <input type="password" name="password" placeholder="Password">
        </div> 
        <br>
        <div class="input-button">
            <input type="submit" value="Login" name="submit">
        </div>
        </form>
        <br>
        <a href="admin-forgot-pass.php">Forgot Password?</a>
    </div>
  </div>
   </div>
</section>
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
.text-log h1 {
    font-weight: 800;
    text-transform: uppercase;
    cursor: default;
    box-shadow: none;
    transition: all ease .5s;
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
#cont-admin a {
    color: rgba(95, 95, 230, 0.801);
    text-decoration: none;
    transition: all ease .5s;
}
#cont-admin a:hover {
    text-decoration: underline;
}
#cont-admin:hover h1 {
    transition: all ease .5s;
    text-shadow: #02c712 0 0 7px;
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

@media screen and (max-width: 480px) {
    #cont-admin {
        width: 90%;
    }
    #mainLog {
        width: 150px;
        height: 150px;
    }
    .text-log {
        font-size: 18px;
    }
    .input-log input, .input-button input {
        width: 100%;
        font-size: 14px;
    }
}
@media screen and (max-width:1244px) {
    #mainLog {
        width: 70%; 
        height: 70%;
        margin-left:-5px;
    }
}
@media screen and (max-width:1031px) {
    #mainLog {
        width: 70%; 
        height: 70%;
        margin-left:-10px;
    }
}
</style>

</body>
</html>
