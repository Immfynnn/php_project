<?php
// Start the session
session_start();

// Include your database connection
include '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signin.php");
    exit();
}

// Get the user ID from the session
$user_id = intval($_SESSION['uid']);

// Function to get notifications
function getNotifications($conn, $user_id) {
    $notifications = [];

    // Query to get services with the user ID and service status
    $query = "
        SELECT s.s_status, s.r_date, s.service_type, u.username, a.admin_name, s.updated_at
        FROM services s
        LEFT JOIN users u ON s.uid = u.uid
        LEFT JOIN admins a ON a.admin_id = s.admin_id
        WHERE s.uid = ?
        ORDER BY s.r_date DESC
    ";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check each service status and generate messages
        while ($row = $result->fetch_assoc()) {
            switch ($row['s_status']) {
                case 'To Pay':
                    $message = "Hello {$row['username']}, please pay to process your Reservation.";
                    break;
                case 'Pending':
                    $message = "Hello {$row['username']}, your Reservation is now pending. Please wait for the admin's response.";
                    break;
                case 'Processing':
                    $message = "Your Reservation is now Processing. Please wait.";
                    break;
                case 'Canceled':
                    $message = "Your Reservation was canceled due to unforeseen circumstances. Please contact support for details.";
                    break;
                case 'Approved':
                    $message = "Your Reservation has been approved by {$row['admin_name']}. Thank you for your patience!";
                    break;
                case 'Ongoing':
                    $message = "Your Reservation is now ongoing!";
                    break;
                case 'Completed':
                    $message = "Your reservation has been completed. Thank you for using our service!";
                    break;
                default:
                    $message = "Unknown status.";
                    break;
            }

            // Format the date with time (AM/PM)
            $dateTime = new DateTime($row['r_date']);
            $formattedDate = $dateTime->format('F j, Y, g:i A');

            // Add each message to the notifications array
            $notifications[] = [
                'message' => $message,
                'date' => $formattedDate
            ];

            // Check if the service was updated and add an additional notification
            if (!empty($row['updated_at'])) {
                $updateDateTime = new DateTime($row['updated_at']);
                $updateFormattedDate = $updateDateTime->format('F j, Y, g:i A');
                $updateMessage = "Your service has been updated by {$row['admin_name']} on {$updateFormattedDate}.";

                // Add update notification
                $notifications[] = [
                    'message' => $updateMessage,
                    'date' => $updateFormattedDate
                ];
            }
        }
        $stmt->close();
    }

    return $notifications;
}

// Fetch notifications for the logged-in user
$notifications = getNotifications($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <style>
        /* Base Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        a {
            color: #007bff;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
        a:hover {
            text-decoration: underline;
        }

        /* Notification Card Styling */
        .notification-list {
            list-style: none;
            padding: 0;
            width: 100%;
            max-width: 600px;
        }
        .notification-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .notification-card:hover {
            transform: scale(1.02);
        }
        .notification-message {
            font-size: 16px;
            color: #555;
            margin-bottom: 8px;
        }
        .notification-date {
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
    <a href="home.php" style="margin-right:140vh;">Back</a>
    <h2>Notifications</h2>
    <ul class="notification-list">
        <?php foreach ($notifications as $notification): ?>
            <li class="notification-card">
                <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                <div class="notification-date">Date: <?php echo htmlspecialchars($notification['date']); ?></div>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>

<?php
$conn->close();
?>
