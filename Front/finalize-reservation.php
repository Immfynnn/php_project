<?php
// Start the session
session_start();

// Include database connection
include '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php");
    exit();
}

// Check if s_id is set in the session
if (!isset($_POST['s_id'])) {
    echo "Invalid reservation ID.";
    exit();
}

$s_id = $_POST['s_id'];

// Update reservation status to 'Confirmed' or another status
$stmt = $conn->prepare("UPDATE reservation SET s_status = 'Confirmed' WHERE s_id = ?");
$stmt->bind_param("i", $s_id);

if ($stmt->execute()) {
    // Reservation confirmed, redirect to a confirmation page or message
    header("Location: reservation-confirmed.php");
    exit();
} else {
    echo "Error confirming reservation: " . $stmt->error;
}
?>
