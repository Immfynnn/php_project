<?php
//pincode-api.php to login the pincode to confirm the payment
// Start the session
session_start();

// Include database connection
include '../config.php'; // Adjust the path as necessary

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php");
    exit();
}

$uid = intval($_SESSION['uid']);


if (!isset($_SESSION['a_number'])) {
    // If payment number is not set in session, redirect back to check-number.php
    header("Location: check-number.php");
    exit();
}

$successMessage = '';
$errorMessage = '';
$a_number = $_SESSION['a_number'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $p_pincode = $_POST['a_pincode'];

    // Prepare SQL statement to check if pincode matches the stored pincode for the payment number
    $stmt = $conn->prepare("SELECT a_id FROM API WHERE a_number = ? AND a_pincode = ?");
    $stmt->bind_param("ii", $a_number, $a_pincode);
    $stmt->execute();
    $stmt->store_result();

    // Check if pincode matches
    if ($stmt->num_rows > 0) {
        // PIN verified successfully, redirect to success page to display p_name and p_balance
        $_SESSION['pincode_verified'] = true;
        header("Location: confirm-payment.php");
        exit();
    } else {
        $errorMessage = "Incorrect PIN. Please try again.";
    }

    // Close the statement
    $stmt->close();
}

$conn->close();


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
        <form action="pincode-api.php" method="POST">
            <div class="content">
                <h3>Enter PIN Code</h3>
                <div class="login">
                    <input type="text" name="a_pincode" placeholder="Enter PIN Code" required>
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
