<?php
// Assuming you have already connected to your database
$query = "
    SELECT 
        users.userimg, 
        CONCAT(users.firstname, ' ', users.lastname) AS name, 
        payment.pay_id, 
        reservation.payment_type, 
        payment.p_status,
        payment.total_amount  -- Assuming 'total_amount' is in the payment table
    FROM 
        payment
    JOIN 
        reservation ON payment.s_id = reservation.s_id
    JOIN 
        users ON payment.uid = users.uid
    WHERE 
        payment.p_status = 'Refund';  -- Change to 'Refund' status
";

$result = mysqli_query($conn, $query);

// Check if records are available
if (mysqli_num_rows($result) > 0) {
    // Fetch each record and display
    while ($row = mysqli_fetch_assoc($result)) {
        // Display the row with the Amount
        echo "
            <tr>
                <td><img src='" . $row['userimg'] . "' alt='Profile Pic' width='50' height='50'></td>
                <td>" . $row['name'] . "</td>
                <td>" . $row['pay_id'] . "</td>
                <td>" . $row['payment_type'] . "</td>
                <td>PHP " . number_format($row['total_amount'], 2) . "</td>  <!-- Display amount -->
                <td><span style='color:#DB504A; font-weight: bold;'>" . $row['p_status'] . "</span></td>
            </tr>
        ";
    }
} else {
    echo "<tr><td colspan='6'>No refund records found.</td></tr>"; 
}
?>
