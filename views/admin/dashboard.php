<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireRole('admin');

$pageTitle = 'Dashboard Admin';
$activePage = 'dashboard';

$pdo = getDBConnection();

$sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
            SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as process_count,
            SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done_count,
            SUM(CASE WHEN status = 'reject' THEN 1 ELSE 0 END) as reject_count
        FROM laporan";

$stmt = $pdo->query($sql);
$stats = $stmt->fetch();

$sql = "SELECT l.*, u.nama_lengkap as pelapor, t.nama_lengkap as teknisi_nama 
        FROM laporan l
        JOIN users u ON l.user_id = u.user_id
        LEFT JOIN users t ON l.assigned_to = t.user_id
        ORDER BY l.tanggal_lapor DESC
        LIMIT 10";

$stmt = $pdo->query($sql);
$recentReports = $stmt->fetchAll();

include '../partials/header.php';
?>

<h1>Dashboard Admin</h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?= $stats['total'] ?></h3>
        <p>Total Laporan</p>
    </div>
    <div class="stat-card" style="border-left-color: #ffc107;">
        <h3><?= $stats['open_count'] ?></h3>
        <p>Menunggu Validasi</p>
    </div>
    <div class="stat-card" style="border-left-color: #17a2b8;">
        <h3><?= $stats['process_count'] ?></h3>
        <p>Sedang Dikerjakan</p>
    </div>
    <div class="stat-card" style="border-left-color: #28a745;">
        <h3><?= $stats['done_count'] ?></h3>
        <p>Selesai</p>
    </div>
    <div class="stat-card" style="border-left-color: #dc3545;">
        <h3><?= $stats['reject_count'] ?></h3>
        <p>Ditolak</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Laporan Terbaru
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Pelapor</th>
                        <th>Judul</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Teknisi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentReports as $report): ?>
                        <tr>
                            <td>#<?= $report['report_id'] ?></td>
                            <td><?= formatDate($report['tanggal_lapor']) ?></td>
                            <td><?= htmlspecialchars($report['pelapor']) ?></td>
                            <td><?= htmlspecialchars($report['judul']) ?></td>
                            <td><?= htmlspecialchars($report['lokasi']) ?></td>
                            <td><span
                                    class="badge <?= getStatusBadge($report['status']) ?>"><?= formatStatus($report['status']) ?></span>
                            </td>
                            <td><?= $report['teknisi_nama'] ? htmlspecialchars($report['teknisi_nama']) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
