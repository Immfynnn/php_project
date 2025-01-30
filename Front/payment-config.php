<?php
session_start();
require_once "../config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ensure the user is logged in
    if (!isset($_SESSION['uid'])) {
        echo "User is not logged in.";
        exit();
    }

    $uid = $_SESSION['uid'];
    $s_id = $_SESSION['s_id']; // Reservation ID from session

    // Fetch and sanitize user input
    $total_amount = $_POST['total_amount'] ?? 0;
    $ref_num = $_POST['ref_num'] ?? null;
    $pay_date = $_POST['pay_date'] ?? null;
    $p_screenshot = $_FILES['p_screenshot'] ?? null;

    // Validate screenshot
    if ($p_screenshot && $p_screenshot['error'] === UPLOAD_ERR_OK) {
        // Generate unique file name
        $uploadDir = "uploads/payments/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid("payment_", true) . "." . pathinfo($p_screenshot['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $fileName;

        // Move file to upload directory
        if (move_uploaded_file($p_screenshot['tmp_name'], $filePath)) {
            // Generate a random 5-digit pay_id
            $pay_id = rand(10000, 99999);

            // Ensure the pay_id is unique by checking the database
            $stmtCheckPayId = $conn->prepare("SELECT pay_id FROM payment WHERE pay_id = ?");
            $stmtCheckPayId->bind_param("i", $pay_id);
            $stmtCheckPayId->execute();
            $resultCheckPayId = $stmtCheckPayId->get_result();

            // If pay_id is already taken, generate a new one
            while ($resultCheckPayId->num_rows > 0) {
                $pay_id = rand(10000, 99999);
                $stmtCheckPayId->execute();
                $resultCheckPayId = $stmtCheckPayId->get_result();
            }
            $stmtCheckPayId->close();

            // Insert payment details into the `payment` table with ref_num and pay_date
            $stmtPayment = $conn->prepare("INSERT INTO payment (pay_id, uid, s_id, p_screenshot, total_amount, ref_num, pay_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtPayment->bind_param("iiissis", $pay_id, $uid, $s_id, $filePath, $total_amount, $ref_num, $pay_date);

            if ($stmtPayment->execute()) {
                // Update the reservation status to 'Pending'
                $stmtReservation = $conn->prepare("UPDATE reservation SET s_status = 'Pending' WHERE s_id = ?");
                $stmtReservation->bind_param("i", $s_id);

                if ($stmtReservation->execute()) {
                    // Fetch the service type for the reservation
                    $stmtService = $conn->prepare("SELECT service_type FROM reservation WHERE s_id = ?");
                    $stmtService->bind_param("i", $s_id);
                    $stmtService->execute();
                    $resultService = $stmtService->get_result();
                    $service = $resultService->fetch_assoc();
                    $service_type = $service['service_type'] ?? "unknown service";

                    // Fetch the username of the user
                    $stmtUser = $conn->prepare("SELECT username FROM users WHERE uid = ?");
                    $stmtUser->bind_param("i", $uid);
                    $stmtUser->execute();
                    $resultUser = $stmtUser->get_result();
                    $user = $resultUser->fetch_assoc();
                    $username = $user['username'] ?? "unknown user";

                    // Notify all admins
                    $notificationMessage = "$username has sent Gcash for $service_type. Check the receipt now.";
                    $stmtAdmins = $conn->prepare("SELECT admin_id FROM admins");
                    $stmtAdmins->execute();
                    $resultAdmins = $stmtAdmins->get_result();

                    while ($admin = $resultAdmins->fetch_assoc()) {
                        $admin_id = $admin['admin_id'];
                        $stmtNotify = $conn->prepare("INSERT INTO notification_admin (admin_id, uid, s_id, pay_id, message_noti) VALUES (?, ?, ?, ?, ?)");
                        $stmtNotify->bind_param("iiiss", $admin_id, $uid, $s_id, $pay_id, $notificationMessage);
                        $stmtNotify->execute();
                        $stmtNotify->close();
                    }

                    $stmtAdmins->close();
                    $stmtUser->close();
                    $stmtService->close();

                    // Redirect or display success message
                    header("Location: reservation-receipt.php?status=success");
                    exit();
                } else {
                    echo "Error updating reservation status: " . $conn->error;
                }
                $stmtReservation->close();
            } else {
                echo "Error inserting payment details: " . $conn->error;
            }
            $stmtPayment->close();
        } else {
            echo "Error uploading payment screenshot.";
        }
    } else {
        echo "Please upload a valid payment screenshot.";
    }
} else {
    echo "Invalid request method.";
}

// Close the connection
$conn->close();
?>
