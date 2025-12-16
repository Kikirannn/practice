<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Sistem Pelaporan Kerusakan' ?></title>
    <link rel="stylesheet" href="/Learning1/assets/css/style.css">
</head>
<body>
    <div class="header">
        <nav class="navbar">
            <a href="/Learning1/index.php" class="navbar-brand">
                🏫 Sistem Pelaporan Kerusakan
            </a>
            
            <ul class="navbar-menu">
                <?php if (isLoggedIn()): ?>
                    <?php $role = getCurrentUserRole(); ?>
                    
                    <?php if ($role === 'siswa'): ?>
                        <li><a href="/Learning1/siswa/dashboard.php" class="<?= isset($activePage) && $activePage === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
                        <li><a href="/Learning1/siswa/lapor.php" class="<?= isset($activePage) && $activePage === 'lapor' ? 'active' : '' ?>">Buat Laporan</a></li>
                        <li><a href="/Learning1/siswa/riwayat.php" class="<?= isset($activePage) && $activePage === 'riwayat' ? 'active' : '' ?>">Riwayat</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'admin'): ?>
                        <li><a href="/Learning1/admin/dashboard.php" class="<?= isset($activePage) && $activePage === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
                        <li><a href="/Learning1/admin/validasi.php" class="<?= isset($activePage) && $activePage === 'validasi' ? 'active' : '' ?>">Validasi</a></li>
                        <li><a href="/Learning1/admin/laporan.php" class="<?= isset($activePage) && $activePage === 'laporan' ? 'active' : '' ?>">Semua Laporan</a></li>
                        <li><a href="/Learning1/admin/reporting.php" class="<?= isset($activePage) && $activePage === 'reporting' ? 'active' : '' ?>">Reporting</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'teknisi'): ?>
                        <li><a href="/Learning1/teknisi/dashboard.php" class="<?= isset($activePage) && $activePage === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
                        <li><a href="/Learning1/teknisi/tugas.php" class="<?= isset($activePage) && $activePage === 'tugas' ? 'active' : '' ?>">Tugas Saya</a></li>
                    <?php endif; ?>
                    
                    <li style="border-left: 1px solid #ccc; padding-left: 10px;">
                        <span style="font-weight: bold;"><?= getCurrentUserName() ?> (<?= getRoleName($role) ?>)</span>
                    </li>
                    <li><a href="/Learning1/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/Learning1/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    
    <div class="container">
