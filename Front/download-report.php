<?php
// Include your database configuration file
include '../config.php';

// Get the search query from the URL parameter (if it exists)
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Set headers to force download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=total_reservation_report.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Add title with padding for center alignment
fputcsv($output, ['             The Roman Catholic Archdiocese of Cebu']);
fputcsv($output, ['     Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan']);
fputcsv($output, ['        Santa Rosa Street, Poblacion, Daanbantayan, Cebu']);
fputcsv($output, []); // Empty row for spacing
fputcsv($output, ['--RESERVATION--']);

fputcsv($output, ['Total Reservation:']);
fputcsv($output, []); // Add an empty row for spacing


// Add "Signature over Printed Name" field and a line for the signature
fputcsv($output, []); // Empty row for spacing
fputcsv($output, ['______________________________']); 
fputcsv($output, [' Signature over Printed Name:']);
 // Line for signature
// Add column headers to the CSV file
fputcsv($output, ['Service ID', 'User Name', 'Service Type', 'Reservation Date', 'Status']);

// Fetch data from the database based on the search query
$query = "SELECT 
            reservation.s_id AS service_id, 
            users.username AS user_name, 
            reservation.service_type, 
            reservation.r_date AS reservation_date, 
            reservation.s_status AS status
          FROM reservation
          JOIN users ON reservation.uid = users.uid
          WHERE reservation.s_status IN ('Pending', 'Canceled', 'Processing', 'Approved', 'Ongoing', 'Completed')";

if (!empty($searchQuery)) {
    $query .= " AND (users.username LIKE ? OR reservation.s_id LIKE ? OR reservation.service_type LIKE ?)";
}

$query .= " ORDER BY reservation.r_date DESC";

$stmt = $conn->prepare($query);

if (!empty($searchQuery)) {
    $searchTerm = "%" . $searchQuery . "%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
}

$stmt->execute();
$result = $stmt->get_result();

// Check if there are results and process them
if ($result->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['No matching records found for your search.']);
}


fclose($output);
exit;
?>
