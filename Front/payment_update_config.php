<?php
session_start();
require_once '../config.php'; // Include database connection

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get form values
    $s_status = trim($_POST['s_status'] ?? '');
    $service_id = intval($_POST['service_id'] ?? 0);

    // Validate inputs
    if ($s_status && $service_id > 0) {
        try {
            // Begin transaction
            $conn->begin_transaction();

            // Update payment status
            $sqlUpdate = "UPDATE payment SET p_status = ? WHERE s_id = ?";
            $stmt = $conn->prepare($sqlUpdate);
            $stmt->bind_param('si', $s_status, $service_id);

            if ($stmt->execute()) {
                // Fetch related data for notification
                $sqlServiceInfo = "SELECT r.uid, r.service_type, r.payment_type, r.s_status FROM reservation r WHERE r.s_id = ?";
                $stmtInfo = $conn->prepare($sqlServiceInfo);
                $stmtInfo->bind_param('i', $service_id);
                $stmtInfo->execute();
                $serviceInfo = $stmtInfo->get_result()->fetch_assoc();

                if ($serviceInfo) {
                    $user_id = $serviceInfo['uid'];
                    $service_type = $serviceInfo['service_type'];
                    $payment_type = $serviceInfo['payment_type'];

                    // Fetch payment ID
                    $sqlPayment = "SELECT pay_id FROM payment WHERE s_id = ?";
                    $stmtPayment = $conn->prepare($sqlPayment);
                    $stmtPayment->bind_param('i', $service_id);
                    $stmtPayment->execute();
                    $paymentInfo = $stmtPayment->get_result()->fetch_assoc();

                    if ($paymentInfo) {
                        $pay_id = $paymentInfo['pay_id'];

                        // Prepare notification message
                        $messages = [
                            'Over the Counter-Paid' => "Your Over the Counter $service_type is Successfully Paid. Thank you!",
                            'Over the Counter-Canceled' => "Your Over the Counter is successfully Canceled due to unforeseen circumstances. We apologize for the inconvenience.",
                            'Gcash (Scan / Send Money)-Paid' => "Your GCash payment has been successfully paid! Your $service_type is now being processed. You will receive further updates shortly.",
                            'Gcash (Scan / Send Money)-Refund' => "Your refund request has been received. The amount will be processed and credited back to your GCash account within 5 minutes. Thank you for your patience.",
                            'Gcash (Scan / Send Money)-Canceled' => "Your transaction has been successfully canceled due to unforeseen circumstances. We apologize for the inconvenience."
                        ];

                        $key = "$payment_type-$s_status";
                        $message = $messages[$key] ?? "Your $service_type status has been updated to $s_status. Thank You!";

                        // Insert notification
                        $sqlNotification = "INSERT INTO notifications (uid, s_id, pay_id, message) VALUES (?, ?, ?, ?)";
                        $stmtNotification = $conn->prepare($sqlNotification);
                        $stmtNotification->bind_param('iiis', $user_id, $service_id, $pay_id, $message);
                        $stmtNotification->execute();

                        // Commit transaction
                        $conn->commit();

                        // Redirect to success page
                        header("Location: admin-payment-reservation.php?status=success");
                        exit();
                    } else {
                        throw new Exception("Payment record not found.");
                    }
                } else {
                    throw new Exception("Service information not found.");
                }
            } else {
                throw new Exception("Failed to update payment status.");
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        } finally {
            $stmt->close();
        }
    } else {
        echo "Invalid status or service ID.";
    }
} else {
    // Redirect if not a POST request
    header("Location: admin-payment-reservation.php");
    exit();
}

// Close database connection
$conn->close();
?>