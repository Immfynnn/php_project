<?php
session_start();
require_once "../config.php";

// Check if the user is logged in
if (!isset($_SESSION['uid']) || empty($_SESSION['uid'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php");
    exit();
}

$uid = $_SESSION['uid']; // Ensure that $uid is set properly

// Check if the user has a session for the reservation ID
$s_id = $_SESSION['s_id'] ?? null; // Using null coalescing operator for safety

if ($s_id) {
    // Prepare and execute the query to retrieve the reservation details
    $stmt = $conn->prepare("SELECT * FROM reservation WHERE s_id = ?");
    $stmt->bind_param("i", $s_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if reservation exists
    if ($result->num_rows > 0) {
        // Fetch reservation data
        $reservation = $result->fetch_assoc();
    } else {
        echo "Reservation not found.";
        exit();
    }
} else {
    echo "Invalid reservation ID.";
    exit();
}

// Handle form submission to update reservation details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_reservation'])) {
    // Capture POST data from the form
    $service_type = $_POST['service_type'];
    $s_description = $_POST['s_description'];
    $schedule = $_POST['schedule'];
    $time_slot = $_POST['time-slot'];  // Capture the selected time slot from the form
    $s_address = $_POST['s_address'];
    $amount = $_POST['amount'];
    $payment_type = $_POST['payment_type'];

    // Debugging: Check if the time_slot is being set correctly
    if (empty($time_slot)) {
        echo "Error: Time slot is empty!";
        exit();
    }

   // Handle file upload for valid ID
if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] == 0) {
    $valid_id = $_FILES['valid_id'];
    $valid_id_filename = time() . '_' . basename($valid_id['name']);
    $valid_id_target = "uploads/" . $valid_id_filename;
    
    if (move_uploaded_file($valid_id['tmp_name'], $valid_id_target)) {
        // Save the file path in the database (JSON encoded if multiple files are allowed)
        $valid_id_path = json_encode([$valid_id_filename]);
    } else {
        $valid_id_path = $reservation['valid_id'];  // Keep the old file if upload fails
    }
} else {
    $valid_id_path = $reservation['valid_id'];  // Keep the old file if no new file uploaded
}

// Handle file upload for requirements
if (isset($_FILES['requirements']) && $_FILES['requirements']['error'] == 0) {
    $requirements = $_FILES['requirements'];
    $requirements_filename = time() . '_' . basename($requirements['name']);
    $requirements_target = "uploads/" . $requirements_filename;
    
    if (move_uploaded_file($requirements['tmp_name'], $requirements_target)) {
        // Save the file path in the database (JSON encoded if multiple files are allowed)
        $requirements_path = json_encode([$requirements_filename]);
    } else {
        $requirements_path = $reservation['s_requirements'];  // Keep the old file if upload fails
    }
} else {
    $requirements_path = $reservation['s_requirements'];  // Keep the old file if no new file uploaded
}

// Prepare and execute the update query
$stmtUpdate = $conn->prepare("UPDATE reservation SET service_type = ?, s_description = ?, set_date = ?, time_slot = ?, s_address = ?, amount = ?, payment_type = ?, valid_id = ?, s_requirements = ?, updated_at = NOW() WHERE s_id = ?");
$stmtUpdate->bind_param("sssssssssi", $service_type, $s_description, $schedule, $time_slot, $s_address, $amount, $payment_type, $valid_id_path, $requirements_path, $s_id);

if ($stmtUpdate->execute()) {
    // Redirect after successful update
    header("Location: preview-details.php?s_id=" . $s_id);
    exit();
} else {
    echo "Error updating reservation.";
}

}
?>
