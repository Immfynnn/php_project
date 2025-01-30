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
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_type = htmlspecialchars($_POST["service_type"]);
    $s_description = htmlspecialchars($_POST["s_description"]);
    $s_description1 = htmlspecialchars($_POST["s_description1"]);
    $set_date = htmlspecialchars($_POST["set_date"]);
    $time_slot = htmlspecialchars($_POST["time-slot"]);
    $r_type = htmlspecialchars($_POST["r_type"]);
    $priest = htmlspecialchars($_POST["priest"] ?? '');
    $per_head = htmlspecialchars($_POST["per_head"]);
    $amount = htmlspecialchars($_POST["amount"]);
    $payment_type = htmlspecialchars($_POST["payment_type"]);

    // Handle uploads for all requirements
    $requirements_fields = [
        'validId' => 'valid_id',
        'requirements' => 's_requirements',
        'requirements1' => 's_requirements1',
        'requirements2' => 's_requirements2',
        'requirements3' => 's_requirements3',
        'requirements4' => 's_requirements4'
    ];

    $uploads = [];
    foreach ($requirements_fields as $input_name => $db_column) {
        if (!empty($_FILES[$input_name]['name'][0])) {
            $files = [];
            foreach ($_FILES[$input_name]['tmp_name'] as $key => $tmp_name) {
                $file_name = time() . '_' . basename($_FILES[$input_name]['name'][$key]);
                $target_path = "uploads/" . $file_name;
                if (move_uploaded_file($tmp_name, $target_path)) {
                    $files[] = $file_name;
                }
            }
            $uploads[$db_column] = json_encode($files);
        } else {
            $uploads[$db_column] = $reservation[$db_column];
        }
    }

    // Update reservation details
    $stmtUpdate = $conn->prepare("
        UPDATE reservation 
        SET service_type = ?, s_description = ?, s_description1 = ?, set_date = ?, time_slot = ?, r_type = ?, priest = ?, per_head = ?, amount = ?, 
            payment_type = ?, valid_id = ?, s_requirements = ?, s_requirements1 = ?, s_requirements2 = ?, s_requirements3 = ?, s_requirements4 = ?, updated_at = NOW()
        WHERE s_id = ?
    ");
    $stmtUpdate->bind_param(
        "ssssssssssssssssi",
        $service_type,
        $s_description,
        $s_description1,
        $set_date,
        $time_slot,
        $r_type,
        $priest,
        $per_head,
        $amount,
        $payment_type,
        $uploads['valid_id'],
        $uploads['s_requirements'],
        $uploads['s_requirements1'],
        $uploads['s_requirements2'],
        $uploads['s_requirements3'],
        $uploads['s_requirements4'],
        $s_id
    );

    if ($stmtUpdate->execute()) {
        header("Location: wedding-preview2.php?s_id=" . $s_id);
        exit();
    } else {
        echo "Error updating reservation: " . $stmtUpdate->error;
    }
}
?>
