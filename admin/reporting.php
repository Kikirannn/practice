<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$pageTitle = 'Reporting & Statistik';
$activePage = 'reporting';

$pdo = getDBConnection();

// 1. Total laporan per bulan (last 6 months)
$sql = "SELECT 
            DATE_FORMAT(tanggal_lapor, '%Y-%m') as bulan,
            COUNT(*) as total
        FROM laporan
        WHERE tanggal_lapor >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(tanggal_lapor, '%Y-%m')
        ORDER BY bulan DESC";
$stmt = $pdo->query($sql);
$monthlyReports = $stmt->fetchAll();

// 2. Teknisi paling produktif (berdasarkan jumlah laporan selesai)
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

// 3. Jumlah kerusakan berdasarkan lokasi
$sql = "SELECT 
            SUBSTRING_INDEX(lokasi, '-', 1) as gedung,
            COUNT(*) as total
        FROM laporan
        GROUP BY SUBSTRING_INDEX(lokasi, '-', 1)
        ORDER BY total DESC
        LIMIT 10";
$stmt = $pdo->query($sql);
$locationStats = $stmt->fetchAll();

// 4. Jumlah status done per minggu (last 8 weeks)
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

// 5. Status breakdown
$sql = "SELECT status, COUNT(*) as total FROM laporan GROUP BY status";
$stmt = $pdo->query($sql);
$statusBreakdown = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- Print Header (hidden on screen, shown on print) -->
<div class="print-header" style="display: none;">
    <h2>LAPORAN STATISTIK KERUSAKAN FASILITAS SEKOLAH</h2>
    <p>Reporting & Analisis Data</p>
    <p>Dicetak pada: <?= formatDate(date('Y-m-d H:i:s')) ?></p>
</div>

<h1>Reporting & Statistik</h1>

<!-- Status Breakdown -->
<div class="card">
    <div class="card-header">Status Laporan</div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Jumlah</th>
                        <th>Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = array_sum(array_column($statusBreakdown, 'total'));
                    foreach ($statusBreakdown as $stat): 
                        $percentage = $total > 0 ? round(($stat['total'] / $total) * 100, 1) : 0;
                    ?>
                        <tr>
                            <td><span class="badge <?= getStatusBadge($stat['status']) ?>"><?= formatStatus($stat['status']) ?></span></td>
                            <td><?= $stat['total'] ?></td>
                            <td><?= $percentage ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Monthly Reports -->
<div class="card">
    <div class="card-header">Total Laporan Per Bulan (6 Bulan Terakhir)</div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Total Laporan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($monthlyReports)): ?>
                        <tr><td colspan="2" style="text-align: center;">Tidak ada data</td></tr>
                    <?php else: ?>
                        <?php foreach ($monthlyReports as $report): ?>
                            <tr>
                                <td><?= date('F Y', strtotime($report['bulan'] . '-01')) ?></td>
                                <td><?= $report['total'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Top Technicians -->
<div class="card">
    <div class="card-header">Teknisi Paling Produktif (Berdasarkan Laporan Selesai)</div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Nama Teknisi</th>
                        <th>Total Laporan Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($topTechnicians)): ?>
                        <tr><td colspan="3" style="text-align: center;">Tidak ada data</td></tr>
                    <?php else: ?>
                        <?php foreach ($topTechnicians as $index => $tech): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($tech['nama_lengkap']) ?></td>
                                <td><?= $tech['total_done'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Location Stats -->
<div class="card">
    <div class="card-header">Jumlah Kerusakan Berdasarkan Lokasi/Gedung</div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Lokasi/Gedung</th>
                        <th>Total Laporan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($locationStats)): ?>
                        <tr><td colspan="2" style="text-align: center;">Tidak ada data</td></tr>
                    <?php else: ?>
                        <?php foreach ($locationStats as $loc): ?>
                            <tr>
                                <td><?= htmlspecialchars(trim($loc['gedung'])) ?></td>
                                <td><?= $loc['total'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Weekly Done -->
<div class="card">
    <div class="card-header">Laporan Selesai Per Minggu (8 Minggu Terakhir)</div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Minggu</th>
                        <th>Total Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($weeklyDone)): ?>
                        <tr><td colspan="2" style="text-align: center;">Tidak ada data</td></tr>
                    <?php else: ?>
                        <?php foreach ($weeklyDone as $week): ?>
                            <tr>
                                <td>Week <?= $week['minggu_ke'] ?> - <?= $week['tahun'] ?></td>
                                <td><?= $week['total'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-center mt-20">
    <button onclick="window.print()" class="btn btn-primary">Cetak Laporan</button>
</div>

<!-- Print Footer (hidden on screen, shown on print) -->
<div class="print-footer" style="display: none;">
    <p>Sistem Pelaporan Kerusakan Fasilitas Sekolah | Laporan Statistik dicetak pada <?= date('d/m/Y H:i') ?></p>
</div>

<?php include '../includes/footer.php'; ?>
