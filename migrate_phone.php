<?php
// ============================================================================
// MIGRACIÓN: Agregar campos de teléfono (phone_prefix, phone_number)
// ============================================================================
// Ejecuta este archivo una sola vez para agregar los campos de teléfono

$config = require __DIR__ . '/api/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}
$mysqli->set_charset($config['charset']);

echo "<pre>";
echo "=== MIGRACIÓN: Agregando campos de teléfono ===\n\n";

// 1. Comprobar si la tabla employees existe
echo "Comprobando tabla 'employees'...\n";
$checkTable = $mysqli->query("SHOW TABLES LIKE 'employees'");
if ($checkTable->num_rows === 0) {
    die("✗ La tabla 'employees' no existe\n");
}
echo "✓ Tabla 'employees' encontrada\n\n";

// 2. Agregar columna phone_prefix si no existe
echo "Comprobando columna 'phone_prefix'...\n";
$checkPhonePrefix = $mysqli->query("SHOW COLUMNS FROM employees LIKE 'phone_prefix'");
if ($checkPhonePrefix->num_rows === 0) {
    echo "La columna 'phone_prefix' no existe. Agregándola...\n";
    $addPhonePrefix = "ALTER TABLE employees ADD COLUMN phone_prefix VARCHAR(10) NOT NULL DEFAULT '+34' AFTER email";
    if ($mysqli->query($addPhonePrefix)) {
        echo "✓ Columna 'phone_prefix' agregada correctamente\n";
    } else {
        echo "✗ Error al agregar 'phone_prefix': " . $mysqli->error . "\n";
        exit(1);
    }
} else {
    echo "✓ La columna 'phone_prefix' ya existe\n";
}

// 3. Agregar columna phone_number si no existe
echo "\nComprobando columna 'phone_number'...\n";
$checkPhoneNumber = $mysqli->query("SHOW COLUMNS FROM employees LIKE 'phone_number'");
if ($checkPhoneNumber->num_rows === 0) {
    echo "La columna 'phone_number' no existe. Agregándola...\n";
    $addPhoneNumber = "ALTER TABLE employees ADD COLUMN phone_number VARCHAR(20) NOT NULL DEFAULT '000000000' AFTER phone_prefix";
    if ($mysqli->query($addPhoneNumber)) {
        echo "✓ Columna 'phone_number' agregada correctamente\n";
    } else {
        echo "✗ Error al agregar 'phone_number': " . $mysqli->error . "\n";
        exit(1);
    }
} else {
    echo "✓ La columna 'phone_number' ya existe\n";
}

// 4. Generar números de teléfono inventados para usuarios sin número
echo "\nGenerando números de teléfono para usuarios sin número...\n";
$result = $mysqli->query("SELECT id, nombre, apellidos FROM employees WHERE phone_number = '000000000' OR phone_number = ''");
if ($result && $result->num_rows > 0) {
    $updated = 0;
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        // Generar número inventado basado en el ID
        $phone = sprintf('6%08d', $id * 1234567 % 100000000);
        $update = "UPDATE employees SET phone_number = '$phone' WHERE id = $id";
        if ($mysqli->query($update)) {
            $updated++;
            echo "  ✓ Usuario {$row['nombre']} {$row['apellidos']}: +34 $phone\n";
        } else {
            echo "  ✗ Error actualizando usuario $id: " . $mysqli->error . "\n";
        }
    }
    echo "\n✓ $updated usuarios actualizados con números de teléfono\n";
} else {
    echo "✓ Todos los usuarios ya tienen números de teléfono asignados\n";
}

echo "\n=== MIGRACIÓN COMPLETADA EXITOSAMENTE ===\n";
echo "Los campos de teléfono han sido agregados correctamente a la tabla employees.\n";
echo "</pre>";

$mysqli->close();
?>
