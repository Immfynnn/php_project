<?php
session_start();
require '../config.php';

// Check if the status is being sent via POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status']) && isset($_SESSION['username'])) {
    $status = $_POST['status'];
    $username = $_SESSION['username'];

    // Update the user's status in the database
    $query = "UPDATE users SET status = ? WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $status, $username);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo 'Status updated successfully';
    } else {
        echo 'Failed to update status';
    }

    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid request';
}
?>
