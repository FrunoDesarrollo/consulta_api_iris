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
                    <li class="list-group-item"><a href="./direcciones/dir_crear.php" target="_blank">Crear direcci&oacute;n</a>
                    </li>
                    <li class="list-group-item"><a href="./direcciones/dir_lista.php" target="_blank">Lista de
                            direcciones</a></li>
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
                </ul>
            </li>
        </ul>
    </div>
</div>
<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
?>
