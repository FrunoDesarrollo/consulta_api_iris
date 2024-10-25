<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>
<h2 class="pb-2 border-bottom">Men&uacute;</h2>
<div class="row g-4 py-5 row-cols-1">
    <div class="col">
        <ul class="list-group list-group-flush list-group-numbered">
            <li class="list-group-item">Divisi&oacute;n territorial
                <ul class="list-group list-group-flush list-group-numbered">
                    <li class="list-group-item"><a href="./divisionTerritorial/listar_provincias.php" target="_blank">Provincias</a>
                    </li>
                    <li class="list-group-item"><a href="./divisionTerritorial/listar_cantones.php" target="_blank">Cantones</a>
                        <small>Para
                            el ejemplo solo imprime los cantones de la provincia de Alajuela (2).</small></li>
                    <li class="list-group-item"><a href="./divisionTerritorial/listar_distritos.php" target="_blank">Distritos</a>
                        <small>Para
                            el ejemplo solo imprime los distritos del cant&oacute;n de Atenas (205).</small></li>
                </ul>
            </li>
            <li class="list-group-item">Direcciones
                <ul class="list-group list-group-flush list-group-numbered">
                    <li class="list-group-item">
                        <a href="./direcciones/dir_crear.php" target="_blank">Crear direcci&oacute;n</a>
                    </li>
                    <li class="list-group-item">
                        Lista de direcciones:
                        <ul class="list-group list-group-flush list-group-numbered">
                            <li class="list-group-item">
                                <a href="./direcciones/dir_lista.php" target="_blank">Versión Web</a>
                            </li>
                            <li class="list-group-item">
                                <a href="./direcciones/dir_lista_json.php" target="_blank">Versión Json</a>
                            </li>
                        </ul>
                    </li>
                    <li class="list-group-item">Consultar direcci&oacute;n:

                        <div class="row">
                            <label for="txtConsultaDir" class="visually-hidden">Direcci&oacute;n</label>
                            <div class="col-sm-10">
                                <form method="get" action="./direcciones/dir_consulta.php" target="_blank"
                                      class="row g-2 w-75 ms-4">
                                    <div class="col-12 col-md-7">
                                        <div class="input-group mb-3">
                                            <input type="text" id="txtConsultaDir" class="form-control"
                                                   placeholder="Direcci&oacute;n" name="dir" required="">
                                            <button type="submit" class="btn btn-outline-secondary">Consultar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">Borrar direcci&oacute;n:

                        <div class="row">
                            <label for="txtBorrarDir" class="visually-hidden">Direcci&oacute;n</label>
                            <div class="col-sm-10">
                                <form method="get" action="./direcciones/dir_borrar.php" target="_blank"
                                      class="row g-2 w-75 ms-4">
                                    <div class="col-12 col-md-7">
                                        <div class="input-group mb-3">
                                            <input type="text" id="txtBorrarDir" class="form-control"
                                                   placeholder="Direcci&oacute;n" name="dir" required="">
                                            <button type="submit" class="btn btn-outline-secondary">Borrar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <a href="./direcciones/dir_trasladar.php" target="_blank">Copiar todas las direcciones de
                            "organizacion_1" a "organizacion_2".</a>
                    </li>
                    <li class="list-group-item">
                        <a href="./direcciones/cambiar_direccion.php" target="_blank">Cambiar direcci&oacute;n.</a>
                    </li>
                </ul>
            </li>
            <li class="list-group-item">Paquetes
                <ul class="list-group list-group-flush list-group-numbered">
                    <li class="list-group-item"><a href="./paquetes/paq_crear.php" target="_blank">Crear paquete</a>
                        <div class="alert alert-warning ms-2 mt-1">
                            Cambiar <b>$id_direccion_fuente</b> y <b>$id_direccion_destino</b> antes de crear el
                            paquete.
                        </div>
                    </li>
                    <li class="list-group-item"><a href="./paquetes/paq_lista.php" target="_blank">Lista de paquetes</a>
                        <small>Para el ejemplo solo imprime los paquetes creados el día de hoy.</small></li>
                    <li class="list-group-item">Consultar gu&iacute;a:

                        <div class="row">
                            <label for="inputGuia" class="visually-hidden">Gu&iacute;a</label>
                            <div class="col-sm-10">
                                <form method="get" action="./paquetes/paq_consulta.php" target="_blank"
                                      class="row g-2 w-75 ms-4">
                                    <div class="col-auto">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="inputGuia"
                                                   placeholder="Gu&iacute;a" name="guia" required>
                                            <button type="submit" class="btn btn-outline-secondary">Consultar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">Cancelar gu&iacute;a:

                        <div class="row">
                            <label for="inputGuia" class="visually-hidden">Gu&iacute;a</label>
                            <div class="col-sm-10">
                                <form method="get" action="./paquetes/paq_cancelar.php" target="_blank"
                                      class="row g-2 w-75 ms-4">
                                    <div class="col-auto">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="inputGuia"
                                                   placeholder="Gu&iacute;a" name="guia" required>
                                            <button type="submit" class="btn btn-outline-danger">Cancelar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>
<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
?>
