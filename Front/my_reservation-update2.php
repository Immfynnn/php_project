<?php
// Include database configuration
include '../config.php';

// Check if the s_id is passed in the form (POST)
$s_id = $_POST['s_id'] ?? null; // Use $_POST to retrieve s_id from the form submission

// Ensure s_id is provided
if (!$s_id) {
    echo "Reservation ID is missing.";
    exit();
}

// Fetch reservation details from the database
$stmt = $conn->prepare("SELECT * FROM reservation WHERE s_id = ?");
$stmt->bind_param("i", $s_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if reservation exists
if ($result->num_rows > 0) {
    $reservation = $result->fetch_assoc(); // Fetch reservation data
} else {
    echo "Reservation not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_reservation'])) {
    // Get the form data
    $s_id = $_POST['s_id']; // Ensure s_id is passed as a hidden input in the form
    $service_type = $_POST['service_type'];
    $s_description = $_POST['s_description'];
    $set_date = $_POST['schedule'];
    $time_slot = $_POST['time-slot'];
    $s_address = $_POST['s_address'];
    $amount = $_POST['amount'];

    // File uploads
    $valid_id = $_FILES['valid_id']['name'] ? $_FILES['valid_id']['name'] : $reservation['valid_id'];
    $requirements = $_FILES['requirements']['name'] ? $_FILES['requirements']['name'] : $reservation['s_requirements'];

    // Target directories
    $target_dir = "uploads/";
    $valid_id_path = $target_dir . basename($valid_id);
    $requirements_path = $target_dir . basename($requirements);

    // File upload handling
    if ($_FILES['valid_id']['tmp_name']) {
        move_uploaded_file($_FILES['valid_id']['tmp_name'], $valid_id_path);
    }

    if ($_FILES['requirements']['tmp_name']) {
        move_uploaded_file($_FILES['requirements']['tmp_name'], $requirements_path);
    }

    // Update query
    $sql = "UPDATE reservation SET 
                service_type = ?,
                s_description = ?,
                set_date = ?,
                time_slot = ?,
                s_address = ?,
                valid_id = ?,
                s_requirements = ?,
                amount = ?,
                updated_at = NOW()
            WHERE s_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $service_type, $s_description, $set_date, $time_slot, $s_address, $valid_id, $requirements, $amount, $s_id);

    if ($stmt->execute()) {
        // Redirect to reservation details page with a success message
        header("Location: preview-details2.php?s_id=$s_id&status=success");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
