<?php
session_start();
include 'sql/config.php'; // Database connection

// Check if PIN verification was successful
if (!isset($_SESSION['pincode_verified']) || !isset($_SESSION['a_number'])) {
    // Redirect to the PIN verification page if the session is not set
    header("Location: api-pincode.php");
    exit();
}

$a_number = $_SESSION['a_number'];

// Prepare SQL statement to get the name and balance
$stmt = $conn->prepare("SELECT a_name, a_balance FROM API WHERE a_number = ?");
$stmt->bind_param("i", $a_number);
$stmt->execute();
$stmt->bind_result($a_name, $a_balance);
$stmt->fetch();
$stmt->close();

// Format balance with commas and add "Php"
$formatted_balance = "Php " . number_format($a_balance, 0, '.', ',');

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #007bff;
        }
        p {
            font-size: 1.2em;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        a:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Account Details</h1>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($a_name); ?></p>
        <p><strong>Balance:</strong> <?php echo $formatted_balance; ?></p>
        <a href="#" style="background:green;">Cash in</a>
        <a href="#" style="background:blue;">Cash Out</a>
        <a href="api-logout.php">Logout</a> <!-- Logout link -->
    </div>
</body>
</html>
