<?php
include 'sql/config.php';

session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

$user_id = intval($_SESSION['uid']);

// Fetch messages sent by or to the user
$sql = "SELECT messages.*, admins.admin_username AS sender_username, admins2.admin_username AS recipient_username
        FROM messages
        LEFT JOIN admins ON messages.sender_id = admins.admin_id
        LEFT JOIN admins AS admins2 ON messages.recipient_id = admins2.admin_id
        WHERE messages.recipient_id = ? OR messages.sender_id = ?
        ORDER BY messages.sent_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $user_id, $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $messageCount = $result->num_rows;

    if ($messageCount > 0) {
        // Mark messages as read
        $updateSql = "UPDATE messages SET read_status = 1 WHERE recipient_id = ? AND read_status = 0";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('i', $user_id);
        $updateStmt->execute();
        $updateStmt->close();

        // Display messages
        while ($row = $result->fetch_assoc()) {
            $messageId = htmlspecialchars($row['message_id']);
            $senderUsername = htmlspecialchars($row['sender_username']);
            $recipientUsername = htmlspecialchars($row['recipient_username']);
            $messageContent = htmlspecialchars($row['message_content']);
            $sentAt = htmlspecialchars($row['sent_at']);
            echo "<div class='msg-cont'>";
            echo "<h3><strong>From: {$senderUsername}</strong></h3>";
            echo "<p><strong>Time:</strong> {$sentAt}</p>";
            echo "<br>";
            echo "<p>{$messageContent}</p>";
            echo "<br>";
            echo "<a href='messages1.php?reply_to=" . urlencode($recipientUsername) . "'>Reply</a>";

            // Delete Button Form
            echo "<form action='delete_message.php' method='POST' style='display:inline;'>
                    <input type='hidden' name='message_id' value='{$messageId}'>
                    <button type='submit'>Delete</button>
                  </form>";

            echo "</div>";
        }
    } else {
        echo "<p>No messages found.</p>";
    }
} else {
    echo "Error: " . htmlspecialchars($stmt->error);
}

$stmt->close();
$conn->close();
?>
<!-- Form to Delete All Messages -->
<form id="delete-all-form" action="delete_all_messages.php" method="POST" style="text-align: center; margin-top: 20px;">
    <button type="button" onclick="confirmDeleteAll()" style="background-color: #dc3545; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
        Delete All Messages
    </button>
</form>

<a href="home.php">Back</a>

<script>
function confirmDeleteAll() {
    if (confirm("Are you sure you want to delete all messages? This action cannot be undone.")) {
        document.getElementById('delete-all-form').submit();
    }
}
</script>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }
    .msg-cont {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin: 20px;
        padding: 20px;
        width: 90%;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    .msg-cont h3 {
        margin-bottom: 10px;
        color: #333;
    }
    .msg-cont p {
        margin: 5px 0;
        color: #555;
    }
    .msg-cont a {
        color: #007bff;
        text-decoration: none;
    }
    .msg-cont a:hover {
        text-decoration: underline;
    }
    form {
        display: inline;
    }
    button {
        background-color: #dc3545;
        color: #fff;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
    }
    button:hover {
        background-color: #c82333;
    }
</style>
