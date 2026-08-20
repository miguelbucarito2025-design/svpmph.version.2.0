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
     * Obtiene el arreglo de metadatos de un archivo cargado en la petición HTTP ($_FILES).
     *
     * @param string $clave Nombre del campo de entrada tipo file (ej: 'foto_perfil').
     * @return array<string, mixed>|null Arreglo nativo de $_FILES o null si el campo no existe.
     */
    protected function getArchivoEntrada(string $clave): ?array
    {
        return $_FILES[$clave] ?? null;
    }

    /**
     * Filtra, inspecciona y valida la seguridad de un archivo recibido en la petición.
     *
     * Evalúa el archivo contra la clase Validar. Si la validación falla por tamaño,
     * extensión o incongruencia MIME, interrumpe el flujo emitiendo una respuesta JSON 400.
     *
     * @param string $clave Nombre del campo input en la petición.
     * @param array $extensionesPermitidas Lista de extensiones válidas en minúscula (ej: ['jpg', 'png']).
     * @param int $maxMegas Límite máximo de peso permitido en MB (Por defecto 2 MB).
     * @return array{mime: string, extension: string, tmp_name: string, name: string, size: int} Metadatos validados del archivo.
     */
    protected function filtrarArchivo(string $clave, array $extensionesPermitidas, int $maxMegas = 2): array
    {
        $archivo = $this->getArchivoEntrada($clave);

        if (!$archivo) {
            $this->respuesta->json(null, 400, "No se recibió ningún archivo en el campo '{$clave}'.");
            exit;
        }

        // Ejecutamos la validación profunda de bytes mágicos con el Helper Validar
        $validacion = Validar::esArchivoValido($archivo, $extensionesPermitidas, $maxMegas);

        if (!$validacion['valido']) {
            $this->respuesta->json(null, 400, $validacion['mensaje']);
            exit;
        }

        return [
            'valido'    => true,
            'mime'      => $validacion['mime'],
            'extension' => $validacion['extension'],
            'tmp_name'  => $archivo['tmp_name'],
            'name'      => $archivo['name'],
            'size'      => $archivo['size']
        ];
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
            $this->respuesta->json(null, 403, 'Acceso denegado: Sesión no iniciada o expirada.');
            exit;
        }
    }

    /**
     * verifica la existencia de datos en un array 
     *
     * @return bool false si estan bien y true si uno no existe
     */
    protected function faltanDatos(array $reglas, array|null $datos): bool
    {

        if (empty($datos)) {
            return false;
        }


        foreach ($reglas as $r) {
            if (empty($datos[$r])) {
                return true;
            }
        }

        return false;
    }
}
