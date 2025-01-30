<?php
session_start();
require_once "../config.php";

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signup.php");
    exit();
}

// Get the sender's user ID (make sure it's properly authenticated)
$sender_uid = $_SESSION['uid']; // Assuming user ID is stored in the session

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $recipient_id_or_username = trim($_POST['recipient_admin_id_or_username']);
    $message_content = trim($_POST['message_cont']);
    $image_upload = null;

    // Handle file upload (if provided)
    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] == 0) {
        // Sanitize and validate the file
        $image_name = basename($_FILES['image_upload']['name']);
        $image_tmp_name = $_FILES['image_upload']['tmp_name'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        // Define allowed file types (e.g., jpg, png, gif)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array(strtolower($image_ext), $allowed_types)) {
            // Generate a unique file name and move the uploaded file to a folder
            $upload_dir = 'uploads/';
            $image_path = $upload_dir . uniqid() . '.' . $image_ext;

            if (move_uploaded_file($image_tmp_name, $image_path)) {
                $image_upload = $image_path;
            } else {
                echo "Error uploading the image.";
                exit();
            }
        } else {
            echo "Invalid image type.";
            exit();
        }
    }

    // Get recipient ID by username or admin ID
    $recipient_id = null;

    if (is_numeric($recipient_id_or_username)) {
        // If it's a numeric ID, use it directly
        $recipient_id = (int)$recipient_id_or_username;
    } else {
        // If it's a username, fetch the admin ID based on username
        $sql = "SELECT admin_id FROM admins WHERE admin_username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $recipient_id_or_username);
        $stmt->execute();
        $stmt->bind_result($recipient_id);
        $stmt->fetch();
        $stmt->close();

        if (!$recipient_id) {
            echo "Recipient not found.";
            exit();
        }
    }

    // Prepare and execute the SQL query to insert the message
    $sql = "INSERT INTO messages1 (sender_uid, recipient_aid, image_upload, message_cont)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiss', $sender_uid, $recipient_id, $image_upload, $message_content);

    if ($stmt->execute()) {
        // Send JSON response with success message
        echo json_encode(["status" => "success", "message" => "Message sent successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error sending message: " . $stmt->error]);
    }

    $stmt->close();
    exit();
}
?>
