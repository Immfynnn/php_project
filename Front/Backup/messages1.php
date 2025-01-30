<?php
include 'sql/config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

// Get the sender ID from session
$sender_id = intval($_SESSION['uid']);

// Fetch the sender's username from the database
$senderSql = "SELECT username FROM users WHERE uid = ?";
$senderStmt = $conn->prepare($senderSql);
$senderStmt->bind_param('i', $sender_id);

if ($senderStmt->execute()) {
    $senderResult = $senderStmt->get_result();
    if ($senderRow = $senderResult->fetch_assoc()) {
        $sender_username = htmlspecialchars($senderRow['username']);
    } else {
        echo "Error: Sender username not found.";
        $senderStmt->close();
        $conn->close();
        exit();
    }
} else {
    echo "Error: " . $senderStmt->error;
    $senderStmt->close();
    $conn->close();
    exit();
}

$senderStmt->close();

// Get recipient username from URL parameter
$recipient_username = isset($_GET['reply_to']) ? htmlspecialchars($_GET['reply_to']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if 'recipient_admin_id' and 'message_content' are set in POST data
    if (!isset($_POST['recipient_admin_id']) || !isset($_POST['message_cont'])) {
        echo "Please provide both recipient and message content.";
        exit();
    }

    $recipient_input = $conn->real_escape_string($_POST['recipient_admin_id']);
    $message_content = $conn->real_escape_string($_POST['message_cont']);

    // Determine if the recipient input is an ID or username
    if (is_numeric($recipient_input)) {
        // Recipient input is an ID
        $recipient_id = intval($recipient_input);
    } else {
        // Recipient input is a username
        $recipient_username = $recipient_input;
        
        // Fetch recipient ID from username
        $recipientSql = "SELECT admin_id FROM admins WHERE admin_username = ?";
        $recipientStmt = $conn->prepare($recipientSql);
        $recipientStmt->bind_param('s', $recipient_username);
        
        if ($recipientStmt->execute()) {
            $recipientResult = $recipientStmt->get_result();
            if ($recipientRow = $recipientResult->fetch_assoc()) {
                $recipient_id = intval($recipientRow['admin_id']);
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
    $sql = "INSERT INTO messages1 (sender_uid, recipient_aid, message_cont) VALUES (?, ?, ?)";
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
    <form action="messages1.php" method="post">
        <label for="recipient_admin_id">Recipient To:</label>
        <input type="text" id="recipient_admin_id" name="recipient_admin_id" value="<?php echo $recipient_username; ?>" required>
        <br>
        <label for="message_cont">Message:</label>
        <textarea id="message_cont" name="message_cont" required></textarea>
        <br>
        <input type="submit" value="Send Message">
        <a href="home.php">Back</a>
    </form>
</body>
</html>
