<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Route Prefix
    |--------------------------------------------------------------------------
    |
    | This is the URL prefix for the ACF admin panel.
    |
    */
    'route_prefix' => 'admin/advanced-custom-fields',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to the ACF admin routes.
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Theme Preset
    |--------------------------------------------------------------------------
    |
    | The default UI theme. Options: 'bootstrap5', 'tailwind' (future), 'adminlte3'.
    |
    */
    'theme' => 'bootstrap5',

    /*
    |--------------------------------------------------------------------------
    | HTML Support
    |--------------------------------------------------------------------------
    |
    | Whether to allow safe HTML snippets in "before/after input" slots.
    | Enable with caution.
    |
    */
    'enable_html_snippets' => false,

    /*
    |--------------------------------------------------------------------------
    | Export Path
    |--------------------------------------------------------------------------
    |
    | Where to store JSON export files.
    |
    */
    'export_path' => storage_path('app/acf/definitions.json'),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Enable caching for resolved field groups and fields.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'acf_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | Whether to support multi-language custom fields.
    |
    */
    'localization' => [
        'enabled' => false,
        'fallback_to_default' => true,
    ],
];
