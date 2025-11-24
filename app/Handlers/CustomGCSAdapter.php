<?php

namespace App\Handlers;

use Illuminate\Filesystem\FilesystemAdapter;

class CustomGCSAdapter extends FilesystemAdapter
{
    /**
     * Get the URL for the file at the given path.
     */
    public function url($path)
    {
        $path = ltrim($path, '/');
        $bucket = $this->config['bucket'] ?? 'astrolok';
        $pathPrefix = rtrim($this->config['path_prefix'] ?? '', '/');
        
        $fullPath = $pathPrefix ? $pathPrefix . '/' . $path : $path;
        $fullPath = ltrim($fullPath, '/');
        
        return "https://storage.googleapis.com/{$bucket}/{$fullPath}";
    }

    /**
     * Get a temporary URL for the file at the given path.
     */
    public function temporaryUrl($path, $expiration, array $options = [])
    {
        return $this->url($path);
    }

    /**
     * Set the visibility for the given path.
     */
    public function setVisibility($path, $visibility)
    {
        // GCS mein visibility bucket-level hai, ignore karo
        return true;
    }

    /**
     * Get the visibility for the given path.
     */
    public function getVisibility($path)
    {
        return 'public';
    }

    /**
     * Create a directory.
     */
    public function makeDirectory($path)
    {
        $placeholder = rtrim($path, '/') . '/.gitkeep';
        
        try {
            $this->put($placeholder, '');
            return true;
        } catch (\Exception $e) {
            \Log::warning("GCS makeDirectory failed: {$path}", ['error' => $e->getMessage()]);
            return true;
        }
    }

    /**
     * Delete a directory.
     */
    public function deleteDirectory($directory)
    {
        try {
            return parent::deleteDirectory($directory);
        } catch (\Exception $e) {
            \Log::warning("GCS deleteDirectory failed: {$directory}", ['error' => $e->getMessage()]);
            return true;
        }
    }

    /**
     * Check if file or directory exists.
     */
    public function exists($path)
    {
        try {
            // Pehle file check karo
            if ($this->fileExists($path)) {
                return true;
            }
            
            // Directory check - listContents ko array mein convert karo
            try {
                $listing = $this->listContents($path, false);
                // DirectoryListing ko array mein convert karo
                $contents = iterator_to_array($listing, false);
                return count($contents) > 0;
            } catch (\Exception $e) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Determine if a file exists.
     */
    public function fileExists($path)
    {
        try {
            return parent::fileExists($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Determine if a directory exists.
     */
    public function directoryExists($path)
    {
        try {
            $listing = $this->listContents($path, false);
            // DirectoryListing ko array mein convert karo
            $contents = iterator_to_array($listing, false);
            return count($contents) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all files in a directory.
     */
    public function files($directory = null, $recursive = false)
    {
        try {
            return parent::files($directory, $recursive);
        } catch (\Exception $e) {
            \Log::error('GCS files() error', ['dir' => $directory, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get all directories in a directory.
     */
    public function directories($directory = null, $recursive = false)
    {
        try {
            $listing = $this->listContents($directory ?: '', $recursive);
            
            // DirectoryListing ko array mein convert karo
            $contents = iterator_to_array($listing, false);
            
            return collect($contents)
                ->filter(function ($item) {
                    return ($item['type'] ?? $item->type() ?? '') === 'dir';
                })
                ->map(function ($item) {
                    return $item['path'] ?? $item->path();
                })
                ->values()
                ->all();
        } catch (\Exception $e) {
            \Log::error('GCS directories() error', ['dir' => $directory, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get all files and directories in a directory.
     */
    public function listContents($directory = '', $recursive = false)
    {
        try {
            return parent::listContents($directory, $recursive);
        } catch (\Exception $e) {
            \Log::error('GCS listContents() error', ['dir' => $directory, 'error' => $e->getMessage()]);
            // Empty DirectoryListing return karo instead of array
            return new \League\Flysystem\DirectoryListing([]);
        }
    }

    /**
     * Get array of all files and directories.
     * Helper method jo DirectoryListing ko array mein convert karta hai
     */
    public function listContentsArray($directory = '', $recursive = false)
    {
        try {
            $listing = $this->listContents($directory, $recursive);
            return iterator_to_array($listing, false);
        } catch (\Exception $e) {
            return [];
        }
    }
}