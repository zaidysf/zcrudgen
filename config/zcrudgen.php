<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Namespace Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the base namespace for all generated classes
    |
    */
    'namespace' => 'App',

    /*
    |--------------------------------------------------------------------------
    | Path Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the paths where generated files will be placed
    |
    */
    'paths' => [
        'model' => app_path('Models'),
        'controller' => app_path('Http/Controllers/API'),
        'repository' => app_path('Repositories'),
        'service' => app_path('Services'),
        'resource' => app_path('Http/Resources'),
        'request' => app_path('Http/Requests'),
        'event' => app_path('Events'),
        'listener' => app_path('Listeners'),
        'policy' => app_path('Policies'),
        'event' => app_path('Events'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication & Authorization
    |--------------------------------------------------------------------------
    |
    | Configure authentication and authorization settings
    |
    */
    'auth' => [
        'middleware' => [
            'enabled' => true,
            'default' => ['auth:sanctum'],
            'custom' => [],
        ],
        'permissions' => [
            'enabled' => true,
            'prefix' => ['create', 'read', 'update', 'delete'],
            'custom' => [],
            'model' => 'Spatie\Permission\Models\Permission',
        ],
        'roles' => [
            'enabled' => true,
            'default' => ['admin', 'user'],
            'custom' => [],
            'model' => 'Spatie\Permission\Models\Role',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API-specific settings
    |
    */
    'api' => [
        'prefix' => 'api',
        'version' => 'v1',
        'format' => 'json',
        'pagination' => [
            'enabled' => true,
            'per_page' => 15,
            'max_per_page' => 100,
        ],
        'rate_limiting' => [
            'enabled' => true,
            'attempts' => 60,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Swagger/OpenAPI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure OpenAPI documentation generation
    |
    */
    'swagger' => [
        'enabled' => true,
        'version' => '3.0.0',
        'title' => 'Your API Documentation',
        'description' => 'API documentation for your application',
        'servers' => [
            ['url' => env('APP_URL').'/api', 'description' => 'API Server'],
        ],
        'security_schemes' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configure AI-powered code generation settings
    |
    */
    'ai' => [
        'enabled' => false,
        'provider' => env('ZCRUDGEN_AI_PROVIDER', 'openai'),
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('ZCRUDGEN_AI_MODEL', 'gpt-4'),
        'temperature' => 0.7,
        'features' => [
            'business_logic' => true,
            'validation_rules' => true,
            'comments' => true,
            'tests' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Style Configuration
    |--------------------------------------------------------------------------
    |
    | Configure code style preferences
    |
    */
    'code_style' => [
        'indent_type' => 'space',
        'indent_size' => 4,
        'line_ending' => PHP_EOL,
        'comments' => [
            'class' => true,
            'method' => true,
            'property' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Configure test generation settings
    |
    */
    'testing' => [
        'enabled' => true,
        'framework' => 'pest', // or 'phpunit'
        'generate' => [
            'feature_tests' => true,
            'unit_tests' => true,
            'database_factories' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'driver' => 'redis', // redis, file, array
        'prefix' => 'zcrudgen',
        'ttl' => 3600, // in seconds
        'auto_clear_on_update' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Configuration
    |--------------------------------------------------------------------------
    */
    'response' => [
        'format' => [
            'success' => [
                'status' => 'success',
                'message' => ':message',
                'data' => ':data'
            ],
            'error' => [
                'status' => 'error',
                'message' => ':message',
                'errors' => ':errors'
            ]
        ],
        'status_codes' => [
            'success' => 200,
            'created' => 201,
            'updated' => 200,
            'deleted' => 200,
            'error' => 400,
            'unauthorized' => 401,
            'forbidden' => 403,
            'not_found' => 404,
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Relationship Configuration
    |--------------------------------------------------------------------------
    */
    'relationships' => [
        'auto_include' => true,
        'max_depth' => 2,
        'allowed_includes' => [], // empty means all
        'load_count' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    */
    'file_upload' => [
        'disk' => 'public',
        'allowed_types' => [
            'image/*',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'max_size' => 5120, // in KB
        'path' => 'uploads/:model/:field/:year/:month',
    ],
];
