<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect based on role if already logged in
if (isLoggedIn()) {
    $role = getCurrentUserRole();

    switch ($role) {
        case 'siswa':
            redirect('/Learning1/siswa/dashboard.php');
            break;
        case 'admin':
            redirect('/Learning1/admin/dashboard.php');
            break;
        case 'teknisi':
            redirect('/Learning1/teknisi/dashboard.php');
            break;
        default:
            redirect('/Learning1/login.php');
    }
} else {
    redirect('/Learning1/login.php');
}
?>