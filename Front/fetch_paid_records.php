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
        payment.p_status = 'Paid';
";

$result = mysqli_query($conn, $query);


if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Apply CSS for "Paid" status
        $status_style = ($row['p_status'] == 'Paid') ? 'style="color: green; font-weight: bold;"' : '';
        
        // Display the row with a clickable link
        echo "
            <tr onclick=\"window.location.href='view-payment.php?pay_id=" . $row['pay_id'] . "'\" style=\"cursor: pointer;\">
                <td><img src='" . htmlspecialchars($row['userimg']) . "' alt='Profile Pic' width='50' height='50'></td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['pay_id']) . "</td>
                <td>" . htmlspecialchars($row['payment_type']) . "</td>
                <td>PHP " . number_format($row['total_amount'], 2) . "</td>
                <td><span $status_style>" . htmlspecialchars($row['p_status']) . "</span></td>
            </tr>
        ";
    }
} else {
    echo "<tr><td colspan='6'>No paid records found.</td></tr>"; 
}
?>