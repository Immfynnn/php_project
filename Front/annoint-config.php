<?php
// Start the session
session_start();

// Include database connection
include '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['uid'];
    $service_type = htmlspecialchars($_POST["service_type"]);
    $s_description = htmlspecialchars($_POST["s_description"]);
    $set_date = htmlspecialchars($_POST["set_date"]);
    $time_slot = htmlspecialchars($_POST["time_slot"]);
    $s_address = htmlspecialchars($_POST["s_address"]);
    $amount = htmlspecialchars($_POST["amount"]);
    $payment_type = isset($_POST["payment_type"]) && !empty($_POST["payment_type"]) ? htmlspecialchars($_POST["payment_type"]) : null;

    $s_status = $payment_type === null ? "Pending" : "To Pay";

    $target_dir = "uploads/";
    $valid_id_files = [];
    $allowed_file_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

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

    $stmt = $conn->prepare("INSERT INTO reservation (uid, service_type, s_description, set_date, time_slot, s_address, amount, payment_type, valid_id, s_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $valid_id_json = json_encode($valid_id_files);
    $stmt->bind_param("isssssssss", $user_id, $service_type, $s_description, $set_date, $time_slot, $s_address, $amount, $payment_type, $valid_id_json, $s_status);

    if ($stmt->execute()) {
        $_SESSION['s_id'] = $stmt->insert_id;

        $user_query = $conn->prepare("SELECT username FROM users WHERE uid = ?");
        $user_query->bind_param("i", $user_id);
        $user_query->execute();
        $user_query->bind_result($username);
        $user_query->fetch();
        $user_query->close();

        $admin_query = $conn->query("SELECT admin_id FROM admins");
        while ($admin = $admin_query->fetch_assoc()) {
            $admin_id = $admin['admin_id'];
            $notification_message = "You have a new Reservation $service_type from $username. Check now!";
            
            $notif_stmt = $conn->prepare("INSERT INTO notification_admin (admin_id, uid, s_id, message_noti) VALUES (?, ?, ?, ?)");
            $notif_stmt->bind_param("iiis", $admin_id, $user_id, $_SESSION['s_id'], $notification_message);
            $notif_stmt->execute();
            $notif_stmt->close();
        }

        header("Location: annoint-preview.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

?>
