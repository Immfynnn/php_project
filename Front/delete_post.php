<?php
include '../config.php'; // Database connection

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['post_id'])) {
    $postId = intval($_GET['post_id']);

    // Delete the post
    $sql = "DELETE FROM posts WHERE post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postId);

    if ($stmt->execute()) {
        echo "<script>alert('Post deleted successfully.'); window.location.href = 'admin-post.php';</script>";
    } else {
        echo "<script>alert('Failed to delete post.'); window.location.href = 'admin-post.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Invalid request.'); window.location.href = 'admin-post.php';</script>";
}

$conn->close();
?>
