<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/tracking.php';

requireRole('admin');

$pageTitle = 'Validasi Laporan';
$activePage = 'validasi';

$success = '';
$error = '';

$pdo = getDBConnection();

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportId = (int) ($_POST['report_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        $technician_id = (int) ($_POST['technician_id'] ?? 0);

        if ($technician_id <= 0) {
            $error = 'Pilih teknisi yang akan ditugaskan';
        } else {
            try {
                $pdo->beginTransaction();

                // Get current status
                $sql = "SELECT status FROM laporan WHERE report_id = :report_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':report_id' => $reportId]);
                $currentStatus = $stmt->fetchColumn();

                // Update laporan
                $sql = "UPDATE laporan SET status = 'process', assigned_to = :technician_id WHERE report_id = :report_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':report_id' => $reportId,
                    ':technician_id' => $technician_id
                ]);

                // Log status change (manual trigger)
                logStatusChange($reportId, $currentStatus, 'process', $technician_id, 'Laporan di-approve dan ditugaskan ke teknisi');

                $pdo->commit();
                $success = 'Laporan berhasil di-approve dan ditugaskan ke teknisi';

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Gagal approve laporan: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'reject') {
        $catatan = sanitize($_POST['catatan'] ?? '');

        try {
            $pdo->beginTransaction();

            // Get current status
            $sql = "SELECT status FROM laporan WHERE report_id = :report_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':report_id' => $reportId]);
            $currentStatus = $stmt->fetchColumn();

            // Update laporan
            $sql = "UPDATE laporan SET status = 'reject' WHERE report_id = :report_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':report_id' => $reportId]);

            // Log status change (manual trigger)
            logStatusChange($reportId, $currentStatus, 'reject', null, $catatan ?: 'Laporan ditolak');

            $pdo->commit();
            $success = 'Laporan berhasil ditolak';

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal reject laporan: ' . $e->getMessage();
        }
    }
}

// Get pending reports (status = open)
$sql = "SELECT l.*, u.nama_lengkap as pelapor 
        FROM laporan l
        JOIN users u ON l.user_id = u.user_id
        WHERE l.status = 'open'
        ORDER BY l.tanggal_lapor ASC";

$stmt = $pdo->query($sql);
$pendingReports = $stmt->fetchAll();

// Get technicians
$sql = "SELECT user_id, nama_lengkap FROM users WHERE role = 'teknisi' ORDER BY nama_lengkap";
$stmt = $pdo->query($sql);
$technicians = $stmt->fetchAll();

include '../partials/header.php';
?>

<h1>Validasi Laporan</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Laporan Menunggu Validasi (<?= count($pendingReports) ?>)
    </div>
    <div class="card-body">
        <?php if (empty($pendingReports)): ?>
            <p style="text-align: center; color: #999;">Tidak ada laporan yang menunggu validasi</p>
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
                            <th>Deskripsi</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingReports as $report): ?>
                            <tr>
                                <td>#<?= $report['report_id'] ?></td>
                                <td><?= formatDate($report['tanggal_lapor']) ?></td>
                                <td><?= htmlspecialchars($report['pelapor']) ?></td>
                                <td><?= htmlspecialchars($report['judul']) ?></td>
                                <td><?= htmlspecialchars($report['lokasi']) ?></td>
                                <td><?= htmlspecialchars(substr($report['deskripsi'], 0, 50)) ?>...</td>
                                <td>
                                    <?php if ($report['foto']): ?>
                                        <a href="<?= baseUrl('/public/uploads/' . htmlspecialchars($report['foto'])) ?>"
                                            target="_blank">Lihat</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Approve Form -->
                                    <form method="POST" style="display: inline-block; margin-right: 5px;">
                                        <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <select name="technician_id" required style="padding: 5px; font-size: 12px;">
                                            <option value="">Pilih Teknisi</option>
                                            <?php foreach ($technicians as $tech): ?>
                                                <option value="<?= $tech['user_id'] ?>">
                                                    <?= htmlspecialchars($tech['nama_lengkap']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-success btn-sm"
                                            onclick="return confirm('Approve laporan ini dan tugaskan ke teknisi?')">
                                            Approve
                                        </button>
                                    </form>

                                    <!-- Reject Form -->
                                    <form method="POST" style="display: inline-block;"
                                        onsubmit="return confirm('Tolak laporan ini?')">
                                        <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="text" name="catatan" placeholder="Alasan (opsional)"
                                            style="padding: 5px; font-size: 12px; width: 150px;">
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../partials/footer.php'; ?>