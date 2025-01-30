<?php
session_start();
include '../config.php';

// Check if the user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);

// Handle the form submission to update service status and priest
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['s_status'], $_POST['service_id'], $_POST['priest'])) {
    $new_status = $_POST['s_status'];
    $priest = htmlspecialchars($_POST["priest"]);
    $service_id = intval($_POST['service_id']);
    $admin_id = intval($_SESSION['admin_id']); // Get the admin ID from the session

    // Update the service status and priest in the database
    $sqlUpdateStatus = "UPDATE reservation SET s_status = ?, priest = ?, admin_id = ? WHERE s_id = ?";
    $stmtUpdateStatus = $conn->prepare($sqlUpdateStatus);
    $stmtUpdateStatus->bind_param('ssii', $new_status, $priest, $admin_id, $service_id);

    if ($stmtUpdateStatus->execute()) {
        // Fetch user ID and service type for notification
        $sqlFetchDetails = "SELECT uid, service_type FROM reservation WHERE s_id = ?";
        $stmtFetchDetails = $conn->prepare($sqlFetchDetails);
        $stmtFetchDetails->bind_param('i', $service_id);
        $stmtFetchDetails->execute();
        $reservationDetails = $stmtFetchDetails->get_result()->fetch_assoc();
        $stmtFetchDetails->close();

        if ($reservationDetails) {
            $uid = $reservationDetails['uid'];
            $service_type = $reservationDetails['service_type'];

            // Set the notification message based on the status
            if ($new_status === 'Approved') {
                $notificationMessage = "Your Reservation for {$service_type} has been Approved. You will receive further updates shortly.";
            } elseif ($new_status === 'Canceled') {
                $notificationMessage = "Your Reservation for {$service_type} has been Canceled.";
            } else {
                $notificationMessage = "Your Reservation for {$service_type} is now {$new_status}.";
            }

            // Insert notification into the notifications table
            $sqlInsertNotification = "INSERT INTO notifications (uid, s_id, message) VALUES (?, ?, ?)";
            $stmtInsertNotification = $conn->prepare($sqlInsertNotification);
            $stmtInsertNotification->bind_param('iis', $uid, $service_id, $notificationMessage);
            $stmtInsertNotification->execute();
            $stmtInsertNotification->close();
        }

        header("Location: view_reservation_details.php?update=success");
        exit();
        
    } else {
        // Optionally handle the error
        echo "<script>alert('Error updating status and priest: " . $conn->error . "');</script>";
    }
    $stmtUpdateStatus->close();
}

// Fetch the service details after the update to display the current status
$sqlServiceDetails = "SELECT reservation.*, users.username, users.userimg, payment.p_status, payment.total_amount 
                      FROM reservation
                      JOIN users ON reservation.uid = users.uid
                      LEFT JOIN payment ON reservation.s_id = payment.s_id
                      WHERE reservation.s_id = ?";
$stmtServiceDetails = $conn->prepare($sqlServiceDetails);
$stmtServiceDetails->bind_param('i', $service_id);
$stmtServiceDetails->execute();
$resultServiceDetails = $stmtServiceDetails->get_result();
$serviceDetails = $resultServiceDetails->fetch_assoc();

$stmtServiceDetails->close();
?>
