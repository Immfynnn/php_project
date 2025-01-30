<?php
// delete_message.php

include '../config.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);

// Check if message_id is set and is an integer
if (isset($_POST['message_id']) && is_numeric($_POST['message_id'])) {
    $message_id = intval($_POST['message_id']);

    // Prepare delete statement
    $deleteSql = "DELETE FROM messages1 WHERE msg_id = ? AND (recipient_aid = ? OR sender_uid = ?)";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param('iii', $message_id, $user_id, $user_id);

    if ($stmt->execute()) {
        header("Location: admin-messages.php");
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
} else {
    echo "Invalid message ID.";
}

$conn->close();
?>
