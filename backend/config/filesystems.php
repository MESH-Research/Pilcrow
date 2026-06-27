<?php

/*
| Optional deployment-supplied disks. The application defines the seam; the
| deployment owns the content. Point FILESYSTEM_DISKS_CONFIG at a PHP file that
| returns an array of disk definitions and they are merged into the disks below
| (deployment keys win on conflict). This keeps host-/environment-specific
| storage choices — an off-host backup bucket, an object store, etc. — out of
| the application's own config and lets each deployment add disks without a code
| change here. Inert unless the env var points at a readable file.
*/
$deploymentDisks = [];
if (($deploymentDisksConfig = env('FILESYSTEM_DISKS_CONFIG')) && is_file($deploymentDisksConfig)) {
    $deploymentDisks = require $deploymentDisksConfig;
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => array_merge([

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        // Private, non-public-served store for moderation evidence (e.g.
        // retained snapshots of reported avatars). Deliberately has no `url`:
        // files here are never publicly addressable and are streamed only
        // through an ability-gated controller. Override the driver via a
        // deployment disk (see $deploymentDisks above) to move it off-host.
        'media_private' => [
            'driver' => 'local',
            'root' => storage_path('app/media-private'),
            'visibility' => 'private',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
        ],

    ], $deploymentDisks),

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
