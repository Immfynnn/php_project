<?php
session_start();
require_once "../config.php"; // Database connection

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

// Initialize message variable
$message = '';
$error_message = '';
// Fetch user details
$user_id = $_SESSION['uid']; // Get the logged-in user's ID from the session
$sqlUserDetails = "SELECT * FROM users WHERE uid = ?";
$stmtUser = $conn->prepare($sqlUserDetails);
$stmtUser->bind_param('i', $user_id); // Bind the user ID to the query

if ($stmtUser->execute()) {
    $resultUser = $stmtUser->get_result();
    if ($resultUser->num_rows > 0) {
        $userDetails = $resultUser->fetch_assoc(); // Fetch user details
    } else {
        echo "User details not found!";
        exit();
    }
} else {
    echo "Error fetching user details: " . $conn->error;
    exit();
}
$stmtUser->close();

// Handle the form submission to update the user details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $contactnum = $_POST['contactnum'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];

    // Check if a new profile image is uploaded
    if (isset($_FILES['userimg']) && $_FILES['userimg']['error'] == 0) {
        // Handle image upload
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($_FILES['userimg']['name']);
        if (move_uploaded_file($_FILES['userimg']['tmp_name'], $uploadFile)) {
            $userimg = $uploadFile; // Save the uploaded image path
        } else {
            echo "Error uploading the image.";
            exit();
        }
    } else {
        // Use the existing image if no new image is uploaded
        $userimg = $userDetails['userimg'];
    }

    // Update user details in the database
    $sqlUpdate = "UPDATE users SET firstname = ?, lastname = ?, username = ?, email = ?, contactnum = ?, gender = ?, address = ?, userimg = ? WHERE uid = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param('ssssssssi', $firstname, $lastname, $username, $email, $contactnum, $gender, $address, $userimg, $user_id);
    
    if ($stmtUpdate->execute()) {
        $_SESSION['update_success'] = true; // Set a session variable
        header("Location: my_profile.php");
        exit();
    }
    else {
        echo "Error updating profile: " . $conn->error;
        exit();
    }
    $stmtUpdate->close();
}

// Check for session messages and clear them
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

?>