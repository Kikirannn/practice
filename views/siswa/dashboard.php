<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireRole('siswa');

$pageTitle = 'Dashboard Siswa';
$activePage = 'dashboard';

$userId = getCurrentUserId();
$pdo = getDBConnection();

$sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
            SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as process_count,
            SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done_count,
            SUM(CASE WHEN status = 'reject' THEN 1 ELSE 0 END) as reject_count
        FROM laporan 
        WHERE user_id = :user_id";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$stats = $stmt->fetch();

$sql = "SELECT l.*, u.nama_lengkap as teknisi_nama 
        FROM laporan l
        LEFT JOIN users u ON l.assigned_to = u.user_id
        WHERE l.user_id = :user_id
        ORDER BY l.tanggal_lapor DESC
        LIMIT 5";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$recentReports = $stmt->fetchAll();

include '../partials/header.php';
?>

<h1>Dashboard Siswa</h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?= $stats['total'] ?></h3>
        <p>Total Laporan</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['open_count'] ?></h3>
        <p>Menunggu Validasi</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['process_count'] ?></h3>
        <p>Sedang Dikerjakan</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['done_count'] ?></h3>
        <p>Selesai</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['reject_count'] ?></h3>
        <p>Ditolak</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Laporan Terbaru
    </div>
    <div class="card-body">
        <?php if (empty($recentReports)): ?>
            <p style="text-align: center; color: #999;">Belum ada laporan. <a href="lapor.php">Buat laporan pertama</a></p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Judul</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Teknisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReports as $report): ?>
                            <tr>
                                <td><?= formatDate($report['tanggal_lapor']) ?></td>
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
            <div class="text-center mt-20">
                <a href="riwayat.php" class="btn btn-primary">Lihat Semua Laporan</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
