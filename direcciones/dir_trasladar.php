<?php
declare(strict_types=1);

/**
 * Página para trasladar todas las direcciones de "organizacion_1" a "organizacion_2".
 */

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
    die("No fue posible realizar la acción solicitada - {$lista_direcciones["message"]}");
}

// Imprimir el resultado:

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';


function trasladar($direccion): void
{
    sleep(5);

    global $api_iris, $configuracion_iris;

    $api_iris->set("CR", "organizacion_2");

    $direccion1 = [
        "nombre" => (string)$direccion["nombre"],
        "detalle" => (string)$direccion["detalle"],
        "código_distrito" => (string)$direccion["código_distrito"],
        "teléfono1" => (string)$direccion["teléfono1"],
        "teléfono2" => (string)$direccion["teléfono2"],
        "correo" => (string)$direccion["correo"],
        "referencia" => (string)$direccion["referencia"],
    ];

    $laDireccion = $api_iris->crearDireccion($direccion1);

    if (null !== $api_iris->log) {
        error_log("Iris, error {$laDireccion["code"]}: $api_iris->log");

        if ($configuracion_iris["iris_modo_pruebas"]) {
            die($api_iris->log);
        }
    }

    if (0 === $laDireccion["status"]) {
        die("No fue posible realizar la acción solicitada - {$laDireccion["message"]}");
    }

    echo '  <div class="row g-4 py-5 row-cols-1">
        <div class="col">
            <p>El <b>id</b> de la dirección es:</p>';

    pretty_print($laDireccion["message"]["id"]);

    echo '</div>
        <div class="col">
            <p>Como respuesta se recibe la estructura completa de la direcci&oacute;n creada:</p>';
    pretty_print($laDireccion["message"]);
    echo '</div>
    </div>';

}


?>

    <h2 class="pb-2 border-bottom">Lista de direcciones</h2>
    <div class="row g-4 py-5 row-cols-1">
        <div class="col">
            <?php

            // Ejemplo básico de como usar la paginación:

            $sin_paginacion = false;

            while (false === $sin_paginacion) {

                if (isset($lista_direcciones['message']['página'])) {

                    if (false === empty($lista_direcciones['message']['direcciones'])) {
                        foreach ($lista_direcciones['message']['direcciones'] as $i) {
                            //formatoPagina($i);
                            trasladar($i);
                            echo "<hr>";
                        }
                    } else {
                        if (is_array($lista_direcciones['message'])) {
                            /* foreach ($lista_direcciones['message'] as &$i) {
                                 formatoPagina($i);
                             }*/
                            trasladar($lista_direcciones['message']);
                        }
                    }

                    // Cuota de tiempo para que no se active el "rate limit" de Iris:
                    sleep(5);

                    $lista_direcciones = $api_iris->listarDirecciones(["pág_despues_de" => $lista_direcciones['message']['página']]);

                    if (null !== $api_iris->log) {
                        error_log("Iris, error {$lista_direcciones["code"]}: $api_iris->log");

                        if ($configuracion_iris["iris_modo_pruebas"]) {
                            die($api_iris->log);
                        }
                    }

                } else {
                    $sin_paginacion = true;
                    if (is_array($lista_direcciones['message'])) {
                        /*foreach ($lista_direcciones['message'] as &$i) {
                            formatoPagina($i);
                        }*/

                        trasladar($lista_direcciones['message']);
                    }
                }
            }

            ?>
        </div>
    </div>

<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
