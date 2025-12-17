<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$error = '';
$success = '';

if (isLoggedIn()) {
    redirect('/Learning1/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');

    if (empty($username) || empty($password) || empty($confirm_password) || empty($nama_lengkap)) {
        $error = 'Semua field harus diisi';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username hanya boleh mengandung huruf, angka, dan underscore';
    } else {
        try {
            $pdo = getDBConnection();

            $sql = "SELECT user_id FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':username' => $username]);

            if ($stmt->fetch()) {
                $error = 'Username sudah terdaftar, silakan gunakan username lain';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (username, password, nama_lengkap, role) 
                        VALUES (:username, :password, :nama_lengkap, 'siswa')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashed_password,
                    ':nama_lengkap' => $nama_lengkap
                ]);

                $success = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                
                $username = '';
                $nama_lengkap = '';
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
    <title>Registrasi - Sistem Pelaporan Kerusakan Fasilitas</title>
    <link rel="stylesheet" href="<?= baseUrl('/public/css/style.css') ?>">
    <style>
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }

        .password-strength.weak {
            color: #d32f2f;
        }

        .password-strength.medium {
            color: #f57c00;
        }

        .password-strength.strong {
            color: #388e3c;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .register-link a {
            color: #000;
            text-decoration: underline;
        }

        .register-link a:hover {
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Registrasi Akun Siswa</h2>
                <p>Buat akun baru untuk melaporkan kerusakan fasilitas</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        placeholder="Minimal 3 karakter, tanpa spasi">
                    <small style="font-size: 11px; color: #666;">Hanya huruf, angka, dan underscore (_)</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required
                        value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>"
                        placeholder="Nama lengkap Anda">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required
                        placeholder="Minimal 8 karakter">
                    <div id="passwordStrength" class="password-strength"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required
                        placeholder="Ulangi password Anda">
                    <div id="passwordMatch" style="margin-top: 5px; font-size: 12px;"></div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Daftar</button>
            </form>

            <div class="register-link">
                <p>Sudah punya akun? <a href="<?= baseUrl('/views/auth/login.php') ?>">Login di sini</a></p>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const strengthDiv = document.getElementById('passwordStrength');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = '';
            let className = '';

            if (password.length === 0) {
                strength = '';
                className = '';
            } else if (password.length < 8) {
                strength = 'Terlalu pendek (minimal 8 karakter)';
                className = 'weak';
            } else if (password.length < 12) {
                strength = 'Password sedang';
                className = 'medium';
            } else {
                strength = 'Password kuat';
                className = 'strong';
            }

            strengthDiv.textContent = strength;
            strengthDiv.className = 'password-strength ' + className;
        });

        const confirmPasswordInput = document.getElementById('confirm_password');
        const matchDiv = document.getElementById('passwordMatch');

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (confirmPassword.length === 0) {
                matchDiv.textContent = '';
                matchDiv.style.color = '';
            } else if (password === confirmPassword) {
                matchDiv.textContent = '✓ Password cocok';
                matchDiv.style.color = '#388e3c';
            } else {
                matchDiv.textContent = '✗ Password tidak cocok';
                matchDiv.style.color = '#d32f2f';
            }
        }

        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const username = document.getElementById('username').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak sama!');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password minimal 8 karakter!');
                return false;
            }

            if (username.length < 3) {
                e.preventDefault();
                alert('Username minimal 3 karakter!');
                return false;
            }

            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                e.preventDefault();
                alert('Username hanya boleh mengandung huruf, angka, dan underscore!');
                return false;
            }
        });
    </script>
</body>

</html>
