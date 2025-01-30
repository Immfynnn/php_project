<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement</title>
</head>
<body>
<h1>Announcement</h1>

<?php
include 'sql/config.php'; // Database connection

session_start();

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

// Get the sender ID from session
$sender_id = intval($_SESSION['uid']);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mark announcements as read for the current user
$updateSql = "UPDATE announcement SET check_status = 1 WHERE check_status = 0";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->execute();
$updateStmt->close();

$sql = "
    SELECT announcement.*, admins.admin_name, admins.admin_image 
    FROM announcement 
    JOIN admins ON announcement.admin_id = admins.admin_id 
    ORDER BY announcement.post_date1 DESC
";
$result = $conn->query($sql);

// Check for SQL errors
if ($conn->error) {
    echo "SQL Error: " . $conn->error;
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div>";

        // Display the admin's profile image and name
        echo "<p>";
        if (!empty($row["admin_image"]) && file_exists($row["admin_image"])) {
            echo "<img src='" . htmlspecialchars($row["admin_image"]) . "' alt='Admin Image' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;'>";
        }
        echo "<strong>Posted by:</strong> " . htmlspecialchars($row["admin_name"]);
        echo "</p>";

        // Display the post content
        echo "<p>" . htmlspecialchars($row["post_content1"]) . "</p>";
        // Display date
        echo "<p>Date: " . htmlspecialchars($row["post_date1"]) . "</p>";

        echo "</div><hr>";
    }
} else {
    echo "No posts found.";
}

$conn->close();
?>
<br>
<br>

<a href="home.php">Back</a>

</body>
</html>
