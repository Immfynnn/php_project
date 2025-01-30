<?php
session_start();
require_once "../config.php";

// Check if the user is logged in
if (isset($_SESSION['uid'])) {
    // Update user status to "Offline"
    $uid = $_SESSION['uid'];
    $updateStatusSql = "UPDATE users SET user_status = 'Offline' WHERE uid = ?";
    $stmt = $conn->prepare($updateStatusSql);
    $stmt->bind_param("i", $uid);
    


    header("Location: signin.php");

    if ($stmt->execute()) {
        // Destroy the session
        session_destroy();
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }

    $stmt->close();
} else {
    exit();
} 
?>
x
