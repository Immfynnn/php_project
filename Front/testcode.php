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


<div style="width:100%; text-align:right; padding-top:10px;">
    <button id="downloadButton" 
            style="padding:11px; padding-left:20px; border:none; outline:none; padding-right:20px; background: #3C91E6; margin-right:5px; cursor:pointer; color:#fff; border-radius:5px;">
        Download
    </button>
        <a href="admin-total-refund.php" style="padding:10px; padding-left:20px; padding-right:20px; border-radius:5px; background:darkorange; color:#fff; margin-right:5px;">Refund</a>
        <a href="admin_dashboard.php"  style="padding:10px; padding-left:20px; padding-right:20px; border-radius:5px; background:#DB504A; color:#fff; margin-right:10px;">Back</a>
    </div>


<div class="overlay-download" id="overlay-download" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000;">
    <div class="dialog-download" style="padding:20px; background:#fff; outline:solid 1px rgba(0,0,0,.5); display:flex;flex-direction:column; align-items:center; width:30%; border-radius:6px; margin: auto; position: relative; top: 0;">
        <p><b>Select reservation you want to download</b></p>
        <form id="downloadForm" method="POST" action="download-receipt.php">
    <select name="payment_type" id="payment_type" style="padding:10px; border-radius:10px; margin-top:10px; width:100%;">
        <option value="Overall">Overall</option>
        <option value="Gcash (Scan / Send Money)">Gcash (Scan / Send Money)</option>
        <option value="Over The Counter">Over The Counter</option>
    </select>
    <div style="margin-top: 10px;">
        <button type="submit" id="confirmDownload" style="padding:10px; padding-left:30px; padding-right:30px; background:blue; cursor:pointer;  outline:none; border:none; border-radius:5px; color:#fff;">Download</button>
        <button type="button" id="closeOverlay" style="padding:10px; padding-left:30px; padding-right:30px; background:#DB504A; cursor:pointer; outline:none; border:none; border-radius:5px; color:#fff;">Close</button>
    </div>
</form>

    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const downloadButton = document.getElementById('downloadButton');
        const overlayDownload = document.getElementById('overlay-download');
        const closeOverlayButton = document.getElementById('closeOverlay');

        // Show overlay when "Download" button is clicked
        downloadButton.addEventListener('click', function () {
            overlayDownload.style.display = 'flex';
        });

        // Hide overlay when "Close" button is clicked
        closeOverlayButton.addEventListener('click', function () {
            overlayDownload.style.display = 'none';
        });
    });
</script>
