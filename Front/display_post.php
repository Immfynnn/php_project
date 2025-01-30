<?php
include '../config.php'; // Database connection

session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sql = "
    SELECT posts.*, admins.admin_name, admins.admin_image 
    FROM posts 
    JOIN admins ON posts.admin_id = admins.admin_id 
    ORDER BY posts.post_date DESC
";
$result = $conn->query($sql);

// Check for SQL errors
if ($conn->error) {
    echo "SQL Error: " . $conn->error;
}

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<a href='admin_post.php'>Upload New</a>";
        echo "<div>";
        
        // Display the admin's profile image and name
        echo "<p>";
        if (!empty($row["admin_image"]) && file_exists($row["admin_image"])) {
            echo "<img src='" . htmlspecialchars($row["admin_image"]) . "' alt='Admin Image' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;'>";
        } else {
            echo "";
        }
        echo "<strong>Posted by:</strong> " . htmlspecialchars($row["admin_name"]);
        echo "</p>";
        
        // Display the post content
        echo "<p>" . htmlspecialchars($row["post_content"]) . "</p>";
        
        // Check if post_image is not empty and exists
        if (!empty($row["post_image"]) && file_exists($row["post_image"])) {
            echo "<img src='" . htmlspecialchars($row["post_image"]) . "' alt='Post Image' style='max-width: 300px;'><br>";
        } else {
            echo "<p>No post image available.</p>";
        }
        
        // Display likes and date
        echo "<p>Likes: " . htmlspecialchars($row["likes"]) . " <a href='like_post.php?post_id=" . $row["post_id"] . "'>Like</a></p>";
        echo "<p>Date: " . htmlspecialchars($row["post_date"]) . "</p>";
        echo "</div><hr>";

    }
} else {
    echo "No posts found.";
}

$conn->close();
?>
