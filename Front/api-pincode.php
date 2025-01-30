<?php
session_start();
include 'sql/config.php'; // Database connection

if (!isset($_SESSION['a_number'])) {
    // If payment number is not set in session, redirect back to check-number.php
    header("Location: check-number.php");
    exit();
}

$successMessage = '';
$errorMessage = '';
$a_number = $_SESSION['a_number'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $a_pincode = $_POST['a_pincode'];

    // Prepare SQL statement to check if pincode matches the stored pincode for the payment number
    $stmt = $conn->prepare("SELECT a_id FROM API WHERE a_number = ? AND a_pincode = ?");
    $stmt->bind_param("ii", $a_number, $a_pincode);
    $stmt->execute();
    $stmt->store_result();

    // Check if pincode matches
    if ($stmt->num_rows > 0) {
        // PIN verified successfully, redirect to success page to display p_name and p_balance
        $_SESSION['pincode_verified'] = true;
        header("Location: api-home.php");
        exit();
    } else {
        $errorMessage = "Incorrect PIN. Please try again.";
    }

    // Close the statement
    $stmt->close();
}

$conn->close();  // Close database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter PIN Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin-top: 50px;
        }
        h1 {
            color: #333;
        }
        input[type="password"], input[type="submit"] {
            padding: 10px;
            margin: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: #fff;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Enter Your PIN Code</h1>

    <?php if (!empty($errorMessage)): ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <form action="api-pincode.php" method="POST">
        <label for="a_pincode">PIN Code (4 digits):</label><br>
        <input type="password" name="a_pincode" pattern="\d{4}" required><br><br>

        <input type="submit" value="Verify PIN">
    </form>
</body>
</html>
