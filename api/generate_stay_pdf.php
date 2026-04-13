<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Solo administradores pueden descargar PDFs
requireRole('admin');

$stayId = $_GET['stay_id'] ?? null;

if (!$stayId || !is_numeric($stayId)) {
    http_response_code(400);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Error</h1><p>ID de estancia inválido</p>';
    exit;
}

$stayId = (int)$stayId;

try {
    $config = require __DIR__ . '/config.php';
    $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    
    if ($mysqli->connect_errno) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<h1>Error</h1><p>Error de conexión a la base de datos</p>';
        exit;
    }
    
    $mysqli->set_charset($config['charset']);
    
    // Obtener datos de la estancia
    $query = "
        SELECT 
            s.id, s.employee_id, s.fecha_inicio, s.fecha_fin, s.group_id,
            e.nombre, e.apellidos, e.email, e.dni_pasaporte, 
            e.fecha_nacimiento, e.institucion, e.pais, e.horario, e.rol,
            g.name as grupo_nombre
        FROM stays s
        LEFT JOIN employees e ON s.employee_id = e.id
        LEFT JOIN groups g ON s.group_id = g.id
        WHERE s.id = ?
        LIMIT 1
    ";
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<h1>Error</h1><p>Error preparando consulta</p>';
        exit;
    }
    
    $stmt->bind_param('i', $stayId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stay = $result->fetch_assoc();
    $stmt->close();
    
    if (!$stay) {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<h1>Error</h1><p>Estancia no encontrada</p>';
        exit;
    }
    
    $filename = 'estancia_' . $stay['id'] . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $stay['apellidos'] ?? 'sin_data');
    
    // Descargar como HTML
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.html"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo generarHTML($stay);
    
    $mysqli->close();
    exit;
    
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}

function generarHTML($stay) {
    $fechaNacimiento = isset($stay['fecha_nacimiento']) ? date('d/m/Y', strtotime($stay['fecha_nacimiento'])) : '—';
    $fechaInicio = isset($stay['fecha_inicio']) ? date('d/m/Y', strtotime($stay['fecha_inicio'])) : '—';
    $fechaFin = isset($stay['fecha_fin']) ? date('d/m/Y', strtotime($stay['fecha_fin'])) : '—';
    $horarioLabel = (isset($stay['horario']) && $stay['horario'] == 0) ? 'Solo lectivo' : 'Completo';
    $rolLabel = ($stay['rol'] === 'empleado' ? 'Usuario' : ($stay['rol'] ?? '—'));
    
    $html = <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estancia Finalizada</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            background: #f9f9f9;
        }
        
        .document {
            max-width: 850px;
            margin: 0 auto;
            background: white;
            padding: 50px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #5c068c;
            padding-bottom: 20px;
        }
        
        h1 {
            color: #5c068c;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .subtitle {
            color: #666;
            font-size: 12px;
        }
        
        h2 {
            background-color: #5c068c;
            color: white;
            padding: 12px 15px;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .label {
            font-weight: bold;
            color: #5c068c;
            width: 150px;
            flex-shrink: 0;
        }
        
        .value {
            flex: 1;
            color: #333;
        }
        
        footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #999;
            font-size: 11px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .document {
                box-shadow: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="document">
        <header>
            <h1>Datos de Estancia Finalizada</h1>
            <p class="subtitle">GestIUBO - Gestión de Estancias Académicas</p>
        </header>
        
        <h2>Información Personal</h2>
        <div class="row">
            <div class="label">Nombre:</div>
            <div class="value">__NOMBRE__</div>
        </div>
        <div class="row">
            <div class="label">Apellidos:</div>
            <div class="value">__APELLIDOS__</div>
        </div>
        <div class="row">
            <div class="label">Email:</div>
            <div class="value">__EMAIL__</div>
        </div>
        <div class="row">
            <div class="label">DNI / Pasaporte:</div>
            <div class="value">__DNI__</div>
        </div>
        <div class="row">
            <div class="label">Fecha de Nacimiento:</div>
            <div class="value">__NACIMIENTO__</div>
        </div>
        
        <h2>Información Académica</h2>
        <div class="row">
            <div class="label">Institución:</div>
            <div class="value">__INSTITUCION__</div>
        </div>
        <div class="row">
            <div class="label">País:</div>
            <div class="value">__PAIS__</div>
        </div>
        <div class="row">
            <div class="label">Grupo:</div>
            <div class="value">__GRUPO__</div>
        </div>
        
        <h2>Detalles de la Estancia</h2>
        <div class="row">
            <div class="label">Rol:</div>
            <div class="value">__ROL__</div>
        </div>
        <div class="row">
            <div class="label">Horario:</div>
            <div class="value">__HORARIO__</div>
        </div>
        <div class="row">
            <div class="label">Fecha Inicio:</div>
            <div class="value">__INICIO__</div>
        </div>
        <div class="row">
            <div class="label">Fecha Fin:</div>
            <div class="value">__FIN__</div>
        </div>
        
        <footer>
            <p>Documento generado automáticamente el __FECHA__</p>
            <p>Para guardar como PDF: Usa Ctrl+P o el botón de Imprimir y selecciona "Guardar como PDF"</p>
        </footer>
    </div>
</body>
</html>
HTML;

    // Reemplazar placeholders
    $html = str_replace('__NOMBRE__', htmlspecialchars($stay['nombre'] ?? '—'), $html);
    $html = str_replace('__APELLIDOS__', htmlspecialchars($stay['apellidos'] ?? '—'), $html);
    $html = str_replace('__EMAIL__', htmlspecialchars($stay['email'] ?? '—'), $html);
    $html = str_replace('__DNI__', htmlspecialchars($stay['dni_pasaporte'] ?? '—'), $html);
    $html = str_replace('__NACIMIENTO__', htmlspecialchars($fechaNacimiento), $html);
    $html = str_replace('__INSTITUCION__', htmlspecialchars($stay['institucion'] ?? '—'), $html);
    $html = str_replace('__PAIS__', htmlspecialchars($stay['pais'] ?? '—'), $html);
    $html = str_replace('__GRUPO__', htmlspecialchars($stay['grupo_nombre'] ?? '—'), $html);
    $html = str_replace('__ROL__', htmlspecialchars($rolLabel), $html);
    $html = str_replace('__HORARIO__', htmlspecialchars($horarioLabel), $html);
    $html = str_replace('__INICIO__', htmlspecialchars($fechaInicio), $html);
    $html = str_replace('__FIN__', htmlspecialchars($fechaFin), $html);
    $html = str_replace('__FECHA__', date('d/m/Y H:i:s'), $html);
    
    return $html;
}





    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->AddPage();
    
    // Información Personal
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Información Personal', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(240, 240, 240);
    
    $data = [
        ['Nombre:', $stay['nombre'] ?? '—'],
        ['Apellidos:', $stay['apellidos'] ?? '—'],
        ['Email:', $stay['email'] ?? '—'],
        ['DNI / Pasaporte:', $stay['dni_pasaporte'] ?? '—'],
        ['Fecha de Nacimiento:', isset($stay['fecha_nacimiento']) ? date('d/m/Y', strtotime($stay['fecha_nacimiento'])) : '—'],
    ];
    
    foreach ($data as $row) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 7, $row[0], 0, 0, 'L', true);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, $row[1], 0, 1, 'L', true);
    }
    
    $pdf->Ln(3);
    
    // Información Académica
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Información Académica', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $data2 = [
        ['Institución:', $stay['institucion'] ?? '—'],
        ['País:', $stay['pais'] ?? '—'],
        ['Grupo:', $stay['grupo_nombre'] ?? '—'],
    ];
    
    foreach ($data2 as $row) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 7, $row[0], 0, 0, 'L', true);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, $row[1], 0, 1, 'L', true);
    }
    
    $pdf->Ln(3);
    
    // Información de Estancia
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Detalles de la Estancia', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $horarioLabel = (isset($stay['horario']) && $stay['horario'] == 0) ? 'Solo lectivo' : 'Completo';
    
    $data3 = [
        ['Rol:', ($stay['rol'] === 'empleado' ? 'Usuario' : ($stay['rol'] ?? '—'))],
        ['Horario:', $horarioLabel],
        ['Fecha Inicio:', isset($stay['fecha_inicio']) ? date('d/m/Y', strtotime($stay['fecha_inicio'])) : '—'],
        ['Fecha Fin:', isset($stay['fecha_fin']) ? date('d/m/Y', strtotime($stay['fecha_fin'])) : '—'],
    ];
    
    foreach ($data3 as $row) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 7, $row[0], 0, 0, 'L', true);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, $row[1], 0, 1, 'L', true);
    }
    
    $pdf->Ln(5);
    
    // Footer informativo
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 10, 'Este documento fue generado automáticamente el ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    
    // Descargar PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="estancia_' . $stay['id'] . '_' . str_replace(' ', '_', $stay['apellidos'] ?? 'sin_datos') . '.pdf"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    $pdf->Output('', 'D');
    $mysqli->close();
    
} catch (Throwable $e) {
    http_response_code(500);
    die('Error generando PDF: ' . $e->getMessage());
}
