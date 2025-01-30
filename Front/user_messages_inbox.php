<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Inbox</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .msg-cont {
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin: 15px 0;
            padding: 15px;
            transition: background 0.3s ease;
        }
        .msg-cont:hover {
            background: #f1f1f1;
        }
        .msg-cont h3 {
            margin: 0 0 5px;
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
        button {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #c82333;
        }
        .delete-all-btn {
            background-color: #dc3545;
            color: #fff;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            text-align: center;
            display: block;
            margin: 20px auto;
            transition: background-color 0.3s ease;
        }
        .delete-all-btn:hover {
            background-color: #c82333;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>User Inbox</h1>
    <a href="reply_message_to_admin.php">Messages to Admin</a>

    <?php
    // Include the database configuration
    include '../config.php';
    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['uid'])) {
        header("Location: signup.php");
        exit();
    }

    // Get the user ID from the session
    $user_id = intval($_SESSION['uid']);

    // Check user status
    $statusSql = "SELECT user_status FROM users WHERE uid = ?";
    $statusStmt = $conn->prepare($statusSql);
    $statusStmt->bind_param('i', $user_id);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();

    if ($statusResult->num_rows > 0) {
        $userStatus = $statusResult->fetch_assoc()['user_status'];
        if ($userStatus === 'Offline') {
            header("Location: signin.php");
            exit();
        }
    } else {
        // If user status is not found, redirect to sign in
        header("Location: signin.php");
        exit();
    }

    // Fetch messages where the user is either the sender or the recipient
    $sql = "SELECT m.message_id, 
                   CASE WHEN m.sender_id = a.admin_id THEN a.admin_username ELSE u.username END AS sender_username, 
                   m.message_content, 
                   m.sent_at 
            FROM messages m
            LEFT JOIN admins a ON m.sender_id = a.admin_id
            LEFT JOIN users u ON m.recipient_id = u.uid
            WHERE m.sender_id = ? OR m.recipient_id = ?
            ORDER BY m.sent_at DESC";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $user_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $messageCount = $result->num_rows;

        if ($messageCount > 0) {
            // Mark all unread messages as read
            $updateSql = "UPDATE messages SET read_status = 1 WHERE recipient_id = ? AND read_status = 0";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('i', $user_id);
            $updateStmt->execute();
            $updateStmt->close();

            // Display each message
            while ($row = $result->fetch_assoc()) {
                $messageId = htmlspecialchars($row['message_id']);
                $senderUsername = htmlspecialchars($row['sender_username']);
                $messageContent = htmlspecialchars($row['message_content']);
                $sentAt = htmlspecialchars($row['sent_at']);

                // Output message content
                echo "<div class='msg-cont'>";
                echo "<h3><strong>From: {$senderUsername}</strong></h3>";
                echo "<p><strong>To: You</strong></p>";
                echo "<p><strong>Time:</strong> {$sentAt}</p>";
                echo "<br>";
                echo "<p>" . nl2br($messageContent) . "</p>";
                echo "<br>";

                // Display delete button for each message
                echo "<form action='delete_message.php' method='POST' style='display:inline;'>
                        <input type='hidden' name='message_id' value='{$messageId}'>
                        <button type='submit'>Delete</button>
                      </form>";
                echo "</div>";
            }
        } else {
            // If no messages are found
            echo "<p>No messages found.</p>";
        }
    } else {
        // Handle SQL execution errors
        echo "Error: " . htmlspecialchars($stmt->error);
    }

    // Close the statement and the database connection
    $stmt->close();
    $conn->close();
    ?>

    <!-- Form to Delete All Messages -->
    <form id="delete-all-form" action="delete_all_messages.php" method="POST">
        <button type="button" onclick="confirmDeleteAll()" class="delete-all-btn">
            Delete All Messages
        </button>
    </form>

    <a href="home.php" class="back-link">Back</a>
</div>

<script>
function confirmDeleteAll() {
    if (confirm("Are you sure you want to delete all messages? This action cannot be undone.")) {
        document.getElementById('delete-all-form').submit();
    }
}
</script>

</body>
</html>
