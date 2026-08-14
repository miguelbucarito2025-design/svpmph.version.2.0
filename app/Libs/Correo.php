<?php

declare(strict_types=1);

namespace App\Libs;

use Exception;

/**
 * Clase Correo
 *
 * Encargada de gestionar el envío de correos electrónicos transaccionales
 * conectándose de forma nativa a la API de Resend mediante cURL.
 *
 * @package App\Libs
 */
class Correo
{
    /**
     * Envía un correo electrónico a través de Resend.
     *
     * @param string $destinatario Correo del receptor.
     * @param string $asunto Título del mensaje.
     * @param string $contenidoHtml Cuerpo del correo en formato HTML.
     * @return bool Retorna true si el envío fue exitoso.
     * @throws Exception Si falla la comunicación con la API.
     */
    public static function enviar(string $destinatario, string $asunto, string $contenidoHtml): bool
    {
        $apiKey = $_ENV['RESEND_API_KEY'] ?? '';
        $remitente = $_ENV['MAIL_FROM'] ?? '';

        if (empty($apiKey) || empty($remitente)) {
            throw new Exception("Error de configuración: Faltan credenciales de correo en el archivo .env", 500);
        }

        // Estructura de datos exigida por Resend
        $datosCarga = json_encode([
            'from'    => "Sistema SVPMPH <{$remitente}>",
            'to'      => [$destinatario],
            'subject' => $asunto,
            'html'    => $contenidoHtml
        ]);

        // Inicializamos cURL
        $curl = curl_init('https://api.resend.com/emails');

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $datosCarga,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
        ]);

        $respuesta = curl_exec($curl);
        $codigoHttp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $errorCurl = curl_error($curl);

        curl_close($curl);

        if ($errorCurl) {
            throw new Exception("Fallo de conexión al enviar el correo: {$errorCurl}", 500);
        }

        // Resend devuelve un código 200 (OK) al aceptar el correo
        if ($codigoHttp !== 200) {
            throw new Exception("La API de Resend rechazó la solicitud. Código HTTP: {$codigoHttp}. Detalles: {$respuesta}", 500);
        }

        return true;
    }
}
