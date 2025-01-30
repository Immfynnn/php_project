<?php
session_start();
require_once "../config.php";

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

// Check if the s_id is passed in the form (POST)
$s_id = $_POST['s_id'] ?? null; // Use $_POST to retrieve s_id from the form submission

// Ensure s_id is provided
if (!$s_id) {
    echo "Reservation ID is missing.";
    exit();
}

// Fetch reservation details
$stmt = $conn->prepare("SELECT * FROM reservation WHERE s_id = ?");
$stmt->bind_param("i", $s_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $reservation = $result->fetch_assoc();
} else {
    echo "Reservation not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_type = htmlspecialchars($_POST["service_type"]);
    $s_description = htmlspecialchars($_POST["s_description"]);
    $s_description1 = htmlspecialchars($_POST["s_description1"]);
    $r_type = htmlspecialchars($_POST["r_type"]);

    // Handle valid ID upload
    $valid_id_path = $reservation['valid_id']; // Default to existing value
    if (!empty($_FILES['validId']['name'][0])) {
        $valid_id_files = [];
        foreach ($_FILES['validId']['tmp_name'] as $key => $tmp_name) {
            $file_name = time() . '_' . basename($_FILES['validId']['name'][$key]);
            $target_path = "uploads/" . $file_name;

            // Validate file (e.g., size, type)
            $file_type = mime_content_type($tmp_name);
            $file_size = filesize($tmp_name);
            if (in_array($file_type, ['image/jpeg', 'image/png', 'application/pdf']) && $file_size <= 2000000) { // 2MB limit
                if (move_uploaded_file($tmp_name, $target_path)) {
                    $valid_id_files[] = $file_name;
                } else {
                    echo "Failed to upload file: " . $_FILES['validId']['name'][$key];
                    exit();
                }
            } else {
                echo "Invalid file type or size for: " . $_FILES['validId']['name'][$key];
                exit();
            }
        }
        $valid_id_path = json_encode($valid_id_files);
    }

    // Update reservation details
    $stmtUpdate = $conn->prepare("
        UPDATE reservation 
        SET service_type = ?, s_description = ?, s_description1 = ?, r_type = ?, valid_id = ?, updated_at = NOW() 
        WHERE s_id = ?
    ");
    $stmtUpdate->bind_param(
        "sssssi",
        $service_type,
        $s_description,
        $s_description1,
        $r_type,
        $valid_id_path,
        $s_id
    );

    if ($stmtUpdate->execute()) {
        header("Location: mass-preview2.php?s_id=" . $s_id);
        exit();
    } else {
        echo "Error updating reservation: " . $stmtUpdate->error;
    }
}
?>
