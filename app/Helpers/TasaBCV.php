<?php

namespace App\Helpers;

use App\Helpers\Validar;
use App\Models\TasaCambioModel;
use App\Libs\Exceptions\AppException;

/**
 * Servicio: TasaBcvService
 * Descripción: Gestión automatizada de la tasa oficial del BCV y cálculos
 *              de conversión para divisas y efectivo.
 * Autor: Gremio Dev
 * Fecha: 2026-10-03
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



    public function getId()
    {
        $model = new TasaCambioModel;
        $registro = $model->ultimaTasaRegistrada();

        if (!empty($registro)) {
            return  $registro['id'];
        }

        throw new AppException('Id Invalido ' . date('Y-m-d H:i:s'), 400);
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

    /**
     * Convierte USD a Bolívares de forma exacta (2 decimales).
     */
    public function convertirUsdABs(float|string $montoUsd): float
    {
        $montoNum = (float)$montoUsd;
        return ($this->tasaBs > 0.00) ? round($montoNum * $this->tasaBs, 2) : 0.00;
    }

    /**
     * Convierte Bolívares a USD (2 decimales).
     */
    public function convertirBsAUsd(float $montoBs): float
    {
        return ($this->tasaBs > 0.00) ? round($montoBs / $this->tasaBs, 2) : 0.00;
    }

    /**
     * Calcula el monto mínimo exacto en bolívares para pagar en EFECTIVO.
     * Redondea hacia arriba al múltiplo del billete más cercano (ej. múltiplos de 5 Bs).
     * 
     * @param float|string $montoUsd Monto base en dólares
     * @param int $denominacionMinima Denominación del billete más pequeño disponible (ej: 1, 5, 10 Bs)
     * @return float Monto redondeado hacia arriba en Bolívares
     */
    public function calcularMontoMinimoEfectivoBs(float|string $montoUsd, int $denominacionMinima = 50): float
    {
        if ($this->tasaBs <= 0.00) return 0.00;

        $montoBsExacto = (float)$montoUsd * $this->tasaBs;

        if ($denominacionMinima <= 1) {
            return ceil($montoBsExacto);
        }

        return ceil($montoBsExacto / $denominacionMinima) * $denominacionMinima;
    }

    /**
     * Compara y valida si el dinero entregado en efectivo cubre el mínimo requerido.
     * 
     * @param float $montoRecibidoBs Monto reportado/entregado por el cliente
     * @param float|string $montoUsd Monto que debía pagar en dólares
     * @param int $denominacionMinima Múltiplo de redondeo de billetes
     * @return array Estructura detallada del resultado de la validación
     */
    public function validarPagoEfectivo(float $montoRecibidoBs, float|string $montoUsd, int $denominacionMinima = 50): array
    {
        $montoMinimoRequerido = $this->calcularMontoMinimoEfectivoBs($montoUsd, $denominacionMinima);
        $montoRecibidoBs = round($montoRecibidoBs, 2);

        if ($montoRecibidoBs >= $montoMinimoRequerido) {
            return [
                'valido'           => true,
                'mensaje'          => 'Pago en efectivo aceptado.',
                'monto_recibido'   => $montoRecibidoBs,
                'monto_requerido'  => $montoMinimoRequerido,
                'vuelto'           => round($montoRecibidoBs - $montoMinimoRequerido, 2)
            ];
        }

        return [
            'valido'          => false,
            'mensaje'         => 'El monto en efectivo es inferior al mínimo aceptable.',
            'monto_recibido'  => $montoRecibidoBs,
            'monto_requerido' => $montoMinimoRequerido,
            'faltante'        => round($montoMinimoRequerido - $montoRecibidoBs, 2)
        ];
    }

    /**
     * Calcula y valida el monto en efectivo a recibir partiendo EXCLUSIVAMENTE 
     * del monto registrado en divisas (USD).
     * 
     * Si el monto recibido en efectivo (ingresado por el cajero/usuario) no cubre 
     * el mínimo requerido ajustado a la denominación de billetes, lanza un AppException.
     * 
     * @param float|string $montoUsdBD Monto original en dólares traído desde la Base de Datos
     * @param float $montoEfectivoEntregado Monto real físico ingresado en caja/formulario
     * @param int $denominacionMinima Denominación del billete más pequeño (ej: 5 Bs)
     * @return array Detalle del pago procesado con su vuelto
     * 
     * @throws AppException Si la tasa no está disponible o el pago es insuficiente
     */
    public function procesarPagoEfectivoSeguro(
        float|string $montoUsdBD,
        float $montoEfectivoEntregado,
        int $denominacionMinima = 50
    ): array {
        // 1. Verificación de seguridad de la tasa
        if ($this->tasaBs <= 0.00) {
            throw new AppException('No se pudo determinar la tasa de cambio oficial del BCV', 500);
        }

        $montoUsd = (float)$montoUsdBD;

        if ($montoUsd <= 0.00) {
            throw new AppException('El monto en divisas registrado no es válido', 400); //[cite: 3]
        }

        // 2. El servidor calcula el monto mínimo exacto en Bolívares (redondeado hacia arriba)
        $montoMinimoRequeridoBs = $this->calcularMontoMinimoEfectivoBs($montoUsd, $denominacionMinima);
        $montoRecibidoBs = round($montoEfectivoEntregado, 2);

        // 3. Validar si lo entregado en efectivo cubre la cuota mínima calculada por el servidor
        if ($montoRecibidoBs < $montoMinimoRequeridoBs) {
            $faltante = round($montoMinimoRequeridoBs - $montoRecibidoBs, 2);

            // Lanzamos la excepción con la estructura que utiliza su sistema
            throw new AppException("El monto en efectivo es insuficiente. El mínimo requerido es de {$montoMinimoRequeridoBs} Bs (Faltan {$faltante} Bs)", 400); //[cite: 3]
        }

        // 4. Si todo está correcto, retorna la información calculada
        return [
            'monto_usd'         => $montoUsd,
            'tasa_bcv'          => $this->tasaBs,
            'monto_requerido_bs' => $montoMinimoRequeridoBs,
            'monto_recibido_bs'  => $montoRecibidoBs,
            'vuelto_bs'         => round($montoRecibidoBs - $montoMinimoRequeridoBs, 2)
        ];
    }
}
