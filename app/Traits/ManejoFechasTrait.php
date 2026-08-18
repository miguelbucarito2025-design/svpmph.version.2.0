<?php

namespace App\Traits;

use DateTime;
use DateInterval;
use Exception;

/**
 * Trait ManejoFechasTrait
 *
 * Proporciona utilidades para el cálculo de edades, operaciones de tiempo,
 * diferencias entre fechas y validaciones para el sistema SVPMPH.
 *
 * @package App\Traits
 */
trait ManejoFechasTrait
{
    /**
     * Calcula la edad cumplida en años a partir de una fecha de nacimiento.
     *
     * Evalúa el día y mes exacto contra la fecha actual del servidor.
     *
     * @param string $fechaNacimiento Fecha de nacimiento en formato 'YYYY-MM-DD'.
     * @return int Edad exacta en años.
     * 
     * @throws Exception Si la cadena proporcionada no es una fecha válida.
     */
    public function obtenerEdad(string $fechaNacimiento): int
    {
        $nacimiento = new DateTime($fechaNacimiento);
        $hoy = new DateTime('now');

        return $hoy->diff($nacimiento)->y;
    }

    /**
     * Calcula la diferencia detallada entre dos fechas (Años, meses, días).
     *
     * @param string $fechaInicial Fecha de inicio en formato 'YYYY-MM-DD'.
     * @param string $fechaFinal   Fecha de término en formato 'YYYY-MM-DD'.
     * @return DateInterval Objeto con el desglose de la diferencia.
     * 
     * @throws Exception Si alguna de las fechas no es válida.
     */
    public function diferenciaEntreFechas(string $fechaInicial, string $fechaFinal): DateInterval
    {
        $inicio = new DateTime($fechaInicial);
        $fin = new DateTime($fechaFinal);

        return $inicio->diff($fin);
    }

    /**
     * Suma o resta tiempo a una fecha dada mediante expresiones relativas.
     *
     * @param string $fechaBase      Fecha inicial en formato 'YYYY-MM-DD'.
     * @param string $modificador    Expresión relativa (ej: '-30 days', '-1 year', '+2 months').
     * @param string $formatoSalida  Formato de retorno (por defecto 'Y-m-d').
     * @return string Fecha calculada.
     * 
     * @throws Exception Si el modificador o la fecha son inválidos.
     */
    public function modificarFecha(string $fechaBase, string $modificador, string $formatoSalida = 'Y-m-d'): string
    {
        $fecha = new DateTime($fechaBase);
        $fecha->modify($modificador);

        return $fecha->format($formatoSalida);
    }

    /**
     * Valida si una cadena de texto es una fecha real y cumple el formato especificado.
     *
     * Utilidad clave para validar inputs de formularios antes de procesarlos.
     *
     * @param string $fecha   Cadena con la fecha a validar.
     * @param string $formato Formato esperado (por defecto 'Y-m-d').
     * @return bool True si la fecha existe y coincide con el formato; False en caso contrario.
     */
    public function esFechaValida(string $fecha, string $formato = 'Y-m-d'): bool
    {
        $dt = DateTime::createFromFormat($formato, $fecha);
        return $dt && $dt->format($formato) === $fecha;
    }
}
