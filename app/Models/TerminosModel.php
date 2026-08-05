<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Libs\Exceptions\AppException;
use App\Libs\Exceptions\DatabaseException;

class TerminosModel extends Model
{
    protected string $tabla = 'terminos';

    protected array $campos = [
        'titulo'    => 'esCadena',
        'version'   => 'esAlfanumerico',
        'contenido' => 'esTexto',
        'activo'    => 'esBooleano',
        'fecha'     => 'esFecha',
        'rol_id'    => 'esEntero'
    ];

    protected array $camposMinimos = [
        'titulo',
        'version',
        'contenido',
        'activo',
        'fecha',
        'rol_id'
    ];

    protected array $camposUnicos = ['version', 'titulo'];



    /**
     * Obtiene todos los términos y condiciones registrados.
     *
     * @return array
     * @throws AppException Si ocurre un error en la base de datos.
     */
    public function selectAll(): array
    {
        $sql = "SELECT * FROM {$this->tabla}";

        try {
            return $this->db->select(
                $sql,
                [],
                'all'
            );
        } catch (DatabaseException $e) {
            throw new AppException("Error al obtener los registros: " . $e->getMessage(), 500);
        }
    }
}
