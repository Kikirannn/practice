<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Password Hash</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            border-bottom: 3px solid #FFFDD0;
            padding-bottom: 10px;
        }

        .hash-box {
            background: #f9f9f9;
            padding: 15px;
            border: 2px solid #FFFDD0;
            border-radius: 5px;
            margin: 20px 0;
            word-wrap: break-word;
            font-family: monospace;
            font-size: 14px;
        }

        .sql-box {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
        }

        .success {
            color: #28a745;
            font-weight: bold;
        }

        .step {
            background: #e7f3ff;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #2196F3;
            border-radius: 4px;
        }

        .step-number {
            background: #2196F3;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: bold;
            margin-right: 10px;
        }

        button {
            background: #FFFDD0;
            border: 1px solid #e8e6b8;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }

        button:hover {
            background: #f5f3c0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔐 Password Hash Generator</h1>

        <?php
        $password = 'password12345';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        ?>

        <p>Password yang akan di-hash: <strong><?= $password ?></strong></p>

        <div class="hash-box">
            <strong>Hash yang di-generate:</strong><br>
            <span class="success" id="hashValue"><?= $hash ?></span>
        </div>

        <button onclick="copyHash()">📋 Copy Hash</button>

        <hr style="margin: 30px 0;">

        <h2>📝 Langkah-Langkah:</h2>

        <div class="step">
            <span class="step-number">1</span>
            <strong>Copy hash di atas</strong> (klik tombol "Copy Hash")
        </div>

        <div class="step">
            <span class="step-number">2</span>
            <strong>Buka phpMyAdmin</strong> → Database <code>learning1</code> → Tab <strong>SQL</strong>
        </div>

        <div class="step">
            <span class="step-number">3</span>
            <strong>Copy & Paste SQL query di bawah</strong> ini ke phpMyAdmin, lalu klik <strong>Go</strong>
        </div>

        <div class="sql-box" id="sqlQuery">-- Update password untuk semua user
            -- Password: password123

            UPDATE users SET password = '<?= $hash ?>' WHERE username = 'siswa1';
            UPDATE users SET password = '<?= $hash ?>' WHERE username = 'siswa2';
            UPDATE users SET password = '<?= $hash ?>' WHERE username = 'siswa3';
            UPDATE users SET password = '<?= $hash ?>' WHERE username = 'admin1';
            UPDATE users SET password = '<?= $hash ?>' WHERE username = 'admin2';
            UPDATE users SET password = '<?= $hash ?>' WHERE username = 'teknisi1';
            UPDATE users SET password = '<?= $hash ?>' WHERE username = 'teknisi2';
        </div>

        <button onclick="copySqlQuery()">📋 Copy SQL Query</button>

        <div class="step">
            <span class="step-number">4</span>
            Setelah query berhasil dijalankan, <strong>coba login lagi</strong> dengan:
            <ul>
                <li>Username: <code>siswa1</code></li>
                <li>Password: <code>password123</code></li>
            </ul>
        </div>

        <hr style="margin: 30px 0;">

        <h3>🔄 Generate Hash Baru</h3>
        <p>Jika ingin generate hash untuk password lain, refresh halaman ini atau ubah variable <code>$password</code>
            di file ini.</p>

        <button onclick="location.reload()">🔄 Refresh Page</button>
        <a href="login.php" style="display: inline-block; margin-left: 10px;">
            <button>🔐 Ke Halaman Login</button>
        </a>
    </div>

    <script>
        function copyHash() {
            const hash = document.getElementById('hashValue').textContent;
            navigator.clipboard.writeText(hash).then(() => {
                alert('✅ Hash berhasil di-copy!');
            });
        }

        function copySqlQuery() {
            const sql = document.getElementById('sqlQuery').textContent;
            navigator.clipboard.writeText(sql).then(() => {
                alert('✅ SQL Query berhasil di-copy!\n\nSekarang paste di phpMyAdmin → SQL tab');
            });
        }
    </script>
</body>

</html>