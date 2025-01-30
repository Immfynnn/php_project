<?php
// Start the session
session_start();

// Include database connection
include '../config.php'; // Adjust the path as necessary

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $user_id = $_SESSION['uid']; // Get user ID from session
    $service_type = htmlspecialchars($_POST["service_type"]);
    $s_description = htmlspecialchars($_POST["s_description"]);
    $s_description1 = htmlspecialchars($_POST["s_description1"]);
    $schedule = htmlspecialchars($_POST["r_type"]);
    $amount = htmlspecialchars($_POST["amount"]);
    $payment_type = isset($_POST["payment_type"]) && !empty($_POST["payment_type"]) ? htmlspecialchars($_POST["payment_type"]) : null;

    // Determine the initial status
    $s_status = $payment_type === null ? "Pending" : "To Pay";

    // File handling for valid ID
    $target_dir = "uploads/";
    $valid_id_files = [];
    $allowed_file_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

    // Upload valid ID files
    foreach ($_FILES['validId']['name'] as $key => $name) {
        $file_type = $_FILES['validId']['type'][$key];
        $target_file = $target_dir . basename($name);

        if (in_array($file_type, $allowed_file_types)) {
            if (move_uploaded_file($_FILES['validId']['tmp_name'][$key], $target_file)) {
                $valid_id_files[] = $name;
            } else {
                echo "Error uploading the file: " . htmlspecialchars($name);
            }
        } else {
            echo "Invalid file type for file: " . htmlspecialchars($name);
        }
    }

    // Prepare SQL statement to insert reservation details
    $stmt = $conn->prepare("INSERT INTO reservation (uid, service_type, s_description, s_description1, r_type, amount, payment_type, valid_id, s_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Convert file names to JSON string for storing in database
    $valid_id_json = json_encode($valid_id_files);

    // Bind parameters to the SQL statement
    $stmt->bind_param("issssssss", $user_id, $service_type, $s_description, $s_description1, $schedule, $amount, $payment_type, $valid_id_json, $s_status);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Store the last inserted service ID in the session
        $_SESSION['s_id'] = $stmt->insert_id;

        // Get the username of the logged-in user
        $user_query = $conn->prepare("SELECT username FROM users WHERE uid = ?");
        $user_query->bind_param("i", $user_id);
        $user_query->execute();
        $user_query->bind_result($username);
        $user_query->fetch();
        $user_query->close();

        // Create notification for each admin
        $admin_query = $conn->query("SELECT admin_id FROM admins");
        while ($admin = $admin_query->fetch_assoc()) {
            $admin_id = $admin['admin_id'];
            $notification_message = "You have a new Reservation $service_type from $username. Check now!";
            
            // Insert the notification into the notification_admin table
            $notif_stmt = $conn->prepare("INSERT INTO notification_admin (admin_id, uid, s_id, message_noti) VALUES (?, ?, ?, ?)");
            $notif_stmt->bind_param("iiis", $admin_id, $user_id, $_SESSION['s_id'], $notification_message);
            $notif_stmt->execute();
            $notif_stmt->close();
        }

        // Redirect to preview page
        header("Location: mass-preview.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}
?>
