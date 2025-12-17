<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); 
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserRole()
{
    return $_SESSION['role'] ?? null;
}

function getCurrentUserName()
{
    return $_SESSION['nama_lengkap'] ?? null;
}
function setSessionData($user)
{
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
    $_SESSION['role'] = $user['role'];
}

function destroySession()
{
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}
?>