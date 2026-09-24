<?php

declare(strict_types=1);

namespace App\Libs;

use Exception;
use App\Libs\Seguridad;
use App\Libs\Session;

/**
 * Clase Enrutador
 *
 * Administra el registro, análisis y despacho de las rutas HTTP de la aplicación.
 * Evalúa la petición entrante, gestiona el control de acceso y transfiere la 
 * ejecución al controlador correspondiente. Delega los fallos al Manejador de Excepciones.
 *
 * @package App\Libs
 */
class Enrutador
{
    /**
     * @var array<int, array{metodo: string, pattern: string, controlador: class-string, accion: string, roles: array<string>}>
     */
    private static array $rutas = [];

    public static function get(string $ruta, array $handler, array $rolesPermitidos = []): void
    {
        self::registrar('GET', $ruta, $handler, $rolesPermitidos);
    }

    public static function post(string $ruta, array $handler, array $rolesPermitidos = []): void
    {
        self::registrar('POST', $ruta, $handler, $rolesPermitidos);
    }

    private static function registrar(string $metodo, string $ruta, array $handler, array $roles): void
    {
        self::$rutas[] = [
            'metodo'      => strtoupper($metodo),
            'pattern'     => trim($ruta, '/'),
            'controlador' => $handler[0],
            'accion'      => $handler[1],
            'roles'       => $roles
        ];
    }

    /**
     * Evalúa la petición HTTP entrante y despacha al controlador correspondiente.
     * Si ocurre algún fallo de permisos, método o existencia, lanza una Excepción.
     *
     * @return void
     * @throws Exception
     */
    public static function despachar(): void
    {
        $session = new Session();
        $session->start();

        // Middleware de inspección y rate limiting
        \App\Middlewares\SeguridadMiddleware::inspeccionarPeticion();

        $metodoPeticion = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // 1. Limpieza de query strings y subcarpetas
        $rawUri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

        if ($scriptDir !== '/' && strpos($rawUri, $scriptDir) === 0) {
            $rawUri = substr($rawUri, strlen($scriptDir));
        }

        $uriPeticion = trim($rawUri, '/');
        $partesUri = array_values(array_filter(explode('/', $uriPeticion)));

        // Extracción dinámica: la base puede ser toda la URI o la URI menos el último segmento (token)
        $posibleToken = !empty($partesUri) ? end($partesUri) : '';
        $baseRutaSugerida = count($partesUri) > 1 ? implode('/', array_slice($partesUri, 0, -1)) : $uriPeticion;

        $rutaEncontrada = false;

        // 2. Búsqueda de coincidencia
        foreach (self::$rutas as $ruta) {
            $patternRegistrado = trim($ruta['pattern'], '/');

            // Evaluamos si coincide con la URI completa o con la ruta base omitiendo el parámetro final
            $esCoincidenciaExacta = ($patternRegistrado === $uriPeticion);
            $esCoincidenciaConToken = ($patternRegistrado === $baseRutaSugerida && !empty($posibleToken) && $baseRutaSugerida !== $uriPeticion);

            if ($esCoincidenciaExacta || $esCoincidenciaConToken) {
                $rutaEncontrada = true;

                // Si el método no coincide, continuamos buscando por si existe sobrecarga de ruta en otro verbo
                if ($ruta['metodo'] !== $metodoPeticion) {
                    continue;
                }

                // 3. Control de acceso por Rol
                if (!empty($ruta['roles']) && !self::validarAccesoRol($session, $ruta['roles'])) {
                    throw new Exception("Acceso denegado: No tienes permisos o tu sesión expiró.", 403);
                }

                // 4. Procesamiento de Parámetros Encriptados
                $parametros = [];
                if ($esCoincidenciaConToken) {
                    $resultadoToken = Seguridad::desencriptarParams($posibleToken);
                    if ($resultadoToken === null) {
                        throw new Exception("El token de la URL es inválido o ha expirado.", 403);
                    }
                    $parametros = $resultadoToken;
                }

                $claseControlador = $ruta['controlador'];
                $metodoControlador = $ruta['accion'];

                // 5. Instanciación dinámica
                if (!class_exists($claseControlador)) {
                    throw new Exception("El controlador {$claseControlador} no existe.", 500);
                }

                $instancia = new $claseControlador();

                if (!method_exists($instancia, $metodoControlador)) {
                    throw new Exception("El método {$metodoControlador} no existe en {$claseControlador}.", 500);
                }

                // Ejecutamos la acción en el controlador pasando los parámetros procesados
                call_user_func_array([$instancia, $metodoControlador], [$parametros]);
                return;
            }
        }

        // Si la ruta existía pero el verbo no coincidió
        if ($rutaEncontrada) {
            throw new Exception("Método {$metodoPeticion} no permitido para esta ruta.", 405);
        }

        // La ruta no existe en el sistema
        throw new Exception("Ruta no encontrada: {$uriPeticion}", 404);
    }

    /**
     * Valida la sesión activa y comprueba si el rol del usuario posee permisos sobre la ruta.
     * 
     * @param Session $session Instancia de la sesión activa.
     * @param array<string> $rolesPermitidos Lista de roles autorizados.
     * @return bool True si tiene acceso, false en caso contrario.
     */
    private static function validarAccesoRol(Session $session, array $rolesPermitidos): bool
    {
        if (!$session->comprobarInactividad()) {
            return false;
        }

        $rolUsuario = $session->get('usuario_rol');
        if (!$rolUsuario) {
            return false;
        }

        return in_array($rolUsuario, $rolesPermitidos, true);
    }
}
