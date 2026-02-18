<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use Google\Cloud\Storage\StorageClient;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Share $subscriptions with all web views (marketing theme requirement)
        View::composer('web.*', function ($view) {
            if (!isset($view->getData()['subscriptions'])) {
                $view->with('subscriptions', Cache::remember('global_active_subscriptions', 3600, function () {
                    return \App\Models\Subscription::select('id', 'slug', 'price', 'status', 'thumbnail', 'creator_id')
                        ->where('status', 'active')
                        ->orderBy('created_at', 'desc')
                        ->get();
                }));
            }
        });

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