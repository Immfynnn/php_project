<?php
session_start();
include '../config.php'; // DB connection file

// Function to get payment and service details
function getPaymentAndServiceDetails($conn, $s_id) {
    $query = "
        SELECT 
            s.service_type, s.s_fee, s.s_ammount, s.s_date,
            a.a_name, a.a_number, a.a_type, u.email, p.p_status, p.p_date, p.pay_id, p.total_amount
        FROM services s
        JOIN payment p ON s.s_id = p.s_id
        JOIN API a ON p.a_id = a.a_id
        JOIN users u ON s.uid = u.uid
        WHERE s.s_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $s_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc(); // Return payment and service details
}

// Get the service ID from the session or URL
$s_id = $_GET['s_id'] ?? $_SESSION['s_id']; 

// Fetch the details
$payment_data = getPaymentAndServiceDetails($conn, $s_id);

if ($payment_data) {
    // Assign values to variables
    $service_type = $payment_data['service_type'];
    $s_fee = $payment_data['s_fee'];
    $total_amount = $payment_data['total_amount'];
    
    // Format p_date to include both date and time
    $formatted_date = date("F j, Y, g:i A", strtotime($payment_data['p_date'])); // Example: "October 22, 2024, 3:45 PM"
    
    $a_name = $payment_data['a_name'];
    $a_number = $payment_data['a_number'];
    $a_type = $payment_data['a_type'];
    $email = $payment_data['email'];
    $p_status = $payment_data['p_status'];
    $pay_id = $payment_data['pay_id'];
} else {
    echo "No payment details found.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .receipt-container {
            background-color: #fff;
            width: 350px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #0066d1;
            color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 18px;
        }
        .header p {
            margin: 0;
        }
        .receipt-body {
            padding: 20px;
        }
        .payment-received {
            text-align: center;
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
        }
        .payment-info {
            text-align: center;
        }
        .payment-info .amount {
            font-size: 24px;
            color: #333;
        }
        .payment-info .subtext {
            font-size: 12px;
            color: #777;
        }
        .details {
            background-color: #f7f7f7;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .details p {
            font-size: 14px;
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            padding: 10px;
            color: #999;
            font-size: 12px;
        }
        .footer a {
            color: #0066d1;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="header">
        <p>Payment</p>
    </div>
    <div class="receipt-body">
        <div class="payment-received">
            <p>Payment Received</p>
        </div>
        <div class="payment-info">
            <p><?php echo htmlspecialchars($a_name); ?></p>
            <p class="amount">PHP <?php echo number_format($total_amount, 2); ?><br> <b style="font-size:16px; font-weight:500;"> using your <?php echo htmlspecialchars($a_type); ?> </b></p>
            <p class="subtext"></p>
        </div>
        <div class="details">
            <p>Service: <?php echo htmlspecialchars($service_type); ?></p>
            <p>Ref. No: <?php echo htmlspecialchars($pay_id); ?></p>
            <p>Date: <?php echo htmlspecialchars($formatted_date); ?></p>
            <hr>
            <p>Amount Paid: <?php echo htmlspecialchars($payment_data['s_ammount']); ?></p>
            <p>Fee: <?php echo htmlspecialchars($s_fee); ?></p>
            <p>Account Number: <?php echo htmlspecialchars($a_number); ?></p>
            <p>Email: <?php echo htmlspecialchars($email); ?></p>
            <p>Status: <?php echo htmlspecialchars($p_status); ?></p>
        </div>
    </div>
    <div class="footer">
        <p>This has been processed and your payment will be posted within 24 hours.</p>
        <p><a href="#">GCash Pay Bills</a></p>
        <a href="reservation-process.php">Done</a>
    </div>
</div>

</body>
</html>
