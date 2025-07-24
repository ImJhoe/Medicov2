<?php
require_once 'includes/phpmailer/PHPMailer.php';
require_once 'includes/phpmailer/SMTP.php';
require_once 'includes/phpmailer/Exception.php';
require_once 'includes/email-config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function generarPasswordTemporal($longitud = 8) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    $password = '';
    
    for ($i = 0; $i < $longitud; $i++) {
        $password .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    
    return $password;
}

function enviarCredencialesPorEmail($email, $username, $passwordTemporal, $nombreCompleto) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Remitente y destinatario
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $nombreCompleto);
        $mail->addReplyTo(SUPPORT_EMAIL, 'Soporte Clínica ');
        
        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenido a Clínica  - Credenciales de Acceso';
        $mail->Body = generarPlantillaEmail($nombreCompleto, $username, $passwordTemporal);
        $mail->AltBody = generarTextoPlano($nombreCompleto, $username, $passwordTemporal);
        
        $mail->send();
        
        // Log exitoso
        error_log("Email enviado exitosamente a: {$email}");
        return true;
        
    } catch (Exception $e) {
        // Log del error
        error_log("Error enviando email a {$email}: {$mail->ErrorInfo}");
        return false;
    }
}

function generarPlantillaEmail($nombreCompleto, $username, $passwordTemporal) {
    // Para desarrollo, puedes incluir los estilos inline
    // Para producción, se recomienda usar un servidor de archivos estáticos
    $cssPath = 'styles/correo.css'; // Ruta a tu archivo CSS
    
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
        <title>Bienvenido Usuari@</title>
        
        <!-- Para mejor compatibilidad con clientes de email, incluir CSS inline -->
        <style>
    /* Reset y estilo base para evitar inconsistencias */
    body {
        margin: 0;
        padding: 0;
        background-color: #f4f4f4; /* Fondo claro para mejor contraste */
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        color: #333333;
        line-height: 1.6;
    }

    .email-container {
        max-width: 650px;
        margin: 0 auto;
        background-color: #ffffff; /* Fondo blanco para el contenido principal */
        border-radius: 8px; /* Bordes ligeramente redondeados */
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05); /* Sombra sutil */
        overflow: hidden;
    }

    .email-header,
    .email-footer {
        background-color: #007BFF; /* Color de acento (puede personalizarse) */
        color: #ffffff;
        padding: 20px;
        text-align: center;
    }

    .email-body {
        padding: 25px;
    }

    @media (max-width: 600px) {
        .email-container {
            width: 100% !important;
            border-radius: 0; /* Elimina bordes redondeados en móviles si se prefiere */
        }
    }
</style>
        
        <!-- Enlace a hoja de estilos externa (opcional) -->
        <link rel='stylesheet' href='{$cssPath}'>
    </head>
    <body>
        <div class='email-container'>
            <!-- Header Section -->
            <header class='email-header'>
                <div class='logo-section'>
                    <h1 class='clinic-name'></h1>
                    <p class='system-subtitle'> Citas </p>
                </div>
            </header>

            <!-- Main Content -->
            <main class='email-content'>
                <!-- Welcome Section -->
                <section class='welcome-section'>
                    <h2 class='welcome-title'>¡Bienvenido/a, {$nombreCompleto}!</h2>
                    <p class='welcome-text'>
                       Hemos creado su cuenta en nuestro Sistema de Citas Médicas.
                    Pronto recibirá un correo con las instrucciones detalladas para acceder a la plataforma. Si tiene alguna duda, no dude en contactarnos. 
                    </p>
                </section>

                <!-- Credentials Card -->
                <section class='credentials-card'>
                    <h3 class='credentials-title'>
                        Credenciales
                    </h3>
                    
                    <div class='credential-item'>
                        <div class='credential-label'>
                            usuario
                        </div>
                        <div class='username-display'>{$username}</div>
                    </div>
                    
                    <div class='credential-item'>
                        <div class='credential-label'>
                            temporal
                        </div>
                        <div class='password-display'>{$passwordTemporal}</div>
                    </div>
                    
                    <div class='credential-item'>
                        <div class='credential-label'>
                            Sistema
                        </div>
                        <a href='" . SYSTEM_URL . "' class='access-button'>
                        </a>
                    </div>
                </section>
                <!-- Warning Section -->
                <section class='warning-section'>
                    <h3 class='warning-title'>
                        Importante
                    </h3>
                    <ul class='warning-list'>
                        <li><strong>temporal:</strong> Cambio de contraseña obligatorio en el primer acceso</li>
                        <li><strong>Nueva clave:</strong> Mínimo 6 caracteres. Asegúrese de que sea segura.</li>
                    </ul>
                </section>

                <!-- Steps Section -->
                <section class='steps-section'>
                    <h3 class='steps-title'>
                        Pasos para Acceder
                    </h3>
                    <ol class='steps-list'>
                    </ol>
                </section>

                <!-- Support Text -->
                <p style='margin-top: 40px; text-align: center; color: #5a6c7d; font-size: 16px;'>
                </p>
            </main>

            <!-- Footer Section -->
            <footer class='email-footer'>
                <div class='footer-brand'>Sistema</div>
                <p class='footer-disclaimer'>
                    Genenerado por sistema
                </p>
                <p class='footer-support'>
                    consulte con nosotros:
                    <a href='mailto:" . SUPPORT_EMAIL . "'>" . SUPPORT_EMAIL . "</a>
                </p>
            </footer>
        </div>
    </body>
    </html>
    ";
}
function generarTextoPlano($nombreCompleto, $username, $passwordTemporal) {
    return "
Sistema

¡Bienvenido/a, {$nombreCompleto}!

Sus credenciales de acceso:
- Usuario: {$username}
-  temporal: {$passwordTemporal}
- URL: " . SYSTEM_URL . "

IMPORTANTE:
- Esta es una contraseña temporal
- Debe cambiarla en su primer acceso
- No comparta estas credenciales


Para soporte: " . SUPPORT_EMAIL . "

Sistema 
    ";
}
function enviarEmailPersonalizado($email, $nombreDestinatario, $asunto, $contenidoHtml) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Remitente y destinatario
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $nombreDestinatario);
        $mail->addReplyTo(SUPPORT_EMAIL, 'Soporte Clínica');
        
        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $contenidoHtml;
        
        // Versión texto plano (fallback)
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $contenidoHtml));
        
        $mail->send();
        
        // Log exitoso
        error_log("Email personalizado enviado a: {$email} - Asunto: {$asunto}");
        return true;
        
    } catch (Exception $e) {
        // Log del error
        error_log("Error enviando email personalizado a {$email}: {$mail->ErrorInfo}");
        return false;
    }
}

?>