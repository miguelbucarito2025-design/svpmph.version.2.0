<?php

namespace App\Helpers;

/**
 * Clase LogAnalyzerService
 * 
 * Monitorea logs de seguridad, base de datos y aplicación de forma inteligente.
 * Implementa control de estados (notificado: true/false) para evitar spam 
 * de notificaciones y optimiza el consumo de tokens consultando a la IA 
 * únicamente ante eventos nuevos.
 * 
 * @package App\Helper
 */
class LogAnalyzer
{

    private string $logDir;
    private string $storageDir;
    private string $aiToken;
    private string $aiEndpoint;

    public function __construct($logDir = null, $storageDir = null, $aiToken = null, $aiEndpoint = null)
    {
        $rootPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;

        $this->logDir     = rtrim($logDir ?? getenv('LOG_DIR') ?: ($rootPath . 'logs'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->storageDir = rtrim($storageDir ?? getenv('STORAGE_DIR') ?: ($rootPath . 'storage'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->aiToken    = $aiToken ?? getenv('CLOUDFLARE_AI_TOKEN') ?: '';

        $accountId       = getenv('CLOUDFLARE_ACCOUNT_ID') ?: '';
        // Usando el modelo de Google Gemma 4 optimizado que validamos
        $defaultEndpoint = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/ai/run/@cf/google/gemma-4-26b-a4b-it";

        $this->aiEndpoint = $aiEndpoint ?? getenv('CLOUDFLARE_AI_ENDPOINT') ?: $defaultEndpoint;
    }

    /**
     * Ejecuta el análisis completo y estructurado para el panel y el worker.
     */
    public function ejecutarAnalisisCompleto()
    {
        $resultadoSeguridad = $this->procesarSeguridadYMemoria();
        $resultadoDb = $this->procesarBaseDatosInteligente();
        $resultadoApp = $this->procesarAplicacion();

        return [
            'status' => 'success',
            'timestamp' => date('Y-m-d H:i:s'),
            'seguridad' => $resultadoSeguridad,
            'base_datos' => $resultadoDb,
            'aplicacion' => $resultadoApp
        ];
    }

    /**
     * Procesa la seguridad y gestiona la bandera 'notificado' para el worker.
     */
    private function procesarSeguridadYMemoria()
    {
        $archivoLog = $this->logDir . 'seguridad.log';
        $archivoMemoria = $this->storageDir . 'ip_memory.json';
        $archivoReporteIA = $this->storageDir . 'resumen_ia_seguridad.json';

        if (!file_exists($archivoLog)) {
            return ['mensaje' => 'No hay archivo de seguridad registrado.'];
        }

        $memoriaIps = [];
        if (file_exists($archivoMemoria)) {
            $memoriaIps = json_decode(file_get_contents($archivoMemoria), true) ?: [];
        }

        $lineas = array_slice(file($archivoLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -50);
        $nuevasIpsDetectadas = [];

        foreach ($lineas as $linea) {
            if (preg_match('/IP:\s*([0-9\.]+)/', $linea, $match)) {
                $ip = $match[1];

                if (!isset($memoriaIps[$ip])) {
                    $memoriaIps[$ip] = [
                        'puntos' => 0,
                        'primera_vez' => date('Y-m-d H:i:s'),
                        'ultima_vez' => date('Y-m-d H:i:s'),
                        'analizado_por_ia' => false,
                        'notificado' => false // Bandera clave para tu script worker
                    ];
                }

                $memoriaIps[$ip]['puntos']++;
                $memoriaIps[$ip]['ultima_vez'] = date('Y-m-d H:i:s');
                $nuevasIpsDetectadas[$ip] = $memoriaIps[$ip]['puntos'];
            }
        }

        file_put_contents($archivoMemoria, json_encode($memoriaIps, JSON_PRETTY_PRINT));

        // Filtrar solo las que superan umbral y NO han sido analizadas por la IA
        $sospechososParaIa = [];
        foreach ($memoriaIps as $ip => $datos) {
            if ($datos['puntos'] > 15 && empty($datos['analizado_por_ia'])) {
                $sospechososParaIa[$ip] = $datos;
            }
        }

        $diagnosticoIa = null;

        if (!empty($sospechososParaIa)) {
            $payloadResumen = "Analiza este reporte de IPs sospechosas acumuladas en el servidor web:\n";
            foreach ($sospechososParaIa as $ip => $datos) {
                $payloadResumen .= "- IP: {$ip} con {$datos['puntos']} intentos acumulados.\n";
            }
            $payloadResumen .= "Explica brevemente el riesgo técnico y si se recomienda bloquearla.";

            $diagnosticoIa = $this->consultarCloudflareAI($payloadResumen);

            foreach ($sospechososParaIa as $ip => $datos) {
                $memoriaIps[$ip]['analizado_por_ia'] = true;
                $memoriaIps[$ip]['notificado'] = false; // Listo para que el worker lo detecte y notifique
            }
            file_put_contents($archivoMemoria, json_encode($memoriaIps, JSON_PRETTY_PRINT));

            // Guardar en historial de reportes
            $reportesAnteriores = file_exists($archivoReporteIA) ? json_decode(file_get_contents($archivoReporteIA), true) : [];
            array_unshift($reportesAnteriores, [
                'fecha' => date('Y-m-d H:i:s'),
                'tipo' => 'seguridad',
                'analisis' => $diagnosticoIa,
                'notificado' => false // Bandera para el worker de notificaciones
            ]);
            file_put_contents($archivoReporteIA, json_encode(array_slice($reportesAnteriores, 0, 10), JSON_PRETTY_PRINT));
        }

        return [
            'ips_en_memoria' => count($memoriaIps),
            'alertas_nuevas' => $nuevasIpsDetectadas,
            'ultimo_reporte_ia' => $diagnosticoIa ?? 'Sin nuevas amenazas críticas que requieran consulta a IA.'
        ];
    }

    /**
     * Procesa la Base de Datos y analiza errores críticos nuevos con IA si es necesario.
     */
    private function procesarBaseDatosInteligente()
    {
        $archivo = $this->logDir . 'log_db.log';
        $archivoReporteDbIA = $this->storageDir . 'resumen_ia_db.json';

        if (!file_exists($archivo)) {
            return ['estado' => 'Base de datos estable.'];
        }

        $lineas = array_slice(file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -5);
        $ultimoError = end($lineas);

        if (!$ultimoError) {
            return ['estado' => 'Base de datos estable.'];
        }

        // Revisar si este último error ya fue procesado antes para no gastar tokens
        $historialDb = file_exists($archivoReporteDbIA) ? json_decode(file_get_contents($archivoReporteDbIA), true) : [];
        $ultimoAnalizado = $historialDb[0]['error_original'] ?? '';

        $analisisDb = $historialDb[0]['analisis'] ?? null;

        // Si el error cambió (es un error nuevo), consultamos a la IA
        if ($ultimoError !== $ultimoAnalizado) {
            $prompt = "Analiza este error crítico de Base de Datos ocurrido en un sistema PHP MVC e indica la causa y solución técnica exacta de forma concisa en español:\n{$ultimoError}";

            $analisisDb = $this->consultarCloudflareAI($prompt);

            array_unshift($historialDb, [
                'fecha' => date('Y-m-d H:i:s'),
                'error_original' => $ultimoError,
                'analisis' => $analisisDb,
                'notificado' => false // Bandera para el worker
            ]);

            file_put_contents($archivoReporteDbIA, json_encode(array_slice($historialDb, 0, 10), JSON_PRETTY_PRINT));
        }

        return [
            'ultimo_error' => $ultimoError,
            'analisis_experto' => $analisisDb
        ];
    }

    /**
     * Procesa los errores de aplicación filtrando usuarios de prueba.
     */
    private function procesarAplicacion()
    {
        $archivo = $this->logDir . 'log_app.log';
        if (!file_exists($archivo)) {
            return ['estado' => 'Sin errores de aplicación recientes.'];
        }

        $lineas = array_slice(file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -10);
        $filtradas = [];

        foreach ($lineas as $linea) {
            if (strpos($linea, 'miguel') !== false) {
                continue;
            }
            $filtradas[] = $linea;
        }

        return $filtradas;
    }

    /**
     * Consulta la API de Cloudflare adaptada para el modelo Gemma 4.
     */
    private function consultarCloudflareAI(string $promptTexto)
    {
        $ch = curl_init($this->aiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->aiToken,
            "Content-Type: application/json"
        ]);

        $body = json_encode([
            "messages" => [
                [
                    "role" => "system",
                    "content" => "Eres un auditor técnico de sistemas web. Responde ÚNICAMENTE en español, de forma directa, técnica y concisa."
                ],
                [
                    "role" => "user",
                    "content" => $promptTexto
                ]
            ]
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $response = curl_exec($ch);

        if (!$response) {
            return 'Error de conexión al consultar la API de Cloudflare.';
        }

        $data = json_decode($response, true);

        // Extracción limpia para la estructura de los modelos de Google en Cloudflare
        $textoIa = $data['result']['choices'][0]['message']['content'] ?? 'No se pudo obtener una respuesta válida de la IA.';

        return trim($textoIa);
    }
    /**
     * Devuelve únicamente los elementos pendientes por notificar para el script Worker.
     * Ideal para que el worker revise, envíe el correo y luego marque como notificado.
     */
    public function obtenerReportesPendientesDeNotificacion()
    {
        $archivoReporteSeguridad = $this->storageDir . 'resumen_ia_seguridad.json';
        $archivoReporteDb = $this->storageDir . 'resumen_ia_db.json';

        $pendientes = [
            'seguridad' => [],
            'base_datos' => []
        ];

        // Revisar seguridad pendientes
        if (file_exists($archivoReporteSeguridad)) {
            $reportesSeg = json_decode(file_get_contents($archivoReporteSeguridad), true) ?: [];
            foreach ($reportesSeg as $index => $rep) {
                if (isset($rep['notificado']) && $rep['notificado'] === false) {
                    $pendientes['seguridad'][] = [
                        'indice' => $index,
                        'fecha' => $rep['fecha'],
                        'analisis' => $rep['analisis']
                    ];
                }
            }
        }

        // Revisar base de datos pendientes
        if (file_exists($archivoReporteDb)) {
            $reportesDb = json_decode(file_get_contents($archivoReporteDb), true) ?: [];
            foreach ($reportesDb as $index => $rep) {
                if (isset($rep['notificado']) && $rep['notificado'] === false) {
                    $pendientes['base_datos'][] = [
                        'indice' => $index,
                        'fecha' => $rep['fecha'],
                        'error' => $rep['error_original'],
                        'analisis' => $rep['analisis']
                    ];
                }
            }
        }

        return $pendientes;
    }

    /**
     * Prepara un resumen estructurado y limpio para ser consumido directamente por una vista PHP.
     */
    public function obtenerResumenParaVista()
    {
        $archivoReporteSeguridad = $this->storageDir . 'resumen_ia_seguridad.json';
        $archivoReporteDb = $this->storageDir . 'resumen_ia_db.json';

        $ultimoSeguridad = null;
        if (file_exists($archivoReporteSeguridad)) {
            $regSeg = json_decode(file_get_contents($archivoReporteSeguridad), true) ?: [];
            $ultimoSeguridad = $regSeg[0] ?? null;
        }

        $ultimoDb = null;
        if (file_exists($archivoReporteDb)) {
            $regDb = json_decode(file_get_contents($archivoReporteDb), true) ?: [];
            $ultimoDb = $regDb[0] ?? null;
        }

        return [
            'ultima_alerta_seguridad' => $ultimoSeguridad,
            'ultimo_analisis_db' => $ultimoDb
        ];
    }


    /**
     * Marca un reporte como ya notificado para evitar que el worker lo repita.
     */
    public function marcarComoNotificado(string $tipo, int $indice): void
    {
        if ($tipo === 'seguridad') {
            $archivo = $this->storageDir . 'resumen_ia_seguridad.json';
        } elseif ($tipo === 'base_datos') {
            $archivo = $this->storageDir . 'resumen_ia_db.json';
        } else {
            return;
        }

        if (file_exists($archivo)) {
            $reportes = json_decode(file_get_contents($archivo), true) ?: [];
            if (isset($reportes[$indice])) {
                $reportes[$indice]['notificado'] = true;
                file_put_contents($archivo, json_encode($reportes, JSON_PRETTY_PRINT));
            }
        }
    }
}
