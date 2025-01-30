<?php
session_start();  // Start session if necessary
include 'sql/config.php'; // Database connection

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $a_number = $_POST['a_number'];

    // Prepare SQL statement to check if payment number exists in the 'payment' table
    $stmt = $conn->prepare("SELECT a_number FROM API WHERE a_number = ?");
    $stmt->bind_param("i", $a_number);
    $stmt->execute();
    $stmt->store_result();

    // Check if payment number exists
    if ($stmt->num_rows > 0) {
        // If payment number exists, store it in session and redirect to the PIN page
        $_SESSION['a_number'] = $a_number;
        header("Location: api-pincode.php");
        exit();
    } else {
        $errorMessage = "Payment number does not exist.";
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
    <title>Enter Payment Number</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        input[type="number"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        p {
            margin: 0;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Enter Your Payment Number</h1>

        <?php if (!empty($errorMessage)): ?>
            <p class="error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <form action="api-check-number.php" method="POST">
            <label for="a_number">Payment Number:</label><br>
            <input type="number" name="a_number" required><br>

            <input type="submit" value="Proceed">
            <a href="api-create-acc.php">Create account</a>
        </form>
    </div>
</body>
</html>
