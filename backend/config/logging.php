<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

<<<<<<< HEAD
    'default' => env('LOG_CHANNEL', 'stack'),
=======
    'default' => env('LOG_CHANNEL', 'tenant'),
    // 'default' => env('LOG_CHANNEL', 'stack'),
>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

<<<<<<< HEAD
=======
        // --- Custom channel ---
        'tenant' => [
            'driver' => 'custom',
            'via'    => \App\Logging\CreateTenantLogger::class,
            'path'   => storage_path('logs/laravel.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
        ],

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', env('APP_NAME', 'Laravel')),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];
