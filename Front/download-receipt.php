<?php
// Include database connection
include '../config.php';

// Check if the form is submitted and the key exists
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_type'])) {
    // Get the selected payment type
    $paymentType = $_POST['payment_type'];

    // Start output buffering
    ob_start();

    // Set the filename based on the payment type
    $fileName = "Payment_Report_" . str_replace(" ", "_", $paymentType) . ".csv";

    // Query to get the payment details based on the selected type
    $query = "SELECT 
                users.firstname, 
                users.lastname, 
                users.username, 
                reservation.service_type, 
                payment.pay_id, 
                payment.total_amount, 
                payment.p_status, 
                payment.p_date,
                reservation.payment_type   -- Include payment_type from reservation table
              FROM payment
              JOIN reservation ON payment.s_id = reservation.s_id
              JOIN users ON payment.uid = users.uid";

    // Add a filter for payment type if a specific type is selected
    if ($paymentType !== 'Overall') {
        $query .= " WHERE reservation.payment_type = '" . mysqli_real_escape_string($conn, $paymentType) . "'";
    }

    $result = mysqli_query($conn, $query);

    // Initialize variables for total calculations
    $totalPayment = 0;
    $totalPaid = 0;
    $totalRefund = 0;

    // Check for results
    if (mysqli_num_rows($result) > 0) {
        // Open output as a file pointer
        $output = fopen("php://output", "w");

        // Add the title and header information
        fputcsv($output, ['             The Roman Catholic Archdiocese of Cebu']);
        fputcsv($output, ['     Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan']);
        fputcsv($output, ['        Santa Rosa Street, Poblacion, Daanbantayan, Cebu']);
        fputcsv($output, []); // Empty row for spacing
        fputcsv($output, ['                          --PAYMENT--']);
        
        // Loop through the results to calculate totals
        while ($row = mysqli_fetch_assoc($result)) {
            // Add the total_amount to the totalPayment variable
            $totalPayment += (float)$row['total_amount'];

            // Check if the payment is paid or refunded and add to respective totals
            if ($row['p_status'] == 'Paid') {
                $totalPaid += (float)$row['total_amount'];
            } elseif ($row['p_status'] == 'Refunded') {
                $totalRefund += (float)$row['total_amount'];
            }
        }

        // Reset the result pointer to the beginning of the result set for outputting data
        mysqli_data_seek($result, 0);

        // Output the totals: total payment, total paid, and total refunded
        fputcsv($output, ['                   Total Payment: ' . number_format($totalPayment, 2)]);
        fputcsv($output, ['                   Total Payment Paid: ' . number_format($totalPaid, 2)]);
        fputcsv($output, ['                   Total Payment Refund: ' . number_format($totalRefund, 2)]);

        fputcsv($output, []); // Empty row for spacing

fputcsv($output, ['  ______________________________']); 
fputcsv($output, ['    Signature over Printed Name']);
        fputcsv($output, []); // Empty row for spacing

        // Output the column headers with the new order, including Payment Type
        fputcsv($output, ['Receipt No.', 'Full Name', 'Service Type', 'Payment Date', 'Total Amount', 'Payment Type', 'Payment Status']);

        // Output each row of the data with conditional full name
        while ($row = mysqli_fetch_assoc($result)) {
            // If firstname or lastname is blank, use the username
            $fullName = (!empty($row['firstname']) && !empty($row['lastname'])) ? $row['firstname'] . ' ' . $row['lastname'] : $row['username'];

            // Output the row with the new order of columns, including Payment Type
            fputcsv($output, [
                $row['pay_id'],           // Payment ID
                $fullName,                // Full Name or Username
                $row['service_type'],     // Service Type
                $row['p_date'],           // Payment Date
                $row['total_amount'],     // Total Amount
                $row['payment_type'],     // Payment Type
                $row['p_status'],         // Payment Status
            ]);
        }

        // Close the output stream
        fclose($output);

        // Set headers for downloading the file
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Flush the output buffer
        ob_end_flush();
    } else {
        echo "No records found for the selected payment type.";
    }
} else {
    echo "Invalid request or payment type not selected.";
}
?>
