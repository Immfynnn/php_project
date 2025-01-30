    <?php
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

    // Check if the fee and amount are set in the session
    $fee = isset($_SESSION['fee']) ? floatval(str_replace('PHP', '', $_SESSION['fee'])) : 0;
    $amount = isset($_SESSION['amount']) ? floatval(str_replace('PHP', '', $_SESSION['amount'])) : 0;

    // Calculate the total amount (fee + amount)
    $total_amount = $fee + $amount;

    $a_number = $_SESSION['a_number'];

    // Fetch the payment details based on the payment number
    $stmt = $conn->prepare("SELECT a_id, a_name, a_balance FROM API WHERE a_number = ?");
    $stmt->bind_param("s", $a_number);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($a_id, $a_name, $a_balance);
    $stmt->fetch();
    $stmt->close();

    // If no payment found, handle accordingly
    if (!$a_id) {
        echo "Payment information not found.";
        exit();
    }

    // Display confirmation message
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCash Payment Confirmation</title>
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
            width: 350px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #2c0060; /* Purple header */
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 18px;
            position: relative;
        }
        .header img {
            width: 120px;
            margin-bottom: 10px;
        }
        .payment-section {
            padding: 20px;
        }
        .payment-section h4 {
            color: #2c0060;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .payment-detail {
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }
        .payment-detail strong {
            float: right;
        }
        .total {
            font-size: 18px;
            color: #000;
            margin-bottom: 20px;
            text-align: right;
        }
        .pay-button {
            width: 100%;
            padding: 15px;
            background-color: #0066d5;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .pay-button:hover {
            background-color: #005bb5;
        }/* Loading Screen Styles */
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
                }, 7000); // 5000 milliseconds = 5 seconds
            });
        });
    </script>
</head>
<body>
    <!-- Loading Screen -->
<div id="loading-screen">
    <div class="spinner"></div><br> <!-- Spinner element -->
    <h2 style="margin-left: 10px;">Purchasing....</h2>
</div>

    <div class="container">
        <div class="header">
            <p>GCash</p>
        </div>
        <div class="payment-section">
            <h4>Daanbantayan Chuch RC.</h4>

            <div class="payment-detail">
                <p>Pay With</p>
                <br>
                <p>GCash <strong>PHP <?php echo number_format($a_balance, 2); ?></strong></p> <!-- Balance with comma formatting -->
                <p style="font-size:12px; text-align:right;">
                Available Balance</p>
            </div>

            <h4>YOU ARE ABOUT TO PAY</h4>

            <div class="payment-detail">
                <p>Amount <strong>PHP <?php echo number_format($total_amount, 2); ?></strong></p> <!-- Total amount with formatting -->
            </div>
            <div class="payment-detail">
                <p>Discount <strong>No available voucher</strong></p>
            </div>

            <hr>

            <div class="total">
                <p>Total <strong>PHP <?php echo number_format($total_amount, 2); ?></strong></p> <!-- Total amount with formatting -->
            </div>

            <!-- Payment Form -->
            <form method="POST" action="process_payment.php">
                <input type="hidden" name="a_id" value="<?php echo $a_id; ?>">
                <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
                <button type="submit" class="pay-button">PAY PHP <?php echo number_format($total_amount, 2); ?></button>
            </form>
        </div>
    </div>

    

</body>
</html>

