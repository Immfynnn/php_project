<?php
session_start();
include '../config.php';

if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$uid = intval($_SESSION['uid']); // Get user ID from session

// Update all notifications for the user to read
$sqlMarkAsRead = "UPDATE notifications SET is_read = TRUE WHERE uid = ?";
$stmtMarkAsRead = $conn->prepare($sqlMarkAsRead);
$stmtMarkAsRead->bind_param('i', $uid);

if ($stmtMarkAsRead->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmtMarkAsRead->close();
$conn->close();
?>
