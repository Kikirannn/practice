<?php
// Helper Functions

// Redirect to a URL with auto-detect base path
function redirect($url)
{
    // Jika URL sudah lengkap (http/https), langsung redirect
    if (strpos($url, 'http') === 0) {
        header("Location: " . $url);
        exit();
    }

    // Deteksi apakah menggunakan virtual host atau localhost
    $host = $_SERVER['HTTP_HOST'];
    $isVirtualHost = (strpos($host, '.test') !== false || strpos($host, '.local') !== false);

    // Tentukan base path
    $basePath = $isVirtualHost ? '' : '/Learning1';

    // Jika URL dimulai dengan /Learning1, hilangkan untuk normalisasi
    if (strpos($url, '/Learning1') === 0) {
        $url = substr($url, strlen('/Learning1'));
    }

    // Tambahkan base path dari server
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $fullUrl = $protocol . '://' . $host . $basePath . $url;

    header("Location: " . $fullUrl);
    exit();
}

// Get base URL for assets and links
function baseUrl($path = '')
{
    // Deteksi apakah menggunakan virtual host atau localhost
    $host = $_SERVER['HTTP_HOST'];
    $isVirtualHost = (strpos($host, '.test') !== false || strpos($host, '.local') !== false);

    // Tentukan base path
    $basePath = $isVirtualHost ? '' : '/Learning1';

    // Hilangkan /Learning1 dari path jika ada
    if (strpos($path, '/Learning1') === 0) {
        $path = substr($path, strlen('/Learning1'));
    }

    // Pastikan path dimulai dengan /
    if (!empty($path) && strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }

    return $basePath . $path;
}

// Check if user is logged in, redirect to login if not
function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('/Learning1/login.php');
    }
}

// Check if user has required role
function requireRole($allowedRoles)
{
    requireLogin();

    $currentRole = getCurrentUserRole();

    if (is_array($allowedRoles)) {
        if (!in_array($currentRole, $allowedRoles)) {
            redirect('/Learning1/index.php');
        }
    } else {
        if ($currentRole !== $allowedRoles) {
            redirect('/Learning1/index.php');
        }
    }
}

// Sanitize input
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Format date to Indonesian format
function formatDate($date)
{
    if (!$date)
        return '-';

    $timestamp = strtotime($date);
    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $d = date('d', $timestamp);
    $m = $bulan[(int) date('m', $timestamp)];
    $y = date('Y', $timestamp);
    $time = date('H:i', $timestamp);

    return "$d $m $y, $time";
}

// Format status to Indonesian
function formatStatus($status)
{
    $statusMap = [
        'open' => 'Menunggu Validasi',
        'process' => 'Sedang Dikerjakan',
        'done' => 'Selesai',
        'reject' => 'Ditolak'
    ];

    return $statusMap[$status] ?? $status;
}

// Get status badge class
function getStatusBadge($status)
{
    $badges = [
        'open' => 'badge-warning',
        'process' => 'badge-info',
        'done' => 'badge-success',
        'reject' => 'badge-danger'
    ];

    return $badges[$status] ?? 'badge-secondary';
}

// Validate and upload file
function uploadFile($file, $uploadDir = 'uploads/')
{
    // Check if file was uploaded
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $file['error']);
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('File harus berupa gambar (JPG, JPEG, PNG)');
    }

    // Validate file size (max 2MB)
    $maxSize = 2 * 1024 * 1024; // 2MB in bytes
    if ($file['size'] > $maxSize) {
        throw new Exception('Ukuran file maksimal 2MB');
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('foto_', true) . '.' . $extension;
    $filepath = $uploadDir . $filename;

    // Create upload directory if not exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Gagal meng-upload file');
    }

    return $filename;
}

// Delete file
function deleteFile($filename, $uploadDir = 'uploads/')
{
    if ($filename && file_exists($uploadDir . $filename)) {
        unlink($uploadDir . $filename);
    }
}

// Get role name in Indonesian
function getRoleName($role)
{
    $roles = [
        'siswa' => 'Siswa',
        'admin' => 'Admin',
        'teknisi' => 'Teknisi'
    ];

    return $roles[$role] ?? $role;
}

// Generate random color for charts
function generateColor($index)
{
    $colors = [
        '#FF6384',
        '#36A2EB',
        '#FFCE56',
        '#4BC0C0',
        '#9966FF',
        '#FF9F40',
        '#FF6384',
        '#C9CBCF',
        '#4BC0C0',
        '#FF9F40'
    ];

    return $colors[$index % count($colors)];
}
?>