<?php
session_start();
require_once "../config.php";


// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signup.php");
    exit();
}

// Get the search term from the AJAX request
if (isset($_GET['term'])) {
    $searchTerm = $_GET['term'];

    // Query the database to fetch matching usernames
    $sql = "SELECT admin_username FROM admins WHERE admin_username LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTermLike = "%$searchTerm%";
    $stmt->bind_param("s", $searchTermLike);
    $stmt->execute();
    $result = $stmt->get_result();

    // Create an array to store the results
    $usernames = [];
    while ($row = $result->fetch_assoc()) {
        $usernames[] = $row['admin_username'];
    }

    // Return the results as JSON
    echo json_encode($usernames);
}
?>
