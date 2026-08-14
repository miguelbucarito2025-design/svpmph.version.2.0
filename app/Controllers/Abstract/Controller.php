<?php

declare(strict_types=1);

namespace App\Controllers\Abstract;

use App\Helpers\Validar;
use App\Libs\Session;
use App\Libs\Response;
use App\Libs\Vista;
use App\Traits\TimestampsTrait;

/**
 * Clase Abstracta Controller
 *
 * Plantilla base para todos los controladores del sistema. Encapsula las dependencias
 * de sesión, respuesta e interfaz de vistas, e implementa controles de seguridad 
 * automatizados para filtrado de datos, autenticación y protección CSRF con origen.
 *
 * @package App\Controllers\Abstract
 */
abstract class Controller
{

    //iniciando el trait
    use TimestampsTrait;



    /**
     * Instancia manejadora de la sesión del usuario.
     *
     * @var Session
     */
    protected Session $session;

    /**
     * Instancia para la emisión de respuestas HTTP/JSON.
     *
     * @var Response
     */
    protected Response $respuesta;

    /**
     * Instancia para el renderizado de plantillas y vistas.
     *
     * @var Vista
     */
    protected Vista $vista;

    /**
     * Inicializa las dependencias base e inicia el contenedor de sesión.
     */
    public function __construct()
    {
        $this->session = new Session();
        $this->session->start();
        $this->respuesta = new Response();
        $this->vista = new Vista();
        $this->registrarTiempos();
    }

    /**
     * Obtiene y unifica los datos de entrada de la petición HTTP actual.
     *
     * Lee payloads codificados en formato JSON (Fetch/Ajax) o arreglos
     * de datos de formulario tradicional ($_POST).
     *
     * @return array<string, mixed> Arreglo asociativo de datos recibidos.
     */
    protected function getDatosEntrada(): array
    {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);

        if (!is_array($datos)) {
            $datos = $_POST;
        }

        return $datos;
    }

    /**
     * Filtra y sanea los datos de entrada según las reglas especificadas.
     *
     * Si la validación falla, interrumpe el flujo y emite una respuesta JSON 400.
     *
     * @param array<string, mixed> $reglas Reglas de validación para la clase Validar.
     * @return array<string, mixed> Arreglo con las variables filtradas.
     */
    protected function filtrarDatos(array $reglas): array
    {
        $entrada = $this->getDatosEntrada();
        $datos = Validar::validarFormulario($reglas, $entrada);

        if (!empty($datos['errores'])) {
            $this->respuesta->json(null, 400, 'Formato de datos inválido.', $datos['errores']);
            exit;
        }

        return $datos['datos'];
    }

    /**
     * Valida la coincidencia del token CSRF y la procedencia del dominio (Origen/Referer).
     *
     * Garantiza que la petición provenga del cliente autorizado y no de sitios de terceros.
     * En caso de discrepancia, interrumpe la ejecución con una respuesta HTTP 403.
     *
     * @return void
     */
    protected function verificarCSRF(): void
    {
        $this->validarOrigenDominio();

        $datos = $this->getDatosEntrada();
        $tokenEnviado = $datos['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$this->session->esTokenValido(is_string($tokenEnviado) ? $tokenEnviado : null)) {
            $this->respuesta->json(null, 403, 'Petición no autorizada: Token CSRF inválido o ausente.');
            exit;
        }
    }

    /**
     * Verifica que la petición HTTP provenga del mismo host del servidor.
     *
     * @return void
     */
    private function validarOrigenDominio(): void
    {
        $hostEsperado = $_SERVER['HTTP_HOST'] ?? '';
        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;

        if ($origin !== null) {
            $hostOrigen = parse_url($origin, PHP_URL_HOST);
            $puertoOrigen = parse_url($origin, PHP_URL_PORT);
            $origenCompleto = $hostOrigen . ($puertoOrigen ? ":{$puertoOrigen}" : '');

            if ($origenCompleto !== $hostEsperado) {
                $this->respuesta->json(null, 403, 'Petición rechazada: Origen no autorizado.');
                exit;
            }
            return;
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        if ($referer !== null) {
            $hostReferer = parse_url($referer, PHP_URL_HOST);
            $puertoReferer = parse_url($referer, PHP_URL_PORT);
            $refererCompleto = $hostReferer . ($puertoReferer ? ":{$puertoReferer}" : '');

            if ($refererCompleto !== $hostEsperado) {
                $this->respuesta->json(null, 403, 'Petición rechazada: Procedencia externa no autorizada.');
                exit;
            }
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->respuesta->json(null, 403, 'Petición rechazada: Ausencia de cabeceras de origen.');
            exit;
        }
    }

    /**
     * Verifica la existencia de una identidad autenticada en la sesión activa.
     *
     * En caso de ausencia de credenciales, destruye la sesión y emite un HTTP 401.
     *
     * @return void
     */
    protected function requerirAutenticacion(): void
    {
        if (!$this->session->has('usuario_id')) {
            $this->session->destroy();
            $this->respuesta->json(null, 401, 'Acceso denegado: Sesión no iniciada o expirada.');
            exit;
        }
    }
}
