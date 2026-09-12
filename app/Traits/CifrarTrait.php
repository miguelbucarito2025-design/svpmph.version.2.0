<?php

declare(strict_types=1);

namespace App\Traits;

use App\Libs\Seguridad;



trait CifrarTrait
{

    /**
     * Cifra campos específicos de un arreglo, ya sea unidimensional o multidimensional.
     *
     * @param array $datos Matriz de datos (1 sola fila o lista de filas).
     * @param array $camposNombres Claves o columnas que se desean encriptar.
     * @return array Matriz con los campos especificados encriptados.
     */
    protected function cifrarDatos(array $datos, array $camposNombres): array
    {
        if (empty($datos)) {
            return $datos;
        }

        $esMultidimensional = isset($datos[0]) && is_array($datos[0]);

        if ($esMultidimensional) {
            foreach ($datos as &$fila) {
                foreach ($camposNombres as $campo) {
                    if (isset($fila[$campo])) {
                        $fila[$campo] = Seguridad::encriptarID($fila[$campo]);
                    }
                }
            }
            unset($fila);
        } else {
            foreach ($camposNombres as $campo) {
                if (isset($datos[$campo])) {
                    $datos[$campo] = Seguridad::encriptarID($datos[$campo]);
                }
            }
        }

        return $datos;
    }
}
