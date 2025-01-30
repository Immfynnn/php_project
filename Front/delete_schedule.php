<?php 
require_once('../config.php');

// Check if 'id' is passed
if (!isset($_GET['id'])) {
    echo "<script> alert('Undefined Schedule ID.'); location.replace('./') </script>";
    $conn->close();
    exit;
}

// Delete the schedule
$delete = $conn->query("DELETE FROM `schedule_list` WHERE id = '{$_GET['id']}'");

if ($delete) {
    // Redirect to admin-schedule.php with status=deleted
    echo "<script> location.replace('admin-schedule.php?status=deleted'); </script>";
} else {
    // Handle errors
    echo "<pre>";
    echo "An Error occurred.<br>";
    echo "Error: " . $conn->error . "<br>";
    echo "SQL: " . $sql . "<br>";
    echo "</pre>";
}

$conn->close();
?>
