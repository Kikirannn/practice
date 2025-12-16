<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/Learning1/index.php');
}

// Process login
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
                // Set session
                setSessionData($user);

                // Redirect based on role
                switch ($user['role']) {
                    case 'siswa':
                        redirect('/Learning1/siswa/dashboard.php');
                        break;
                    case 'admin':
                        redirect('/Learning1/admin/dashboard.php');
                        break;
                    case 'teknisi':
                        redirect('/Learning1/teknisi/dashboard.php');
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
    <link rel="stylesheet" href="/Learning1/assets/css/style.css">
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

            <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
                <p><strong>Demo Credentials:</strong></p>
                <p>Siswa: siswa1 / password123</p>
                <p>Admin: admin1 / password123</p>
                <p>Teknisi: teknisi1 / password123</p>
            </div>
        </div>
    </div>
</body>

</html>