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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_reservation'])) {
    // Get the form data
    $service_type = $_POST['service_type'];
    $s_description = $_POST['s_description'];
    $set_date = $_POST['schedule'];

    // File uploads
    $valid_id = $_FILES['validId']['name'] ? $_FILES['validId']['name'] : $reservation['valid_id'];
    $requirements = $_FILES['requirements']['name'] ? $_FILES['requirements']['name'] : $reservation['s_requirements'];

   // Handle file upload for 'valid_id'
$valid_id = "";
if (!empty($_FILES['validId']['name'][0])) {
    // Handle multiple files
    $valid_id = [];
    $target_dir = "uploads/";
    foreach ($_FILES['validId']['name'] as $key => $value) {
        $file_name = basename($_FILES['validId']['name'][$key]);
        $file_path = $target_dir . $file_name;
        move_uploaded_file($_FILES['validId']['tmp_name'][$key], $file_path);
        $valid_id[] = $file_path; // Store each uploaded file path
    }
    $valid_id = implode(",", $valid_id); // Join file paths with a comma
} else {
    // Use existing file if no new file is uploaded
    $valid_id = $reservation['valid_id'];
}

// Handle file upload for 'requirements'
$requirements = "";
if (!empty($_FILES['requirements']['name'][0])) {
    // Handle multiple files
    $requirements = [];
    foreach ($_FILES['requirements']['name'] as $key => $value) {
        $file_name = basename($_FILES['requirements']['name'][$key]);
        $file_path = $target_dir . $file_name;
        move_uploaded_file($_FILES['requirements']['tmp_name'][$key], $file_path);
        $requirements[] = $file_path; // Store each uploaded file path
    }
    $requirements = implode(",", $requirements); // Join file paths with a comma
} else {
    // Use existing file if no new file is uploaded
    $requirements = $reservation['s_requirements'];
}

// Prepare SQL query
$sql = "UPDATE reservation SET 
            service_type = ?,
            s_description = ?,
            set_date = ?,
            valid_id = ?,
            s_requirements = ?,
            updated_at = NOW()
        WHERE s_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $service_type, $s_description, $set_date, $valid_id, $requirements, $s_id);

if ($stmt->execute()) {
    // Redirect to reservation details page with a success message
    header("Location: confrm-preview2.php?s_id=$s_id&status=success");
    exit();
} else {
    echo "Error updating record: " . $conn->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
}
?>
