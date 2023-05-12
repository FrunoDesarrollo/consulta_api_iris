<?php
declare(strict_types=1);

// Construye la clase ConsultarIris en el objeto $api_iris
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'iniciar_clase.php';

// Le indicamos los parámetros de a dónde se va a realizar la consulta.
$api_iris->set("CR", "organizacion_1");


//TODO: se pasa el código del distrito llamado "Santa Eulalia" (20507).
$codigo_distrito = "20507";


// TODO: La siguiente función es para que el nombre de la tienda sea diferente cada vez que se accede a esta página. Solo es para la demostración.
function str_rand(int $length = 10): string
{
    $length = ($length < 4) ? 4 : $length;
    return bin2hex(random_bytes(($length - ($length % 2)) / 2));
}

$random = str_rand();


// crear la dirección:
$direccion = [
    "nombre" => substr("$random Tienda el gabi nete", 0, 40), // Cada dirección debe ser un nombre significativo y único.
    "detalle" => substr("400 metros sur del palo de mango", 0, 200), // Todas las señas e indicaciones necesarias para encontrar el lugar.
    "código_distrito" => $codigo_distrito, // El código del distrito al que pertenece la dirección.
    "teléfono1" => substr("85215465", 10), // Un teléfono asociado a la dirección.
    "teléfono2" => substr("952364549000000", 10), // Otro teléfono asociado a la dirección.
    "correo" => substr("alguien@tiendaxqa.com", 60), // Un correo electrónico asociado con la dirección.
    "referencia" => substr("CODE_001", 0, 40), // Este campo es completamente libre para el cliente que registra la dirección. Un uso frecuente es usar el identificador correspondiente a la dirección en los sistemas internos del cliente.
];


$laDireccion = $api_iris->crearDireccion($direccion);

// Si hubo errores durante la consulta los guardamos en un log:
if (null !== $api_iris->log) {
    error_log("Iris, error {$laDireccion["code"]}: $api_iris->log");

    if ($configuracion_iris["iris_modo_pruebas"]) {
        die($api_iris->log);
    }
}

if (0 === $laDireccion["status"]) {
    die("No fue posible realizar la acción solicitada - {$laDireccion["message"]}");
}

// Imprimir el resultado:

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

    <h2 class="pb-2 border-bottom">Crear direcci&oacute;n</h2>
    <div class="row g-4 py-5 row-cols-1">
        <div class="col">
            <p>El <b>id</b> de la dirección es:</p>
            <?php
            pretty_print($laDireccion["message"]["id"]);
            ?>
        </div>
        <div class="col">
            <p>Como respuesta se recibe la estructura completa de la direcci&oacute;n creada:</p>
            <?php
            pretty_print($laDireccion["message"]);
            ?>
        </div>
    </div>

<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
