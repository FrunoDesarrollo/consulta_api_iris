<?php
declare(strict_types=1);

// Construye la clase ConsultarIris en el objeto $api_iris
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'iniciar_clase.php';

function formatoPagina(array &$i): void
{
    unset($i["creado_por_usuario_correo"]);
    $i["id"] = '<span style="font-weight: 700;">' . $i["id"] . '</span>';

    $i["fecha_creación"] = str_ireplace("T", " ", substr($i["fecha_creación"], 0, 19));
}

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
?>

    <h2 class="pb-2 border-bottom">Lista de direcciones</h2>
    <div class="row g-4 py-5 row-cols-1">
        <div class="col">
            <?php

            // Ejemplo básico de como usar la paginación:

            $sin_paginacion = false;

            while (false === $sin_paginacion) {

                if (isset($lista_direcciones['message']['página'])) {

                    if (false === empty($lista_direcciones['message']['paquetes'])) {
                        foreach ($lista_direcciones['message']['paquetes'] as $i) {
                            formatoPagina($i);
                            pretty_print($i);
                            echo "<hr>";
                        }
                    } else {
                        if (is_array($lista_direcciones['message'])) {
                            foreach ($lista_direcciones['message'] as &$i) {
                                formatoPagina($i);
                            }
                        }
                        pretty_print($lista_direcciones['message']);
                    }

                    // Cuota de tiempo para que no se active el "rate limit" de Iris:
                    sleep(10);

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
                        foreach ($lista_direcciones['message'] as &$i) {
                            formatoPagina($i);
                        }
                    }
                    pretty_print($lista_direcciones['message']);
                }
            }

            ?>
        </div>
    </div>

<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
