<article>
    <div class="container-title">

        <h1 class="title-pag">Asignaturas</h1>
        <p class="text-pag">
            Creacion y control de las Asignaturas de programas de Academicos , obtencion de metricas clave y monitoreo en tiempo real
        </p>
    </div>


    <section class="sec-table">

        <input type="hidden" name="offset" id="offset" value="0">

        <div class="container-table">
            <table class="">
                <thead>
                    <tr>
                        <th colspan="4"> Asignaturas Registradas</th>
                    </tr>
                    <tr>
                        <th colspan="4">
                            <div class="items-table">
                                <div class="button-table  continer-item-table">
                                    <button type="button" class="success" id="agregar-programa">
                                        <svg class="icono-outline">
                                            <use href="#icono-agregar"></use>
                                        </svg>

                                        <span>Agregar </span>
                                    </button>
                                    <button type="button" class="danger" id="btnEliminarMasivo">
                                        <svg class="icono-outline">
                                            <use href="#icono-eliminar"></use>
                                        </svg>
                                        <span>Eliminar</span>
                                    </button>
                                    <div>

                                    </div>
                                </div>


                                <div class="filtros-table continer-item-table">
                                    Programas
                                    <select name="programa" id="programa-id">
                                        <option value="" class="option-none">--Todos--</option>
                                        <?php

                                        foreach (($programas ?? []) as $t) {
                                            echo "<option value='{$t['id']}'>{$t['programa']}</option> ";
                                        }

                                        ?>

                                    </select>

                                </div>


                                <div class="continer-item-table">
                                    <div class="container-search ">
                                        <div class="container-svg">
                                            <svg class="icono-outline">
                                                <use href="#icono-buscar"></use>
                                            </svg>
                                        </div>
                                        <input
                                            type="search"
                                            name="buscar"
                                            placeholder=""
                                            id="intput-buscar"
                                            required />
                                    </div>
                                </div>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th class="td-option">Opciones | Select All:
                            <input type="checkbox" id="checkSeleccionarTodos" class="form-check-input">
                        </th>
                        <th>Numero</th>
                        <th>Nombre del Asignatura</th>
                        <th>Codigo</th>
                    </tr>
                </thead>
                <tbody id="tablaCuerpo">
                    <tr>
                        <td colspan="4"> No hay registros</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="td-option tfoot-td-option">
                            <span id="textoMetricaPaginacion">Mostrando: 0 de 0</span>
                            <br>
                            Limite:
                            <select name="limit" id="limit">
                                <option value="10">-- 10 Registros --</option>
                                <option value="20">-- 20 Registros --</option>
                                <option value="50">-- 50 Registros --</option>
                                <option value="100">-- 100 Registros --</option>
                            </select>

                        </td>
                        <td id="pag-actual"></td>
                        <td colspan="2">

                            <div class="container-paginacion">
                                <button type="button" id="btnRetroceder" class="table-navigator" disabled>Retroceder</button>
                                <div id="contenedorPaginasNumeradas" style="display: inline-flex; gap: 4px;">
                                </div>
                                <button type="button" id="btnAvanzar" class="table-navigator">Avanzar</button>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>

        </div>
    </section>
</article>
<script src="<?= URL_BASE ?>public/js/<?= $pag ?? '' ?>.js" defer></script>