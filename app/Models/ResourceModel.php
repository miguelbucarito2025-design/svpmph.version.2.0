<?php

declare(sytict_types=1);

namespace App\Models\ResourceModel;

use App\Libs\BuilderQuery;
use App\Libs\Exceptions\AppException;
use App\Libs\Exceptions\DatabaseException;


class ResourceModel
{

    private BuilderQuery $db;

    public function __construct()
    {
        $this->db = new BuilderQuery();
    }
    /**
     * Registra un nuevo proyecto en la base de datos.
     *
     * @param array{
     *     user_id: int,
     *     nombre: string,
     *     proyecto: string,
     *     img?: string,
     *     created: string,
     *     updated: string,
     *     paleta?: string
     * } $datos Estructura completa de los datos del proyecto.
     * 
     * @return bool Devuelve true si el registro fue exitoso.
     * 
     * @throws AppException Si faltan campos obligatorios o el ID de usuario es inválido (400).
     * @throws DatabaseException Si ocurre un fallo durante la inserción en la base de datos (500).
     */
    public function saveProject(array $datos): bool
    {
        $camposObligatorios = ['user_id', 'proyecto', 'created', 'updated'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim((string) $datos[$campo]) === '') {
                throw new AppException("El campo obligatorios '{$campo}' no puede estar vacío.", 400);
            }
        }

        if ((int) $datos['user_id'] <= 0) {
            throw new AppException("El ID de usuario proporcionado no es válido.", 400);
        }

        try {
            return $this->db->insert(
                'proyectos',
                [
                    'user_id'  => (int) $datos['user_id'],
                    'proyecto' => (string) $datos['proyecto'],
                    'img'      => (string) ($datos['img'] ?? ''),
                    'created'  => (string) $datos['created'],
                    'updated'  => (string) $datos['updated'],
                    'paleta'   => (string) ($datos['paleta'] ?? '')
                ],
                ['proyecto' => (string) $datos['proyecto']]
            );
        } catch (\Throwable $e) {
            throw new DatabaseException("Error al registrar el proyecto en el sistema.", 500);
        }
    }


    /**
     * Actualiza un proyecto según los campos proporcionados.
     *
     * @param array{
     *     id: int,
     *     proyecto?: string,
     *     img?: string,
     *     updated?: string,
     *     paleta?: string
     * } $datos Arreglo con el ID y los datos opcionales a actualizar.
     * 
     * @return bool Devuelve true si la actualización fue exitosa.
     * 
     * @throws AppException Si el ID es inválido o no se proporciona ningún campo para actualizar (400).
     * @throws DatabaseException Si ocurre un fallo durante la ejecución de la consulta (500).
     */
    public function updateProject(array $datos): bool
    {
        if (!isset($datos['id']) || (int) $datos['id'] <= 0) {
            throw new AppException('El ID del proyecto no es válido.', 400);
        }

        $camposPermitidos = ['proyecto', 'img', 'updated', 'paleta'];
        $valores = [];

        foreach ($camposPermitidos as $campo) {
            if (isset($datos[$campo]) && trim((string) $datos[$campo]) !== '') {
                $valores[$campo] = (string) $datos[$campo];
            }
        }

        if (empty($valores)) {
            throw new AppException('No se ha detectado ningún campo válido para actualizar.', 400);
        }

        try {
            return $this->db->update(
                'proyectos',
                $valores,
                ['id' => (int) $datos['id']]
            );
        } catch (\Throwable $e) {
            throw new DatabaseException('No se pudo actualizar el proyecto debido a un error en el sistema.', 500);
        }
    }






    /**
     * Guarda un archivo de código o recurso dentro de un proyecto del usuario.
     *
     * @param array{
     *     id_user: int,
     *     proyec_id: int,
     *     contenido: string,
     *     created: string,
     *     archivo: string
     * } $datos Datos del archivo a guardar.
     * 
     * @return bool Devuelve true si la inserción fue exitosa.
     * 
     * @throws AppException Si falta algún parámetro obligatorio (400) o si el nombre del archivo ya existe en el proyecto (409).
     * @throws DatabaseException Si ocurre un error inesperado al interactuar con la base de datos (500).
     */
    public function saveFile(array $datos): bool
    {
        $reglas = ['id_user', 'proyec_id', 'contenido', 'created', 'archivo'];

        foreach ($reglas as $campo) {
            if (!isset($datos[$campo]) || trim((string) $datos[$campo]) === '') {
                throw new AppException("El campo '{$campo}' es obligatorio.", 400);
            }
        }

        if ((int) $datos['id_user'] <= 0 || (int) $datos['proyec_id'] <= 0) {
            throw new AppException("Los IDs de usuario y proyecto deben ser enteros válidos.", 400);
        }

        try {
            return $this->db->insert(
                'archivos',
                [
                    'id_user'   => (int) $datos['id_user'],
                    'proyec_id' => (int) $datos['proyec_id'],
                    'contenido' => (string) $datos['contenido'],
                    'created'   => (string) $datos['created'],
                    'updated'   => (string) $datos['created'],
                    'archivo'   => (string) $datos['archivo']
                ],
                [
                    'archivo'   => (string) $datos['archivo'],
                    'proyec_id' => (int) $datos['proyec_id']
                ]
            );
        } catch (AppException $e) {
            if ((int) $e->getCode() === 409) {
                throw new AppException("El nombre de archivo '{$datos['archivo']}' ya está registrado en este proyecto.", 409);
            }

            throw $e;
        } catch (\Throwable $e) {
            throw new DatabaseException("Error de base de datos al intentar guardar el archivo.", 500);
        }
    }


    /**
     * Actualiza el contenido , la fecha de modificación de un archivo o el nombre de un archivo.
     * Nota: no es necesario pasarle todos los campos con solo entregarle lo q quieres cambiar y 
     * de quien es suficiente.
     *
     * @param array{
     *     id: int,
     *     contenido: string,
     *     updated: string,
     *     archivo:string
     * } $datos Arreglo con la información a actualizar.
     * 
     * @return bool Devuelve true si la actualización fue exitosa.
     * 
     * @throws AppException Si falta algún parámetro obligatorio o el ID es inválido (400).
     * @throws DatabaseException Si ocurre un fallo al ejecutar la consulta en la base de datos (500).
     */
    public function updateFile(array $datos): bool
    {
        $campos = [
            'contenido',
            'updated',
            'archivo'
        ];

        if (!isset($datos['id']) || (int) $datos['id'] <= 0) {
            throw new AppException('El ID del recurso no es válido.', 400);
        }

        $contenido = isset($datos['contenido']) && trim((string) $datos['contenido']) !== '';
        $updated = isset($datos['updated']) && trim((string) $datos['updated']) !== '';
        $archivo = isset($datos['archivo']) && trim((string) $datos['archivo']) !== '';

        if (!$contenido && !$updated && !$archivo) {
            throw new AppException('Se requiere al menos un campo válido (contenido , updated o archivo) para actualizar.', 400);
        }

        $valores = [];
        foreach ($campos as $campo) {
            if (isset($datos[$campo]) && trim((string) $datos[$campo]) !== '') {
                $valores[$campo] = (string) $datos[$campo];
            }
        }

        try {
            return $this->db->update(
                'archivos',
                $valores,
                ['id' => (int) $datos['id']]
            );
        } catch (\Throwable $e) {
            throw new DatabaseException("Hubo un problema al actualizar el archivo en el sistema.", 500);
        }
    }



    /**
     * Registra un nuevo recurso (src) para un usuario en la base de datos.
     *
     * @param array{
     *     id_user: int,
     *     ruta: string,
     *     src: string,
     *     tipo_id: int
     * } $datos Estructura completa con los datos del recurso.
     * 
     * @return bool Devuelve true si la inserción fue exitosa.
     * 
     * @throws AppException Si faltan campos obligatorios o algún ID es inválido (400).
     * @throws DatabaseException Si ocurre un fallo en la inserción en la base de datos (500).
     */
    public function saveSrc(array $datos): bool
    {
        $reglas = ['id_user', 'ruta', 'src', 'tipo_id'];

        foreach ($reglas as $campo) {
            if (!isset($datos[$campo]) || trim((string) $datos[$campo]) === '') {
                throw new AppException("El campo '{$campo}' es obligatorio.", 400);
            }
        }

        if ((int) $datos['id_user'] <= 0 || (int) $datos['tipo_id'] <= 0) {
            throw new AppException("Los IDs de usuario y tipo deben ser enteros válidos.", 400);
        }

        try {
            return $this->db->insert(
                'src',
                [
                    'id_user' => (int) $datos['id_user'],
                    'ruta'    => (string) $datos['ruta'],
                    'src'     => (string) $datos['src'],
                    'tipo_id' => (int) $datos['tipo_id']
                ],
                [
                    'ruta' => (string) $datos['ruta'],
                    'src'  => (string) $datos['src']
                ]
            );
        } catch (\Throwable $e) {
            throw new DatabaseException("Hubo un error de inserción al guardar el recurso.", 500);
        }
    }




    /**
     * Actualiza los recursos del usuario de forma parcial o total según su ID.
     *
     * @param array{
     *     id: int,
     *     ruta?: string,
     *     src?: string
     * } $datos Arreglo asociativo con el ID y los campos a actualizar.
     * 
     * @return bool Devuelve true si la actualización fue exitosa.
     * 
     * @throws AppException Si el ID es inválido o no se proporciona al menos un campo válido (400).
     * @throws DatabaseException Si ocurre un error durante la ejecución de la consulta (500).
     */
    public function updateSrc(array $datos): bool
    {
        if (!isset($datos['id']) || (int) $datos['id'] <= 0) {
            throw new AppException('El ID del recurso no es válido.', 400);
        }

        $tieneRuta = isset($datos['ruta']) && trim((string) $datos['ruta']) !== '';
        $tieneSrc  = isset($datos['src']) && trim((string) $datos['src']) !== '';

        if (!$tieneRuta && !$tieneSrc) {
            throw new AppException('Se requiere al menos un campo válido (ruta o src) para actualizar.', 400);
        }

        $campos = ['ruta', 'src'];
        $valores = [];

        foreach ($campos as $campo) {
            if (isset($datos[$campo]) && trim((string) $datos[$campo]) !== '') {
                $valores[$campo] = (string) $datos[$campo];
            }
        }

        try {
            return $this->db->update(
                'src',
                $valores,
                ['id' => (int) $datos['id']]
            );
        } catch (\Throwable $e) {
            throw new DatabaseException('No se pudo actualizar el recurso debido a un error en el sistema.', 500);
        }
    }
}
