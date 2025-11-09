<?php

declare(strict_types=1);

function current_user(): ?array
{
    $config = require __DIR__ . '/config.php';
    $sessionKey = $config['auth']['session_key'];

    return $_SESSION[$sessionKey] ?? null;
}

function login_user(array $user): void
{
    $config = require __DIR__ . '/config.php';
    
    // Regenerate session ID for security (prevent session fixation)
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    
    $_SESSION[$config['auth']['session_key']] = $user;
}

function logout_user(): void
{
    $config = require __DIR__ . '/config.php';
    
    // Clear session data
    if (isset($_SESSION[$config['auth']['session_key']])) {
        unset($_SESSION[$config['auth']['session_key']]);
    }
    
    // Destroy session and clear cookie
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Clear all session data
        $_SESSION = [];
        
        // Delete session cookie
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
        
        // Destroy session
        session_destroy();
        
        // Start a new session for subsequent requests
        session_start();
    }
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        json_response(['error' => 'Authentication required'], 401);
    }

    return $user;
}

function require_role(string $role): array
{
    $user = require_auth();
    if (($user['role'] ?? '') !== $role) {
        json_response(['error' => 'Insufficient permissions'], 403);
    }

    return $user;
}




