<?php

declare(strict_types=1);


namespace App\Traits;


trait LikesSQLTraits
{
    use ModificarArraysTrait;


    public function likeClaus(array $camposBusqueda, mixed $buscar, string $clausIni = '')
    {

        $result = [];
        $whereClauses = [];
        $params       = [];

        $whereClauses[] = $clausIni;

        if ($buscar !== null) {
            $paramBuscar    = mb_strtoupper($buscar, 'UTF-8') . '%';
            $whereClauses[] = $this->construirClausulaBusqueda($camposBusqueda);

            foreach ($camposBusqueda as $c) {
                $params[] = $paramBuscar;
            }
        }


        if (!empty($whereClauses)) {
            $result['claus'] = ' WHERE ' . implode(' AND ', $whereClauses);
            $result['values'] = $params;
        }

        return $result;
    }
}
