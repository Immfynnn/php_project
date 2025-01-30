<?php
session_start();
require_once "../config.php";

if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Mark all unread messages as read
    $updateSql = "UPDATE messages SET read_status = 1 WHERE recipient_id = ? AND read_status = 0";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('i', $user_id);
    $updateStmt->execute();
    $updateStmt->close();

    echo json_encode(["status" => "success"]); // Optional response
}
$conn->close();
?>
