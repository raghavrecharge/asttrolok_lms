<?php
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
    
    'default' => env('FILESYSTEM_DRIVER', 'gcs'),
    
    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */
    
    'cloud' => env('FILESYSTEM_CLOUD', 'gcs'),
    
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
    
    'disks' => [
        
        'local' => [
            'driver' => 'local',
            'root' => public_path('store'),
            'visibility' => 'public',
        ],
        
        'public' => [
            'driver' => 'local',
            'root' => public_path('store'),
            'visibility' => 'public',
            'url' => '/store',
            'permissions' => [
                'file' => [
                    'public' => 0664,
                    'private' => 0600,
                ],
                'dir' => [
                    'public' => 0775,
                    'private' => 0700,
                ],
            ],
        ],
        
        // 'upload' => [
            //     'driver' => 'local',
            //     'root' => public_path('store'),
            //     'url' => '/store',
            //     'visibility' => 'public',
            //     'permissions' => [
                //         'file' => [
                    //             'public' => 0664,
                    //             'private' => 0600,
                    //         ],
                    //         'dir' => [
                        //             'public' => 0775,
                        //             'private' => 0700,
                        //         ],
        //     ],
        // ],
        
        'upload' => [
            'driver' => 'gcs',
            'base_url' => 'https://storage.googleapis.com/astrolok',
            'storage_path' => 'https://storage.googleapis.com/astrolok',
            'root' => 'https://storage.googleapis.com/astrolok',
            'key_file' => base_path('absolute-water-387410-3c083ae6a069.json'),
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'absolute-water-387410'),
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'astrolok'),
            'path_prefix' =>'webp/store',
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''),
            'storage_api_uri' => env('GOOGLE_CLOUD_STORAGE_API_URI', 'https://storage.googleapis.com/astrolok'),
            'apiEndpoint' => env('GOOGLE_CLOUD_STORAGE_API_ENDPOINT', 'https://storage.googleapis.com/astrolok'),
            'visibility' => 'public',
            'visibility_handler' => \League\Flysystem\GoogleCloudStorage\UniformBucketLevelAccessVisibility::class,
            'metadata' => ['cacheControl'=> 'public,max-age=86400'],
            'throw' => true,
            'visibility' => 'public',
            'permissions' => [
                'file' => [
                    'public' => 0664,
                    'private' => 0600,
                ],
                'dir' => [
                    'public' => 0775,
                    'private' => 0775,
                ],
            ],
        ],
        
        'images' => [
            'driver' => 'local',
            'root' => public_path('store'),
            'visibility' => 'public',
            'permissions' => [
                'file' => [
                    'public' => 0664,
                    'private' => 0600,
                ],
                'dir' => [
                    'public' => 0775,
                    'private' => 0700,
                ],
            ],
        ],
        
        'uploadOnHost' => [
            'driver' => 'local',
            'root' => 'app',
            'visibility' => 'public',
            'permissions' => [
                'file' => [
                    'public' => 0664,
                    'private' => 0600,
                ],
                'dir' => [
                    'public' => 0775,
                    'private' => 0700,
                ],
            ],
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
        
        'minio' => [
            'driver' => 'minio',
            'key' => env('MINIO_KEY'),
            'secret' => env('MINIO_SECRET'),
            'region' => env('MINIO_REGION'),
            'bucket' => env('MINIO_BUCKET'),
            'endpoint' => env('MINIO_ENDPOINT', env('app_url')),
            'visibility' => 'public',
        ],
        
        'gcs' => [
            'driver' => 'gcs',
            'key_file' => base_path('absolute-water-387410-1ea261a9eede.json'),
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'absolute-water-387410'),
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'astrolok'),
            'path_prefix' => 'webp/store/',
            'visibility' => 'public',
            'url' => 'https://storage.googleapis.com/astrolok',
            'throw' => true,
        ],
    ],
    
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
        public_path('images') => storage_path('app/images'),
        public_path('bin') => storage_path('app/public/bin'),
    ],
    
];