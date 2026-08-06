<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'maniobras'),

    'connections' => [

        'maniobras' => [
            'driver' => env('DB_DRIVER', 'sqlsrv'),
            'host' => env('DB_SAP_HOST', '127.0.0.1'),
            'port' => env('DB_SAP_PORT', '1433'),
            'database' => env('DB_SAP_DATABASE', 'Maniobras'),
            'username' => env('DB_SAP_USERNAME', ''),
            'password' => env('DB_SAP_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,


        ],

        'telescope' => [
            'driver' => 'sqlite',
            'database' => database_path('telescope.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],

    ],

    'migrations' => 'migrations',

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env(
                'REDIS_PREFIX',
                Str::slug(env('APP_NAME', 'laravel'), '_')
                    . '_database_'
            ),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];