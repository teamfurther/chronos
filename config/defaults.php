<?php

return [

    'version' => '3.0',


    'access_token' => env('CHRONOS_ACCESS_TOKEN'),

    'alerts' => [
        'auto_dismiss' => 5000
    ],

    'default_image_style' => '',

    'items_per_page' => 15,
    'media_items_per_page' => 30,

    'upload_paths' => [
        [
            // E.g.: http://chronos.ro/uploads/media/{year}/{month}
            'asset_path' => env('APP_URL') . '/uploads/media/' . date('Y') . '/' . date('m'),
            // E.g.: /var/www/public/uploads/media/{year}/{month}
            'upload_path' => env('UPLOAD_URL') . 'uploads/media/' . date('Y') . '/' . date('m')
        ]
    ]

];