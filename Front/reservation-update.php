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
    $s_status = $_POST['s_status']; // Get the selected status
    $priest = isset($_POST['priest']) ? $_POST['priest'] : null; // Get the selected priest if available

    // Prepare the update query to set the selected status and priest
    $sqlUpdate = "UPDATE reservation SET s_status = ?, priest = ? WHERE s_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param('ssi', $s_status, $priest, $service_id);

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

            // Set the notification message dynamically based on the selected status
            $notificationMessage = "Your reservation for {$service_type} has been updated to {$s_status}.";
            if ($s_status === 'Reschedule') {
                $notificationMessage .= " Please coordinate with the office for further arrangements.";
            }

            // Insert notification into the notifications table
            $sqlInsertNotification = "INSERT INTO notifications (uid, s_id, message) VALUES (?, ?, ?)";
            $stmtInsertNotification = $conn->prepare($sqlInsertNotification);
            $stmtInsertNotification->bind_param('iis', $uid, $service_id, $notificationMessage);
            $stmtInsertNotification->execute();
            $stmtInsertNotification->close();
        }

        // Redirect back to the dashboard after successful update
        header("Location: admin-ongoing-reservation.php"); // Change to your actual dashboard page
        exit();
    } else {
        // Handle error (optional)
        echo "Error updating record: " . $stmtUpdate->error;
    }
    $stmtUpdate->close();
}
?>
