<?php
// Assuming a valid connection is already established in $conn
require_once "../config.php";

// Check if the s_id is passed in the URL or POST
$s_id = $_GET['s_id'] ?? $_POST['s_id'] ?? null; // Retrieve s_id from either GET or POST

// Ensure s_id is provided
if (!$s_id) {
    echo "Reservation ID is missing.";
    exit();
}

// Fetch reservation details from the database
$stmt = $conn->prepare("SELECT * FROM reservation WHERE s_id = ?");
$stmt->bind_param("i", $s_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if reservation exists
if ($result->num_rows > 0) {
    $reservation = $result->fetch_assoc(); // Fetch reservation data
} else {
    echo "Reservation not found.";
    exit();
}

// Handle file upload for payment screenshot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the file is uploaded
    if (isset($_FILES['p_screenshot']) && $_FILES['p_screenshot']['error'] == 0) {
        // Process the uploaded file (e.g., move to a directory)
        $upload_dir = 'uploads/screenshots/';
        $file_name = basename($_FILES['p_screenshot']['name']);
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['p_screenshot']['tmp_name'], $file_path)) {
            // File uploaded successfully, now insert payment details into the database
            $uid = $reservation['uid']; // Get user ID from reservation data
            $total_amount = $_POST['total_amount'];
            $ref_num = $_POST['ref_num']; // Get reference number
            $pay_date = $_POST['pay_date']; // Get payment date
            $payment_status = 'Pending'; // Set initial payment status to 'Pending'

            // Insert into the payment table
            $insert_payment_stmt = $conn->prepare("INSERT INTO payment (uid, s_id, p_screenshot, total_amount, ref_num, pay_date, p_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_payment_stmt->bind_param("iisssss", $uid, $s_id, $file_path, $total_amount, $ref_num, $pay_date, $payment_status);
            
            if ($insert_payment_stmt->execute()) {
                // After successful payment insertion, update the reservation status to 'Pending'
                $update_reservation_stmt = $conn->prepare("UPDATE reservation SET s_status = 'Pending' WHERE s_id = ?");
                $update_reservation_stmt->bind_param("i", $s_id);
                $update_reservation_stmt->execute();

                // Redirect to reservation-receipt1.php with success status and include s_id in the URL
                header("Location: reservation-receipt1.php?status=success&s_id=" . $s_id);
                exit();
            } else {
                echo "Error inserting payment details.";
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Please upload a screenshot of the payment receipt.";
    }
    exit();
}
?>
