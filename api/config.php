<?php
// ============================================================================
// CONFIGURACIÓN DE BASE DE DATOS Y SMTP (Gmail)
// ============================================================================
// IMPORTANTE: Reemplaza los valores placeholder con tus valores REALES

return [
    'host' => '127.0.0.1',
    'user' => 'root',
    // XAMPP por defecto usa root sin contraseña; ajusta si tu MySQL usa otra.
    'pass' => '',
    'db'   => 'mayhem_db',
    'charset' => 'utf8mb4',
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'secure' => 'tls',
        // IMPORTANTE: Tu email REAL de Gmail
        'username' => 'jairilicious@gmail.com',
        // IMPORTANTE: Tu App Password (NO la contraseña de Google)
        // Obtén aquí: https://myaccount.google.com/apppasswords
        // (Requiere 2FA activado en Google Account)
        'password' => 'dqbe lydf ypbj kwaz',
        // Opcional - cambiar a tu dominio
        'from_email' => 'jairilicious@gmail.com',
        'from_name' => 'Lab Portal - Recuperación',
    ],
];
