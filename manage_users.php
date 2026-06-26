<?php
ob_start();
include("../db.php");
require("../fpdf/fpdf.php");

$result = mysqli_query($conn, "
    SELECT tasks.*, users.fullname AS customer, helper.fullname AS helper_name
    FROM tasks
    LEFT JOIN users ON tasks.user_id = users.id
    LEFT JOIN users helper ON tasks.helper_id = helper.id
    ORDER BY tasks.id DESC
");

$pdf = new FPDF();
$pdf->AddPage('L'); // Landscape for more columns

// ── Colours ──────────────────────────────────────────────
$pinkR = 255; $pinkG = 143; $pinkB = 171;   // #FF8FAB
$darkR = 61;  $darkG = 43;  $darkB = 53;    // #3D2B35
$lightR= 255; $lightG= 214; $lightB= 224;   // #FFD6E0
$greyR = 158; $greyG = 122; $greyB = 136;   // #9E7A88

// ── Header banner ────────────────────────────────────────
$pdf->SetFillColor($pinkR, $pinkG, $pinkB);
$pdf->Rect(0, 0, 297, 38, 'F');

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 22);
$pdf->SetXY(10, 8);
$pdf->Cell(0, 10, 'ErrandPal', 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->SetXY(10, 20);
$pdf->Cell(0, 8, 'Task Report  |  Generated: ' . date('d M Y, H:i'), 0, 1);

$pdf->Ln(18);

// ── Summary boxes ─────────────────────────────────────────
// FIXED: use correct column name 'status' and correct values
$total      = mysqli_num_rows($result);
$pend_r     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM tasks WHERE status='Pending'"));
$accept_r   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM tasks WHERE status='Accepted'"));
$prog_r     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM tasks WHERE status='In Progress'"));
$done_r     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM tasks WHERE status='Completed'"));

$boxes = [
    ['Total Tasks',   $total,           [255,245,247]],
    ['Pending',       $pend_r['c'],     [255,232,239]],
    ['Accepted',      $accept_r['c'],   [243,232,255]],
    ['In Progress',   $prog_r['c'],     [255,240,232]],
    ['Completed',     $done_r['c'],     [234,243,222]],
];
$bw = 46; $bx = 14;
foreach ($boxes as $b) {
    $pdf->SetFillColor($b[2][0], $b[2][1], $b[2][2]);
    $pdf->SetDrawColor($pinkR, $pinkG, $pinkB);
    $pdf->SetLineWidth(0.4);
    $pdf->Rect($bx, 42, $bw, 20, 'DF');

    $pdf->SetTextColor($darkR, $darkG, $darkB);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetXY($bx, 44);
    $pdf->Cell($bw, 8, $b[1], 0, 1, 'C');

    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor($greyR, $greyG, $greyB);
    $pdf->SetXY($bx, 52);
    $pdf->Cell($bw, 6, strtoupper($b[0]), 0, 1, 'C');

    $bx += $bw + 4;
}

$pdf->Ln(22);

// ── Table header ─────────────────────────────────────────
$cols = [
    ['#',          10],
    ['Title',      60],
    ['Customer',   40],
    ['Helper',     40],
    ['Status',     30],
    ['Budget',     25],
];

$pdf->SetFillColor($darkR, $darkG, $darkB);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetDrawColor(255, 255, 255);
$pdf->SetLineWidth(0);

foreach ($cols as $col) {
    $pdf->Cell($col[1], 9, $col[0], 0, 0, 'C', true);
}
$pdf->Ln();

// ── Table rows ───────────────────────────────────────────
mysqli_data_seek($result, 0);
$rowNum = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $rowNum++;
    $fill = ($rowNum % 2 === 0);
    if ($fill) {
        $pdf->SetFillColor($lightR, $lightG, $lightB);
    } else {
        $pdf->SetFillColor(255, 250, 252);
    }

    // FIXED: correct column name 'status' and correct values
    $status = $row['status'] ?? '-';
    switch ($status) {
        case 'Pending':     $sr=156;$sg=111;$sb=214; break; // purple
        case 'Accepted':    $sr=255;$sg=143;$sb=171; break; // pink
        case 'In Progress': $sr=249;$sg=160;$sb= 92; break; // orange
        case 'Completed':   $sr= 99;$sg=160;$sb= 34; break; // green
        default:            $sr=158;$sg=122;$sb=136;
    }

    $pdf->SetTextColor($darkR, $darkG, $darkB);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetDrawColor(245, 198, 213);
    $pdf->SetLineWidth(0.2);

    $pdf->Cell(10,  8, $row['id'],                          'B', 0, 'C', $fill);
    $pdf->Cell(60,  8, $row['title'],                       'B', 0, 'L', $fill);
    $pdf->Cell(40,  8, $row['customer'] ?? '-',             'B', 0, 'L', $fill);
    $pdf->Cell(40,  8, $row['helper_name'] ?? 'Unassigned', 'B', 0, 'L', $fill);

    // Coloured status text
    $pdf->SetTextColor($sr, $sg, $sb);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(30,  8, $status,                             'B', 0, 'C', $fill);

    $pdf->SetTextColor($darkR, $darkG, $darkB);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(25,  8, '$'.number_format($row['budget'],2), 'B', 0, 'R', $fill);
    $pdf->Ln();
}

// ── Footer ───────────────────────────────────────────────
$pdf->SetY(-18);
$pdf->SetFillColor($pinkR, $pinkG, $pinkB);
$pdf->Rect(0, $pdf->GetY(), 297, 18, 'F');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 18, 'ErrandPal  |  Confidential  |  Page ' . $pdf->PageNo(), 0, 0, 'C');

ob_end_clean();
$pdf->Output('I', 'ErrandPal_Report_' . date('Ymd') . '.pdf');
?>