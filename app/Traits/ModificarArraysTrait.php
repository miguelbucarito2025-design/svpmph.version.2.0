<?php

declare(strict_types=1);

namespace App\Traits;


trait ModificarArraysTrait
{
    /**
     * Construye la cláusula WHERE para búsquedas por coincidencia en múltiples campos.
     *
     * @param array $campos Lista de nombres de columnas (ej. ['nombre', 'apellido', 'cedula']).
     * @return string Cláusula SQL formateada entre paréntesis.
     */
    protected  function construirClausulaBusqueda(array $campos): string
    {
        // 1. Mapeamos el arreglo $campos (no $campo)
        $likeConditions = array_map(
            fn(string $campo): string => "UPPER({$campo}) LIKE ?",
            $campos
        );

        // 2. Unimos las condiciones con OR y encerramos entre paréntesis
        return '(' . implode(' OR ', $likeConditions) . ')';
    }
}
