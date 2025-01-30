<?php
// Start the session
session_start();

// Include database connection
include '../config.php'; // Adjust the path as necessary

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the delete button was pressed
    if (isset($_POST['delete_reservation']) && isset($_POST['s_id'])) {
        $s_id = $_POST['s_id'];

        // Ensure that s_id is a valid integer
        if (is_numeric($s_id)) {
            // Begin a transaction to ensure data integrity
            $conn->begin_transaction();

            try {
                // Step 1: Delete from `notification_admin` table
                $sqlNotification = "DELETE FROM notification_admin WHERE s_id = ?";
                $stmtNotification = $conn->prepare($sqlNotification);

                if ($stmtNotification) {
                    // Bind the s_id parameter to the prepared statement
                    $stmtNotification->bind_param("i", $s_id);

                    // Execute the statement
                    if (!$stmtNotification->execute()) {
                        throw new Exception("Error deleting notification: " . $stmtNotification->error);
                    }

                    // Close the statement
                    $stmtNotification->close();
                } else {
                    throw new Exception("Error preparing notification query: " . $conn->error);
                }

                // Step 2: Delete from `reservation` table
                $sqlReservation = "DELETE FROM reservation WHERE s_id = ?";
                $stmtReservation = $conn->prepare($sqlReservation);

                if ($stmtReservation) {
                    // Bind the s_id parameter to the prepared statement
                    $stmtReservation->bind_param("i", $s_id);

                    // Execute the statement
                    if (!$stmtReservation->execute()) {
                        throw new Exception("Error deleting reservation: " . $stmtReservation->error);
                    }

                    // Close the statement
                    $stmtReservation->close();
                } else {
                    throw new Exception("Error preparing reservation query: " . $conn->error);
                }

                // Commit the transaction
                $conn->commit();

                // Redirect to my_reservation.php after successful deletion
                header("Location: my_reservation.php");
                exit();
            } catch (Exception $e) {
                // Rollback the transaction if any error occurs
                $conn->rollback();
                echo $e->getMessage();
            }
        } else {
            echo "Invalid reservation ID.";
        }
    }
}
?>
