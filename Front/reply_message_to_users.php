<?php
include '../config.php';
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo "You must be logged in to send a message.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if 'recipient_username_or_id' and 'message_content' are set in POST data
    if (!isset($_POST['recipient_username_or_id']) || !isset($_POST['message_content'])) {
        echo "Please provide both recipient and message content.";
        exit();
    }

    // Get the sender ID from session
    $sender_id = intval($_SESSION['admin_id']);
    $recipient_input = $conn->real_escape_string($_POST['recipient_username_or_id']);
    $message_content = $conn->real_escape_string($_POST['message_content']);

    // Determine if the recipient input is an ID or username
    if (is_numeric($recipient_input)) {
        // Recipient input is an ID
        $recipient_id = intval($recipient_input);
    } else {
        // Recipient input is a username
        $recipient_username = $recipient_input;
        
        // Fetch recipient ID from username
        $recipientSql = "SELECT uid FROM users WHERE username = ?";
        $recipientStmt = $conn->prepare($recipientSql);
        $recipientStmt->bind_param('s', $recipient_username);
        
        if ($recipientStmt->execute()) {
            $recipientResult = $recipientStmt->get_result();
            if ($recipientRow = $recipientResult->fetch_assoc()) {
                $recipient_id = intval($recipientRow['uid']);
            } else {
                echo "Error: Recipient username not found.";
                $recipientStmt->close();
                $conn->close();
                exit();
            }
        } else {
            echo "Error: " . $recipientStmt->error;
            $recipientStmt->close();
            $conn->close();
            exit();
        }
        
        $recipientStmt->close();
    }

    // Prepare and execute the SQL query to insert the message
    $sql = "INSERT INTO messages (sender_id, recipient_id, message_content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iis', $sender_id, $recipient_id, $message_content);
    
    if ($stmt->execute()) {
        echo "Message sent successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Message</title>
</head>
<body>
    <form action="reply_message_to_users.php" method="post">
        <label for="recipient_username_or_id">Recipient Username or ID: </label>
        <input type="text" id="recipient_username_or_id" name="recipient_username_or_id" required>
        <br>
        <label for="message_content">Message:</label>
        <textarea id="message_content" name="message_content" required></textarea>
        <br>
        <input type="submit" value="Send Message">
        <a href="admin_dashboard.php">Back</a>
    </form>
</body>
</html>
