<?php
include '../config.php'; // Database connection

session_start();
$admin_id = $_SESSION['admin_id']; // Ensure you have a way to get the logged-in admin's ID

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    $stmt = $conn->prepare("UPDATE posts SET likes = likes + 1 WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);

    if ($stmt->execute()) {
        header("Location: home.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
