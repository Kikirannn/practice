<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/pdf_generator.php';

requireRole('admin');

$pdo = getDBConnection();

$sql = "SELECT status, COUNT(*) as total FROM laporan GROUP BY status";
$stmt = $pdo->query($sql);
$statusBreakdown = $stmt->fetchAll();

$sql = "SELECT 
            DATE_FORMAT(tanggal_lapor, '%Y-%m') as bulan,
            COUNT(*) as total
        FROM laporan
        WHERE tanggal_lapor >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(tanggal_lapor, '%Y-%m')
        ORDER BY bulan DESC";
$stmt = $pdo->query($sql);
$monthlyReports = $stmt->fetchAll();

$sql = "SELECT 
            u.nama_lengkap,
            COUNT(*) as total_done
        FROM laporan l
        JOIN users u ON l.assigned_to = u.user_id
        WHERE l.status = 'done'
        GROUP BY l.assigned_to, u.nama_lengkap
        ORDER BY total_done DESC
        LIMIT 10";
$stmt = $pdo->query($sql);
$topTechnicians = $stmt->fetchAll();

$sql = "SELECT 
            SUBSTRING_INDEX(lokasi, '-', 1) as gedung,
            COUNT(*) as total
        FROM laporan
        GROUP BY SUBSTRING_INDEX(lokasi, '-', 1)
        ORDER BY total DESC
        LIMIT 10";
$stmt = $pdo->query($sql);
$locationStats = $stmt->fetchAll();

$sql = "SELECT 
            YEARWEEK(tanggal_lapor) as minggu,
            YEAR(tanggal_lapor) as tahun,
            WEEK(tanggal_lapor) as minggu_ke,
            COUNT(*) as total
        FROM laporan
        WHERE status = 'done' 
        AND tanggal_lapor >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
        GROUP BY YEARWEEK(tanggal_lapor), YEAR(tanggal_lapor), WEEK(tanggal_lapor)
        ORDER BY minggu DESC";
$stmt = $pdo->query($sql);
$weeklyDone = $stmt->fetchAll();

$pdf = new PDFGenerator();

$y = $pdf->pageHeight - 60;
$pdf->addCenteredText($y, 'LAPORAN STATISTIK KERUSAKAN FASILITAS SEKOLAH', 16, 'bold');
$y -= 20;
$pdf->addCenteredText($y, 'Reporting & Analisis Data', 11);
$y -= 15;
$pdf->addCenteredText($y, 'Dicetak pada: ' . date('d/m/Y H:i'), 9);
$y -= 30;

$pdf->addText($pdf->margin, $y, 'Status Laporan', 12, 'bold');
$y -= 25;

$headers = ['Status', 'Jumlah', 'Persentase'];
$data = [];
$total = array_sum(array_column($statusBreakdown, 'total'));

foreach ($statusBreakdown as $stat) {
    $percentage = $total > 0 ? round(($stat['total'] / $total) * 100, 1) : 0;
    $data[] = [
        formatStatus($stat['status']),
        $stat['total'],
        $percentage . '%'
    ];
}

$colWidths = [200, 100, 100];
$y = $pdf->addTable($headers, $data, $pdf->margin, $y, $colWidths);
$y -= 30;

if ($y < 150) {
    $pdf->addPage();
    $y = $pdf->pageHeight - $pdf->margin - 20;
}

$pdf->addText($pdf->margin, $y, 'Total Laporan Per Bulan (6 Bulan Terakhir)', 12, 'bold');
$y -= 25;

$headers = ['Bulan', 'Total Laporan'];
$data = [];

foreach ($monthlyReports as $report) {
    $data[] = [
        date('F Y', strtotime($report['bulan'] . '-01')),
        $report['total']
    ];
}

if (empty($data)) {
    $data[] = ['Tidak ada data', '-'];
}

$colWidths = [300, 100];
$y = $pdf->addTable($headers, $data, $pdf->margin, $y, $colWidths);
$y -= 30;

if ($y < 150) {
    $pdf->addPage();
    $y = $pdf->pageHeight - $pdf->margin - 20;
}

$pdf->addText($pdf->margin, $y, 'Teknisi Paling Produktif', 12, 'bold');
$y -= 25;

$headers = ['Rank', 'Nama Teknisi', 'Total Selesai'];
$data = [];

foreach ($topTechnicians as $index => $tech) {
    $data[] = [
        $index + 1,
        $tech['nama_lengkap'],
        $tech['total_done']
    ];
}

if (empty($data)) {
    $data[] = ['-', 'Tidak ada data', '-'];
}

$colWidths = [50, 250, 100];
$y = $pdf->addTable($headers, $data, $pdf->margin, $y, $colWidths);
$y -= 30;

if ($y < 150) {
    $pdf->addPage();
    $y = $pdf->pageHeight - $pdf->margin - 20;
}

$pdf->addText($pdf->margin, $y, 'Kerusakan Berdasarkan Lokasi/Gedung', 12, 'bold');
$y -= 25;

$headers = ['Lokasi/Gedung', 'Total Laporan'];
$data = [];

foreach ($locationStats as $loc) {
    $data[] = [
        trim($loc['gedung']),
        $loc['total']
    ];
}

if (empty($data)) {
    $data[] = ['Tidak ada data', '-'];
}

$colWidths = [300, 100];
$y = $pdf->addTable($headers, $data, $pdf->margin, $y, $colWidths);
$y -= 30;

if ($y < 150) {
    $pdf->addPage();
    $y = $pdf->pageHeight - $pdf->margin - 20;
}

$pdf->addText($pdf->margin, $y, 'Laporan Selesai Per Minggu (8 Minggu Terakhir)', 12, 'bold');
$y -= 25;

$headers = ['Minggu', 'Total Selesai'];
$data = [];

foreach ($weeklyDone as $week) {
    $data[] = [
        'Week ' . $week['minggu_ke'] . ' - ' . $week['tahun'],
        $week['total']
    ];
}

if (empty($data)) {
    $data[] = ['Tidak ada data', '-'];
}

$colWidths = [300, 100];
$y = $pdf->addTable($headers, $data, $pdf->margin, $y, $colWidths);

$y = $pdf->margin + 20;
$pdf->addCenteredText($y, 'Sistem Pelaporan Kerusakan Fasilitas Sekolah', 8);
$y -= 12;
$pdf->addCenteredText($y, 'Laporan Statistik dicetak pada ' . date('d/m/Y H:i'), 8);

$filename = 'Laporan_Statistik_' . date('Y-m-d_His') . '.pdf';
$pdf->output($filename);
