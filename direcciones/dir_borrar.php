<?php declare(strict_types=1);

// Construye la clase ConsultarIris en el objeto $api_iris
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'iniciar_clase.php';

// Le indicamos los parámetros de a dónde se va a realizar la consulta.
$consultar_lugar = ["CR", "organizacion_1"];
$guia_url = $configuracion_iris["iris_api"][$consultar_lugar[0]]["link_busqueda"];

function formatoPagina(array &$i, string $guia_url): void
{
    if (false === empty($i)) {
        unset($i["creado_por_usuario_correo"], $i["id_contrato"], $i["id_dirección_fuente"], $i["id_dirección_destino"]);

        $i["guía"] = "<a href='$guia_url{$i["guía"]}' target='_blank'><b>{$i["guía"]}</b></a>";

        $i["fecha_creación"] = str_ireplace("T", " ", substr($i["fecha_creación"], 0, 19));
        $i["fecha_recolecta"] = substr((string)$i["fecha_recolecta"], 0, 10);
        $i["fecha_cedi"] = substr((string)$i["fecha_cedi"], 0, 10);
        $i["fecha_entrega"] = substr((string)$i["fecha_entrega"], 0, 10);
        $i["recolecta_estimada_a"] = substr((string)$i["recolecta_estimada_a"], 0, 10);
        $i["recolecta_estimada_b"] = substr((string)$i["recolecta_estimada_b"], 0, 10);
        $i["entrega_estimada_a"] = substr((string)$i["entrega_estimada_a"], 0, 10);
        $i["entrega_estimada_b"] = substr((string)$i["entrega_estimada_b"], 0, 10);
    }
}


$api_iris->set($consultar_lugar[0], $consultar_lugar[1]);

if (!isset($_GET["dir"])) {
    die("Falta el parámetro \"dir\"");
}

$la_dir = preg_replace('/[^A-Za-z0-9_\-]/i', '', $_GET["dir"]);


$direccion = $api_iris->consultarDireccion($la_dir);

// Si hubo errores durante la consulta los guardamos en un log:
if (null !== $api_iris->log) {
    error_log("Iris, error {$direccion["code"]}: $api_iris->log");

    if ($configuracion_iris["iris_modo_pruebas"]) {
        die($api_iris->log);
    }
}

if (1 === $direccion["status"]) {

    $direccion_borrada = $api_iris->borrarDireccion($la_dir);

    // Si hubo errores durante la consulta los guardamos en un log:
    if (null !== $api_iris->log) {
        error_log("Iris, error {$direccion_borrada["code"]}: $api_iris->log");

        if ($configuracion_iris["iris_modo_pruebas"]) {
            die($api_iris->log);
        }
    }

    if (0 === $direccion_borrada["status"]) {
        $direccion['message'] = $direccion_borrada["message"];
    }
}

// Imprimir el resultado:

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

    <h2 class="pb-0 border-bottom">Direcci&oacute;n borrada:</h2>
    <h2 class="pb-2 border-bottom"><b><small><?php echo $la_dir; ?></small></b></h2>
    <div class="row g-4 py-5 row-cols-1">
        <div class="col">
            <?php
            pretty_print($direccion['message']);
            ?>
        </div>
    </div>

<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
