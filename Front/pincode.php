<?php
// Start the session
session_start();
include '../config.php'; // Include your database connection

// Check if payment number is set in session
if (!isset($_SESSION['p_number'])) {
    // Redirect back to api-net.php if not set
    header("Location: api-net.php");
    exit();
}

// Initialize variables
$successMessage = '';
$errorMessage = '';
$p_number = $_SESSION['p_number'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize user input
    $p_pincode = $_POST['pincode']; // Changed from p_pincode to pincode for consistency

    // Prepare SQL statement to check if pincode matches the stored pincode for the payment number
    $stmt = $conn->prepare("SELECT p_id FROM payment WHERE p_number = ? AND p_pincode = ?");
    $stmt->bind_param("ss", $p_number, $p_pincode); // Changed to 'ss' for string types
    $stmt->execute();
    $stmt->store_result();

    // Check if pincode matches
    if ($stmt->num_rows > 0) {
        // PIN verified successfully, redirect to confirm payment
        $_SESSION['pincode_verified'] = true;
        header("Location: confirm-payment.php");
        exit();
    } else {
        $errorMessage = "Incorrect PIN. Please try again.";
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
    <title>Enter PIN Code</title>
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
        .content {
            padding: 20px;
            text-align: center;
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
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>GCash Payment</h1>
        </div>
        <form action="pincode.php" method="POST">
            <div class="content">
                <h3>Enter PIN Code</h3>
                <div class="login">
                    <input type="text" name="pincode" placeholder="Enter PIN Code" required>
                    <input type="submit" value="Submit" class="button">
                </div>
                <?php if (!empty($successMessage)): ?>
                    <p style="color: green;"><?php echo htmlspecialchars($successMessage); ?></p>
                <?php endif; ?>
                <?php if (!empty($errorMessage)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
                <?php endif; ?>
            </div>
        </form>
    </div>

</body>
</html>
