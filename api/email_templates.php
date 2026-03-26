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
    $textBody = "Código de restablecimiento de contraseña: $code\n\nEste código expira en 15 minutos.";

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
        $mailer->Subject = 'Restablecimiento de contraseña - Instituto de Bio-Orgánica Antonio González';
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
    $textBody = "Bienvenido a Instituto de Bio-Orgánica Antonio González\n\nTu cuenta ha sido creada exitosamente.\nUsuario: $userName\n\nPuedes iniciar sesión en: $loginUrl";

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
        $mailer->Subject = 'Bienvenido/a al Instituto de Bio-Orgánica Antonio González';
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
                <p style="font-size:14px; margin-top: 8px; color: #e5e7eb;">Instituto de Bio-Orgánica Antonio González - Gestiubo</p>
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
                    <strong>⏱️ Nota importante:</strong> Este código es válido únicamente por 15 minutos. 
                    Si no lo utilizas en este tiempo, deberás solicitar un nuevo código de restablecimiento.
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
                <p>Este es un correo automático. Por favor, no responda a este mensaje.</p>
                <p>© {$year} Instituto de Bio-Orgánica Antonio González - Todos los derechos reservados</p>
                <div class="footer-links">
                    <a href="#">Centro de Ayuda</a> | 
                    <a href="#">Política de Privacidad</a> | 
                    <a href="#">Términos de Servicio</a>
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
                <h1>Bienvenido al Instituto de Bio-Orgánica Antonio González</h1>
                <p>Tu cuenta ha sido creada exitosamente</p>
            </div>
            
            <!-- Main Content -->
            <div class="content">
                <p class="greeting">Hola <strong>{$firstName}</strong>,</p>
                
                <div class="welcome-box">
                    <div class="welcome-text">Tu cuenta está lista para usar / Your account is ready</div>
                    <div class="welcome-subtext">Accede con tus credenciales para comenzar / Log in with your credentials to begin</div>
                </div>
                
                <p class="greeting">
                    Nos complace recibirte en nuestra comunidad. Tu registro ha sido completado exitosamente 
                    y ya puedes acceder a todas las funcionalidades de Gestiubo.
                    <br>
                    We are pleased to welcome you. Your registration is complete and you can now access all Gestiubo features.
                </p>
                
                <!-- Credentials -->
                <div class="credentials-box">
                    <strong>Tus credenciales de acceso:</strong>
                    <div class="credential-item">
                        <span class="label">Usuario:</span>
                        <span class="value">{$userName}</span>
                    </div>
                    <div class="credential-item">
                        <span class="label">Contraseña:</span>
                        <span class="value">La que estableciste en el registro</span>
                    </div>
                </div>
                
                <!-- Login Button -->
                <div style="text-align: center;">
                    <a href="{$loginUrl}" class="login-button">Iniciar Sesión en Gestiubo</a>
                </div>
                
                <!-- Features -->
                <div class="features">
                    <strong>Lo que puedes hacer ahora:</strong>
                    <ul class="feature-list">
                        <li>Acceso a tu perfil personal y datos académicos</li>
                        <li>Gestionar tu información de incorporación</li>
                        <li>Actualizar tu contraseña en cualquier momento</li>
                        <li>Acceder a documentación y recursos del laboratorio</li>
                        <li>Colaborar con otros miembros del equipo</li>
                    </ul>
                </div>
                
                <!-- Info Box -->
                <div class="info-box">
                    <strong>Consejo:</strong> Guarda este correo en un lugar seguro. Contiene tu nombre de usuario 
                    que necesitarás para iniciar sesión. Nunca compartas tu contraseña con nadie.
                </div>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>Este es un correo automático. Por favor, no responda a este mensaje.</p>
                <p>© {$year} Instituto de Bio-Orgánica Antonio González - Todos los derechos reservados</p>
                <div class="footer-links">
                    <a href="#">Centro de Ayuda</a> | 
                    <a href="#">Política de Privacidad</a> | 
                    <a href="#">Términos de Servicio</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    HTML;
}
