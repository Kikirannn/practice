<?php
// Session Configuration and Management

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Get current user ID
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function getCurrentUserRole()
{
    return $_SESSION['role'] ?? null;
}

// Get current user name
function getCurrentUserName()
{
    return $_SESSION['nama_lengkap'] ?? null;
}

// Set session data after login
function setSessionData($user)
{
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
    $_SESSION['role'] = $user['role'];
}

// Destroy session on logout
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