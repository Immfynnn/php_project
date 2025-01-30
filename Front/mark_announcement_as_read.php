<?php
session_start();
require_once "../config.php";

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

// Check if announcement ID is provided
if (isset($_GET['post_aid'])) {
    $post_aid = intval($_GET['post_aid']);

    // Update the announcement status to read
    $sql = "UPDATE announcement SET check_status = 1 WHERE post_aid = ?";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $post_aid);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            // Redirect to the announcement display page or wherever appropriate
            header("Location: anncmnt-display.php");
            exit();
        } else {
            echo "Failed to mark announcement as read.";
        }
    } else {
        echo "Database error: " . mysqli_error($conn);
    }
} else {
    echo "No announcement ID provided.";
}

$conn->close();
?>
