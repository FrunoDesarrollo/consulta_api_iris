<?php
declare(strict_types=1);

return [
    "iris_modo_pruebas" => true,
    "iris_api" => [
        "CR" => [ //Costa Rica
            "link_busqueda" => "https://logistica.fruno.com/p/buscarguia?guia=", // Solo para producción.
            "cliente" => [
                "organizacion_1" => [
                    [ //producción
                        "host" => "api.logistica.fruno.com",
                        "cuentaApi" => "aaaaaaaaaaa00000000000aaaaaaaaaaa0000000000",
                        "llaveApi" => "fr-produccion-kkkkk"
                    ],
                    [ //pruebas
                        "host" => "pruebas.api.logistica.fruno.com",
                        "cuentaApi" => "aaaaaaaaaaa00000000000aaaaaaaaaaa0000000000",
                        "llaveApi" => "fr-pruebas-kkkkk"
                    ]
                ],
                "organizacion_2" => [
                    [ //producción
                        "host" => "api.logistica.fruno.com",
                        "cuentaApi" => "bbbbbbbbbbb00000000000bbbbbbbbbbb0000000000",
                        "llaveApi" => "fr-produccion-nnnnn"
                    ],
                    [ //pruebas
                        "host" => "pruebas.api.logistica.fruno.com",
                        "cuentaApi" => "bbbbbbbbbbb00000000000bbbbbbbbbbb0000000000",
                        "llaveApi" => "fr-pruebas-nnnnn"
                    ]
                ]
            ]
        ]
    ]
];
