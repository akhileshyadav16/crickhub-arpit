<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';

function login(): void
{
    $payload = get_request_body();

    $errors = require_fields($payload, [
        'email' => 'string',
        'password' => 'string',
    ]);

    if ($errors) {
        respond_validation($errors);
    }

    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, email, password_hash, role FROM users WHERE email = :email AND is_active = true');
    $stmt->execute([':email' => strtolower($payload['email'])]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($payload['password'], $user['password_hash'])) {
        json_response(['error' => 'Invalid email or password'], 401);
    }

    unset($user['password_hash']);
    login_user($user);

    // Return user data with session info
    json_response([
        'data' => $user,
        'message' => 'Login successful'
    ]);
}

function logout(): void
{
    logout_user();
    json_response([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

function me(): void
{
    $user = current_user();
    if (!$user) {
        json_response(['data' => null]);
    }

    json_response(['data' => $user]);
}




