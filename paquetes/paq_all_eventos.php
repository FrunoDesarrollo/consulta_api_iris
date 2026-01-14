<?php declare(strict_types=1);

// Construye la clase ConsultarIris en el objeto $api_iris
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'iniciar_clase.php';

// Le indicamos los parámetros de a dónde se va a realizar la consulta.
$consultar_lugar = ["CR", "organizacion_1"];
$guia_url = $configuracion_iris["iris_api"][$consultar_lugar[0]]["link_busqueda"];


$api_iris->set($consultar_lugar[0], $consultar_lugar[1]);

$lista_paquetes = $api_iris->listarEventosPosibles();

// Si hubo errores durante la consulta los guardamos en un log:
if (null !== $api_iris->log) {
    error_log("Iris, error {$lista_paquetes["code"]}: $api_iris->log");

    die($api_iris->log);
}

if (0 === $lista_paquetes["status"]) {
    die("No fue posible realizar la acción solicitada - {$lista_paquetes["message"]}");
}

// Ignorando mayúsculas/minúsculas
usort($lista_paquetes['message']['tipo_eventos'], fn($a, $b) =>
strcasecmp($a['nombre'], $b['nombre']) ?: strcasecmp($a['etapa'], $b['etapa'])
);

// Imprimir el resultado:

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

    <h2 class="pb-2 border-bottom">Lista de eventos posibles</h2>
    <div class="row g-4 py-5 row-cols-1">
        <div class="col">
            <?php
            $columnas = [
                    'id' => 'ID',
                    'nombre' => 'Nombre',
                    'descripcion' => 'Descripción',
                    'etapa' => 'Etapa',
            ];

            echo renderTable($columnas, $lista_paquetes['message']['tipo_eventos']);
            ?>
        </div>
    </div>

<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
