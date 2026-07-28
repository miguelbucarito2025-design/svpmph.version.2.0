<?php

declare(sytict_types=1);

namespace DevCore\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{

    public static function send(string $to, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP (puedes mover estas credenciales a tu .env)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // O el servidor SMTP que utilices
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tu_correo@gmail.com';
            $mail->Password   = 'tu_contraseña_de_aplicacion';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Destinatarios y Contenido
            $mail->setFrom('tu_correo@gmail.com', 'CodeLink / DevCore');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            // Puedes registrar el error en tu carpeta app/Logs
            error_log("Error al enviar correo: {$mail->ErrorInfo}");
            return false;
        }
    }
}
