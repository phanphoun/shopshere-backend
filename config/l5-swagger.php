<?php

return [
    'api' => [
        /*
        |--------------------------------------------------------------------------
        | Edit to set the api's title
        |--------------------------------------------------------------------------
        */
        'title' => 'ShopSphere API Documentation',
    ],

    'routes' => [
        /*
        |--------------------------------------------------------------------------
        | Route for accessing api documentation interface
        |--------------------------------------------------------------------------
        */
        'api' => 'documentation',
    ],

    'paths' => [
        /*
        |--------------------------------------------------------------------------
        | Absolute path to location where parsed annotations will be stored
        |--------------------------------------------------------------------------
        */
        'docs' => storage_path('api-docs'),

        /*
        |--------------------------------------------------------------------------
        | Absolute path to directory containing the swagger annotations are stored.
        |--------------------------------------------------------------------------
        */
        'annotations' => [
            base_path('app/Annotations'),
            base_path('app/Http/Controllers'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Absolute path to directory where the export tool saves the swagger specs.
        |--------------------------------------------------------------------------
        */
        'exports' => null,

        /*
        |--------------------------------------------------------------------------
        | Edit to set the swagger UI base path
        |--------------------------------------------------------------------------
        */
        'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),

        /*
        |--------------------------------------------------------------------------
        | JSON output file name
        |--------------------------------------------------------------------------
        */
        'docs_json' => 'api-docs.json',

        /*
        |--------------------------------------------------------------------------
        | YAML output file name
        |--------------------------------------------------------------------------
        */
        'docs_yaml' => 'api-docs.yaml',

        /*
        |--------------------------------------------------------------------------
        | Custom asset path for swagger-ui (if using local assets)
        |--------------------------------------------------------------------------
        */
        'custom_asset_path' => env('L5_SWAGGER_CUSTOM_ASSET_PATH', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | API security definitions
    |--------------------------------------------------------------------------
    */
    'security' => [
        /*
        |--------------------------------------------------------------------------
        | Examples of SecurityDefinitions
        |--------------------------------------------------------------------------
        */
        'securityDefinitions' => [
            'sanctum' => [
                'type' => 'apiKey',
                'description' => 'Enter your Sanctum token (without Bearer prefix). Get it from POST /api/login or /api/register.',
                'name' => 'Authorization',
                'in' => 'header',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configure default security for all API endpoints
    |--------------------------------------------------------------------------
    */
    'default_security' => env('L5_SWAGGER_DEFAULT_SECURITY', false),

    /*
    |--------------------------------------------------------------------------
    | Set to true to always generate the docs on page load
    |--------------------------------------------------------------------------
    */
    'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', true),

    /*
    |--------------------------------------------------------------------------
    | Uncomment to add server URLs
    |--------------------------------------------------------------------------
    */
    'servers' => [
        [
            'url' => env('APP_URL', 'http://localhost:8000'),
            'description' => 'Local Development Server',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration for the JSON output
    |--------------------------------------------------------------------------
    */
    'json' => [
        'strip_base_path' => env('L5_SWAGGER_STRIP_BASE_PATH', false),
        'dump_headers' => env('L5_SWAGGER_DUMP_HEADERS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Proxy settings
    |--------------------------------------------------------------------------
    */
    'proxy' => env('L5_SWAGGER_PROXY', false),

    /*
    |--------------------------------------------------------------------------
    | Additional configuration
    |--------------------------------------------------------------------------
    */
    'additional' => [
        'info' => [
            'description' => 'ShopSphere is a production-ready e-commerce platform with a REST API built on Laravel 12.',
            'termsOfService' => '',
            'contact' => [
                'email' => '',
            ],
            'license' => [
                'name' => 'MIT',
                'url' => 'https://opensource.org/licenses/MIT',
            ],
        ],
        'externalDocs' => [
            'description' => 'ShopSphere Documentation',
            'url' => '',
        ],
    ],
];
