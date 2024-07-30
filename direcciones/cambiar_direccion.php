<?php declare(strict_types=1);

if (!empty($_POST)) {

    // Construye la clase ConsultarIris en el objeto $api_iris
    require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'iniciar_clase.php';

    // Le indicamos los parámetros de a dónde se va a realizar la consulta.
    $api_iris->set("CR", "organizacion_1");

    $_POST = array_map("sanitizeFilterString", $_POST);

    $nuevo = array_filter($_POST, static fn($key) => in_array($key, [
        "nombre", "detalle", "teléfono1", "teléfono2", "correo", "referencia"
    ]), ARRAY_FILTER_USE_KEY);

    $direccion_cambio = $api_iris->cambiarDireccion($_POST["id_direccion"], $nuevo);

// Si hubo errores durante la consulta los guardamos en un log:
    if (null !== $api_iris->log) {
        error_log("Iris, error {$direccion_cambio["code"]}: $api_iris->log");
    }

    sleep(1);

}


$direccion = [];

if (isset($_GET["id_direccion"]) && 1 === preg_match('{^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$}Di', trim($_GET["id_direccion"]))) {

    // Construye la clase ConsultarIris en el objeto $api_iris
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'iniciar_clase.php';

    // Le indicamos los parámetros de a dónde se va a realizar la consulta.
    $api_iris->set("CR", "organizacion_1");

    $direccion = $api_iris->consultarDireccion($_GET["id_direccion"]);

    // Si hubo errores durante la consulta los guardamos en un log:
    if (null !== $api_iris->log) {
        error_log("Iris, error {$direccion["code"]}: $api_iris->log");
    }
}

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';

?>

    <h1 class="display-6 text-center mb-3">Cambiar dirección</h1>

    <div class="row">
        <div class="col-12 col-md-5 mx-auto mb-3">
            <label class="form-label">ID de la dirección</label>
            <form class="input-group" action="./cambiar_direccion.php" method="get">
                <input name="id_direccion" type="text" class="form-control" placeholder="ID…" autofocus
                       value="<?= $_GET["id_direccion"] ?? "" ?>">
                <button class="btn btn-primary" type="submit">Ver</button>
            </form>
        </div>
    </div>

    <hr>

<?php if (0 === $direccion["status"]) : ?>
    <div class="row">
        <div class="col-12 col-md-7 mx-auto mb-3">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <div class="d-flex">
                    <div>
                        <div class="text-secondary"><?= $direccion["message"] ?? "" ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else :
    $direccion = $direccion["message"];
endif; ?>

<?php if (0 === $direccion_cambio["status"]) : ?>
    <div class="row">
        <div class="col-12 col-md-7 mx-auto mb-3">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <div class="d-flex">
                    <div>
                        <div class="text-secondary"><?= $direccion_cambio["message"] ?? "" ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

    <div class="row">
        <div class="col-12 col-md-7 mx-auto mb-3">
            <form action="./cambiar_direccion.php<?= (empty($_GET["id_direccion"]) ? "" : "?id_direccion={$_GET["id_direccion"]}") ?>"
                  method="post">

                <input name="id_direccion" type="hidden" class="visually-hidden"
                       value="<?= $_GET["id_direccion"] ?? "" ?>">

                <div class="mb-3">
                    <div class="input-group input-group-flat">
                              <span class="input-group-text">
                                Nombre
                              </span>
                        <input name="nombre" type="text" class="form-control ps-0"
                               value="<?= $direccion["nombre"] ?? "" ?>"
                               autocomplete="off" maxlength="40" required>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="input-group input-group-flat">
                              <span class="input-group-text">
                                Detalle
                              </span>
                        <input name="detalle" type="text" class="form-control ps-0"
                               value="<?= $direccion["detalle"] ?? "" ?>" autocomplete="off" maxlength="200" required>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="input-group input-group-flat">
                              <span class="input-group-text">
                                Teléfono 1
                              </span>
                        <input name="teléfono1" type="tel" class="form-control ps-0"
                               value="<?= $direccion["teléfono1"] ?? "" ?>" autocomplete="off" maxlength="10">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="input-group input-group-flat">
                              <span class="input-group-text">
                                Teléfono 2
                              </span>
                        <input name="teléfono2" type="tel" class="form-control ps-0"
                               value="<?= $direccion["teléfono2"] ?? "" ?>" autocomplete="off" maxlength="10">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="input-group input-group-flat">
                              <span class="input-group-text">
                                Correo electrónico
                              </span>
                        <input name="correo" type="email" class="form-control ps-0"
                               value="<?= $direccion["correo"] ?? "" ?>" autocomplete="off" maxlength="256" required>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="input-group input-group-flat">
                              <span class="input-group-text">
                                Referencia
                              </span>
                        <input name="referencia" type="text" class="form-control ps-0"
                               value="<?= $direccion["referencia"] ?? "" ?>" autocomplete="off" maxlength="40">
                    </div>
                </div>
                <div class="mb-3 text-center">
                    <button class="btn btn-primary w-25" type="submit">Actualizar</button>
                </div>

            </form>
        </div>
    </div>


<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
