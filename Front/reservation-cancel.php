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
    if (isset($_POST['delete_reservation']) && isset($_POST['s_id'])) {
        $s_id = $_POST['s_id'];

        if (is_numeric($s_id)) {
            $transactionStarted = false; // Track transaction status
            try {
                // Step 1: Fetch the reservation details
                $sqlFetchDetails = "SELECT payment_type, s_status, 
                                    (SELECT CONCAT(firstname, ' ', lastname) FROM users WHERE users.uid = reservation.uid) AS user_name
                                    FROM reservation WHERE s_id = ?";
                $stmtFetchDetails = $conn->prepare($sqlFetchDetails);
                $stmtFetchDetails->bind_param("i", $s_id);
                $stmtFetchDetails->execute();
                $result = $stmtFetchDetails->get_result();

                if ($result->num_rows > 0) {
                    $reservation = $result->fetch_assoc();
                    $paymentType = strtolower($reservation['payment_type']);
                    $status = strtolower($reservation['s_status']);
                    $userName = htmlspecialchars($reservation['user_name']);

                    // Check if conditions are met for updating to "Canceling Reservation"
                    if ($paymentType === 'over the counter' && ($status === 'approved' || $status === 'ongoing')) {
                        // Update the s_status to "Canceling Reservation"
                        $sqlUpdateStatus = "UPDATE reservation SET s_status = 'Canceling' WHERE s_id = ?";
                        $stmtUpdateStatus = $conn->prepare($sqlUpdateStatus);
                        $stmtUpdateStatus->bind_param("i", $s_id);

                        if (!$stmtUpdateStatus->execute()) {
                            throw new Exception("Error updating status: " . $stmtUpdateStatus->error);
                        }

                                               // Fetch all admins to notify them
                        $sqlFetchAdmins = "SELECT admin_id FROM admins";
                        $resultAdmins = $conn->query($sqlFetchAdmins);
                        
                        if ($resultAdmins->num_rows > 0) {
                            $notificationText = "$userName is canceling a reservation. Check Now!";
                            $stmtNotifyAdmin = $conn->prepare("INSERT INTO notification_admin (admin_id, s_id, message_noti, created_at) VALUES (?, ?, ?, NOW())");
                        
                            while ($admin = $resultAdmins->fetch_assoc()) {
                                $adminId = $admin['admin_id'];
                                $stmtNotifyAdmin->bind_param("iis", $adminId, $s_id, $notificationText);
                        
                                if (!$stmtNotifyAdmin->execute()) {
                                    throw new Exception("Error notifying admin ID $adminId: " . $stmtNotifyAdmin->error);
                                }
                            }
                        
                            $stmtNotifyAdmin->close();
                        }


                        // Redirect to the reservations page after updating status
                        header("Location: my_reservation.php");
                        exit();
                    } else {
                        // Begin a transaction to ensure all deletions are atomic
                        $conn->begin_transaction();
                        $transactionStarted = true;

                        // Step 2: Delete the related payment record(s)
                        $sqlDeletePayment = "DELETE FROM payment WHERE s_id = ?";
                        $stmtPayment = $conn->prepare($sqlDeletePayment);
                        $stmtPayment->bind_param("i", $s_id);

                        if (!$stmtPayment->execute()) {
                            throw new Exception("Error deleting payment: " . $stmtPayment->error);
                        }

                        // Step 3: Delete the related notification_admin record(s)
                        $sqlDeleteNotification = "DELETE FROM notification_admin WHERE s_id = ?";
                        $stmtNotification = $conn->prepare($sqlDeleteNotification);
                        $stmtNotification->bind_param("i", $s_id);

                        if (!$stmtNotification->execute()) {
                            throw new Exception("Error deleting notification: " . $stmtNotification->error);
                        }

                        // Step 4: Delete the reservation record
                        $sqlDeleteReservation = "DELETE FROM reservation WHERE s_id = ?";
                        $stmtReservation = $conn->prepare($sqlDeleteReservation);
                        $stmtReservation->bind_param("i", $s_id);

                        if (!$stmtReservation->execute()) {
                            throw new Exception("Error deleting reservation: " . $stmtReservation->error);
                        }

                        // Commit the transaction if all deletions were successful
                        $conn->commit();
                        $transactionStarted = false;

                        // Redirect to the reservations page after deletion
                        header("Location: my_reservation.php");
                        exit();
                    }
                } else {
                    echo "Reservation not found.";
                }
            } catch (Exception $e) {
                // Rollback the transaction if any error occurs
                if ($transactionStarted) {
                    $conn->rollback();
                }

                // Output the error message
                echo "Error: " . $e->getMessage();
            } finally {
                // Close the statements if they exist
                if (isset($stmtFetchDetails)) $stmtFetchDetails->close();
                if (isset($stmtUpdateStatus)) $stmtUpdateStatus->close();
                if (isset($stmtPayment)) $stmtPayment->close();
                if (isset($stmtNotification)) $stmtNotification->close();
                if (isset($stmtReservation)) $stmtReservation->close();
            }
        } else {
            echo "Invalid reservation ID.";
        }
    }
}
?>
