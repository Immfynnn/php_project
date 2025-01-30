<?php
// confirm-payment.php after pincode-api then confirm the payment

session_start();
include '../config.php'; // Adjust the path as necessary

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php");
    exit();
}

$uid = intval($_SESSION['uid']);

// Check if PIN verification was successful
if (!isset($_SESSION['pincode_verified']) || !isset($_SESSION['a_number'])) {
    // Redirect to the PIN verification page if the session is not set
    header("Location: api-pincode.php");
    exit();
}

$a_number = $_SESSION['a_number'];
$successMessage = '';
$errorMessage = '';

function getPaymentDetails($conn, $a_number) {
    // Query to fetch payment details
    $query = "SELECT a.a_balance, s.s_ammount, s.s_fee
              FROM API a
              JOIN services s ON a.a_id = s.a_id
              WHERE a.a_number = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $a_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Return the first matching row
    } else {
        return null; // No results found
    }
}

// Get payment details from the database
$paymentDetails = getPaymentDetails($conn, $a_number);

// Check if payment details were retrieved
if ($paymentDetails) {
    $_SESSION['balance'] = $paymentDetails['a_balance'];
    $_SESSION['amount_due'] = floatval(str_replace('PHP', '', $paymentDetails['s_ammount'])) + floatval(str_replace('PHP', '', $paymentDetails['s_fee']));
} else {
    $errorMessage = "Payment details not found.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Payment</title>
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
        .button {
            width: 100%;
            padding: 10px;
            background-color: #add6ff;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #82c4ff;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Confirm Payment</h1>
        </div>
        <div class="content">
            <?php if ($paymentDetails): ?>
                <h3>Amount Due: PHP <?php echo number_format($_SESSION['amount_due'], 2); ?></h3>
                <h3>Your Balance: PHP <?php echo number_format($_SESSION['balance'], 2); ?></h3>
            <?php else: ?>
                <p style="color: red;"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
            
            <form action="" method="POST">
                <input type="submit" name="proceed" value="Proceed" class="button">
            </form>
            <?php if (!empty($successMessage)): ?>
                <p style="color: green;"><?php echo $successMessage; ?></p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
