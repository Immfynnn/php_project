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

// Handle form submission (without file upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process payment details (without file upload)
    $uid = $reservation['uid']; // Get user ID from reservation data
    $total_amount = $_POST['total_amount'];
    $payment_status = 'Pending'; // Set initial payment status to 'Pending'

    // Insert into the payment table
    $insert_payment_stmt = $conn->prepare("INSERT INTO payment (uid, s_id, total_amount, p_status) VALUES (?, ?, ?, ?)");
    $insert_payment_stmt->bind_param("iiss", $uid, $s_id, $total_amount, $payment_status);

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
    exit();
}

?>
