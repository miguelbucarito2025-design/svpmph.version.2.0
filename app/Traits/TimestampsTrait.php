<?php


declare(strict_types=1);
/**
 * Archivo: TimestampsTrait.php
 * Descripción: Trait diseñado para inyectar y automatizar la gestión de fechas 
 * de creación y actualización en cualquier entidad o modelo del sistema.
 * 
 * @author Miguel Josue
 * @version 1.0.0
 */

namespace App\Traits;

use DateTime;
use DateTimeZone;

trait TimestampsTrait
{

    /**
     * @var string Almacena la fecha y hora exacta en que se creó el registro.
     */
    protected string $createdAt;

    /**
     * @var string Almacena la fecha y hora de la última modificación del registro.
     */
    protected string $updatedAt;

    /**
     * Establece o actualiza las marcas de tiempo utilizando la zona horaria local.
     * 
     * @return void
     */
    public function registrarTiempos(): void
    {
        // Definimos la zona horaria reglamentaria para mantener consistencia
        $zonaHoraria = new DateTimeZone('America/Caracas');
        $ahora = new DateTime('now', $zonaHoraria);
        $fechaActual = $ahora->format('Y-m-d H:i:s');

        // Si la fecha de creación no está definida, se inicializa; de lo contrario, se mantiene
        if (!isset($this->createdAt)) {
            $this->createdAt = $fechaActual;
        }

        // La fecha de actualización siempre se refresca en cada cambio o guardado
        $this->updatedAt = $fechaActual;
    }

    /**
     * Recupera la fecha de creación del registro.
     * 
     * @return string Devuelve la fecha formateada en cadena.
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt ?? '';
    }

    /**
     * Recupera la fecha de la última actualización del registro.
     * 
     * @return string Devuelve la fecha formateada en cadena.
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt ?? '';
    }
}
