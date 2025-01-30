<?php
// delete_all_messages.php

include '../config.php';

session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

$user_id = intval($_SESSION['uid']);

// Check if there are any messages to delete
$checkSql = "SELECT COUNT(*) as count FROM messages WHERE recipient_id = ? OR sender_id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param('ii', $user_id, $user_id);

if ($checkStmt->execute()) {
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    $messageCount = $row['count'];
    
    if ($messageCount > 0) {
        // Proceed to delete all messages
        $deleteSql = "DELETE FROM messages WHERE recipient_id = ? OR sender_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param('ii', $user_id, $user_id);

        if ($deleteStmt->execute()) {
             // Redirect to the messages page after deleting the message
        header("Location: messages.php"); // Ensure this includes the proper URL path
        exit();
        } else {
            echo "Error: " . htmlspecialchars($deleteStmt->error);
        }

        $deleteStmt->close();
    } else {
         // Redirect to the messages page after deleting the message
         header("Location: messages.php"); // Ensure this includes the proper URL path
         exit();;
    }
    
    $checkStmt->close();
} else {
    echo "Error: " . htmlspecialchars($checkStmt->error);
}

$conn->close();
?>
