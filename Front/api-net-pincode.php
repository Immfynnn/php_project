<?php
// api-net-pincode.php - Checking if the entered pincode matches the payment number to log in

// Start the session
session_start();


// Include the database connection
include 'sql/config.php'; // Adjust the path as necessary

// Check if the user is logged in and the payment number exists in the session
if (!isset($_SESSION['uid']) || !isset($_SESSION['a_number'])) {
    // Redirect to login page if not logged in or payment number is missing
    header("Location: signin.php");
    exit();
}

$uid = intval($_SESSION['uid']);
$a_number = $_SESSION['a_number'];

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $a_pincode = isset($_POST['a_pincode']) ? trim($_POST['a_pincode']) : '';

    // Prepare SQL statement to verify the pincode for the given payment number
    $stmt = $conn->prepare("SELECT a_pincode FROM API WHERE a_number = ? AND a_pincode = ?");
    $stmt->bind_param("si", $a_number, $a_pincode); // 's' for string (p_number), 'i' for integer (p_pincode)
    $stmt->execute();
    $stmt->store_result();

    // Check if pincode matches
    if ($stmt->num_rows > 0) {
        // Pincode is correct, redirect to the next step (e.g., payment confirmation)
        $_SESSION['logged_in'] = true;
        header("Location: payment-confirmation.php");
        exit();
    } else {
        $errorMessage = "Invalid pincode. Please try again.";
    }

    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCash Pincode</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            width: 360px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #0066d5;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .content h3 {
            font-size: 18px;
            color: #999999;
            margin-bottom: 10px;
        }
        .login {
            margin-bottom: 20px;
        }
        .login input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size:40px;
            text-align:center;
        }
        .login .button {
            width: 100%;
            padding: 10px;
            background-color: #add6ff;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .login .button:hover {
            background-color: #82c4ff;
        }
         /* Loading Screen Styles */
         #loading-screen {
            position: fixed; /* Fixed position to cover the entire viewport */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white */
            display: flex;
            justify-content: center;
            flex-direction:column;
            align-items: center;
            z-index: 1000; /* Ensure it's above other elements */
            visibility: hidden; /* Initially hidden */
            opacity: 0; /* Initially fully transparent */
            transition: visibility 0s 0.5s, opacity 0.5s linear; /* Fade out after 0.5s */
        }

        #loading-screen.visible {
            visibility: visible; /* Show it */
            opacity: 1; /* Fade in */
            transition: visibility 0s 0s, opacity 0.5s linear; /* Fade in */
        }

        /* Spinner Styles */
        .spinner {
            border: 8px solid #f3f3f3; /* Light gray */
            border-top: 8px solid #3498db; /* Blue */
            border-radius: 50%; /* Circle */
            width: 50px; /* Size of the spinner */
            height: 50px; /* Size of the spinner */
            animation: spin 1s linear infinite; /* Spin animation */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        // JavaScript to show loading screen when the form is submitted
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent immediate form submission
                const loadingScreen = document.getElementById('loading-screen');
                loadingScreen.classList.add('visible'); // Show the loading screen

                // Wait for 5 seconds before submitting the form
                setTimeout(function() {
                    form.submit(); // Submit the form after 5 seconds
                }, 3000); // 5000 milliseconds = 5 seconds
            });
        });
    </script>
</head>
<body>
    <!-- Loading Screen -->
<div id="loading-screen">
    <div class="spinner"></div><br> <!-- Spinner element -->
    <h2 style="margin-left: 10px;">Loading, Please Wait....</h2>
</div>

    <div class="container">
        <div class="header">
            <img src="css/img/log-gcash1.png" alt="GCash Logo">
        </div>
        <form action="" method="POST">
            <div class="content">
                <h3>Enter your GCash Pincode</h3>
                <div class="login">
                    <input type="password" name="a_pincode" placeholder="Enter Pincode" required>
                    <input type="submit" value="Login" class="button">
                </div>
                <?php if (!empty($errorMessage)): ?>
                    <p style="color: red;"><?php echo $errorMessage; ?></p>
                <?php endif; ?>
            </div>
        </form>
    </div>

</body>
</html>
