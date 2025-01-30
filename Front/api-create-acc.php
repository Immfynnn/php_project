<?php
session_start();  // Start session if necessary
include 'sql/config.php'; // Database connection

// Function to generate a random balance starting from 10,000 (range: 10,000 - 99,999)
function generateRandomBalance() {
    return rand(10000, 99999);  // Generates a random 5-digit number starting at 10,000
}

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $a_name = $_POST['a_name'];
    $a_number = $_POST['a_number'];
    $a_pincode = $_POST['a_pincode'];
    $confirm_pincode = $_POST['confirm_pincode'];

    // Check if the entered PIN code and confirmation PIN code match
    if ($a_pincode === $confirm_pincode) {
        // Validate that the payment number is exactly 11 digits
        if (preg_match("/^\d{11}$/", $a_number)) {
            // Generate random balance
            $a_balance = generateRandomBalance();

            // Prepare SQL statement to insert into 'payment' table
            $stmt = $conn->prepare("INSERT INTO API (a_name, a_number, a_balance, a_pincode) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $a_name, $a_number, $a_balance, $a_pincode);

            // Execute the query and check for success
            if ($stmt->execute()) {
                $successMessage = "Account payment created successfully!";
                header("Location: api-check-number.php");
                exit();
            } else {
                $errorMessage = "Error creating payment account: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            $errorMessage = "Payment number must be exactly 11 digits.";
        }
    } else {
        $errorMessage = "PIN code and confirm PIN code do not match.";
    }
}

$conn->close();  // Close database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Payment Account</title>
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
        input[type="text"],
        input[type="password"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        p {
            margin: 0;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Gcash Account</h1>

        <?php if (!empty($successMessage)): ?>
            <p class="success"><?php echo $successMessage; ?></p>
        <?php elseif (!empty($errorMessage)): ?>
            <p class="error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <form action="api-create-acc.php" method="POST">
            <label for="a_name">Name:</label><br>
            <input type="text" name="a_name" required><br>

            <label for="a_number">Payment Number (11 digits):</label><br>
            <input type="text" name="a_number" pattern="\d{11}" title="Please enter exactly 11 digits." required><br>

            <label for="a_pincode">PIN Code (4 digits):</label><br>
            <input type="password" name="a_pincode" pattern="\d{4}" required><br>

            <label for="confirm_pincode">Confirm PIN Code:</label><br>
            <input type="password" name="confirm_pincode" pattern="\d{4}" required><br>

            <input type="submit" value="Create Payment Account">
        </form>
    </div>
</body>
</html>
