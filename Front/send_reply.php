<?php
require_once('../config.php'); // Include database configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $msg_id = $_POST['msg_id'];
    $reply_message = $_POST['reply_message'];
    $sender_id = 1; // Assuming you are the admin (replace with session value if necessary)
    $recipient_id = $_POST['recipient_id']; // Get recipient_id from the form
    $recipient_username = 'User'; // Replace with the actual recipient username if needed

    // Step 1: Ensure recipient exists in the users table
    $stmt = $conn->prepare("SELECT uid FROM users WHERE uid = ?");
    $stmt->bind_param('i', $recipient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // If no matching recipient found, exit with error
        echo "<script>alert('Recipient not found in the system. Please verify the recipient ID.');</script>";
        exit;
    }

    // Step 2: Handle file upload (image)
    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
        $image_temp = $_FILES['image_upload']['tmp_name'];
        $image_name = $_FILES['image_upload']['name'];
        $upload_dir = 'uploads/messages/';
        $image_path = $upload_dir . basename($image_name);
        move_uploaded_file($image_temp, $image_path);
    } else {
        $image_path = null; // No image uploaded
    }

    // Step 3: Insert the reply message into the database
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, recipient_username, message_content, image_upload) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iisss', $sender_id, $recipient_id, $recipient_username, $reply_message, $image_path);

    if ($stmt->execute()) {
        // If insert is successful, show the success overlay
        echo "<script>
                setTimeout(function() {
                    document.getElementById('success-overlay').style.display = 'flex';
                    setTimeout(function() {
                        document.getElementById('success-overlay').style.display = 'none';
                    }, 3000); // Fade out after 3 seconds
                }, 500); // Delay to allow for the reply to be processed
              </script>";
    } else {
        echo "<script>alert('Error occurred while sending the reply.');</script>";
    }

    // Close the connection
    $stmt->close();
    $conn->close();
}
?>
