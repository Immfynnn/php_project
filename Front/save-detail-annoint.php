<?php
session_start();
require_once "../config.php";

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

$uid = $_SESSION['uid'];
$s_id = $_SESSION['s_id'] ?? null;

if (!$s_id || !is_numeric($s_id)) {
    echo "Invalid reservation ID.";
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
    $set_date = htmlspecialchars($_POST["set_date"]);
    $time_slot = htmlspecialchars($_POST["time_slot"]);
    $s_address = htmlspecialchars($_POST["s_address"]);
    $amount = htmlspecialchars($_POST["amount"]);
    $payment_type = htmlspecialchars($_POST["payment_type"]);


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
        SET service_type = ?, s_description = ?, set_date = ?, time_slot = ?, s_address = ?, amount = ?, payment_type = ?, valid_id = ?, updated_at = NOW() 
        WHERE s_id = ?
    ");
    $stmtUpdate->bind_param(
        "ssssssssi",
        $service_type,
        $s_description,
        $set_date,
        $time_slot,
        $s_address,
        $amount,
        $payment_type,
        $valid_id_path,
        $s_id
    );

    if ($stmtUpdate->execute()) {
        header("Location: annoint-preview.php?s_id=" . $s_id);
        exit();
    } else {
        echo "Error updating reservation: " . $stmtUpdate->error;
    }
}
?>
