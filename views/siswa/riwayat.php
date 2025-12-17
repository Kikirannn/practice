<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/tracking.php';

requireRole('siswa');

$pageTitle = 'Riwayat Laporan';
$activePage = 'riwayat';

$userId = getCurrentUserId();
$pdo = getDBConnection();

$sql = "SELECT l.*, u.nama_lengkap as teknisi_nama 
        FROM laporan l
        LEFT JOIN users u ON l.assigned_to = u.user_id
        WHERE l.user_id = :user_id
        ORDER BY l.tanggal_lapor DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$reports = $stmt->fetchAll();

$selectedReport = null;
$tracking = [];

if (isset($_GET['detail'])) {
    $reportId = (int) $_GET['detail'];

    $sql = "SELECT l.*, u.nama_lengkap as teknisi_nama 
            FROM laporan l
            LEFT JOIN users u ON l.assigned_to = u.user_id
            WHERE l.report_id = :report_id AND l.user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':report_id' => $reportId, ':user_id' => $userId]);
    $selectedReport = $stmt->fetch();

    if ($selectedReport) {
        $tracking = getTrackingHistory($reportId);
    }
}

include '../partials/header.php';
?>

<h1>Riwayat Laporan</h1>

<?php if ($selectedReport): ?>
    <div class="card">
        <div class="card-header">
            Detail Laporan: <?= htmlspecialchars($selectedReport['judul']) ?>
        </div>
        <div class="card-body">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 200px; font-weight: bold;">ID Laporan</td>
                    <td>#<?= $selectedReport['report_id'] ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Tanggal Lapor</td>
                    <td><?= formatDate($selectedReport['tanggal_lapor']) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Status</td>
                    <td><span
                            class="badge <?= getStatusBadge($selectedReport['status']) ?>"><?= formatStatus($selectedReport['status']) ?></span>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Lokasi</td>
                    <td><?= htmlspecialchars($selectedReport['lokasi']) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Deskripsi</td>
                    <td><?= nl2br(htmlspecialchars($selectedReport['deskripsi'])) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Teknisi</td>
                    <td><?= $selectedReport['teknisi_nama'] ? htmlspecialchars($selectedReport['teknisi_nama']) : '-' ?>
                    </td>
                </tr>
                <?php if ($selectedReport['foto']): ?>
                    <tr>
                        <td style="font-weight: bold;">Foto</td>
                        <td>
                            <img src="<?= baseUrl('/foto_laporan/' . htmlspecialchars($selectedReport['foto'])) ?>"
                                class="img-preview" alt="Foto Kerusakan">
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

            <?php if (!empty($tracking)): ?>
                <h3 style="margin-top: 30px; margin-bottom: 15px;">Riwayat Perubahan Status</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Status Awal</th>
                                <th>Status Akhir</th>
                                <th>Teknisi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tracking as $track): ?>
                                <tr>
                                    <td><?= formatDate($track['timestamp']) ?></td>
                                    <td><span
                                            class="badge <?= getStatusBadge($track['status_awal']) ?>"><?= formatStatus($track['status_awal']) ?></span>
                                    </td>
                                    <td><span
                                            class="badge <?= getStatusBadge($track['status_akhir']) ?>"><?= formatStatus($track['status_akhir']) ?></span>
                                    </td>
                                    <td><?= $track['teknisi_nama'] ? htmlspecialchars($track['teknisi_nama']) : '-' ?></td>
                                    <td><?= $track['catatan'] ? nl2br(htmlspecialchars($track['catatan'])) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="mt-20">
                <a href="riwayat.php" class="btn btn-info">Kembali ke Daftar</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            Semua Laporan Saya
        </div>
        <div class="card-body">
            <?php if (empty($reports)): ?>
                <p style="text-align: center; color: #999;">Belum ada laporan. <a href="lapor.php">Buat laporan baru</a></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tanggal</th>
                                <th>Judul</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th>Teknisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td>#<?= $report['report_id'] ?></td>
                                    <td><?= formatDate($report['tanggal_lapor']) ?></td>
                                    <td><?= htmlspecialchars($report['judul']) ?></td>
                                    <td><?= htmlspecialchars($report['lokasi']) ?></td>
                                    <td><span
                                            class="badge <?= getStatusBadge($report['status']) ?>"><?= formatStatus($report['status']) ?></span>
                                    </td>
                                    <td><?= $report['teknisi_nama'] ? htmlspecialchars($report['teknisi_nama']) : '-' ?></td>
                                    <td>
                                        <a href="?detail=<?= $report['report_id'] ?>" class="btn btn-info btn-sm">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include '../partials/footer.php'; ?>