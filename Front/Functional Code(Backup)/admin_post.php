<?php
include 'sql/config.php';

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo "Admin not logged in.";
    exit();
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_content = $_POST['post_content'];
    $post_image = null;

    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["post_image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["post_image"]["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        if ($_FILES["post_image"]["size"] > 5000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file)) {
                $post_image = $target_file;
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO posts (admin_id, post_image, post_content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $admin_id, $post_image, $post_content);

    if ($stmt->execute()) {
        echo "The post has been uploaded.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Post</title>
</head>
<body>
    <h1>Upload Post</h1>
    <form action="admin_post.php" method="post" enctype="multipart/form-data">
        <label for="post_content">Post Content:</label><br>
        <textarea id="post_content" name="post_content" rows="4" cols="50" required></textarea><br><br>
        
        <label for="post_image">Post Image (Optional):</label><br>
        <input type="file" id="post_image" name="post_image" accept="image/*"><br><br>
        
        <input type="submit" value="Upload Post">
        <a href="display_post.php">View post</a>
    </form>
</body>
</html>
