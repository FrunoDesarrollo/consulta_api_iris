<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');
date_default_timezone_set('America/Costa_Rica');
setlocale(LC_ALL, 'es_CR.UTF8');

use IFR\Logistica\ConsultarIris;


require __DIR__ . DIRECTORY_SEPARATOR . 'ConsultarIris.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'IrisRateLimit.php';


/** @var array $configuracion_iris */
$configuracion_iris = require __DIR__ . DIRECTORY_SEPARATOR . 'configuracion.php';

if ($configuracion_iris["iris_modo_pruebas"]) {
    $debugConfig = [E_ALL, "1"];
} else {
    $debugConfig = [0, 1];
}

error_reporting($debugConfig[0]);
ini_set('display_errors', $debugConfig[1]);


$api_iris = new ConsultarIris($configuracion_iris["iris_api"], $configuracion_iris["iris_modo_pruebas"], false);


// NOTA:
// La siguiente función es para la demostración.
function pretty_print(mixed $value, bool $is_child = false): void
{
    $main_tipo_val = gettype($value);

    if ("boolean" == $main_tipo_val) {
        $value = (true === $value) ? 'true' : 'false';
    }
    if ("NULL" == $main_tipo_val) {
        $value = 'NULL';
    }

    if (in_array($main_tipo_val, ["string", "boolean", "NULL"])) {
        echo '<head><meta charset="utf-8"><style>body,html,table{font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"}table{font-size:15px;margin:0;padding:0;border-spacing:0;border-collapse:collapse;background:#fff}td,th{border:1px solid #ddd;text-align:left;padding:8px}table tr:nth-child(even){background:#e8e8e8}</style></head><table><tbody><tr><th>'
            . $value . '</th></tbody></table>';

        return;
    }

    if ("array" == $main_tipo_val || "object" == $main_tipo_val) {
        $even = "";
        if (!$is_child) {
            echo '<head><meta charset="utf-8"><style>body,html,table{font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"}table{font-size:15px;margin:0;padding:0;border-spacing:0;border-collapse:collapse;background:#fff}td,th{border:1px solid #ddd;text-align:left;padding:8px}table.is_child tr:nth-child(even){background:#e7e7e7}</style></head>';
        } else {
            $even = 'class="is_child"';
        }
        echo "<table $even><tbody>";

        if ("object" == $main_tipo_val) {
            $value = (array)$value;
        }

        foreach ($value as $key => $val) {

            if (is_string($key) || is_numeric($key)) {
                echo "<tr><th>";
                echo $key;
                echo "</th><td>";
            } else {
                echo "<tr><td>";
            }

            $tipo_val = gettype($val);

            if ("array" == $tipo_val || "object" == $tipo_val) {
                pretty_print((array)$val, true);
            } else {
                echo match ($tipo_val) {
                    "NULL" => 'NULL',
                    "integer", "double", "string" => $val,
                    "boolean" => (true === $val) ? 'true' : 'false',
                    default => var_export($val, true),
                };
            }

            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }
}


/**
 * @param string $value
 * @return string
 * @see https://stackoverflow.com/a/76461504
 */
function sanitizeFilterString(string $value): string
{
    // Strip the tags
    $value = strip_tags($value);

    // Run the replacement for FILTER_SANITIZE_STRING
    $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE);

    // Fix that HTML entities are converted to entity numbers instead of entity name (e.g. ' -> &#34; and not ' -> &quote;)
    // https://stackoverflow.com/questions/64083440/use-php-htmlentities-to-convert-special-characters-to-their-entity-number-rather
    $value = str_replace(["&quot;", "&#039;"], ["&#34;", "&#39;"], $value);

    // Decode all entities
    return html_entity_decode($value, ENT_NOQUOTES | ENT_SUBSTITUTE);
}
