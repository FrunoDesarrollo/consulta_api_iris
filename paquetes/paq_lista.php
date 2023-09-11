<?php
declare(strict_types=1);

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


$fecha_de_hoy = date("Y-m-d");

// TODO: Le estamos indicando que solo traiga la lista de los paquetes que se han creado hoy:
$lista_paquetes = $api_iris->listarPaquetes(["fecha" => $fecha_de_hoy]);

// Si hubo errores durante la consulta los guardamos en un log:
if (null !== $api_iris->log) {
    error_log("Iris, error {$lista_paquetes["code"]}: $api_iris->log");

    if ($configuracion_iris["iris_modo_pruebas"]) {
        die($api_iris->log);
    }
}

if (0 === $lista_paquetes["status"]) {
    die("No fue posible realizar la acción solicitada - {$lista_paquetes["message"]}");
}

// Imprimir el resultado:

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

    <h2 class="pb-2 border-bottom">Lista de paquetes <small>(<?php echo $fecha_de_hoy; ?>)</small></h2>
    <div class="row g-4 py-5 row-cols-1">
        <div class="col">
            <?php

            // Ejemplo básico de como usar la paginación:

            $sin_paginacion = false;

            while (false === $sin_paginacion) {

                if (isset($lista_paquetes['message']['página'])) {

                    if (false === empty($lista_paquetes['message']['paquetes'])) {
                        foreach ($lista_paquetes['message']['paquetes'] as $i) {
                            formatoPagina($i, $guia_url);
                            pretty_print($i);
                            echo "<hr>";
                        }
                    } else {
                        if (is_array($lista_paquetes['message'])) {
                            foreach ($lista_paquetes['message'] as &$i) {
                                formatoPagina($i, $guia_url);
                            }
                        }
                        pretty_print($lista_paquetes['message']);
                    }

                    // Cuota de tiempo para que no se active el "rate limit" de Iris:
                    sleep(5);

                    // TODO: Le estamos indicando que solo traiga la lista de los paquetes que se han creado hoy:
                    $lista_paquetes = $api_iris->listarPaquetes(["fecha" => $fecha_de_hoy, "pág_despues_de" => $lista_paquetes['message']['página']]);


                    if (null !== $api_iris->log) {
                        error_log("Iris, error {$lista_paquetes["code"]}: $api_iris->log");

                        if ($configuracion_iris["iris_modo_pruebas"]) {
                            die($api_iris->log);
                        }
                    }

                } else {
                    $sin_paginacion = true;
                    if (is_array($lista_paquetes['message'])) {
                        foreach ($lista_paquetes['message'] as &$i) {
                            formatoPagina($i, $guia_url);
                        }
                    }
                    pretty_print($lista_paquetes['message']);
                }
            }

            ?>
        </div>
    </div>

<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
