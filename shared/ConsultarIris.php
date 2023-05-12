<?php
declare(strict_types=1);

namespace IFR\Logistica;

use DateTime;

/**
 * Clase para comunicarse con la API de logística Iris.
 *
 * @package    API
 * @subpackage Logistica
 * @docs https://docs.api.logistica.fruno.com/
 */
class ConsultarIris
{
    /**
     * @var string|null
     */
    public ?string $log = null;

    private string $CUENTA_API;
    private string $LLAVE_API;
    private string $URL_BASE;

    /**
     * Constructor.
     *
     * @param array $CONFIG_IRIS Configuración de Iris que viene desde el archivo de configuración.
     * @param bool $MODO_PRUEBAS Por default es <b>false</b> (producción).
     * @param bool $REGISTRAR_LOG_STATUS_OK Por default es <b>false</b>. True si se desea registrar en el log los status http 2xx.
     */
    public function __construct(
        private readonly array $CONFIG_IRIS,
        private readonly bool  $MODO_PRUEBAS = false,
        private readonly bool  $REGISTRAR_LOG_STATUS_OK = false
    )
    {
    }

    /**
     * Se agrega el país y el índice de organización a la que se harán las consultas.
     * <b>Se asume</b> que la configuración tiene el formato correcto.
     * @param string $indice_de_pais
     * @param string $indice_de_organizacion
     */
    public function set(string $indice_de_pais, string $indice_de_organizacion): void
    {
        $es_modo_pruebas = ($this->MODO_PRUEBAS) ? 1 : 0;
        $this->URL_BASE = $this->CONFIG_IRIS[$indice_de_pais]["cliente"][$indice_de_organizacion][$es_modo_pruebas]["host"];
        $this->CUENTA_API = $this->CONFIG_IRIS[$indice_de_pais]["cliente"][$indice_de_organizacion][$es_modo_pruebas]["cuentaApi"];
        $this->LLAVE_API = $this->CONFIG_IRIS[$indice_de_pais]["cliente"][$indice_de_organizacion][$es_modo_pruebas]["llaveApi"];
    }

    /**
     * @param mixed $mixed
     * @return mixed mixed (array|false|string|int)
     */
    private function utf8ize(mixed $mixed): mixed
    {
        if (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "auto");
        }
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                unset($mixed[$key]);
                $mixed[$this->utf8ize($key)] = $this->utf8ize($value);
            }
        }

        return $mixed;
    }

    private function validaError400(string $body, array $id_distritos)
    {
        if (false === $this->MODO_PRUEBAS) {
            return "Se encontraron datos inválidos";
        }

        $tmp_body = json_decode($body, true);

        if (isset($tmp_body["datos-inválidos"])) {
            $hilera = "";
            foreach ($tmp_body["datos-inválidos"] as $i) {
                $tmp_error = trim($i["error"], ".");
                if (0 === strcasecmp("El distrito no cuenta con servicio", $tmp_error)) {
                    if (0 === strcasecmp("id_dirección_fuente", $i["nombre"])) {
                        $hilera .= "El distrito de procedencia ($id_distritos[0]) no cuenta con servicio, ";
                    }
                    if (0 === strcasecmp("id_dirección_destino", $i["nombre"])) {
                        $hilera .= "El distrito de destino ($id_distritos[1]) no cuenta con servicio, ";
                    }
                } elseif (
                    0 === strcasecmp("El paquete ya no se encuentra en un estado en el que pueda ser cancelado", $tmp_error)
                ) {
                    $hilera .= "El paquete ya no se encuentra en un estado en el que pueda ser cancelado, ";
                } else {
                    $hilera .= "{$i["nombre"]}: $tmp_error, ";
                }
            }
            unset($tmp_body);

            $hilera = trim($hilera, ", ");

            return mb_strtoupper(mb_substr($hilera, 0, 1), "UTF-8") . mb_strtolower(
                    mb_substr($hilera, 1), "UTF-8"
                );
        }

        if (isset($tmp_body["title"])) {
            return $tmp_body["title"];
        }

        return "Se encontraron datos inválidos";
    }

    /**
     * Llamar al API de logística.
     *
     * @param string $ruta ejemplo: '/v1/provincias'.
     * @param string $metodo [opcional] por default es 'GET'. ['GET', 'POST', 'PUT', 'DELETE']. Se asume que el método es soportado por la API.
     * @param mixed $cuerpo [opcional] array asociativo, ejemplo: ['foo'=>'bar']. O puede ser un json string, ejemplo: {"foo":"bar"} (se asume que tiene un formato json válido ).
     * @param string|null $accept_header Por default es: null.
     * @param string $query_string Parámetros (query string). Debe incluir el ? inicial, por ej: '?fecha=2021-12-14'
     * @param array $id_distritos ["fuente", "destino"] Por default está vacío: ["", ""].
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (array|string), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     * @see https://curl.se/
     * @noinspection PhpSameParameterValueInspection
     */
    private function run(string $ruta, string $metodo = 'GET', mixed $cuerpo = null, string $accept_header = null, string $query_string = '', array $id_distritos = ["", ""]): array
    {
        $this->log = null;
        $fechaHora = (new DateTime())->format(DATE_RFC3339_EXTENDED);

        $headers = ['x-fr-hora:' . $fechaHora];

        if (is_string($accept_header) && false === empty($accept_header)) {
            //application/pdf,image/png,image/jpg,image/jpeg
            //image/*
            $headers[] = "Accept: $accept_header";
        } else {
            $headers[]
                = "Accept: application/json,application/vnd.api+json,application/problem+json";
        }

        $strCuerpo = '';

        if (false === empty($cuerpo)
            && in_array(
                $metodo, ['POST', 'PUT'], true
            )
        ) {
            if (is_array($cuerpo)) {

                $strCuerpo = json_encode($this->utf8ize($cuerpo),
                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_NUMERIC_CHECK
                );

                if (JSON_ERROR_NONE !== json_last_error()) {
                    return [
                        "status" => 0,
                        "message" => '{"type":"local","title":"No se pudo convertir el cuerpo del mensaje al formato JSON.' . ($this->MODO_PRUEBAS ? " " . json_last_error_msg() : "") . '"}',
                        "code" => 500,
                        "content_type" => 'text/plain; charset=utf-8',
                        "rate_limit" => null,
                    ];
                }
            } else {
                $strCuerpo = $cuerpo;
            }
            $headers[] = "Content-type: application/json; charset=utf-8";
        }

        $ch = curl_init("https://" . $this->URL_BASE . $ruta . $query_string);

        if (false === $ch) {
            return [
                "status" => 0,
                "message" => '{"type":"local","title":"Error al iniciar curl"}',
                "code" => 500,
                "content_type" => 'text/plain; charset=utf-8',
                "rate_limit" => null,
            ];
        }

        if (false === empty($strCuerpo)
            && false === curl_setopt($ch, CURLOPT_POSTFIELDS, $strCuerpo)
        ) {
            curl_close($ch);

            return [
                "status" => 0,
                "message" => '{"type":"local","title":"Error al establecer el body en curl"}',
                "code" => 500,
                "content_type" => 'text/plain; charset=utf-8',
                "rate_limit" => null,
            ];
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        unset($headers);

        //
        // Abre un stream temporal con el fin de recopilar información detallada
        // de la conexión generada por cURL. Esto es útil para rastrear
        // problemas de conectividad relacionados con el certificado SSL.
        //
        $curl_stderr = fopen('php://temp', 'w+');

        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYPEER => false == $this->MODO_PRUEBAS,
            CURLOPT_SSL_VERIFYHOST => ($this->MODO_PRUEBAS ? 0 : 2),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 300, //5 min
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FAILONERROR => false,
            CURLOPT_USERPWD => $this->CUENTA_API . ":" . base64_encode(
                    hash_hmac(
                        'sha256', "$metodo$ruta$fechaHora$strCuerpo", $this->LLAVE_API,
                        true
                    )
                ),
            CURLOPT_VERBOSE => true,
            CURLOPT_STDERR => $curl_stderr
        ]);

        unset($fechaHora);

        $response_body = curl_exec($ch);

        if (false === $response_body || CURLE_OK < curl_errno($ch)) {

            rewind($curl_stderr);
            $this->log = stream_get_contents($curl_stderr, 4096);
            @fclose($curl_stderr);

            $err = curl_error($ch);
            @curl_close($ch);

            return [
                "status" => 0,
                "message" => '{"type":"local","title":"Error de ejecución.' . ($this->MODO_PRUEBAS ? trim(" $err") : "") . '"}',
                "code" => 500,
                "content_type" => 'text/plain; charset=utf-8',
                "rate_limit" => null,
            ];
        }

        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        fclose($curl_stderr);
        curl_close($ch);

        if (false === $response_code) {
            $response_code = 500;
        }

        if (false === $content_type) {
            $content_type = 'application/json; charset=utf-8';
        }

        /*
         * Éxito:
         * GET     200
         * DELETE  200
         * POST    201
         * PUT     204
        */
        $exito = in_array($response_code, [200, 201, 204], true);

        if (false === $exito || $this->REGISTRAR_LOG_STATUS_OK) {
            $ip_server = $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'];

            $this->log = "status: $response_code | method: $metodo | url: https://" . $this->URL_BASE . $ruta . $query_string . " | ip: $ip_server | json enviado: $strCuerpo | json recibido: " . $response_body;
        }

        if ($exito) {
            return [
                "status" => 1,
                "message" => json_decode($response_body, true),
                "code" => $response_code,
                "content_type" => $content_type,
                "rate_limit" => null,
            ];
        }

        return [
            "status" => 0,
            "message" => match ($response_code) {
                102 => "El sistema ha recibido y está procesando la solicitud, pero aún no hay respuesta disponible",
                400 => $this->validaError400($response_body, $id_distritos),
                401 => "No autorizado",
                403 => "Acceso denegado a la acción solicitada",
                404 => "Recurso no encontrado",
                429 => "En este momento el sistema está ocupado, por favor intente de nuevo en unos segundos",
                500 => "Error interno del sistema",
                502, 503 => "Temporalmente fuera de servicio, intente más tarde",
                default => "Error $response_code",
            },
            "code" => $response_code,
            "content_type" => 'text/plain; charset=utf-8',
            "rate_limit" => null,
        ];
    }

    /**
     * @param string $id_direccion
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => string, "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     */
    public function borrarDireccion(string $id_direccion): array
    {
        return $this->run("/v1/direcciones/$id_direccion", "DELETE");
    }

    /**
     * @param string $guia
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => string, "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     */
    public function cancelarPaquete(string $guia): array
    {
        return $this->run("/v1/paquetes/$guia/cancelar", "PUT");
    }

    /**
     * Retorna el estado del paquete.
     *
     * @param string $guia
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (array|string), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     */
    public function consultarEstadoPaquete(string $guia): array
    {
        $arr = $this->run("/v1/paquetes/$guia");
        if (1 === $arr["status"]) {
            $temp_arr = [];
            if (isset($arr["message"]["estado"])) {
                $temp_arr["estado"] = $arr["message"]["estado"];
            }
            if (isset($arr["message"]["descripción_estado"])) {
                $temp_arr["descripción_estado"] = $arr["message"]["descripción_estado"];
            }
            $arr["message"] = $temp_arr;
            $arr["content_type"] = 'application/json; charset=utf-8';
        }

        return $arr;
    }

    /**
     * Retorna un array con la información de la dirección consultada.
     *
     * @param string $direccion_id
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (array|string), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     */
    public function consultarDireccion(string $direccion_id): array
    {
        return $this->run("/v1/direcciones/$direccion_id");
    }

    /**
     * Retorna un array con la información del paquete consultado.
     *
     * @param string $guia
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (array|string), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     */
    public function consultarPaquete(string $guia): array
    {
        return $this->run("/v1/paquetes/$guia");
    }

    /**
     * Retorna un array con la información del paquete creado.
     * ```php
     * //Ejemplo de uso:
     * $cuerpo = [
     * "urgente" => false, "alto" => 1, "ancho" => 1, "longitud" => 1, "peso_neto" => 1,
     * "id_dirección_fuente" => "1d86815d-7a10-4f93-8b80-adbf9ed785a0",
     * "id_dirección_destino" => "a72266cc-ed3a-4042-926b-463ce1db545f",
     * "descripción" => "Pedido tienda XYZ",
     * "entregar_a" => "Hernán Soto",
     * ];
     * $paquete = $app->iris->crearPaquete($cuerpo);
     * ```
     *
     * @param array $paquete
     * @param bool $get_solo_guia Default false.
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (array|string), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     * @link https://docs.api.logistica.fruno.com/#crear-2 Parámetros para crear paquete.
     *
     */
    public function crearPaquete(array $paquete, bool $get_solo_guia = false): array
    {
        $arr = $this->run("/v1/paquetes", "POST", $paquete, null, '', [($paquete["id_dirección_fuente"] ?? ''), ($paquete["id_dirección_destino"] ?? '')]);

        if (1 === $arr["status"]) {
            if (empty($arr["message"]["guía"])) {
                return [
                    "status" => 0,
                    "message" => "Guía vacía",
                    "code" => 204,
                    "content_type" => 'text/plain; charset=utf-8',
                    "rate_limit" => null,
                ];
            }
            if ($get_solo_guia) {
                $arr["message"] = $arr["message"]["guía"];
                $arr["content_type"] = 'text/plain; charset=utf-8';
            }
        }

        return $arr;
    }

    /**
     * Retorna un array con la información de la dirección creada.
     *
     * ```php
     * //Ejemplo de uso:
     * $direccion = $app->iris->crearDireccion([
     * "nombre" => "Tienda ABC",
     * "detalle" => "200 norte",
     * "código_distrito" => "1604",
     * "teléfono1" => "123456789",
     * "correo" => "correo@email.com",
     * "referencia" => "NR01",
     * ]);
     * ```
     *
     * @param array $direccion
     * @param bool $get_solo_id Default false.
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (array|string), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     * @link https://docs.api.logistica.fruno.com/#crear Parámetros para crear dirección.
     *
     */
    public function crearDireccion(array $direccion, bool $get_solo_id = false): array
    {
        $strCuerpo = json_encode($this->utf8ize($direccion),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return [
                "status" => 0,
                "message" => '{"type":"local","title":"No se pudo convertir el cuerpo del mensaje al formato JSON.' . ($this->MODO_PRUEBAS ? " " . json_last_error_msg() : "") . '"}',
                "code" => 500,
                "content_type" => 'text/plain; charset=utf-8',
                "rate_limit" => null,
            ];
        }

        $arr = $this->run("/v1/direcciones", "POST", $strCuerpo);
        if ($get_solo_id && 1 === $arr["status"]) {
            $arr["message"] = $arr["message"]["id"];
            $arr["content_type"] = 'text/plain; charset=utf-8';
        }

        return $arr;
    }

    /**
     * ```php
     * //Ejemplo de uso:
     * $direccion_cambiada = $app->iris->cambiarDireccion(
     * "2e094b26-fc0e-44c4-8581-c2d587207a9e",
     * [
     * "nombre" => "Tienda XYZ",
     * "detalle" => "400 sur",
     * ]
     * );
     * ```
     *
     * @param string $id_direccion
     * @param array $cambios
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => string|null, "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     */
    public function cambiarDireccion(string $id_direccion, array $cambios): array
    {
        $strCuerpo = json_encode($this->utf8ize($cambios),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return [
                "status" => 0,
                "message" => '{"type":"local","title":"No se pudo convertir el cuerpo del mensaje al formato JSON.' . ($this->MODO_PRUEBAS ? " " . json_last_error_msg() : "") . '"}',
                "code" => 500,
                "content_type" => 'text/plain; charset=utf-8',
                "rate_limit" => null,
            ];
        }

        return $this->run("/v1/direcciones/$id_direccion", "PUT", $strCuerpo);
    }

    /**
     * ```php
     * //Ejemplo de uso:
     * $provincias = $app->iris->obtenerTerritorios("", false);
     * $cantones   = $app->iris->obtenerTerritorios("/{provincia_id}/cantones", false);
     * $distritos  = $app->iris->obtenerTerritorios("/{provincia_id}/cantones/{canton_id}/distritos", false);
     * ```
     * @param string $ruta
     * @param bool $get_como_json Default es false.
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (string (json)|array), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     */
    private function obtenerTerritorios(string $ruta, bool $get_como_json = false): array
    {
        $arr = $this->run("/v1/provincias$ruta");
        if (1 === $arr["status"]) {
            $arrLista = array_map(static function ($v) {
                $v['nombre'] = mb_convert_case($v['nombre'], MB_CASE_TITLE, "UTF-8");

                return $v;
            }, $arr["message"]);

            $arr["message"] = ($get_como_json) ? json_encode($arrLista,
                JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) : $arrLista;
        }

        return $arr;
    }

    /**
     * ```php
     * //Ejemplo de uso:
     * $provincias = $app->iris->obtenerProvincias();
     * ```
     * @param bool $get_como_json Default es false.
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (string (json)|array), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     */
    public function obtenerProvincias(bool $get_como_json = false): array
    {
        return $this->obtenerTerritorios("", $get_como_json);
    }

    /**
     * ```php
     * //Ejemplo de uso:
     * $cantones = $app->iris->obtenerCantones("{provincia_id}");
     * ```
     * @param string $provincia_id
     * @param bool $get_como_json Default es false.
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (string (json)|array), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     */
    public function obtenerCantones(string $provincia_id, bool $get_como_json = false): array
    {
        return $this->obtenerTerritorios("/$provincia_id/cantones", $get_como_json);
    }

    /**
     * ```php
     * //Ejemplo de uso:
     * $distritos = $app->iris->obtenerDistritos("{provincia_id}", "{canton_id}");
     * ```
     * @param string $provincia_id
     * @param string $canton_id
     * @param bool $get_como_json Default es false.
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (string (json)|array), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     */
    public function obtenerDistritos(string $provincia_id, string $canton_id, bool $get_como_json = false): array
    {
        return $this->obtenerTerritorios("/$provincia_id/cantones/$canton_id/distritos", $get_como_json);
    }

    private function listar(string $ruta, array $filtros = []): array
    {
        $fields = "";
        if (false === empty($filtros)) {
            $fields = "?";
            foreach ($filtros as $key => $filtro) {
                $fields .= "$key=" . urlencode($filtro);
                $fields .= "&";
            }
            $fields = rtrim($fields, "&");
        }

        $arr = $this->run("/v1/$ruta", 'GET', null, null, $fields);

        if (1 === $arr["status"]) {
            $arr["message"] = $arr["message"][$ruta];
        }

        return $arr;
    }

    /**
     * Retorna un array con la información de los paquetes.
     *
     * ```php
     * //Ejemplo de uso:
     * $resultado = $api->listarPaquetes([
     *   "pág_despues_de" => "AAAAAAAA",
     *   "fecha" => "YYYY-MM-DD",
     *   "estado" => "porrecolectar",
     * ]);
     * ```
     *
     * @param array $filtros
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (array|string), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     * @link https://docs.api.logistica.fruno.com/#lista-3 Lista de paquetes
     *
     */
    public function listarPaquetes(array $filtros = []): array
    {
        return $this->listar("paquetes", $filtros);
    }

    /**
     * Retorna un array con la información de las direcciones.
     *
     * ```php
     * //Ejemplo de uso:
     * $resultado = $api->listarDirecciones();
     * $resultado = $api->listarDirecciones([
     *   "pág_despues_de" => "AAAAAAAA"
     * ]);
     * ```
     *
     * @return array <b>array</b>. [ "status" => (int (1 = success, 0 = error)), "message" => (array|string), "code" => (int), "content_type" => (string), "rate_limit"   => (array|null) ]
     * @link https://docs.api.logistica.fruno.com/#lista-2 Lista de direcciones
     *
     */
    public function listarDirecciones(array $filtros = []): array
    {
        return $this->listar("direcciones", $filtros);
    }

}
