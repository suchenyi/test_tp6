<?php

return [
    'inject' => [
        'enable'     => true,
        'namespaces' => [],
    ],
    'route'  => [
        'enable'      => true,
        'controllers' => [],
    ],
    'model'  => [
        'enable' => false,
    ],
    'ignore' => [],
    'store'  => null,//env('ANNOTATION.store', 'route'), // 缓存store
];
