<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'yandex-s3' => [
            'driver' => 's3',
            'key' => env('YANDEX_S3_KEY', env('AWS_ACCESS_KEY_ID')),
            'secret' => env('YANDEX_S3_SECRET', env('AWS_SECRET_ACCESS_KEY')),
            'region' => env('YANDEX_S3_REGION', 'ru-central1'),
            'bucket' => env('YANDEX_S3_BUCKET'),
            'endpoint' => env('YANDEX_S3_ENDPOINT', 'https://storage.yandexcloud.net'),
            'url' => env('YANDEX_S3_URL'),
            'use_path_style_endpoint' => true,
            'throw' => false,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];