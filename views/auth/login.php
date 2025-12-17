<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$error = '';

if (isLoggedIn()) {
    redirect('/Learning1/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        try {
            $pdo = getDBConnection();

            $sql = "SELECT * FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':username' => $username]);

            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                setSessionData($user);

                switch ($user['role']) {
                    case 'siswa':
                        redirect('/Learning1/views/siswa/dashboard.php');
                        break;
                    case 'admin':
                        redirect('/Learning1/views/admin/dashboard.php');
                        break;
                    case 'teknisi':
                        redirect('/Learning1/views/teknisi/dashboard.php');
                        break;
                }
            } else {
                $error = 'Username atau password salah';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem';
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pelaporan Kerusakan Fasilitas</title>
    <link rel="stylesheet" href="<?= baseUrl('/public/css/style.css') ?>">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Sistem Pelaporan Kerusakan Fasilitas</h2>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

            <div style="margin-top: 15px; text-align: center; font-size: 14px;">
                <p>Belum punya akun? <a href="<?= baseUrl('/views/auth/register.php') ?>" style="color: #000; text-decoration: underline;">Daftar di sini</a></p>
            </div>
        </div>
    </div>
</body>

</html>