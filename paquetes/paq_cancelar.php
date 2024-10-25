<?php
declare(strict_types=1);

// Construye la clase ConsultarIris en el objeto $api_iris
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'iniciar_clase.php';

// Le indicamos los parámetros de a dónde se va a realizar la consulta.
$consultar_lugar = ["CR", "organizacion_1"];
$guia_url = $configuracion_iris["iris_api"][$consultar_lugar[0]]["link_busqueda"];

$api_iris->set($consultar_lugar[0], $consultar_lugar[1]);

if (!isset($_GET["guia"])) {
    die("Falta el parámetro \"guia\"");
}

$la_guia = preg_replace('/[^A-Za-z0-9]/i', '', $_GET["guia"]);

$paquete = $api_iris->cancelarPaquete($la_guia);

// Si hubo errores durante la consulta los guardamos en un log:
if (null !== $api_iris->log) {
    error_log("Iris, error {$paquete["code"]}: $api_iris->log");

    if ($configuracion_iris["iris_modo_pruebas"]) {
        die($api_iris->log);
    }
}

// Imprimir el resultado:

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

    <h2 class="pb-2 border-bottom">Gu&iacute;a: <b><small><?php echo $la_guia; ?></small></b></h2>
    <div class="row g-4 py-5 row-cols-1">
        <div class="col">
            <?php
            pretty_print($paquete['message']);
            ?>
        </div>
    </div>

<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
