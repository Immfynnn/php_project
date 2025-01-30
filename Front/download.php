<?php
require '../config.php'; // Adjust the path to your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservationType = $_POST['reservation_type'] ?? 'Overall';
    $filename = 'reservation_report_' . strtolower(str_replace(' ', '_', $reservationType)) . '.csv';

    // Set headers for file download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Write title and address as the first row in the CSV
    fputcsv($output, ['                                The Roman Catholic Archdiocese of Cebu']);
    fputcsv($output, ['                        Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan']);
    fputcsv($output, ['                            Santa Rosa Street, Poblacion, Daanbantayan, Cebu']);
    fputcsv($output, []); // Empty row to separate title from the data

    // Calculate reservation counts by status
    $totalsQuery = "
        SELECT 
            COUNT(*) AS total_reservations,
            SUM(CASE WHEN s_status = 'Pending' THEN 1 ELSE 0 END) AS total_pending,
            SUM(CASE WHEN s_status = 'Approved' THEN 1 ELSE 0 END) AS total_approved,
            SUM(CASE WHEN s_status = 'Ongoing' THEN 1 ELSE 0 END) AS total_ongoing,
            SUM(CASE WHEN s_status = 'Completed' THEN 1 ELSE 0 END) AS total_completed,
            SUM(CASE WHEN s_status = 'Canceled' THEN 1 ELSE 0 END) AS total_canceled
        FROM reservation";
    
    if ($reservationType !== 'Overall') {
        $totalsQuery .= " WHERE service_type = ?";
    }

    $stmt = $conn->prepare($totalsQuery);

    if ($reservationType !== 'Overall') {
        $stmt->bind_param('s', $reservationType);
    }

    $stmt->execute();
    $totalsResult = $stmt->get_result();
    $totals = $totalsResult->fetch_assoc();

    // Write reservation counts
    fputcsv($output, ['    Total Reservation:', $totals['total_reservations'] ?? 0]);
    fputcsv($output, ['    Total Pending Reservation:', $totals['total_pending'] ?? 0]);
    fputcsv($output, ['    Total Approved Reservation:', $totals['total_approved'] ?? 0]);
    fputcsv($output, ['    Total Ongoing Reservation:', $totals['total_ongoing'] ?? 0]);
    fputcsv($output, ['    Total Completed Reservation:', $totals['total_completed'] ?? 0]);
    fputcsv($output, ['    Total Canceled Reservation:', $totals['total_canceled'] ?? 0]);
    fputcsv($output, []); // Empty row for spacing
    fputcsv($output, ['                                         ______________________________']); 
    fputcsv($output, ['                                           Signature over Printed Name']);
    fputcsv($output, []); // Empty row for spacing

    // Write the column headers
    fputcsv($output, ['Reservation #', 'User ID', 'User Name', 'Reservation Type', 'Description', 'Date', 'Time Slot', 'Status']);

    // Query to fetch reservations along with user names or usernames
    $query = "
        SELECT 
            r.s_id, 
            r.uid, 
            CASE 
                WHEN u.firstname IS NOT NULL AND u.firstname != '' 
                     AND u.lastname IS NOT NULL AND u.lastname != '' 
                THEN CONCAT(u.firstname, ' ', u.lastname)
                ELSE u.username 
            END AS user_name, 
            r.service_type, 
            r.s_description, 
            r.set_date, 
            r.time_slot, 
            r.s_status 
        FROM reservation r
        LEFT JOIN users u ON r.uid = u.uid";

    if ($reservationType !== 'Overall') {
        $query .= " WHERE r.service_type = ?";
    }

    $stmt = $conn->prepare($query);

    if ($reservationType !== 'Overall') {
        $stmt->bind_param('s', $reservationType);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
}
?>
