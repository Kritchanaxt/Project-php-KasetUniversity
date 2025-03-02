<?php
require 'db_connection.php';
require('fpdf/fpdf.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(190, 10, 'End of Game Report', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);

// กำหนดความกว้างของแต่ละคอลัมน์ให้พอดีกับกระดาษ
$col_widths = [35, 45, 40, 40, 30];

// หัวตาราง
$pdf->SetFillColor(200, 200, 200); // สีพื้นหลังหัวตาราง
$pdf->Cell($col_widths[0], 8, 'Game ID', 1, 0, 'C', true);
$pdf->Cell($col_widths[1], 8, 'Total number of accounts', 1, 0, 'C', true);
$pdf->Cell($col_widths[2], 8, 'Sold Account', 1, 0, 'C', true);
$pdf->Cell($col_widths[3], 8, 'Remaining Account', 1, 0, 'C', true);
$pdf->Cell($col_widths[4], 8, 'STATUS', 1, 1, 'C', true);

$query = "SELECT game_id, COUNT(*) AS total_accounts, 
                 SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) AS sold_accounts, 
                 SUM(CASE WHEN status != 'sold' OR status IS NULL THEN 1 ELSE 0 END) AS available_accounts
          FROM Accounts
          GROUP BY game_id
          ORDER BY available_accounts ASC";

$result = $conn->query($query);

$pdf->SetFont('Arial', '', 10);

while ($row = $result->fetch_assoc()) {
    $status = ($row['available_accounts'] < 5) ? "Restock Now!" : "NORMAL";
    
    // ตั้งสีพื้นหลังให้แถวที่ใกล้หมดเป็นสีแดงอ่อน
    if ($row['available_accounts'] < 5) {
        $pdf->SetFillColor(255, 153, 153); // สีแดงอ่อน
    } else {
        $pdf->SetFillColor(255, 255, 255); // สีขาวปกติ
    }

    $pdf->Cell($col_widths[0], 8, $row['game_id'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[1], 8, $row['total_accounts'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[2], 8, $row['sold_accounts'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[3], 8, $row['available_accounts'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[4], 8, $status, 1, 1, 'C', true);
}

$pdf->Output('D', 'low_stock_report.pdf'); // ดาวน์โหลดไฟล์ PDF
?>
