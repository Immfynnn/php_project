<?php
session_start();
include '../config.php'; // Include your database configuration

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Admin not logged in']);
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Query to count unread notifications for the logged-in admin
$query = $conn->prepare("SELECT COUNT(*) as unread_count FROM notification_admin WHERE admin_id = ? AND is_read1 = FALSE");
$query->bind_param("i", $admin_id);
$query->execute();
$query->bind_result($unread_count);
$query->fetch();
$query->close();

// Return the count as JSON
echo json_encode(['status' => 'success', 'unread_count' => $unread_count]);
?>
