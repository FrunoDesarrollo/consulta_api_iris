<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');
date_default_timezone_set('America/Costa_Rica');
setlocale(LC_ALL, 'es_CR.UTF8');

use IFR\Logistica\ConsultarIris;


require __DIR__ . DIRECTORY_SEPARATOR . 'ConsultarIris.php';


/** @var array $configuracion_iris */
$configuracion_iris = require __DIR__ . DIRECTORY_SEPARATOR . 'configuracion.php';

if ($configuracion_iris["iris_modo_pruebas"]) {
    $debugConfig = [E_ALL, "1"];
} else {
    $debugConfig = [0, "0"];
}

error_reporting($debugConfig[0]);
ini_set('display_errors', $debugConfig[1]);


$api_iris = new ConsultarIris($configuracion_iris["iris_api"], $configuracion_iris["iris_modo_pruebas"], false);


// NOTA:
// Las siguientes funciones son para la demostración.

function pretty_print(mixed $value, bool $is_child = false): void
{
    if (is_string($value)) {
        echo '<head><meta charset="utf-8"><style>body,html,table{font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"}table{font-size:15px;margin:0;padding:0;border-spacing:0;border-collapse:collapse;background:#fff}td,th{border:1px solid #ddd;text-align:left;padding:8px}table tr:nth-child(even){background:#e7e7e7}</style></head><table><tbody><tr><th>'
            . convertToHtmlEntities($value, 'auto') . '</th></tbody></table>';
    }
    if (is_object($value)) {
        $value = (array)$value;
    }
    if (is_array($value)) {
        $even = "";
        if (false === $is_child) {
            echo '<head><meta charset="utf-8"><style>body,html,table{font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"}table{font-size:15px;margin:0;padding:0;border-spacing:0;border-collapse:collapse;background:#fff}td,th{border:1px solid #ddd;text-align:left;padding:8px}table.is_child tr:nth-child(even){background:#e7e7e7}</style></head>';
        } else {
            $even = "is_child";
        }
        echo "<table class='$even'><tbody>";

        foreach ($value as $key => $val) {
            if (is_object($val)) {
                $val = (array)$val;
            }
            if (is_string($key)) {
                echo '<tr><th>' . convertToHtmlEntities($key) . '</th><td>';
            } else {
                echo '<tr><td>';
            }

            if (is_array($val)) {
                pretty_print($val, true);
            } else {
                echo convertToHtmlEntities((string)$val);
            }
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }
}

function convertToHtmlEntities(string $string, ?string $from_encoding = 'UTF-8', int $flags = ENT_QUOTES, bool $encode_all = false, bool $double_encode = false): string
{
    $to_encoding = ini_get("default_charset");

    $from_encoding = null === $from_encoding ? mb_detect_encoding($string, "auto") : $from_encoding;

    $string = mb_convert_encoding($string, $to_encoding, $from_encoding);
    $string = htmlentities($string, $flags | ENT_SUBSTITUTE | ENT_HTML401, $to_encoding, $double_encode);

    if ($encode_all) {
        return strToHtmlDecimal($string, $to_encoding);
    }

    return strToHtmlDecimal(htmlspecialchars_decode($string, ENT_NOQUOTES | ENT_SUBSTITUTE), $to_encoding);
}

function strToHtmlDecimal(string $string, ?string $encoding = 'UTF-8'): string
{
    $dec = "";
    $len = mb_strlen($string, $encoding);
    for ($i = 0; $i < $len; ++$i) {
        $str = mb_substr($string, $i, 1, $encoding);
        $code = mb_ord($str, $encoding);
        $dec .= ($code > 128) ? "&#$code;" : $str;
    }
    return $dec;
}
