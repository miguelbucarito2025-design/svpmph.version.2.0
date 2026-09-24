<?php

namespace App\Traits;

/**
 * Trait para la generación puntual de códigos de agremiados.
 */
trait GeneradorCodigoTrait
{
    /**
     * Formatea y concatena las siglas SVPMPH, el nivel/carrera y el ID de la cuenta.
     *
     * @param string $nivelCarrera Siglas combinadas del nivel y carrera.
     * @param int $idCuenta ID inmutable de la cuenta.
     * @return string
     */
    public function generarCodigoProfesional(string $nivelCarrera, int $idCuenta): string
    {
        $nivelLimpio = strtoupper(trim($nivelCarrera));
        $idFormateado = str_pad((string)$idCuenta, 5, '0', STR_PAD_LEFT);

        return "SVPMPH-{$nivelLimpio}-{$idFormateado}";
    }
}
