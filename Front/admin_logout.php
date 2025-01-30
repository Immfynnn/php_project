<?php
session_start();
include '../config.php'; // Include database configuration

if (isset($_SESSION['admin_id'])) { // Check if the admin is logged in
    $admin_id = intval($_SESSION['admin_id']); // Get the admin ID from the session

    // Update the admin active status to Offline
    $sql = "UPDATE admins SET admin_active_status = 'Offline' WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id); // Bind the admin ID to the query
    if ($stmt->execute()) {
        // Destroy the session
        session_destroy();
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }

    $stmt->close();
}
$conn->close(); // Close the database connection
?>
