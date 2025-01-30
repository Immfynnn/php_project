<?php
include '../config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: home.php");
    exit();
}

// Get the sender ID from session
$sender_id = intval($_SESSION['uid']);

// Fetch the sender's username from the database
$senderSql = "SELECT username FROM users WHERE uid = ?";
$senderStmt = $conn->prepare($senderSql);
$senderStmt->bind_param('i', $sender_id);
$senderStmt->execute();
$senderResult = $senderStmt->get_result();

if ($senderRow = $senderResult->fetch_assoc()) {
    $sender_username = $senderRow['username'];
} else {
    echo "<script>alert('Oops! You Are Offline, To Message The admin Please Signin Again'); window.location.href = 'signin.php';</script>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get recipient admin ID from the form
    $recipient_admin_id = intval($_POST['recipient_admin_id']);
    
    // Get the message content from the form
    $message_content = trim($_POST['message_cont']);
    
    if (!empty($recipient_admin_id) && !empty($message_content)) {
        // Check if the recipient admin exists
        $adminCheckSql = "SELECT admin_id FROM admins WHERE admin_id = ?";
        $adminCheckStmt = $conn->prepare($adminCheckSql);
        $adminCheckStmt->bind_param('i', $recipient_admin_id);
        $adminCheckStmt->execute();
        $adminCheckResult = $adminCheckStmt->get_result();
        
        if ($adminCheckResult->num_rows > 0) {
            // Insert the message into the messages1 table
            $insertSql = "INSERT INTO messages1 (sender_uid, recipient_aid, message_cont) 
                          VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param('iis', $sender_id, $recipient_admin_id, $message_content);
            
            if ($insertStmt->execute()) {
                echo "<script>alert('Message sent successfully!'); window.location.href = 'user_messages_inbox.php';</script>";
            } else {
                echo "<script>alert('Error sending message. Please try again.');</script>";
            }

            $insertStmt->close();
        } else {
            echo "<script>alert('Recipient admin not found.');</script>";
        }

        $adminCheckStmt->close();
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: none; /* Prevents resizing of the textarea */
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
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
        <h2>Send Message to Admin</h2>
        <form action="reply_message_to_admin.php" method="post">
            <label for="recipient_admin_id">Recipient Admin ID:</label>
            <input type="number" id="recipient_admin_id" name="recipient_admin_id" required>
            <label for="message_cont">Message:</label>
            <textarea id="message_cont" name="message_cont" required></textarea>
            <input type="submit" value="Send Message">
            <a href="user_messages_inbox.php" class="back-link">Back</a>
        </form>
    </div>
</body>
</html>
