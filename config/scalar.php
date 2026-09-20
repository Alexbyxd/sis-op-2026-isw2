<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scalar Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Scalar will be accessible from. If this
    | setting is null, Scalar will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    */
    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Scalar Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Scalar will be accessible from.
    |
    */
    'path' => '/scalar',

    /*
    |--------------------------------------------------------------------------
    | Scalar Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Scalar route.
    |
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Scalar OpenAPI Document URL
    |--------------------------------------------------------------------------
    */
    'url' => null,

    /*
    |--------------------------------------------------------------------------
    | Scalar OpenAPI Document Content
    |--------------------------------------------------------------------------
    */
    'content' => null,

    /*
    |--------------------------------------------------------------------------
    | Scalar OpenAPI Document File (Offline / On-Premise)
    |--------------------------------------------------------------------------
    |
    | Local path to the OpenAPI document read directly by the server.
    |
    */
    'file' => storage_path('app/openapi.json'),

    /*
    |--------------------------------------------------------------------------
    | Scalar OpenAPI Documents (multiple / versioned)
    |--------------------------------------------------------------------------
    */
    'sources' => [],

    /*
    |--------------------------------------------------------------------------
    | Scalar CDN URL (Self-Hosted / Local Bundle)
    |--------------------------------------------------------------------------
    |
    | Points to the local standalone script in public/vendor/scalar/scalar.js
    | ensuring 100% offline operation without any internet dependency.
    |
    */
    'cdn' => '/vendor/scalar/scalar.js',

    /*
    |--------------------------------------------------------------------------
    | Scalar Configuration
    |--------------------------------------------------------------------------
    */
    'configuration' => [
        /** Color theme preset */
        'theme' => 'laravel',

        /** The layout to use */
        'layout' => 'modern',

        /** Proxy URL (disabled for local offline environment) */
        'proxyUrl' => '',

        /** Whether to show the sidebar */
        'showSidebar' => true,

        /** Whether to show models in sidebar */
        'hideModels' => false,

        /** File type of the “Download OpenAPI Document” button */
        'documentDownloadType' => 'both',

        /** Whether to show the “Test Request” button */
        'hideTestRequestButton' => false,

        /** Whether to show the sidebar search bar */
        'hideSearch' => false,

        /** Initial dark mode */
        'darkMode' => false,

        /** Force dark mode state */
        'forceDarkModeState' => 'dark',

        /** Whether to show dark mode toggle */
        'hideDarkModeToggle' => false,

        /** Search modal hotkey */
        'searchHotKey' => 'k',

        /** Metadata */
        'metaData' => [
            'title' => 'Police Storage API Documentation',
        ],

        /** Default HTTP Client */
        'defaultHttpClient' => [
            'targetKey' => 'shell',
            'clientKey' => 'curl',
        ],

        /** Disable remote Google fonts to ensure 100% offline functionality */
        'withDefaultFonts' => false,

        'defaultOpenAllTags' => true,
        'defaultOpenFirstTag' => true,
        'showOperationId' => false,
        'hideClientButton' => false,
        'expandAllModelSections' => true,
        'expandAllResponses' => true,
        'expandAllSchemaProperties' => true,
        'modelsSectionLabel' => 'Modelos',
        'operationTitleSource' => 'summary',
        'orderRequiredPropertiesFirst' => true,
        'orderSchemaPropertiesBy' => 'alpha',
        'operationsSorter' => 'alpha',
        'persistAuth' => true,

        /** Disable telemetry for offline privacy */
        'telemetry' => false,

        'showDeveloperTools' => 'localhost',
    ],

];
