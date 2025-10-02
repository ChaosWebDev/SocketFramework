<?php

return [
    'type' => env("SERVER_TYPE", 'telnet'),
    'host' => env("SOCKET_HOST", "0.0.0.0"),
    'port' => env("SOCKET_PORT", 10000),
    'max' => env("MAX_CLIENTS", 5)
];