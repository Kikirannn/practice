<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('teknisi');

$pageTitle = 'Dashboard Teknisi';
$activePage = 'dashboard';

$userId = getCurrentUserId();
$pdo = getDBConnection();

// Get statistics for this technician
$sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as process_count,
            SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done_count
        FROM laporan 
        WHERE assigned_to = :user_id";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$stats = $stmt->fetch();

// Get recent assigned tasks
$sql = "SELECT l.*, u.nama_lengkap as pelapor 
        FROM laporan l
        JOIN users u ON l.user_id = u.user_id
        WHERE l.assigned_to = :user_id AND l.status != 'reject'
        ORDER BY l.tanggal_lapor DESC
        LIMIT 5";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$recentTasks = $stmt->fetchAll();

include '../includes/header.php';
?>

<h1>Dashboard Teknisi</h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?= $stats['total'] ?></h3>
        <p>Total Tugas</p>
    </div>
    <div class="stat-card" style="border-left-color: #17a2b8;">
        <h3><?= $stats['process_count'] ?></h3>
        <p>Sedang Dikerjakan</p>
    </div>
    <div class="stat-card" style="border-left-color: #28a745;">
        <h3><?= $stats['done_count'] ?></h3>
        <p>Selesai</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Tugas Terbaru
    </div>
    <div class="card-body">
        <?php if (empty($recentTasks)): ?>
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
                        <?php foreach ($recentTasks as $task): ?>
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
                                    <a href="tugas.php?detail=<?= $task['report_id'] ?>" class="btn btn-info btn-sm">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-20">
                <a href="tugas.php" class="btn btn-primary">Lihat Semua Tugas</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>