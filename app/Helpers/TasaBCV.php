<?php

namespace App\Helpers;

use App\Helpers\Validar;
use App\Models\TasaCambioModel;

/**
 * Servicio: TasaBcvService
 * Descripción: Gestión automatizada de la tasa oficial del BCV.
 *              Optimizado para consultar la API solo 1 vez al día, respaldar en BD
 *              y manejar contingencias de fines de semana/fallas de red.
 * Autor: Gremio Dev
 * Fecha: 2026-09-28
 */
class TasaBCV
{
    private const API_URL = "https://ve.dolarapi.com/v1/dolares/oficial";

    private TasaCambioModel $model;
    private float $tasaBs = 0.00;
    private string $fechaOficial = '';


    public function __construct()
    {
        $this->model = new TasaCambioModel;
        $this->sincronizarTasaDelDia();
    }

    /**
     * Ciclo de vida inteligente de la tasa:
     * 1. Busca en BD si ya existe la tasa de hoy.
     * 2. Si no existe, consulta la API y la guarda en BD.
     * 3. Si la API falla (o es fin de semana sin cambios), usa la última tasa en BD.
     */
    public function sincronizarTasaDelDia(): void
    {
        $fechaHoy = date('Y-m-d');

        // PASO 1: Intentar cargar la tasa guardada para el día de hoy desde la BD local
        if ($this->cargarDesdeBD($fechaHoy)) {
            return; // Ya la tenemos registrada hoy, no saturamos la API
        }

        // PASO 2: Si no existe en BD para hoy, la consultamos a la API pública
        if ($this->cargarTasaDesdeApi()) {
            // Guardamos en BD la tasa obtenida para no volver a llamar a la API hoy
            $this->registrarEnBD();
            return;
        }

        // PASO 3: Contingencia (Fin de semana, feriado o API fuera de línea)
        // Carga la última tasa histórica registrada en la BD
        $this->cargarUltimaTasaRegistradaBD();
    }

    /**
     * Petición HTTP vía cURL hacia la API externa.
     * 
     * @return bool
     */
    public function cargarTasaDesdeApi(): bool
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => self::API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'User-Agent: SistemaGremioPHP/1.0'
            ]
        ]);

        $respuesta  = curl_exec($ch);
        $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($codigoHttp === 200 && !empty($respuesta)) {
            $datos = json_decode($respuesta, true);

            if (isset($datos['promedio']) && is_numeric($datos['promedio'])) {
                $precioValidado = Validar::esDecimal($datos['promedio']);
                $this->tasaBs = round((float)$precioValidado, 4);
                $this->fechaOficial = date('Y-m-d');
                return true;
            }
        }

        return false;
    }

    // =========================================================================
    // CONSULTAS A BASE DE DATOS (PDO)
    // =========================================================================

    /**
     * Busca la tasa en la tabla `tasas_cambio` por una fecha específica.
     * 
     * @param string $fecha Formato YYYY-MM-DD
     * @return bool
     */
    public function cargarDesdeBD(string $fecha): bool
    {


        $model = new TasaCambioModel;
        $registro = $model->obtenerActual($fecha);

        if (!empty($registro)) {
            $this->tasaBs = (float)$registro['tasa_bs'];
            $this->fechaOficial = $registro['fecha_oficial'];
            return true;
        }

        return false;
    }

    /**
     * Inserta la tasa actual en la tabla `tasas_cambio`.
     * 
     * @return bool
     */
    public function registrarEnBD(): bool
    {
        if ($this->tasaBs <= 0.00) {
            return false;
        }


        return $this->model->insert($this->tasaBs, $this->fechaOficial);
    }

    /**
     * Obtiene el último registro histórico guardado en la BD (para fines de semana/fallas).
     * 
     * @return bool
     */
    public function cargarUltimaTasaRegistradaBD(): bool
    {


        $registro = $this->model->ultimaTasaRegistrada();

        if ($registro) {
            $this->tasaBs = (float)$registro['tasa_bs'];
            $this->fechaOficial = $registro['fecha_oficial'];
            return true;
        }

        return false;
    }

    // =========================================================================
    // GETTERS Y MÉTODOS DE CÁLCULO MONETARIO
    // =========================================================================

    public function getTasa(): float
    {
        return $this->tasaBs;
    }

    public function getFechaOficial(): string
    {
        return $this->fechaOficial;
    }

    public  function convertirUsdABs(float|string $montoUsd): float
    {
        return ($this->tasaBs > 0.00) ? round($montoUsd * $this->tasaBs, 2) : 0.00;
    }

    public function convertirBsAUsd(float $montoBs): float
    {
        return ($this->tasaBs > 0.00) ? round($montoBs / $this->tasaBs, 2) : 0.00;
    }
}
