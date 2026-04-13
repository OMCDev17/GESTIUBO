<?php
// ============================================================================
// Email Templates and Functions - Plantillas y Funciones de Correo
// ============================================================================
// Funciones para enviar correos profesionales con diseño coherente

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envía un correo de restablecimiento de contraseña con código de 4 dígitos
 * 
 * @param string $email Email del usuario
 * @param string $userName Nombre del usuario
 * @param string $code Código de 4 dígitos
 * @param array $config Configuración SMTP
 * @return bool True si se envió exitosamente
 */
function sendPasswordResetEmail($email, $userName, $code, $config) {
    require_once __DIR__ . '/../vendor/autoload.php';

    $htmlBody = getPasswordResetTemplate($userName, $code);
    $textBody = "Restablecimiento de contrasena / Password reset\n\n"
        . "Hola {$userName} / Hello {$userName},\n\n"
        . "Tu codigo de verificacion / Your verification code: {$code}\n"
        . "Este codigo expira en 15 minutos / This code expires in 15 minutes.\n\n"
        . "Si no solicitaste este cambio, ignora este correo / If you did not request this, ignore this email.";

    try {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->CharSet = 'UTF-8';
        $mailer->Host = $config['smtp']['host'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $config['smtp']['username'];
        $mailer->Password = $config['smtp']['password'];
        $mailer->SMTPSecure = $config['smtp']['secure'];
        $mailer->Port = $config['smtp']['port'];
        $mailer->setFrom($config['smtp']['from_email'], 'Instituto de Bio-Orgánica Antonio González');
        $mailer->addAddress($email);
        $mailer->Subject = 'Restablecimiento de contrasena / Password reset - Instituto de Bio-Organica Antonio Gonzalez';
        $mailer->isHTML(true);
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody;
        $mailer->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando correo de restablecimiento: " . $e->getMessage());
        return false;
    }
}

/**
 * Envía un correo de bienvenida al nuevo usuario registrado
 * 
 * @param string $email Email del usuario
 * @param string $userName Nombre de usuario
 * @param string $firstName Nombre del usuario
 * @param string $loginUrl URL de la página de login
 * @param array $config Configuración SMTP
 * @return bool True si se envió exitosamente
 */
function sendWelcomeEmail($email, $userName, $firstName, $loginUrl, $config) {
    require_once __DIR__ . '/../vendor/autoload.php';

    $htmlBody = getWelcomeTemplate($firstName, $userName, $loginUrl);
    $textBody = "Bienvenido/a / Welcome\n\n"
        . "Hola {$firstName} / Hello {$firstName},\n\n"
        . "Tu cuenta ha sido creada correctamente / Your account has been created successfully.\n"
        . "Usuario / Username: {$userName}\n"
        . "Acceso / Login: {$loginUrl}";

    try {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->CharSet = 'UTF-8';
        $mailer->Host = $config['smtp']['host'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $config['smtp']['username'];
        $mailer->Password = $config['smtp']['password'];
        $mailer->SMTPSecure = $config['smtp']['secure'];
        $mailer->Port = $config['smtp']['port'];
        $mailer->setFrom($config['smtp']['from_email'], 'Instituto de Bio-Orgánica Antonio González');
        $mailer->addAddress($email);
        $mailer->Subject = 'Bienvenido/a / Welcome - Instituto de Bio-Organica Antonio Gonzalez';
        $mailer->isHTML(true);
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody;
        $mailer->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando correo de bienvenida: " . $e->getMessage());
        return false;
    }
}

function sendNewStayWelcomeEmail($email, $firstName, $stayData, $loginUrl, $config) {
    require_once __DIR__ . '/../vendor/autoload.php';

    $htmlBody = getNewStayWelcomeTemplate($firstName, $stayData, $loginUrl);
    $textBody = "Bienvenido/a de nuevo / Welcome back\n\n"
        . "Hola {$firstName} / Hello {$firstName},\n\n"
        . "Tu nueva estancia ha sido aprobada.\n"
        . "Your new stay has been approved.\n\n"
        . "Grupo / Group: " . ($stayData['group_name'] ?? '-') . "\n"
        . "Motivo / Purpose: " . ($stayData['motivo'] ?? '-') . "\n"
        . "Inicio / Start: " . ($stayData['fecha_inicio'] ?? '-') . "\n"
        . "Fin / End: " . ($stayData['fecha_fin'] ?? '-') . "\n"
        . "Institucion / Institution: " . ($stayData['institucion'] ?? '-') . "\n"
        . "Pais / Country: " . ($stayData['pais'] ?? '-') . "\n\n"
        . "Accede a tu cuenta / Access your account: {$loginUrl}";

    try {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->CharSet = 'UTF-8';
        $mailer->Host = $config['smtp']['host'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $config['smtp']['username'];
        $mailer->Password = $config['smtp']['password'];
        $mailer->SMTPSecure = $config['smtp']['secure'];
        $mailer->Port = $config['smtp']['port'];
        $mailer->setFrom($config['smtp']['from_email'], 'Instituto de Bio-Organica Antonio Gonzalez');
        $mailer->addAddress($email);
        $mailer->Subject = 'Bienvenido/a de nuevo / Welcome back - Nueva estancia aprobada';
        $mailer->isHTML(true);
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody;
        $mailer->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando correo de nueva estancia: " . $e->getMessage());
        return false;
    }
}

function sendGroupApprovalRequestEmail($email, $supervisorName, $requestData, $config) {
    require_once __DIR__ . '/../vendor/autoload.php';

    $htmlBody = getGroupApprovalRequestTemplate($supervisorName, $requestData);
    $textBody = "Solicitud de aprobacion de grupo / Group approval request\n\n"
        . "Empleado / Employee: " . ($requestData['employee_name'] ?? '') . "\n"
        . "Email: " . ($requestData['employee_email'] ?? '') . "\n"
        . "Grupo / Group: " . ($requestData['group_name'] ?? '') . "\n"
        . "Por favor accede a la aplicacion para aprobar o rechazar la solicitud.\n"
        . "Please access the application to approve or reject this request.";

    try {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->CharSet = 'UTF-8';
        $mailer->Host = $config['smtp']['host'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $config['smtp']['username'];
        $mailer->Password = $config['smtp']['password'];
        $mailer->SMTPSecure = $config['smtp']['secure'];
        $mailer->Port = $config['smtp']['port'];
        $mailer->setFrom($config['smtp']['from_email'], 'Instituto de Bio-Organica Antonio Gonzalez');
        $mailer->addAddress($email);
        $mailer->Subject = 'Nueva solicitud de miembro / New group member request';
        $mailer->isHTML(true);
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody;
        $mailer->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando correo de aprobacion de grupo: " . $e->getMessage());
        return false;
    }
}

function getGroupApprovalRequestTemplate($supervisorName, $requestData) {
    $year = date('Y');
    $employeeName = htmlspecialchars((string)($requestData['employee_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $employeeEmail = htmlspecialchars((string)($requestData['employee_email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $groupName = htmlspecialchars((string)($requestData['group_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $motivo = htmlspecialchars((string)($requestData['motivo'] ?? ''), ENT_QUOTES, 'UTF-8');
    $fechaInicio = htmlspecialchars((string)($requestData['fecha_inicio'] ?? ''), ENT_QUOTES, 'UTF-8');
    $fechaFin = htmlspecialchars((string)($requestData['fecha_fin'] ?? ''), ENT_QUOTES, 'UTF-8');
    $institucion = htmlspecialchars((string)($requestData['institucion'] ?? ''), ENT_QUOTES, 'UTF-8');
    $pais = htmlspecialchars((string)($requestData['pais'] ?? ''), ENT_QUOTES, 'UTF-8');
    $supervisorName = htmlspecialchars((string)$supervisorName, ENT_QUOTES, 'UTF-8');

    return <<<HTML
    <!DOCTYPE html>
    <html lang="es" style="margin:0;padding:0;">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Nueva solicitud de grupo</title>
        <style>
            body { margin:0; padding:0; font-family:Arial,sans-serif; background:#f8fafc; color:#0f172a; }
            .container { max-width:620px; margin:32px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(15,23,42,.08); }
            .header { background:linear-gradient(135deg,#5c068c 0%,#7c1fa8 100%); color:#fff; padding:32px 24px; }
            .content { padding:32px 24px; }
            .card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin:24px 0; }
            .label { font-size:12px; text-transform:uppercase; color:#64748b; margin-bottom:4px; }
            .value { font-size:15px; font-weight:600; color:#0f172a; margin-bottom:14px; }
            .note { margin-top:20px; font-size:13px; color:#475569; line-height:1.6; }
            .footer { padding:24px; background:#f8fafc; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1 style="margin:0;font-size:26px;">Nueva solicitud de miembro / New member request</h1>
                <p style="margin:10px 0 0 0;font-size:14px;color:#ede9fe;">Hay una nueva solicitud pendiente de aprobacion / A new request is pending approval</p>
            </div>
            <div class="content">
                <p>Hola {$supervisorName},<br>Hello {$supervisorName},</p>
                <p>Un nuevo usuario se ha registrado y solicita incorporarse al grupo <strong>{$groupName}</strong>.<br>A new user has registered and requested to join group <strong>{$groupName}</strong>.</p>
                <div class="card">
                    <div class="label">Empleado / Employee</div>
                    <div class="value">{$employeeName}</div>
                    <div class="label">Email</div>
                    <div class="value">{$employeeEmail}</div>
                    <div class="label">Motivo / Purpose</div>
                    <div class="value">{$motivo}</div>
                    <div class="label">Fechas / Dates</div>
                    <div class="value">{$fechaInicio} - {$fechaFin}</div>
                    <div class="label">Institucion / Institution</div>
                    <div class="value">{$institucion}</div>
                    <div class="label">Pais / Country</div>
                    <div class="value">{$pais}</div>
                </div>
                <p class="note"><strong>Accede a la aplicacion GestIUBO / Access GestIUBO</strong> para revisar y tomar una decision sobre esta solicitud desde el panel de coordinador. Podras aprobar o rechazarla directamente en el sistema.<br>Review this request in the coordinator panel and approve or reject it directly in the system.</p>
            </div>
            <div class="footer">
                <p style="margin:0;">Correo automatico de GestIUBO / Automated email from GestIUBO.</p>
                <p style="margin:8px 0 0 0;">&copy; {$year} Instituto de Bio-Organica Antonio Gonzalez</p>
            </div>
        </div>
    </body>
    </html>
    HTML;
}

/**
 * Obtiene la plantilla HTML para correo de restablecimiento de contraseña
 * 
 * @param string $userName Nombre del usuario
 * @param string $code Código de 4 dígitos
 * @return string HTML del correo
 */
function getPasswordResetTemplate($userName, $code) {
    $year = date('Y');
    return <<<HTML
    <!DOCTYPE html>
    <html lang="es" style="margin: 0; padding: 0;">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Restablecimiento de Contraseña</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
                background-color: #f8fafc;
                color: #0f172a;
            }
            .container {
                max-width: 600px;
                margin: 40px auto;
                background-color: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #5c068c 0%, #7c1fa8 100%);
                padding: 40px 20px;
                text-align: center;
                color: #ffffff;
            }
            .logo {
                height: 60px;
                margin-bottom: 20px;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 600;
                letter-spacing: -0.5px;
            }
            .content {
                padding: 40px;
            }
            .greeting {
                font-size: 16px;
                margin-bottom: 20px;
                color: #334155;
                line-height: 1.6;
            }
            .code-section {
                background-color: #f3e8ff;
                border: 2px solid #5c068c;
                border-radius: 8px;
                padding: 30px;
                text-align: center;
                margin: 30px 0;
            }
            .code-label {
                font-size: 12px;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 10px;
            }
            .code-display {
                font-size: 48px;
                font-weight: 700;
                color: #5c068c;
                letter-spacing: 8px;
                font-family: 'Courier New', monospace;
                margin: 0;
            }
            .code-info {
                font-size: 13px;
                color: #64748b;
                margin-top: 15px;
            }
            .info-box {
                background-color: #f1f5f9;
                border-left: 4px solid #5c068c;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
                font-size: 14px;
                color: #475569;
                line-height: 1.6;
            }
            .steps {
                margin: 30px 0;
                font-size: 14px;
                color: #334155;
                line-height: 1.8;
            }
            .steps ol {
                margin: 15px 0;
                padding-left: 25px;
            }
            .steps li {
                margin-bottom: 10px;
            }
            .footer {
                background-color: #f8fafc;
                border-top: 1px solid #e2e8f0;
                padding: 30px 40px;
                font-size: 12px;
                color: #64748b;
                text-align: center;
                line-height: 1.6;
            }
            .footer-links {
                margin-top: 15px;
            }
            .footer-links a {
                color: #5c068c;
                text-decoration: none;
                margin: 0 10px;
            }
            .footer-links a:hover {
                text-decoration: underline;
            }
            .warning {
                color: #dc2626;
                font-size: 13px;
                margin-top: 15px;
                padding: 10px;
                background-color: #fee2e2;
                border-radius: 4px;
                border-left: 3px solid #dc2626;
            }
            @media only screen and (max-width: 600px) {
                .container {
                    margin: 0;
                    border-radius: 0;
                }
                .header {
                    padding: 30px 15px;
                }
                .header h1 {
                    font-size: 22px;
                }
                .content {
                    padding: 25px;
                }
                .code-display {
                    font-size: 36px;
                    letter-spacing: 4px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1>Restablecimiento de contraseña / Password reset</h1>
                <p style="font-size:14px; margin-top: 8px; color: #e5e7eb;">Instituto de Bio-Orgánica Antonio González - GestIUBO</p>
            </div>
            
            <!-- Main Content -->
            <div class="content">
                <p class="greeting">Hola <strong>{$userName}</strong> / Hello <strong>{$userName}</strong>,</p>
                
                <p class="greeting">
                    Recibimos una solicitud para restablecer la contraseña de tu cuenta en Instituto de Bio-Orgánica Antonio González.
                    <br>
                    We received a request to reset your password for Instituto de Bio-Orgánica Antonio González.
                </p>
                
                <!-- Code Box -->
                <div class="code-section">
                    <div class="code-label">Tu Código de Verificación / Your verification code</div>
                    <div class="code-display">{$code}</div>
                    <div class="code-info">Este código expira en 15 minutos / This code expires in 15 minutes</div>
                </div>
                
                <!-- Instructions -->
                <div class="steps">
                    <strong>Pasos para restablecer tu contraseña / Steps to reset your password:</strong>
                    <ol>
                        <li>Accede a la página de restablecimiento de contraseña / Go to the password reset page</li>
                        <li>Ingresa el código de 4 dígitos mostrado arriba / Enter the 4-digit code above</li>
                        <li>Ingresa tu nueva contraseña (mínimo 6 caracteres) / Enter your new password (min 6 characters)</li>
                        <li>Confirma tu nueva contraseña / Confirm your new password</li>
                        <li>Listo - contraseña actualizada / Done - password updated</li>
                    </ol>
                </div>
                
                <!-- Info Box -->
                <div class="info-box">
                    <strong>Nota importante / Important note:</strong> Este código es válido únicamente por 15 minutos. Si no lo utilizas en este tiempo, deberás solicitar un nuevo código.
                    <br>
                    This code is valid for only 15 minutes. If you do not use it in time, you will need to request a new code.
                </div>
                
                <!-- Security Warning -->
                <div class="warning">
                    <strong>Seguridad / Security:</strong> Si no solicitaste este cambio de contraseña, puedes ignorar este correo. Tu cuenta permanece segura.
                    <br>
                    If you did not request this password reset, ignore this email. Your account remains secure.
                </div>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>Este es un correo automático. Por favor, no responda a este mensaje. / This is an automated email. Please do not reply.</p>
                <p>© {$year} Instituto de Bio-Orgánica Antonio González - Todos los derechos reservados / All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Centro de Ayuda / Help Center</a> | 
                    <a href="#">Política de Privacidad / Privacy Policy</a> | 
                    <a href="#">Términos de Servicio / Terms of Service</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    HTML;
}

/**
 * Obtiene la plantilla HTML para correo de bienvenida de nuevo usuario
 * 
 * @param string $firstName Nombre del usuario
 * @param string $userName Nombre de usuario
 * @param string $loginUrl URL de la página de login
 * @return string HTML del correo
 */
function getWelcomeTemplate($firstName, $userName, $loginUrl) {
    $year = date('Y');
    return <<<HTML
    <!DOCTYPE html>
    <html lang="es" style="margin: 0; padding: 0;">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bienvenido al Instituto de Bio-Orgánica Antonio González</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
                background-color: #f8fafc;
                color: #0f172a;
            }
            .container {
                max-width: 600px;
                margin: 40px auto;
                background-color: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #5c068c 0%, #7c1fa8 100%);
                padding: 40px 20px;
                text-align: center;
                color: #ffffff;
            }
            .logo {
                height: 60px;
                margin-bottom: 20px;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 600;
                letter-spacing: -0.5px;
            }
            .header p {
                margin: 10px 0 0 0;
                font-size: 14px;
                opacity: 0.95;
            }
            .content {
                padding: 40px;
            }
            .greeting {
                font-size: 16px;
                margin-bottom: 20px;
                color: #334155;
                line-height: 1.6;
            }
            .welcome-box {
                background-color: #f3e8ff;
                border: 2px solid #5c068c;
                border-radius: 8px;
                padding: 30px;
                text-align: center;
                margin: 30px 0;
            }
            .welcome-icon {
                font-size: 48px;
                margin-bottom: 15px;
            }
            .welcome-text {
                font-size: 22px;
                font-weight: 600;
                color: #5c068c;
                margin-bottom: 10px;
            }
            .welcome-subtext {
                font-size: 14px;
                color: #64748b;
            }
            .credentials-box {
                background-color: #f1f5f9;
                border-left: 4px solid #5c068c;
                padding: 20px;
                margin: 25px 0;
                border-radius: 4px;
                font-size: 14px;
            }
            .credentials-box strong {
                display: block;
                color: #0f172a;
                margin-bottom: 12px;
            }
            .credential-item {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px solid #e2e8f0;
                font-size: 13px;
                color: #475569;
            }
            .credential-item:last-child {
                border-bottom: none;
            }
            .label {
                font-weight: 600;
                color: #334155;
                min-width: 100px;
            }
            .value {
                font-family: 'Courier New', monospace;
                color: #5c068c;
                font-weight: 500;
            }
            .login-button {
                display: inline-block;
                background: linear-gradient(135deg, #5c068c 0%, #7c1fa8 100%);
                color: #ffffff;
                padding: 14px 40px;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 600;
                font-size: 14px;
                margin: 25px 0;
                border: none;
                cursor: pointer;
                transition: transform 0.2s;
            }
            .login-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(92, 6, 140, 0.3);
            }
            .features {
                margin: 25px 0;
                font-size: 14px;
                color: #334155;
                line-height: 1.8;
            }
            .features strong {
                display: block;
                margin-bottom: 12px;
                color: #0f172a;
            }
            .feature-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .feature-list li {
                padding: 8px 0 8px 25px;
                position: relative;
            }
            .feature-list li:before {
                content: "✓";
                position: absolute;
                left: 0;
                color: #5c068c;
                font-weight: 700;
            }
            .info-box {
                background-color: #f3e8ff;
                border-left: 4px solid #5c068c;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
                font-size: 13px;
                color: #334155;
                line-height: 1.6;
            }
            .footer {
                background-color: #f8fafc;
                border-top: 1px solid #e2e8f0;
                padding: 30px 40px;
                font-size: 12px;
                color: #64748b;
                text-align: center;
                line-height: 1.6;
            }
            .footer-links {
                margin-top: 15px;
            }
            .footer-links a {
                color: #5c068c;
                text-decoration: none;
                margin: 0 10px;
            }
            .footer-links a:hover {
                text-decoration: underline;
            }
            @media only screen and (max-width: 600px) {
                .container {
                    margin: 0;
                    border-radius: 0;
                }
                .header {
                    padding: 30px 15px;
                }
                .header h1 {
                    font-size: 22px;
                }
                .content {
                    padding: 25px;
                }
                .credential-item {
                    flex-direction: column;
                }
                .value {
                    margin-top: 4px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Header -->
            <div class="header">
                <img alt="Logo Instituto de Bio-Orgánica Antonio González" class="logo" src="https://localhost/GESTIUBO/imagenes/instituto-biorganica-agonzalez-original.png" />
                <h1>Bienvenido/a al Instituto de Bio-Orgánica Antonio González / Welcome to Instituto de Bio-Orgánica Antonio González</h1>
                <p>Tu cuenta ha sido creada exitosamente / Your account has been created successfully</p>
            </div>
            
            <!-- Main Content -->
            <div class="content">
                <p class="greeting">Hola <strong>{$firstName}</strong> / Hello <strong>{$firstName}</strong>,</p>
                
                <div class="welcome-box">
                    <div class="welcome-text">Tu cuenta está lista para usar / Your account is ready</div>
                    <div class="welcome-subtext">Accede con tus credenciales para comenzar / Log in with your credentials to begin</div>
                </div>
                
                <p class="greeting">
                    Nos complace recibirte en nuestra comunidad. Tu registro ha sido completado exitosamente 
                    y ya puedes acceder a todas las funcionalidades de GestIUBO.
                    <br>
                    We are pleased to welcome you. Your registration is complete and you can now access all GestIUBO features.
                </p>
                
                <!-- Credentials -->
                <div class="credentials-box">
                    <strong>Tus credenciales de acceso / Your access credentials:</strong>
                    <div class="credential-item">
                        <span class="label">Usuario / Username:</span>
                        <span class="value">{$userName}</span>
                    </div>
                    <div class="credential-item">
                        <span class="label">Contraseña / Password:</span>
                        <span class="value">La que estableciste en el registro / The one you set during registration</span>
                    </div>
                </div>
                
                <!-- Login Button -->
                <div style="text-align: center;">
                    <a href="{$loginUrl}" class="login-button">Iniciar Sesión en GestIUBO / Sign in to GestIUBO</a>
                </div>
                
                <!-- Features -->
                <div class="features">
                    <strong>Lo que puedes hacer ahora / What you can do now:</strong>
                    <ul class="feature-list">
                        <li>Acceso a tu perfil personal y datos académicos / Access your personal profile and academic data</li>
                        <li>Gestionar tu información de incorporación / Manage your onboarding information</li>
                        <li>Actualizar tu contraseña en cualquier momento / Update your password at any time</li>
                        <li>Acceder a documentación y recursos del laboratorio / Access lab documentation and resources</li>
                        <li>Colaborar con otros miembros del equipo / Collaborate with other team members</li>
                    </ul>
                </div>
                
                <!-- Info Box -->
                <div class="info-box">
                    <strong>Consejo / Tip:</strong> Guarda este correo en un lugar seguro. Contiene tu nombre de usuario que necesitarás para iniciar sesión.
                    <br>
                    Save this email in a safe place. It contains your username and useful account details. Never share your password with anyone.
                </div>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>Este es un correo automático. Por favor, no responda a este mensaje. / This is an automated email. Please do not reply.</p>
                <p>© {$year} Instituto de Bio-Orgánica Antonio González - Todos los derechos reservados / All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Centro de Ayuda / Help Center</a> | 
                    <a href="#">Política de Privacidad / Privacy Policy</a> | 
                    <a href="#">Términos de Servicio / Terms of Service</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    HTML;
}

function getNewStayWelcomeTemplate($firstName, $stayData, $loginUrl) {
    $year = date('Y');
    $firstName = htmlspecialchars((string)$firstName, ENT_QUOTES, 'UTF-8');
    $groupName = htmlspecialchars((string)($stayData['group_name'] ?? '-'), ENT_QUOTES, 'UTF-8');
    $motivo = htmlspecialchars((string)($stayData['motivo'] ?? '-'), ENT_QUOTES, 'UTF-8');
    $fechaInicio = htmlspecialchars((string)($stayData['fecha_inicio'] ?? '-'), ENT_QUOTES, 'UTF-8');
    $fechaFin = htmlspecialchars((string)($stayData['fecha_fin'] ?? '-'), ENT_QUOTES, 'UTF-8');
    $institucion = htmlspecialchars((string)($stayData['institucion'] ?? '-'), ENT_QUOTES, 'UTF-8');
    $pais = htmlspecialchars((string)($stayData['pais'] ?? '-'), ENT_QUOTES, 'UTF-8');
    $loginUrl = htmlspecialchars((string)$loginUrl, ENT_QUOTES, 'UTF-8');

    return <<<HTML
    <!DOCTYPE html>
    <html lang="es" style="margin:0;padding:0;">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bienvenido/a de nuevo / Welcome back</title>
        <style>
            body { margin:0; padding:0; font-family:Arial,sans-serif; background:#f8fafc; color:#0f172a; }
            .container { max-width:620px; margin:32px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(15,23,42,.08); }
            .header { background:linear-gradient(135deg,#5c068c 0%,#7c1fa8 100%); color:#fff; padding:32px 24px; }
            .content { padding:32px 24px; }
            .card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin:24px 0; }
            .label { font-size:12px; text-transform:uppercase; color:#64748b; margin-bottom:4px; }
            .value { font-size:15px; font-weight:600; color:#0f172a; margin-bottom:14px; }
            .button { display:inline-block; background:#5c068c; color:#fff !important; text-decoration:none; padding:12px 24px; border-radius:8px; font-weight:700; }
            .footer { padding:24px; background:#f8fafc; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1 style="margin:0;font-size:26px;">Bienvenido/a de nuevo / Welcome back</h1>
                <p style="margin:10px 0 0 0;font-size:14px;color:#ede9fe;">Tu nueva estancia fue aprobada / Your new stay has been approved</p>
            </div>
            <div class="content">
                <p>Hola <strong>{$firstName}</strong>,<br>Hello <strong>{$firstName}</strong>,</p>
                <p>Tu solicitud de nueva estancia ha sido aprobada. Aquí tienes el detalle:<br>Your new stay request has been approved. Here are the details:</p>
                <div class="card">
                    <div class="label">Grupo / Group</div>
                    <div class="value">{$groupName}</div>
                    <div class="label">Motivo / Purpose</div>
                    <div class="value">{$motivo}</div>
                    <div class="label">Inicio / Start</div>
                    <div class="value">{$fechaInicio}</div>
                    <div class="label">Fin / End</div>
                    <div class="value">{$fechaFin}</div>
                    <div class="label">Institucion / Institution</div>
                    <div class="value">{$institucion}</div>
                    <div class="label">Pais / Country</div>
                    <div class="value">{$pais}</div>
                </div>
                <p>
                    <a class="button" href="{$loginUrl}">Entrar en GestIUBO / Sign in to GestIUBO</a>
                </p>
            </div>
            <div class="footer">
                <p style="margin:0;">Este es un correo automatico / This is an automated email.</p>
                <p style="margin:8px 0 0 0;">&copy; {$year} Instituto de Bio-Organica Antonio Gonzalez</p>
            </div>
        </div>
    </body>
    </html>
    HTML;
}



