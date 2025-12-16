<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('siswa');

$pageTitle = 'Buat Laporan';
$activePage = 'lapor';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = sanitize($_POST['judul'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $lokasi = sanitize($_POST['lokasi'] ?? '');

    if (empty($judul) || empty($deskripsi) || empty($lokasi)) {
        $error = 'Semua field wajib diisi kecuali foto';
    } else {
        try {
            // Handle file upload
            $foto = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $foto = uploadFile($_FILES['foto']);
            }

            $pdo = getDBConnection();

            $sql = "INSERT INTO laporan (user_id, judul, deskripsi, lokasi, foto, status, tanggal_lapor) 
                    VALUES (:user_id, :judul, :deskripsi, :lokasi, :foto, 'open', NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => getCurrentUserId(),
                ':judul' => $judul,
                ':deskripsi' => $deskripsi,
                ':lokasi' => $lokasi,
                ':foto' => $foto
            ]);

            $success = 'Laporan berhasil dibuat dan menunggu validasi admin';

            // Clear form
            $_POST = array();

        } catch (Exception $e) {
            $error = $e->getMessage();
            error_log($e->getMessage());
        }
    }
}

include '../includes/header.php';
?>

<h1>Buat Laporan Kerusakan</h1>

<?php if ($success): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
        <a href="riwayat.php">Lihat riwayat laporan</a>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Form Laporan Kerusakan Fasilitas
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data" id="laporForm">
            <div class="form-group">
                <label class="form-label">Judul Laporan *</label>
                <input type="text" name="judul" class="form-control" required placeholder="Contoh: Lampu Kelas Mati"
                    value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi Kerusakan *</label>
                <textarea name="deskripsi" class="form-control" required
                    placeholder="Jelaskan detail kerusakan yang terjadi"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Lokasi *</label>
                <input type="text" name="lokasi" class="form-control" required
                    placeholder="Contoh: Gedung A - Lantai 2 - Kelas XII-A"
                    value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Foto (Opsional)</label>
                <input type="file" name="foto" class="form-control" accept="image/jpeg,image/jpg,image/png"
                    onchange="previewImage(this, 'preview')">
                <small style="color: #666;">Format: JPG, JPEG, PNG. Maksimal 2MB</small>
                <br>
                <img id="preview" class="img-preview" style="display: none;">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Kirim Laporan</button>
                <a href="dashboard.php" class="btn btn-info">Kembali</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>