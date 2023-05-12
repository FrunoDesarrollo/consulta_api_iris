<?php
declare(strict_types=1);

// Construye la clase ConsultarIris en el objeto $api_iris
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'iniciar_clase.php';

// Le indicamos los parámetros de a dónde se va a realizar la consulta.
$api_iris->set("CR", "organizacion_1");

// TODO Le pasamos el id de la provincia. "2" corresponde a la provincia de "Alajuela" y el id del cantón "205" que corresponde a "Atenas".
$division_territorail_3 = $api_iris->obtenerDistritos("2", "205");

// Si hubo errores durante la consulta los guardamos en un log:
if (null !== $api_iris->log) {
    error_log("Iris, error {$division_territorail_3["code"]}: $api_iris->log");

    if ($configuracion_iris["iris_modo_pruebas"]) {
        die($api_iris->log);
    }
}

if (0 === $division_territorail_3["status"]) {
    die("No fue posible realizar la acción solicitada - {$division_territorail_3["message"]}");
}

// Imprimir el resultado:

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

    <h2 class="pb-2 border-bottom">Distritos</h2>
    <div class="row g-4 py-5 row-cols-1">
        <div class="col">
            <?php
            pretty_print($division_territorail_3["message"]);
            ?>
        </div>
    </div>

<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
