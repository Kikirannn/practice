<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireRole('admin');

$pageTitle = 'Semua Laporan';
$activePage = 'laporan';

$pdo = getDBConnection();

// Build filter query
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

// Get filtered reports
$sql = "SELECT l.*, u.nama_lengkap as pelapor, t.nama_lengkap as teknisi_nama 
        FROM laporan l
        JOIN users u ON l.user_id = u.user_id
        LEFT JOIN users t ON l.assigned_to = t.user_id
        $whereClause
        ORDER BY l.tanggal_lapor DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Get technicians for filter
$sql = "SELECT user_id, nama_lengkap FROM users WHERE role = 'teknisi' ORDER BY nama_lengkap";
$stmt = $pdo->query($sql);
$technicians = $stmt->fetchAll();

include '../partials/header.php';
?>

<!-- Print Header (hidden on screen, shown on print) -->
<div class="print-header" style="display: none;">
    <h2>LAPORAN KERUSAKAN FASILITAS SEKOLAH</h2>
    <p>Daftar Semua Laporan</p>
    <p>Dicetak pada: <?= formatDate(date('Y-m-d H:i:s')) ?></p>
    <?php if (!empty($_GET['start_date']) || !empty($_GET['end_date'])): ?>
        <p>Periode:
            <?= !empty($_GET['start_date']) ? date('d/m/Y', strtotime($_GET['start_date'])) : '-' ?>
            s/d
            <?= !empty($_GET['end_date']) ? date('d/m/Y', strtotime($_GET['end_date'])) : '-' ?>
        </p>
    <?php endif; ?>
    <?php if (!empty($_GET['status']) && $_GET['status'] !== 'all'): ?>
        <p>Status: <?= formatStatus($_GET['status']) ?></p>
    <?php endif; ?>
</div>

<h1>Semua Laporan</h1>

<div class="filter-form">
    <form method="GET" action="">
        <h3 style="margin-bottom: 15px;">Filter Laporan</h3>

        <div class="filter-grid">
            <div class="form-group">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control"
                    value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control"
                    value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="all">Semua Status</option>
                    <option value="open" <?= ($_GET['status'] ?? '') === 'open' ? 'selected' : '' ?>>Menunggu Validasi
                    </option>
                    <option value="process" <?= ($_GET['status'] ?? '') === 'process' ? 'selected' : '' ?>>Sedang
                        Dikerjakan</option>
                    <option value="done" <?= ($_GET['status'] ?? '') === 'done' ? 'selected' : '' ?>>Selesai</option>
                    <option value="reject" <?= ($_GET['status'] ?? '') === 'reject' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Teknisi</label>
                <select name="technician_id" class="form-control">
                    <option value="all">Semua Teknisi</option>
                    <?php foreach ($technicians as $tech): ?>
                        <option value="<?= $tech['user_id'] ?>" <?= ($_GET['technician_id'] ?? '') == $tech['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tech['nama_lengkap']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Lokasi</label>
                <input type="text" name="lokasi" class="form-control" placeholder="Cari berdasarkan lokasi"
                    value="<?= htmlspecialchars($_GET['lokasi'] ?? '') ?>">
            </div>
        </div>

        <div class="flex gap-10">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="laporan.php" class="btn btn-info">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="flex justify-between align-center">
            <span>Hasil: <?= count($reports) ?> Laporan</span>
            <button onclick="window.print()" class="btn btn-primary btn-sm">Cetak</button>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($reports)): ?>
            <p style="text-align: center; color: #999;">Tidak ada laporan ditemukan</p>
        <?php else: ?>
            <div class="table-responsive">
                <table id="reportTable">
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
                        <?php foreach ($reports as $report): ?>
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
        <?php endif; ?>
    </div>
</div>

<!-- Print Footer (hidden on screen, shown on print) -->
<div class="print-footer" style="display: none;">
    <p>Sistem Pelaporan Kerusakan Fasilitas Sekolah | Halaman ini dicetak pada <?= date('d/m/Y H:i') ?></p>
    <p>Total Laporan Ditampilkan: <?= count($reports) ?></p>
</div>

<?php include '../partials/footer.php'; ?>
