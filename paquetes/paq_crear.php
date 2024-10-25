<?php
declare(strict_types=1);

// Construye la clase ConsultarIris en el objeto $api_iris
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'iniciar_clase.php';

// Le indicamos los parámetros de a dónde se va a realizar la consulta.
$api_iris->set("CR", "organizacion_1");


// TODO cambiar $id_direccion_fuente y $id_direccion_destino por los datos reales:
$id_direccion_fuente = '';

$id_direccion_destino = "cf821259-c372-40c4-91a2-b4d92365df44";


$entregar_a = "Scott Hahn";
$descripcion_de_paquete = "Pedido 'Rome Sweet Home'- ISBN-10: 9780898704785 Tapa blanda";
$observaciones_de_paquete = "Estoy creando un paquete de demostración";
$notificar_a = "recepcion4@email.com";

// crear la dirección:
$paquete = [
    "urgente" => false,
    "alto" => 1,
    "ancho" => 1,
    "longitud" => 1,
    "peso_neto" => 1,
    "id_dirección_fuente" => $id_direccion_fuente,
    "id_dirección_destino" => $id_direccion_destino,
    "descripción" => substr($descripcion_de_paquete, 0, 150),
    "observaciones" => substr($observaciones_de_paquete, 0, 2000), // Cualquier texto que se quiera asociar al paquete.
    "entregar_a" => substr($entregar_a, 0, 40), // Nombre de la persona que recibe el paquete en la dirección destino.
    "en_nombre_de_correo" => substr($notificar_a, 60), // Correo electrónico de la persona responsable en su empresa de resolver asuntos relacionados con el paquete.
];


$elPaquete = $api_iris->crearPaquete($paquete);

// Si hubo errores durante la consulta los guardamos en un log:
if (null !== $api_iris->log) {
    error_log("Iris, error {$elPaquete["code"]}: $api_iris->log");

    if ($configuracion_iris["iris_modo_pruebas"]) {
        die($api_iris->log);
    }
}

if (0 === $elPaquete["status"]) {
    die("No fue posible realizar la acción solicitada. {$elPaquete["code"]} - {$elPaquete["message"]}");
}

// Imprimir el resultado:

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

    <h2 class="pb-2 border-bottom">Crear paquete</h2>
    <div class="row g-4 py-5 row-cols-1">
        <div class="col">
            <p>La <b>gu&iacute;a</b> del paquete es:</p>
            <?php
            pretty_print($elPaquete["message"]["guía"]);
            ?>
        </div>
        <div class="col">
            <p>Como respuesta se recibe la estructura completa del paquete creado:</p>
            <?php
            pretty_print($elPaquete["message"]);
            ?>
        </div>
    </div>

<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
