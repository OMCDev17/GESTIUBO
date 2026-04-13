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

// Forzar UTF-8 en toda la aplicación
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

if (session_status() === PHP_SESSION_NONE) {
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
        return; // requireLogin will already handle
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

