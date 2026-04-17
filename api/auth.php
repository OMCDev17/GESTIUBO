<?php

/**
 * auth.php
 *
 * Provides session-based authentication helpers.
 *
 * Usage (pages):
 *   require_once __DIR__ . '/auth.php';
 *   requireLogin();
 *   requireRole(['admin']);
 *
 * Usage (API):
 *   require_once __DIR__ . '/auth.php';
 *   requireRole(['admin'], true);
 */

// Force UTF-8 across the app.
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

// Avoid cache leakage between users (important on shared hosting).
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Harden PHP session handling.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');

if (session_status() === PHP_SESSION_NONE) {
    session_name('GESTIUBOSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function getSessionUser(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function requireLogin(bool $isApi = false): void
{
    if (!getSessionUser()) {
        if ($isApi) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }

        header('Location: ../Loggin.php');
        exit;
    }
}

/**
 * @param string|string[] $roles
 * @param bool $isApi
 */
function requireRole($roles, bool $isApi = false): void
{
    requireLogin($isApi);

    $user = getSessionUser();
    if (!$user) {
        return; // requireLogin already handled response/redirect.
    }

    $userRole = strtolower(trim($user['rol'] ?? ''));
    $allowed = is_array($roles) ? $roles : [$roles];
    $allowed = array_map(function ($r) {
        return strtolower(trim($r));
    }, $allowed);

    if (!in_array($userRole, $allowed, true)) {
        if ($isApi) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        header('Location: ../Loggin.php');
        exit;
    }
}
