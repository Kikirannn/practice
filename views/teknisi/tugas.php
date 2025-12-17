<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/tracking.php';

requireRole('teknisi');

$pageTitle = 'Tugas Saya';
$activePage = 'tugas';

$userId = getCurrentUserId();
$pdo = getDBConnection();

$sql = "SELECT l.*, u.nama_lengkap as pelapor 
        FROM laporan l
        JOIN users u ON l.user_id = u.user_id
        WHERE l.assigned_to = :user_id AND l.status != 'reject'
        ORDER BY 
            CASE 
                WHEN l.status = 'process' THEN 1
                WHEN l.status = 'done' THEN 2
            END,
            l.tanggal_lapor DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$tasks = $stmt->fetchAll();

$selectedTask = null;
$tracking = [];

if (isset($_GET['detail'])) {
    $reportId = (int) $_GET['detail'];

    $sql = "SELECT l.*, u.nama_lengkap as pelapor 
            FROM laporan l
            JOIN users u ON l.user_id = u.user_id
            WHERE l.report_id = :report_id AND l.assigned_to = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':report_id' => $reportId, ':user_id' => $userId]);
    $selectedTask = $stmt->fetch();

    if ($selectedTask) {
        $tracking = getTrackingHistory($reportId);
    }
}

include '../partials/header.php';
?>

<h1>Tugas Saya</h1>

<?php if ($selectedTask): ?>
    <div class="card">
        <div class="card-header">
            Detail Tugas: <?= htmlspecialchars($selectedTask['judul']) ?>
        </div>
        <div class="card-body">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 200px; font-weight: bold;">ID Laporan</td>
                    <td>#<?= $selectedTask['report_id'] ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Pelapor</td>
                    <td><?= htmlspecialchars($selectedTask['pelapor']) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Tanggal Lapor</td>
                    <td><?= formatDate($selectedTask['tanggal_lapor']) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Status Saat Ini</td>
                    <td><span
                            class="badge <?= getStatusBadge($selectedTask['status']) ?>"><?= formatStatus($selectedTask['status']) ?></span>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Lokasi</td>
                    <td><?= htmlspecialchars($selectedTask['lokasi']) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Deskripsi</td>
                    <td><?= nl2br(htmlspecialchars($selectedTask['deskripsi'])) ?></td>
                </tr>
                <?php if ($selectedTask['foto']): ?>
                    <tr>
                        <td style="font-weight: bold;">Foto</td>
                        <td>
                            <img src="<?= baseUrl('/foto_laporan/' . htmlspecialchars($selectedTask['foto'])) ?>"
                                class="img-preview" alt="Foto Kerusakan">
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

            <?php if ($selectedTask['status'] !== 'done'): ?>
                <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 5px;">
                    <h3 style="margin-bottom: 15px;">Update Status</h3>
                    <form action="update_status.php" method="POST">
                        <input type="hidden" name="report_id" value="<?= $selectedTask['report_id'] ?>">
                        <input type="hidden" name="current_status" value="<?= $selectedTask['status'] ?>">

                        <div class="form-group">
                            <label class="form-label">Status Baru</label>
                            <select name="new_status" class="form-control" required>
                                <option value="">Pilih Status</option>
                                <?php if ($selectedTask['status'] === 'process'): ?>
                                    <option value="done">Selesai</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Catatan Perbaikan</label>
                            <textarea name="catatan" class="form-control" required
                                placeholder="Tuliskan apa yang telah dilakukan untuk memperbaiki kerusakan"></textarea>
                        </div>

                        <button type="submit" class="btn btn-success">Update Status</button>
                    </form>
                </div>
            <?php endif; ?>

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
                <a href="tugas.php" class="btn btn-info">Kembali ke Daftar</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            Semua Tugas yang Ditugaskan (<?= count($tasks) ?>)
        </div>
        <div class="card-body">
            <?php if (empty($tasks)): ?>
                <p style="text-align: center; color: #999;">Belum ada tugas yang ditugaskan kepada Anda</p>
            <?php else: ?>
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
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td>#<?= $task['report_id'] ?></td>
                                    <td><?= formatDate($task['tanggal_lapor']) ?></td>
                                    <td><?= htmlspecialchars($task['pelapor']) ?></td>
                                    <td><?= htmlspecialchars($task['judul']) ?></td>
                                    <td><?= htmlspecialchars($task['lokasi']) ?></td>
                                    <td><span
                                            class="badge <?= getStatusBadge($task['status']) ?>"><?= formatStatus($task['status']) ?></span>
                                    </td>
                                    <td>
                                        <a href="?detail=<?= $task['report_id'] ?>" class="btn btn-info btn-sm">Detail</a>
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