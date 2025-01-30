<?php
include '../config.php'; // Include your database connection file

$message = ''; // Initialize message variable
$error_message = ''; // Initialize error variable

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form inputs
    $f_name = trim($_POST['f_name']);
    $f_gmail = trim($_POST['f_gmail']);
    $f_content = trim($_POST['f_content']);

    // Validate inputs
    if (empty($f_name) || empty($f_gmail) || empty($f_content)) {
        $error_message = 'All fields are required.';
    } elseif (!filter_var($f_gmail, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email format.';
    } else {
        // Check if the email already exists
        $sqlCheckEmail = "SELECT * FROM feedback WHERE f_gmail = ?";
        $stmtCheckEmail = $conn->prepare($sqlCheckEmail);
        $stmtCheckEmail->bind_param('s', $f_gmail);
        $stmtCheckEmail->execute();
        $resultCheckEmail = $stmtCheckEmail->get_result();

        if ($resultCheckEmail->num_rows > 0) {
            $error_message = 'This email has already submitted feedback.';
        } else {
            // Insert feedback into the database
            $sqlInsertFeedback = "INSERT INTO feedback (f_name, f_gmail, f_content) VALUES (?, ?, ?)";
            $stmtInsertFeedback = $conn->prepare($sqlInsertFeedback);
            $stmtInsertFeedback->bind_param('sss', $f_name, $f_gmail, $f_content);

            if ($stmtInsertFeedback->execute()) {
                $message = 'Feedback submitted successfully.';

                // Retrieve the last inserted feedback ID
                $feedback_id = $stmtInsertFeedback->insert_id;

                // Notify all admins
                $admin_query = $conn->query("SELECT admin_id FROM admins");
                while ($admin = $admin_query->fetch_assoc()) {
                    $admin_id = $admin['admin_id'];
                    $notification_message = "New feedback from $f_name. Check now!";

                    $notif_stmt = $conn->prepare("INSERT INTO notification_admin (admin_id, f_id, message_noti) VALUES (?, ?, ?)");
                    $notif_stmt->bind_param("iis", $admin_id, $feedback_id, $notification_message);
                    $notif_stmt->execute();
                    $notif_stmt->close();
                }
            } else {
                $error_message = 'Failed to submit feedback. Please try again.';
            }

            $stmtInsertFeedback->close();
        }

        $stmtCheckEmail->close();
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
.feedback-container {
    max-width: 600px;
    margin: auto;
    margin-top:30px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h2 {
    text-align: center;
    color: #333;
}

.input-group {
    margin-bottom: 15px;
}

.input-group label {
    display: block;
    margin-bottom: 5px;
    color: #555;
}

.input-group input,
.input-group textarea {
    width: 96%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.input-group textarea {
    resize: vertical;
}

.submit-btn {
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.submit-btn:hover {
    background-color: #0056b3;
}

.alert-sent-success {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    margin-bottom: 15px;
}


.alert-sent-failed {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    margin-bottom: 15px;
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
    <div class="feedback-container">
        <h2>Feedback Form</h2>

        <?php if (!empty($message)): ?>
            <div class="alert-sent-success">
                <h5><?php echo htmlspecialchars($message); ?></h5>
            </div>
        <?php elseif (!empty($error_message)): ?>
            <div class="alert-sent-failed">
                <h5><i class='bx bxs-error-circle'></i><?php echo htmlspecialchars($error_message); ?></h5>
            </div>
        <?php endif; ?>

        <form action="feedback.php" method="POST">
            <div class="input-group">
                <label for="f_name">Name:</label>
                <input type="text" name="f_name" id="f_name" required>
            </div>

            <div class="input-group">
                <label for="f_gmail">Email:</label>
                <input type="email" name="f_gmail" id="f_gmail" required>
            </div>

            <div class="input-group">
                <label for="f_content">Feedback:</label>
                <textarea name="f_content" id="f_content" rows="5" required></textarea>
            </div>

            <button type="submit" class="submit-btn">Submit Feedback</button>
            <a href="index.php">Back</a>
        </form>
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
