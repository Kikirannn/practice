<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/pdf_generator.php';

requireRole('admin');

$pdo = getDBConnection();

$where = [];
$params = [];

if (!empty($_GET['start_date'])) {
    $where[] = "DATE(l.tanggal_lapor) >= :start_date";
    $params[':start_date'] = $_GET['start_date'];
}

if (!empty($_GET['end_date'])) {
    $where[] = "DATE(l.tanggal_lapor) <= :end_date";
    $params[':end_date'] = $_GET['end_date'];
}

if (!empty($_GET['status']) && $_GET['status'] !== 'all') {
    $where[] = "l.status = :status";
    $params[':status'] = $_GET['status'];
}

if (!empty($_GET['technician_id']) && $_GET['technician_id'] !== 'all') {
    $where[] = "l.assigned_to = :technician_id";
    $params[':technician_id'] = $_GET['technician_id'];
}

if (!empty($_GET['lokasi'])) {
    $where[] = "l.lokasi LIKE :lokasi";
    $params[':lokasi'] = '%' . $_GET['lokasi'] . '%';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT l.*, u.nama_lengkap as pelapor, t.nama_lengkap as teknisi_nama 
        FROM laporan l
        JOIN users u ON l.user_id = u.user_id
        LEFT JOIN users t ON l.assigned_to = t.user_id
        $whereClause
        ORDER BY l.tanggal_lapor DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

$pdf = new PDFGenerator();

$y = $pdf->pageHeight - 60;
$pdf->addCenteredText($y, 'LAPORAN KERUSAKAN FASILITAS SEKOLAH', 16, 'bold');
$y -= 20;
$pdf->addCenteredText($y, 'Daftar Semua Laporan', 11);
$y -= 15;
$pdf->addCenteredText($y, 'Dicetak pada: ' . date('d/m/Y H:i'), 9);
$y -= 25;

$filterInfo = [];
if (!empty($_GET['start_date']) || !empty($_GET['end_date'])) {
    $start = !empty($_GET['start_date']) ? date('d/m/Y', strtotime($_GET['start_date'])) : '-';
    $end = !empty($_GET['end_date']) ? date('d/m/Y', strtotime($_GET['end_date'])) : '-';
    $filterInfo[] = "Periode: $start s/d $end";
}

if (!empty($_GET['status']) && $_GET['status'] !== 'all') {
    $filterInfo[] = "Status: " . formatStatus($_GET['status']);
}

if (!empty($_GET['technician_id']) && $_GET['technician_id'] !== 'all') {
    $sql = "SELECT nama_lengkap FROM users WHERE user_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $_GET['technician_id']]);
    $techName = $stmt->fetchColumn();
    if ($techName) {
        $filterInfo[] = "Teknisi: $techName";
    }
}

if (!empty($_GET['lokasi'])) {
    $filterInfo[] = "Lokasi: " . $_GET['lokasi'];
}

if (!empty($filterInfo)) {
    foreach ($filterInfo as $info) {
        $pdf->addText($pdf->margin, $y, $info, 9);
        $y -= 15;
    }
    $y -= 10;
}

$pdf->addText($pdf->margin, $y, 'Total Laporan: ' . count($reports), 10, 'bold');
$y -= 25;

$headers = ['ID', 'Tanggal', 'Pelapor', 'Judul', 'Lokasi', 'Status', 'Teknisi'];

$data = [];
foreach ($reports as $report) {
    $tanggal = date('d/m/y', strtotime($report['tanggal_lapor']));

    $judul = strlen($report['judul']) > 25 ? substr($report['judul'], 0, 22) . '...' : $report['judul'];
    $lokasi = strlen($report['lokasi']) > 20 ? substr($report['lokasi'], 0, 17) . '...' : $report['lokasi'];
    $pelapor = strlen($report['pelapor']) > 15 ? substr($report['pelapor'], 0, 12) . '...' : $report['pelapor'];
    $teknisi = $report['teknisi_nama'] ?
        (strlen($report['teknisi_nama']) > 15 ? substr($report['teknisi_nama'], 0, 12) . '...' : $report['teknisi_nama'])
        : '-';

    $data[] = [
        '#' . $report['report_id'],
        $tanggal,
        $pelapor,
        $judul,
        $lokasi,
        formatStatus($report['status']),
        $teknisi
    ];
}

if (empty($data)) {
    $data[] = ['-', '-', '-', 'Tidak ada laporan', '-', '-', '-'];
}

$colWidths = [35, 50, 75, 120, 95, 70, 65];

$y = $pdf->addTable($headers, $data, $pdf->margin, $y, $colWidths);

$y = $pdf->margin + 20;
$pdf->addCenteredText($y, 'Sistem Pelaporan Kerusakan Fasilitas Sekolah', 8);
$y -= 12;
$pdf->addCenteredText($y, 'Halaman ini dicetak pada ' . date('d/m/Y H:i'), 8);

$filename = 'Daftar_Laporan_' . date('Y-m-d_His') . '.pdf';
$pdf->output($filename);
