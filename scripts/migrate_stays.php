<?php
// Migración única: mover estancias desde employees y employee_stays_history a nueva tabla stays.
// Uso: php scripts/migrate_stays.php

declare(strict_types=1);

$config = require __DIR__ . '/../api/config.php';

mysqli_report(MYSQLI_REPORT_OFF);
$db = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($db->connect_errno) {
    fwrite(STDERR, "Error de conexión: {$db->connect_error}\n");
    exit(1);
}
$db->set_charset($config['charset']);

$db->begin_transaction();

try {
    // Crear tabla stays
    $db->query("
        CREATE TABLE IF NOT EXISTS stays (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            fecha_inicio DATE NOT NULL,
            fecha_fin DATE NOT NULL,
            motivo VARCHAR(150) NULL,
            group_id INT NULL,
            horario TINYINT(1) NOT NULL DEFAULT 1,
            institucion VARCHAR(255) NULL,
            pais VARCHAR(255) NULL,
            status ENUM('active','archived') NOT NULL DEFAULT 'active',
            archived_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_stays_employee (employee_id),
            INDEX idx_stays_status (status),
            CONSTRAINT fk_stay_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
            CONSTRAINT fk_stay_group FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL
        )
    ");

    // Copiar estancias activas actuales desde employees si aún no existen (solo si las columnas siguen presentes)
    $colCheck = $db->query("SHOW COLUMNS FROM employees LIKE 'fecha_inicio'");
    if ($colCheck && $colCheck->num_rows) {
        $db->query("
            INSERT INTO stays (employee_id, fecha_inicio, fecha_fin, motivo, group_id, horario, institucion, pais, status, archived_at)
            SELECT e.id, e.fecha_inicio, e.fecha_fin, e.motivo, e.group_id, e.horario, e.institucion, e.pais, 'active', NULL
            FROM employees e
            WHERE e.fecha_inicio IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1 FROM stays s WHERE s.employee_id = e.id AND s.status = 'active'
              )
        ");
    }

    // Copiar histórico
    $db->query("
        INSERT INTO stays (employee_id, fecha_inicio, fecha_fin, motivo, group_id, horario, institucion, pais, status, archived_at)
        SELECT h.employee_id, h.fecha_inicio, h.fecha_fin, h.motivo, h.group_id, h.horario, h.institucion, h.pais, 'archived', h.archived_at
        FROM employee_stays_history h
        WHERE NOT EXISTS (
            SELECT 1 FROM stays s
            WHERE s.employee_id = h.employee_id
              AND s.fecha_inicio = h.fecha_inicio
              AND s.fecha_fin = h.fecha_fin
              AND s.status = 'archived'
        )
    ");

    // Desactivar claves foráneas para poder eliminar columnas legacy
    $db->query("SET FOREIGN_KEY_CHECKS=0");
    // Soltar FK de grupo si existe (ignorar error si no está)
    $db->query("ALTER TABLE employees DROP FOREIGN KEY fk_employees_group");

    // Eliminar columnas legacy de employees si existen
    $cols = ['fecha_inicio','fecha_fin','motivo','grupo','group_id','horario'];
    foreach ($cols as $col) {
        $check = $db->query("SHOW COLUMNS FROM employees LIKE '{$col}'");
        if ($check && $check->num_rows > 0) {
            $db->query("ALTER TABLE employees DROP COLUMN {$col}");
        }
    }

    // Reactivar claves foráneas
    $db->query("SET FOREIGN_KEY_CHECKS=1");

    $db->commit();
    fwrite(STDOUT, "Migración completada correctamente.\n");
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, "Error: {$e->getMessage()}\n");
    exit(1);
}
