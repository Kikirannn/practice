<?php

function redirect($url)
{
    if (strpos($url, 'http') === 0) {
        header("Location: " . $url);
        exit();
    }

    $host = $_SERVER['HTTP_HOST'];
    $isVirtualHost = (strpos($host, '.test') !== false || strpos($host, '.local') !== false);

    $basePath = $isVirtualHost ? '' : '/Learning1';

    if (strpos($url, '/Learning1') === 0) {
        $url = substr($url, strlen('/Learning1'));
    }
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $fullUrl = $protocol . '://' . $host . $basePath . $url;

    header("Location: " . $fullUrl);
    exit();
}

function baseUrl($path = '')
{
    $host = $_SERVER['HTTP_HOST'];
    $isVirtualHost = (strpos($host, '.test') !== false || strpos($host, '.local') !== false);

    $basePath = $isVirtualHost ? '' : '/Learning1';

    if (strpos($path, '/Learning1') === 0) {
        $path = substr($path, strlen('/Learning1'));
    }

    if (!empty($path) && strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }

    return $basePath . $path;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('/Learning1/login.php');
    }
}

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

function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
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

function uploadFile($file, $uploadDir = null)
{
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $file['error']);
    }

    if ($uploadDir === null) {
        $uploadDir = __DIR__ . '/../foto_laporan/';
    }

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('File harus berupa gambar (JPG, JPEG, PNG)');
    }

    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        throw new Exception('Ukuran file maksimal 2MB');
    }
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('foto_', true) . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Gagal meng-upload file');
    }

    return $filename;
}

function deleteFile($filename, $uploadDir = 'uploads/')
{
    if ($filename && file_exists($uploadDir . $filename)) {
        unlink($uploadDir . $filename);
    }
}

function getRoleName($role)
{
    $roles = [
        'siswa' => 'Siswa',
        'admin' => 'Admin',
        'teknisi' => 'Teknisi'
    ];

    return $roles[$role] ?? $role;
}

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