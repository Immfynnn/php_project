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
    $description = htmlspecialchars($_POST["description"]);
    $schedule = htmlspecialchars($_POST["schedule"]);
    $time_slot = htmlspecialchars($_POST["time-slot"]);
    $per_head = isset($_POST['per_head']) ? $_POST['per_head'] : 0; // Default to 0 if not set
    $amount = htmlspecialchars($_POST["amount"]);
    $payment_type = htmlspecialchars($_POST["payment_type"]);
    $r_type = htmlspecialchars($_POST["r_type"]); // Regular or Special
    $fee = ($r_type == 'regular') ? 'PHP200.00' : 'PHP2500.00';
    
    $_SESSION['amount'] = $amount;

    // Define target directory for file uploads
    $target_dir = "uploads/";
    $requirements_names = [];
    $valid_id_names = [];
    $allowed_file_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

    // Process valid ID uploads
    foreach ($_FILES['validId']['name'] as $key => $name) {
        $file_type = $_FILES['validId']['type'][$key];
        $target_file = $target_dir . basename($name);

        if (in_array($file_type, $allowed_file_types)) {
            if (move_uploaded_file($_FILES['validId']['tmp_name'][$key], $target_file)) {
                $valid_id_names[] = $name;
            } else {
                echo "Error uploading the valid ID: " . htmlspecialchars($name);
            }
        } else {
            echo "Invalid file type for valid ID: " . htmlspecialchars($name);
        }
    }

    // Process requirements uploads
    foreach ($_FILES['requirements']['name'] as $key => $name) {
        $file_type = $_FILES['requirements']['type'][$key];
        $target_file = $target_dir . basename($name);

        if (in_array($file_type, $allowed_file_types)) {
            if (move_uploaded_file($_FILES['requirements']['tmp_name'][$key], $target_file)) {
                $requirements_names[] = $name;
            } else {
                echo "Error uploading the requirement: " . htmlspecialchars($name);
            }
        } else {
            echo "Invalid file type for requirement: " . htmlspecialchars($name);
        }
    }

    // Convert file names to JSON strings for storage
    $valid_id_json = json_encode($valid_id_names);
    $requirements_json = json_encode($requirements_names);

    // Prepare SQL statement with the correct columns and values
    $stmt = $conn->prepare("INSERT INTO reservation (uid, service_type, s_description, set_date, time_slot, valid_id, s_requirements, per_head, r_type, fee, amount, payment_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssisiss", $user_id, $service_type, $description, $schedule, $time_slot, $valid_id_json, $requirements_json, $per_head, $r_type, $fee, $amount, $payment_type);

    // Execute the statement
    if ($stmt->execute()) {
        // Store the last inserted service ID in the session
        $reservation_id = $stmt->insert_id;
        $_SESSION['s_id'] = $reservation_id;

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
            
            $notif_stmt = $conn->prepare("INSERT INTO notification_admin (admin_id, uid, s_id, message_noti) VALUES (?, ?, ?, ?)");
            $notif_stmt->bind_param("iiis", $admin_id, $user_id, $reservation_id, $notification_message);
            $notif_stmt->execute();
            $notif_stmt->close();
        }

        // Redirect to preview details page
        header("Location: preview-details-baptism.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
