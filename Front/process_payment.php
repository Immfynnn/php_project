<?php
session_start();

// Include database connection
include '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php");
    exit();
}

$uid = intval($_SESSION['uid']);
// Get the user and payment details from the POST request
$a_id = intval($_POST['a_id']);
$total_amount = floatval($_POST['total_amount']);
$uid = intval($_SESSION['uid']);  // Assuming uid is stored in session
$s_id = $_SESSION['s_id'];  // Assuming service ID is stored in session

// Start transaction
$conn->begin_transaction();

try {
    // Fetch the current balance
    $stmt = $conn->prepare("SELECT a_balance FROM API WHERE a_id = ?");
    $stmt->bind_param("i", $a_id);
    $stmt->execute();
    $stmt->bind_result($a_balance);
    $stmt->fetch();
    $stmt->close();

    // Check if the user has enough balance
    if ($a_balance < $total_amount) {
        throw new Exception("Insufficient balance.");
    }

    // Deduct the total amount from the balance
    $new_balance = $a_balance - $total_amount;
    $stmt = $conn->prepare("UPDATE API SET a_balance = ? WHERE a_id = ?");
    $stmt->bind_param("di", $new_balance, $a_id);
    $stmt->execute();
    $stmt->close();

    // Update payment status to 'Paid' if payment has been processed
    $updatePaymentQuery = "UPDATE payment SET p_status = 'Paid' WHERE s_id = ?";
    $updatePaymentStmt = $conn->prepare($updatePaymentQuery);
    $updatePaymentStmt->bind_param('i', $s_id);
    $updatePaymentStmt->execute();
    $updatePaymentStmt->close();

    // Update service status to 'Paid'
    $updateServiceStatusQuery = "UPDATE services SET s_status = 'Pending' WHERE s_id = ?";
    $updateServiceStatusStmt = $conn->prepare($updateServiceStatusQuery);
    $updateServiceStatusStmt->bind_param('i', $s_id);
    $updateServiceStatusStmt->execute();
    $updateServiceStatusStmt->close();

    // Commit the transaction
    $conn->commit();

    // Redirect to success page
    header("Location: payment_success.php");
    exit();

} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();

    // Display error message (you could redirect to an error page instead)
    echo "Error processing payment: " . $e->getMessage();
    exit();
}
?>
