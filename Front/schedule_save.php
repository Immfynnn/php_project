<?php 
require_once('../config.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "<script> alert('Error: No data to save.'); location.replace('./') </script>";
    $conn->close();
    exit;
}

extract($_POST);

if (empty($id)) {
    $sql = "INSERT INTO `schedule_list` (`title`, `description`, `start_datetime`, `end_datetime`) VALUES ('$title', '$description', '$start_datetime', '$end_datetime')";
} else {
    $sql = "UPDATE `schedule_list` SET `title` = '{$title}', `description` = '{$description}', `start_datetime` = '{$start_datetime}', `end_datetime` = '{$end_datetime}' WHERE `id` = '{$id}'";
}

$save = $conn->query($sql);
if ($save) {
    // On success, redirect with status=success
    echo "<script> location.replace('admin-schedule.php?status=success'); </script>";
} else {
    // On failure, show an error
    echo "<pre>";
    echo "An Error occurred.<br>";
    echo "Error: " . $conn->error . "<br>";
    echo "SQL: " . $sql . "<br>";
    echo "</pre>";
}

$conn->close();
?>
