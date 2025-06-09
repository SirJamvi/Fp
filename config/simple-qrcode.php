<?php

return [
    'driver' => 'gd', // Langsung ubah ke 'gd' di sini
    'error_correction' => 'L',
    'size' => 5,
    'margin' => 2,
    'format' => 'png',
    'round_block_size' => true,
    'style' => 'square',
    'colors' => [
        'background' => [
            'red' => 255,
            'green' => 255,
            'blue' => 255,
            'alpha' => 100,
        ],
        'fill' => [
            'red' => 0,
            'green' => 0,
            'blue' => 0,
            'alpha' => 100,
        ],
    ],
    'logo' => [
        'path' => null,
        'size' => 0.2,
        'image_driver' => null,
    ],
];