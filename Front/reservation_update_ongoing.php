<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
    $service_id = intval($_POST['service_id']);

    // Prepare the update query to set status to 'Ongoing'
    $sqlUpdate = "UPDATE reservation SET s_status = 'Ongoing' WHERE s_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param('i', $service_id);

    if ($stmtUpdate->execute()) {
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

            // Set the notification message for "Ongoing"
            $notificationMessage = "Your reservation for {$service_type} is now Ongoing! Check the Calender.";

            // Insert notification into the notifications table
            $sqlInsertNotification = "INSERT INTO notifications (uid, s_id, message) VALUES (?, ?, ?)";
            $stmtInsertNotification = $conn->prepare($sqlInsertNotification);
            $stmtInsertNotification->bind_param('iis', $uid, $service_id, $notificationMessage);
            $stmtInsertNotification->execute();
            $stmtInsertNotification->close();
        }

        // Redirect back to the dashboard after successful update
        header("Location: admin-approved-reservation.php"); // Change to your actual dashboard page
        exit();
    } else {
        // Handle error (optional)
        echo "Error updating record: " . $stmtUpdate->error;
    }
    $stmtUpdate->close();
}
?>
