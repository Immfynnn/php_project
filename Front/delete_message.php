<?php
// delete_message.php

include '../config.php';

session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

$user_id = intval($_SESSION['uid']);

// Check if message_id is set and is an integer
if (isset($_POST['message_id']) && is_numeric($_POST['message_id'])) {
    $message_id = intval($_POST['message_id']);

    // Prepare delete statement
    $deleteSql = "DELETE FROM messages WHERE message_id = ? AND (recipient_id = ? OR sender_id = ?)";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param('iii', $message_id, $user_id, $user_id);

    if ($stmt->execute()) {
        // Redirect to the messages page after deleting the message
        header("Location: messages.php"); // Ensure this includes the proper URL path
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
