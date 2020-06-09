<?php

return [
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    'Security' => [
        'salt' => env('SECURITY_SALT', '__SALT__'),
    ],

    'Datasources' => [
        'default' => [
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'root',
            'database' => 'cake'
            
        ],
        'test' => [
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'root',
            'database' => 'cake_test',
        ],
    ],
];
