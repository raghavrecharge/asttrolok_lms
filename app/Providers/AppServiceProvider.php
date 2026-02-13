<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use Google\Cloud\Storage\StorageClient;

class AppServiceProvider extends ServiceProvider
{
    // public function boot()
    // {
    //     Storage::extend('gcs', function ($app, $config) {
    //         $storageClient = new StorageClient([
    //             'projectId' => $config['project_id'],
    //             'keyFilePath' => $config['key_file'],
    //         ]);

    //         $bucket = $storageClient->bucket($config['bucket']);
            
    //         $adapter = new GoogleCloudStorageAdapter(
    //             $bucket,
    //             $config['path_prefix'] ?? ''
    //         );

    //         $filesystem = new Filesystem($adapter, $config);

    //         $driver = new FilesystemAdapter($filesystem, $adapter, $config);

    //         // Custom URL generator
    //         $driver->buildTemporaryUrlsUsing(function ($path, $expiration, $options) use ($bucket, $config) {
    //             $object = $bucket->object($path);
                
    //             // Generate signed URL for private files
    //             return $object->signedUrl($expiration);
    //         });

    //         return $driver;
    //     });
    // }

    public function boot()
    {
        Storage::extend('gcs', function ($app, $config) {
            $storageClient = new StorageClient([
                'projectId' => $config['project_id'],
                'keyFilePath' => $config['key_file'],
            ]);

            $bucket = $storageClient->bucket($config['bucket']);
            $pathPrefix = $config['path_prefix'] ?? '';
            
            $adapter = new GoogleCloudStorageAdapter($bucket, $pathPrefix);
            $filesystem = new Filesystem($adapter);

            // IMPORTANT: CustomGCSAdapter ka full namespace
            return new \App\Handlers\CustomGCSAdapter(
                $filesystem,
                $adapter,
                $config
            );
        });
    }
}