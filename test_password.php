<?php
echo "<h2>Password Hash Tester</h2>";

$password = 'password123';

$newHash = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>Information:</h3>";
echo "Plain Password: <strong>$password</strong><br>";
echo "New Generated Hash: <br><code>$newHash</code><br><br>";

$hashFromDatabase = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

echo "<h3>Verification Test:</h3>";
echo "Hash from database.sql: <br><code>$hashFromDatabase</code><br><br>";

if (password_verify($password, $hashFromDatabase)) {
    echo "<span style='color: green;'>✅ VALID - Hash dari database COCOK dengan password '$password'</span><br>";
} else {
    echo "<span style='color: red;'>❌ INVALID - Hash dari database TIDAK COCOK dengan password '$password'</span><br>";
}

echo "<hr>";
echo "<h3>Solution:</h3>";
echo "Jika hash tidak cocok, gunakan SQL query ini untuk update password:<br><br>";
echo "<textarea style='width: 100%; height: 200px; font-family: monospace;'>";
echo "-- Update semua user dengan password baru\n";
echo "UPDATE users SET password = '$newHash' WHERE username = 'siswa1';\n";
echo "UPDATE users SET password = '$newHash' WHERE username = 'siswa2';\n";
echo "UPDATE users SET password = '$newHash' WHERE username = 'siswa3';\n";
echo "UPDATE users SET password = '$newHash' WHERE username = 'admin1';\n";
echo "UPDATE users SET password = '$newHash' WHERE username = 'admin2';\n";
echo "UPDATE users SET password = '$newHash' WHERE username = 'teknisi1';\n";
echo "UPDATE users SET password = '$newHash' WHERE username = 'teknisi2';\n";
echo "</textarea>";

echo "<br><br>";
echo "<h3>Connection Test:</h3>";

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    echo "<span style='color: green;'>✅ Database connection SUCCESS</span><br>";
    
    $sql = "SELECT * FROM users WHERE username = 'siswa1'";
    $stmt = $pdo->query($sql);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<span style='color: green;'>✅ User 'siswa1' EXISTS in database</span><br>";
        echo "User details:<br>";
        echo "- Username: " . htmlspecialchars($user['username']) . "<br>";
        echo "- Nama: " . htmlspecialchars($user['nama_lengkap']) . "<br>";
        echo "- Role: " . htmlspecialchars($user['role']) . "<br>";
        echo "- Password Hash: <code>" . htmlspecialchars($user['password']) . "</code><br><br>";
        
        if (password_verify($password, $user['password'])) {
            echo "<span style='color: green; font-weight: bold;'>✅✅✅ PASSWORD CORRECT! Login seharusnya berhasil!</span><br>";
        } else {
            echo "<span style='color: red; font-weight: bold;'>❌ PASSWORD MISMATCH! Password hash di database salah!</span><br>";
            echo "<br><strong>FIX: Jalankan query ini di phpMyAdmin:</strong><br>";
            echo "<code>UPDATE users SET password = '$newHash' WHERE username = 'siswa1';</code>";
        }
    } else {
        echo "<span style='color: red;'>❌ User 'siswa1' NOT FOUND in database</span><br>";
        echo "Pastikan Anda sudah import database.sql dengan benar!";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database connection FAILED: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    echo "Pastikan database 'learning1' sudah dibuat!";
}
?>
