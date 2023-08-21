<?php
declare(strict_types=1);

header("Content-type: application/json; charset=UTF-8");

// Construye la clase ConsultarIris en el objeto $api_iris
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'iniciar_clase.php';

// Le indicamos los parámetros de a dónde se va a realizar la consulta.
$consultar_lugar = ["CR", "organizacion_1"];
$api_iris->set($consultar_lugar[0], $consultar_lugar[1]);

$lista_direcciones = $api_iris->listarDirecciones();

// Si hubo errores durante la consulta los guardamos en un log:
if (null !== $api_iris->log) {
    error_log("Iris, error {$lista_direcciones["code"]}: $api_iris->log");

    if ($configuracion_iris["iris_modo_pruebas"]) {
        die($api_iris->log);
    }
}

if (0 === $lista_direcciones["status"]) {
    die("{\"error\":\"No fue posible realizar la acción solicitada - {$lista_direcciones["message"]}\"}");
}

// Imprimir el resultado:
            $sin_paginacion = false;

            while (false === $sin_paginacion) {

                if (isset($lista_direcciones['message']['página'])) {

                    echo json_encode($lista_direcciones['message']['direcciones'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_NUMERIC_CHECK);

                    echo ',';

                    // Cuota de tiempo para que no se active el "rate limit" de Iris:
                    sleep(4);

                    $lista_direcciones = $api_iris->listarDirecciones(["pág_despues_de" => $lista_direcciones['message']['página']]);

                    if (null !== $api_iris->log) {
                        error_log("Iris, error {$lista_direcciones["code"]}: $api_iris->log");

                        if ($configuracion_iris["iris_modo_pruebas"]) {
                            die($api_iris->log);
                        }
                    }

                } else {
                    $sin_paginacion = true;

                    echo json_encode($lista_direcciones['message']['direcciones'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_NUMERIC_CHECK);
                }
            }
