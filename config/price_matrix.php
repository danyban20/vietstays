<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Price Matrix Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the price matrix system used by superadmin
    | for setting default apartment prices.
    |
    */

    // Rounding increment in VND
    'rounding_increment' => 50_000, // round50k

    // Default watermark factor range for buildings (if no override set)
    'default_building_factor_min' => 0.96,
    'default_building_factor_max' => 1.06,

    // Type key bathroom defaults
    'pm_default_wc' => [
        'Studio' => 1,
        '1BR' => 1,
        '2BR' => 1,
        '3BR' => 2,
        '4BR' => 2,
        '5BR' => 3,
    ],

    // Types without bathroom variant
    'types_no_wc_variant' => ['Studio', '1BR'],
];
