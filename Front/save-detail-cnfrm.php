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

if (!$s_id) {
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
    $schedule = htmlspecialchars($_POST["schedule"]);
    $r_type = htmlspecialchars($_POST["r_type"]);
    $amount = htmlspecialchars($_POST["amount"]);
    $payment_type = htmlspecialchars($_POST["payment_type"]);

    // Handle valid ID upload
    if (!empty($_FILES['validId']['name'][0])) {
        $valid_id_files = [];
        foreach ($_FILES['validId']['tmp_name'] as $key => $tmp_name) {
            $file_name = time() . '_' . basename($_FILES['validId']['name'][$key]);
            $target_path = "uploads/" . $file_name;
            if (move_uploaded_file($tmp_name, $target_path)) {
                $valid_id_files[] = $file_name;
            }
        }
        $valid_id_path = json_encode($valid_id_files);
    } else {
        $valid_id_path = $reservation['valid_id'];
    }

    // Handle requirements upload
    if (!empty($_FILES['requirements']['name'][0])) {
        $requirements_files = [];
        foreach ($_FILES['requirements']['tmp_name'] as $key => $tmp_name) {
            $file_name = time() . '_' . basename($_FILES['requirements']['name'][$key]);
            $target_path = "uploads/" . $file_name;
            if (move_uploaded_file($tmp_name, $target_path)) {
                $requirements_files[] = $file_name;
            }
        }
        $requirements_path = json_encode($requirements_files);
    } else {
        $requirements_path = $reservation['s_requirements'];
    }

    // Update reservation details
    $stmtUpdate = $conn->prepare("
        UPDATE reservation 
        SET service_type = ?, s_description = ?, set_date = ?, r_type = ?, amount = ?, payment_type = ?, valid_id = ?, s_requirements = ?, updated_at = NOW() 
        WHERE s_id = ?
    ");
    $stmtUpdate->bind_param(
        "ssssssssi",
        $service_type,
        $s_description,
        $schedule,
        $r_type,
        $amount,
        $payment_type,
        $valid_id_path,
        $requirements_path,
        $s_id
    );

    if ($stmtUpdate->execute()) {
        header("Location: confirmation-prev.php?s_id=" . $s_id);
        exit();
    } else {
        echo "Error updating reservation: " . $stmtUpdate->error;
    }
}
?>
