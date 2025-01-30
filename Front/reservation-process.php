<?php
// Include the database configuration
include '../config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signup.php");
    exit();
}

// Get the user ID from the session
$user_id = intval($_SESSION['uid']);

// Check user status
$sqlStatus = "SELECT user_status FROM users WHERE uid = ?";
$statusStmt = $conn->prepare($sqlStatus);
$statusStmt->bind_param('i', $user_id);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();
$user = $statusResult->fetch_assoc();

// Redirect if the user is Offline
if ($user['user_status'] === 'Offline') {
    header("Location: signin.php");
    exit();
}

function getStatusColor($status) {
    switch ($status) {
        case 'Pending':
            return 'orange';
        case 'Paid':
            return 'green';
        case 'To Pay':
            return 'red';
        case 'Canceled':
            return 'red';
        case 'Processing':
            return 'yellow'; // Corrected from 'darkyellow' to 'yellow' for CSS compatibility
        case 'Approved':
            return 'green';
        case 'Ongoing':
            return 'green';
        case 'Completed':
            return 'blue';
        default:
            return 'black'; // Default color for unknown statuses
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .cont-process {
            width: 90%;
            margin: 30px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .header h2 {
            margin: 0;
            color: #333;
        }

        a {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
        }

        a:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 20px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f9;
            font-weight: bold;
        }

        tr {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            color: #000;
        }
    </style>
    <script>
        // Function to handle the row click and navigate to reservation details
        function goToReservationDetails(s_id) {
            window.location.href = "my_reservation_details.php?s_id=" + s_id;
        }
    </script>
</head>
<body>
    <div class="cont-process">
        <div class="header">
            <h2>My Reservation</h2>
        </div>
        <a href="reservation.php">New Reservation</a>
        <a href="home.php" style="margin-left:151vh;">Home</a>
        <table>
            <thead>
               <tr>
                   <th>Name of Reservation</th>
                   <th>Date</th> <!-- Updated column header -->
                   <th>Payment Type</th>
                   <th>Payment Status</th>
                   <th>Status</th>
               </tr>
            </thead>
            <tbody>
                <?php
                // Query to fetch reservations for the logged-in user
                $sql = "SELECT s.s_id, s.service_type, s.s_date, s.payment_type, s.r_date, p.p_status, s.s_status 
                        FROM services s 
                        LEFT JOIN payment p ON s.s_id = p.s_id 
                        WHERE s.uid = ? 
                        ORDER BY s.s_date DESC"; // Only show reservations for the logged-in user

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id); // Bind the user ID to the query
                $stmt->execute();
                $result = $stmt->get_result();

                // Check if there are results
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr onclick='goToReservationDetails(" . $row['s_id'] . ")'>"; // Make the row clickable
                        echo "<td>" . htmlspecialchars($row['service_type']) . "</td>";
                        // Format the reservation date and time
                        $formatted_date_time = date('F j, Y, g:i A', strtotime($row['r_date']));
                        echo "<td>" . $formatted_date_time . "</td>"; // Display formatted date and time
                        echo "<td>" . htmlspecialchars($row['payment_type']) . "</td>";

                        // Apply status colors to payment status
                        $p_status_color = getStatusColor($row['p_status']);
                        echo "<td style='color: $p_status_color;'>" . htmlspecialchars($row['p_status']  ?? 'Pending') . "</td>";

                        // Apply status colors to service status
                        $s_status_color = getStatusColor($row['s_status']);
                        echo "<td style='color: $s_status_color;'>" . htmlspecialchars($row['s_status']) . "</td>";

                        echo "</tr>";
                    }
                } else {
                    // No data found
                    echo "<tr><td colspan='5'>No reservations found</td></tr>";
                }

                $stmt->close();
                $conn->close(); // Close the database connection
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
