<?php
require_once('../fpdf/fpdf.php');

// Database connection
require_once "../config.php";

$pay_id = $_GET['pay_id'] ?? null;

if ($pay_id) {
    $stmt = $conn->prepare("SELECT r.*, u.firstname, u.lastname, u.username, p.pay_id, p.total_amount, p.p_status, a.admin_name 
                            FROM reservation r
                            JOIN users u ON r.uid = u.uid 
                            LEFT JOIN payment p ON r.s_id = p.s_id
                            LEFT JOIN admins a ON r.admin_id = a.admin_id
                            WHERE p.pay_id = ?");
    $stmt->bind_param("i", $pay_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reservation = $result->fetch_assoc();

        $full_name = !empty($reservation['firstname']) && !empty($reservation['lastname']) 
                     ? $reservation['firstname'] . ' ' . $reservation['lastname'] 
                     : $reservation['username'];
        $receipt_no = $reservation['pay_id'] ?? 'N/A';
        $admin_name = $reservation['admin_name'] ?? 'CASHIER / TREASURER';
        $service_type = $reservation['service_type'] ?? 'N/A';
        $amount = $reservation['amount'] ?? 0;
        $payment_type = $reservation['payment_type'] ?? 'N/A';
    } else {
        die("Reservation not found.");
    }
} else {
    die("Invalid payment ID.");
}

// Create PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// Title Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 6, 'ARCHDIOCESAN SHRINE OF SANTA ROSA DE LIMA', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, 'Daanbantayan, Cebu', 0, 1, 'C');
$pdf->Ln(8);

// Receipt Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'OFFICIAL RECEIPT', 0, 1, 'C');
$pdf->Ln(5);

// Receipt Details
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(130, 6, '', 0, 0); // Empty cell for alignment
$pdf->Cell(30, 6, 'RECEIPT NO:', 0, 0, 'R');
$pdf->SetTextColor(255, 0, 0); // Red color for Receipt No
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(30, 6, $receipt_no, 0, 1, 'R');
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(130, 6, '', 0, 0);
$pdf->Cell(30, 6, 'DATE:', 0, 0, 'R');
$pdf->Cell(30, 6, date("Y/m/d"), 0, 1, 'R');
$pdf->Ln(5);

// Details Section
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, 'RECEIVED FROM:', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, $full_name, 0, 1);
$pdf->Cell(0, 0, '', 'T'); // Horizontal line
$pdf->Ln(8);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, 'THE AMOUNT OF:', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'PHP ' . number_format($amount, 2), 0, 1);
$pdf->Cell(0, 0, '', 'T'); // Horizontal line
$pdf->Ln(8);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, 'RECEIVED THROUGH:', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, $payment_type, 0, 1);
$pdf->Cell(0, 0, '', 'T'); // Horizontal line
$pdf->Ln(10);

// Service Table
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(90, 10, 'SERVICE', 1, 0, 'C');
$pdf->Cell(50, 10, 'TYPE', 1, 0, 'C');
$pdf->Cell(50, 10, 'AMOUNT', 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(90, 10, $service_type, 1, 0, 'C');
$pdf->Cell(50, 10, $reservation['r_type'] ?? 'N/A', 1, 0, 'C');
$pdf->Cell(50, 10, 'PHP ' . number_format($amount, 2), 1, 1, 'C');

// Total Row
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(140, 10, 'Total', 1, 0, 'R');
$pdf->Cell(50, 10, 'PHP ' . number_format($amount, 2), 1, 1, 'C');
$pdf->Ln(10);

// Footer Section
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, 'RECEIVED BY:', 0, 1);
$pdf->Cell(0, 0, '', 'T'); // Signature line
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, $admin_name, 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, 'CASHIER / TREASURER', 0, 1);

// Output PDF
$pdf->Output('D', "Receipt_$receipt_no.pdf");
?>
