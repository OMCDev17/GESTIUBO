<?php
// ============================================================================
// MIGRACIÓN: Actualizar tabla password_resets con columna 'code'
// ============================================================================
// Ejecuta este archivo una sola vez para migrar la base de datos

$config = require __DIR__ . '/api/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}
$mysqli->set_charset($config['charset']);

echo "<pre>";
echo "=== MIGRACIÓN: Actualizando tabla password_resets ===\n\n";

// 1. Verificar que la tabla existe
$checkTable = $mysqli->query("SHOW TABLES LIKE 'password_resets'");
if ($checkTable->num_rows === 0) {
    echo "La tabla password_resets no existe. Se creará desde cero...\n";
    $createTableSql = "CREATE TABLE password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        code VARCHAR(4),
        token VARCHAR(64),
        expires_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (code),
        FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($mysqli->query($createTableSql)) {
        echo "✓ Tabla creada exitosamente\n";
    } else {
        echo "✗ Error al crear tabla: " . $mysqli->error . "\n";
    }
} else {
    echo "✓ Tabla password_resets ya existe\n\n";
    
    // 2. Verificar si la columna 'code' existe
    $checkColumn = $mysqli->query("SHOW COLUMNS FROM password_resets LIKE 'code'");
    
    if ($checkColumn->num_rows === 0) {
        echo "La columna 'code' no existe. Agregándola...\n";
        
        // Agregar columna 'code'
        $addColumnSql = "ALTER TABLE password_resets ADD COLUMN code VARCHAR(4) AFTER user_id";
        if ($mysqli->query($addColumnSql)) {
            echo "✓ Columna 'code' agregada exitosamente\n";
        } else {
            echo "✗ Error al agregar columna: " . $mysqli->error . "\n";
            exit;
        }
        
        // Crear índice en 'code' si no existe
        $checkIndex = $mysqli->query("SHOW INDEX FROM password_resets WHERE Key_name = 'code'");
        if ($checkIndex->num_rows === 0) {
            $addIndexSql = "ALTER TABLE password_resets ADD INDEX (code)";
            if ($mysqli->query($addIndexSql)) {
                echo "✓ Índice en 'code' creado exitosamente\n";
            } else {
                echo "✗ Error al crear índice: " . $mysqli->error . "\n";
            }
        }
        
        // Limpiar datos antiguos (opcional)
        echo "\n¿Deseas eliminar los datos antiguos de password_resets? (Recomendado)\n";
        echo "Si ejecutas esto en consola, puedes agregar ?cleanup=1 a la URL\n";
        
        if (isset($_GET['cleanup']) && $_GET['cleanup'] == 1) {
            $mysqli->query("DELETE FROM password_resets WHERE code IS NULL");
            echo "✓ Datos antiguos eliminados\n";
        }
    } else {
        echo "✓ La columna 'code' ya existe\n";
    }
}

// 3. Mostrar estructura final de la tabla
echo "\n=== ESTRUCTURA ACTUAL DE LA TABLA ===\n";
$result = $mysqli->query("DESCRIBE password_resets");
while ($row = $result->fetch_assoc()) {
    printf("%-20s %-20s %s\n", $row['Field'], $row['Type'], $row['Null']);
}

echo "\n=== MIGRACIÓN COMPLETADA ===\n";
echo "</pre>";

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración de Base de Datos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 p-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-slate-800 rounded-lg p-6 border border-green-500/20">
            <h1 class="text-2xl font-bold text-green-400 mb-2">✓ Migración Completada</h1>
            <p class="text-slate-300">La tabla password_resets ha sido actualizada correctamente.</p>
            <p class="text-slate-400 text-sm mt-4">Ya puedes usar el sistema de restablecimiento de contraseña.</p>
            <div class="mt-6 flex gap-3">
                <a href="Loggin.php" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg transition">Ir al Login</a>
                <a href="Recuperacion.html" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg transition">Recuperar Contraseña</a>
            </div>
        </div>
    </div>
</body>
</html>
