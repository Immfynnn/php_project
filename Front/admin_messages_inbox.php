<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>

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
            margin: 20px auto;
            padding: 20px;
            width: 90%;
            max-width: 600px;
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
</head>
<body>
    
<a href="reply_message_to_users.php">Create Messages to Users</a>

<?php
include '../config.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);

// Define the SQL query to fetch messages with sender username
$sql = "SELECT m.msg_id, m.sender_uid, u.username AS sender_username, m.recipient_aid, m.message_cont, m.sent_at1 
        FROM messages1 m
        JOIN users u ON m.sender_uid = u.uid
        WHERE m.sender_uid = ? OR m.recipient_aid = ?";

// Prepare the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $user_id, $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $messageCount = $result->num_rows;

    if ($messageCount > 0) {
        // Mark messages as read
        $updateSql = "UPDATE messages1 SET read_status1 = 1 WHERE recipient_aid = ? AND read_status1 = 0";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('i', $user_id);
        $updateStmt->execute();
        $updateStmt->close();

        // Display messages
        while ($row = $result->fetch_assoc()) {
            $messageId = htmlspecialchars($row['msg_id']);
            $senderUsername = htmlspecialchars($row['sender_username']);
            $recipientId = htmlspecialchars($row['recipient_aid']);
            $messageContent = htmlspecialchars($row['message_cont']);
            $sentAt = htmlspecialchars($row['sent_at1']);
            echo "<div class='msg-cont'>";
            echo "<h3><strong>From: {$senderUsername}</strong></h3>";
            echo "<p><strong>Time:</strong> {$sentAt}</p>";
            echo "<p>{$messageContent}</p>";

            // Delete Button Form
            echo "<form action='delete_message_admin.php' method='POST' style='display:inline;'>
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
<form id="delete-all-form" action="delete_all_messages_admin.php" method="POST" style="text-align: center; margin-top: 20px;">
    <button type="button" onclick="confirmDeleteAll()">
        Delete All Messages
    </button>
</form>

<a href="admin_dashboard.php">Back</a>

<script>
function confirmDeleteAll() {
    if (confirm("Are you sure you want to delete all messages? This action cannot be undone.")) {
        document.getElementById('delete-all-form').submit();
    }
}
</script>

</body>
</html>
