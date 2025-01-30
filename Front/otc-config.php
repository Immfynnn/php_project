<?php
session_start();
require_once "../config.php";

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // User and reservation ID from session
    $uid = $_SESSION['uid'] ?? null;
    $s_id = $_SESSION['s_id'] ?? null;
    $total_amount = $_POST['total_amount'] ?? null; // Get the amount from the form

    // Validate if both UID and Reservation ID are available
    if (!$uid || !$s_id || !$total_amount) {
        echo "Invalid user or reservation ID, or missing total amount.";
        exit();
    }

    // Begin a transaction
    $conn->begin_transaction();

    try {
        // Insert the payment record
        $stmt = $conn->prepare("INSERT INTO payment (uid, s_id, total_amount, p_status) VALUES (?, ?, ?, 'Over the Counter')");
        $stmt->bind_param("iis", $uid, $s_id, $total_amount);

        if (!$stmt->execute()) {
            throw new Exception("Error saving payment information: " . $stmt->error);
        }

        // Update the reservation's s_status to 'pending'
        $updateStmt = $conn->prepare("UPDATE reservation SET s_status = 'Pending' WHERE s_id = ?");
        $updateStmt->bind_param("i", $s_id);

        if (!$updateStmt->execute()) {
            throw new Exception("Error updating reservation status: " . $updateStmt->error);
        }

        // Commit the transaction
        $conn->commit();

        // Redirect to reservation receipt page after successful insertion and update
        header("Location: reservation-receipt.php");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction if any query fails
        $conn->rollback();
        echo $e->getMessage();
        exit();
    } finally {
        // Close the prepared statements
        $stmt->close();
        if (isset($updateStmt)) {
            $updateStmt->close();
        }
    }
} else {
    echo "Invalid request method.";
    exit();
}

// Close the database connection
$conn->close();
?>
