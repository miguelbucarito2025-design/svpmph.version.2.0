<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Traits\ManejoArchivosR2Trait;

class ArchivosModel  extends Model
{

    use ManejoArchivosR2Trait;

    protected string $tabla = 'archivos';

    protected array $campos = [
        'id' => 'esEntero',
        'url' => 'esRutaArchivo',
        'user_id' => 'esEntero',
        'archivo' => 'esTexto',
        'verificado' => 'esBooleano'
    ];

    protected array $camposMinimos = [
        'user_id',
        'archivo'
    ];

    public function insertMultiple(int $cuenta, array $archivos): bool
    {
        if (empty($archivos)) {
            return true;
        }

        // 1. Aplanamos y limpiamos $archivos para que sea SOLO un arreglo de cadenas
        $archivosNormalizados = [];
        foreach ($archivos as $item) {
            if (is_array($item)) {
                // Si $item es un arreglo, extraemos la cadena del nombre
                $valor = $item['archivo'] ?? $item['archvo'] ?? $item['nombre'] ?? reset($item);
                if (is_string($valor) || is_numeric($valor)) {
                    $archivosNormalizados[] = (string)$valor;
                }
            } elseif (is_string($item) || is_numeric($item)) {
                $archivosNormalizados[] = (string)$item;
            }
        }

        // Eliminamos duplicados y vacíos de la petición
        $archivosNormalizados = array_unique(array_filter($archivosNormalizados));

        if (empty($archivosNormalizados)) {
            return true;
        }

        // 2. Obtenemos los archivos ya registrados como vector de strings puros
        $subidos = array_column($this->archivos($cuenta), 'archivo');

        $param = [];
        $values = [];

        // 3. Construcción estricta de marcadores y parámetros escalares
        foreach ($archivosNormalizados as $nombreArchivo) {
            // Ignoramos los que ya existen
            if (in_array($nombreArchivo, $subidos, true)) {
                continue;
            }

            $param[] = '(?,?,?)';

            // Empujamos ÚNICAMENTE valores escalares simples
            $values[] = (int)$cuenta;
            $values[] = (string)$nombreArchivo;
            $values[] = 0;
        }

        // 4. Si ya los tenía todos, no toca la BD
        if (empty($param)) {
            return true;
        }

        // 5. Inserción
        $sql = 'INSERT INTO archivos (user_id, archivo, verificado) VALUES ' . implode(',', $param);

        $this->db->consult($sql, $values);

        return true;
    }
    private function archivos(int $id)
    {

        $sql = 'SELECT id,archivo FROM archivos WHERE  user_id=? ';
        return $this->db->select($sql, [$id], 'all');
    }


    /**
     * Obtiene y procesa la lista de archivos verificados asociados a un usuario.
     *
     * @param int $id Identificador del usuario.
     * @param array $archivos Lista de nombres o identificadores de archivos a consultar.
     * @return array Arreglo de archivos con ID cifrado y recurso de URL procesado.
     */
    public function traerArchivos(int $id, array $archivos): array
    {
        if (empty($archivos)) {
            return [];
        }

        // Construcción de marcadores posicionales
        $param = array_fill(0, count($archivos), '?');
        $user[] = $id;
        $values = array_merge($user, $archivos);
        $sql = 'SELECT id, archivo, url, verificado 
                FROM archivos 
                WHERE user_id = ? AND archivo IN (' . implode(',', $param) . ')';

        $result = $this->db->select($sql, $values);

        return array_map(function (array $archivo): array {
            $archivo = $this->cifrarDatos($archivo, ['id']);
            // Se procesa la URL mediante la función adaptadora
            $archivo['url'] = $this->ObtenerArchivo($archivo['url'] ?? '');

            return $archivo;
        }, $result);
    }

    public function traerPorId(int $id, bool $strict = true)
    {
        $sql = 'SELECT id,url,archivo,verificado FROM archivos WHERE id=?';
        $result = $this->db->select($sql, [$id], 'row');
        if ($strict === true) {
            $result = $this->cifrarDatos($result, ['id']);
            $result['url'] = $this->ObtenerArchivo($result['url']);
            return;
        }
        return $result;
    }

    public function traerTodos()
    {
        $sql = 'SELECT id,url,archivo,verificado FROM archivos ';
        $result = $this->db->select($sql, [], 'all');
        return $result;
    }

    public function traerPorUsuario(int $id)
    {
        $sql = 'SELECT * FROM archivos WHERE user_id=?';
        $result = $this->db->select($sql, [$id]);
        if (empty($result)) {
            return [];
        }
        return array_map(function ($archivos): array {
            $archivos = $this->cifrarDatos($archivos, ['id']);
            $archivos['url'] = $this->obtenerArchivo($archivos['url']);
            return $archivos;
        }, $result);
    }


    public function verificar(array $ids, int $cuenta_id, int|bool $estado = 1): bool
    {

        try {
            $this->db->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = 'UPDATE archivos SET verificado = ? WHERE id IN (' . $placeholders . ') AND url IS NOT NULL 
    AND TRIM(url) !="" ';
            $values = array_merge([$estado], $ids);
            $this->db->consult($sql, $values);

            $sql = "UPDATE gremio g
JOIN mencion m ON g.mencion_id = m.id
SET g.estado = CASE 
    -- Condición para poner en 1 (Activo):
    -- La cantidad de archivos VERIFICADOS que coinciden con los requisitos de su mención
    -- debe ser IGUAL a la cantidad de requisitos solicitados por la mención.
    WHEN (
        SELECT COUNT(DISTINCT a.id)
        FROM archivos a
        WHERE a.user_id = g.cuenta_id
          AND a.verificado = 1
          AND a.url IS NOT NULL 
          AND TRIM(a.url) != ''
          AND FIND_IN_SET(a.archivo, m.requisitos) > 0
    ) = (
        -- Cuenta cuántos requisitos exige la mención (contando las comas + 1)
        CHAR_LENGTH(m.requisitos) - CHAR_LENGTH(REPLACE(m.requisitos, ',', '')) + 1
    )
    AND TRIM(COALESCE(m.requisitos, '')) != ''
    THEN 1
    
    -- Si no cumple exactamente con todos los requisitos verificados, pasa a 0 (Inactivo)
    ELSE 0
END
WHERE g.cuenta_id = ?;";

            $values2[] = $cuenta_id;
            $this->db->consult($sql, $values2);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
