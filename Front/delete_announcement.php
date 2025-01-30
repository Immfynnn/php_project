<?php
include '../config.php'; // Include the database connection

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_aid'])) {
    $postAid = intval($_POST['post_aid']); // Sanitize input to prevent SQL injection

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete related records in the notifications table
        $sqlDeleteNotifications = "DELETE FROM notifications WHERE post_aid = ?";
        $stmtNotifications = $conn->prepare($sqlDeleteNotifications);
        $stmtNotifications->bind_param('i', $postAid);
        $stmtNotifications->execute();
        $stmtNotifications->close();

        // Delete the announcement
        $sqlDeleteAnnouncement = "DELETE FROM announcement WHERE post_aid = ?";
        $stmtAnnouncement = $conn->prepare($sqlDeleteAnnouncement);
        $stmtAnnouncement->bind_param('i', $postAid);
        $stmtAnnouncement->execute();
        $stmtAnnouncement->close();

        // Commit the transaction
        $conn->commit();
        echo "success"; // Response to indicate success
    } catch (Exception $e) {
        // Roll back the transaction on error
        $conn->rollback();
        echo "error: " . $e->getMessage(); // Output error for debugging
    }

    $conn->close();
} else {
    echo "Invalid request";
}
?>
