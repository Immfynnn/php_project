<?php
// api-net-pincode.php - Checking if the entered pincode matches the payment number to log in

// Start the session
session_start();

// Include the database connection
include 'sql/config.php'; // Adjust the path as necessary

// Check if the user is logged in and the payment number exists in the session
if (!isset($_SESSION['uid'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php");
    exit();
}

$uid = intval($_SESSION['uid']);

// Handle form submission
$successMessage = '';
$errorMessage = '';

$fee = isset($_SESSION['fee']) ? floatval(str_replace('PHP', '', $_SESSION['fee'])) : 0;
$amount = isset($_SESSION['amount']) ? floatval(str_replace('PHP', '', $_SESSION['amount'])) : 0;

// Calculate the total amount (fee + amount)
$total_amount = $fee + $amount;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input from the form
    $a_number = $_POST['a_number'];

    // Prepare SQL statement to check if payment number exists in the 'API' table
    $stmt = $conn->prepare("SELECT a_id FROM API WHERE a_number = ?");
    $stmt->bind_param("s", $a_number);
    $stmt->execute();
    $stmt->store_result();
    
    // Check if payment number exists
    if ($stmt->num_rows > 0) {
        // Fetch the a_id (API ID) associated with the payment number
        $stmt->bind_result($a_id);
        $stmt->fetch();

        // Store the a_number and a_id in the session
        $_SESSION['a_number'] = $a_number;
        $_SESSION['a_id'] = $a_id;

        // Check if payment record already exists
        $s_id = isset($_SESSION['s_id']) ? intval($_SESSION['s_id']) : null; // Get service ID from session
        $checkPaymentStmt = $conn->prepare("SELECT * FROM payment WHERE uid = ? AND a_id = ? AND s_id = ?");
        $checkPaymentStmt->bind_param("iii", $uid, $a_id, $s_id);
        $checkPaymentStmt->execute();
        $checkPaymentStmt->store_result();

        if ($checkPaymentStmt->num_rows == 0) {
            // No existing record, insert new payment information into the 'payment' table
            $p_status = 'Pending'; // Default status
            
            $payment_stmt = $conn->prepare("INSERT INTO payment (uid, a_id, s_id, total_amount, p_status) VALUES (?, ?, ?, ?, ?)");
            $payment_stmt->bind_param("iiiss", $uid, $a_id, $s_id, $total_amount, $p_status);

            if ($payment_stmt->execute()) {
                // Payment record successfully inserted
                $successMessage = "Payment successful. Your payment is now pending.";
            } else {
                $errorMessage = "Failed to process payment. Please try again.";
            }

            // Close the payment statement
            $payment_stmt->close();
        } else {
            // Payment record already exists
            $errorMessage = "Payment has already been processed for this service.";
        }

        // Close the check payment statement
        $checkPaymentStmt->close();

        // Redirect to the PIN entry page
        header("Location: api-net-pincode.php");
        exit();
    } else {
        // Payment number does not exist
        $errorMessage = "Payment number does not exist.";
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
    <title>GCash Payment</title>
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
            position: relative;
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
        .content .amount {
            font-size: 24px;
            color: #0066d5;
            margin-bottom: 20px;
        }
        .login {
            margin-bottom: 20px;
        }
        .login input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #cccccc;
            border-radius: 5px;
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
        .register {
            font-size: 14px;
            color: #999999;
        }
        .register a {
            color: #0066d5;
            text-decoration: none;
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
    <h2 style="margin-left: 10px;">Checking Your Gcash Number...</h2>
</div>

<div class="container">
    <div class="header">
        <img src="css/img/log-gcash1.png" alt="GCash Logo">
    </div>
    <form action="" method="POST">
        <div class="content">
            <h3>Merchant</h3>
            <div class="merchant">DBRESERVATION</div>
            <h3>Amount Due</h3>
            <div class="amount">
                PHP <?php echo number_format($total_amount, 2); ?>
            </div>
            <div class="login">
                <h3>Login to pay with GCash</h3>
                <input type="text" name="a_number" placeholder="+63 Mobile number" required>
                <input type="submit" value="Proceed" class="button">
            </div>
            <div class="register">
                Don't have a GCash account? <a href="#">Register now</a>
            </div>
            <?php if (!empty($errorMessage)): ?>
                <p style="color: red;"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <p style="color: green;"><?php echo $successMessage; ?></p>
            <?php endif; ?>
        </div>
    </form>
</div>

</body>
</html>
