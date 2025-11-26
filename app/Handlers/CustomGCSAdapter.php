<?php

namespace App\Handlers;

use Illuminate\Filesystem\FilesystemAdapter;

class CustomGCSAdapter extends FilesystemAdapter
{
    public function url($path)
    {
        $path = ltrim($path, '/');
        $bucket = $this->config['bucket'] ?? 'astrolok';
        $pathPrefix = rtrim($this->config['path_prefix'] ?? '', '/');
        
        $fullPath = $pathPrefix ? $pathPrefix . '/' . $path : $path;
        $fullPath = ltrim($fullPath, '/');
        $parts = explode('/', $fullPath);
        $filtered = array_filter($parts, fn($p) => $p !== 'webp');
        $fullPath = implode('/', $filtered);
        return '/'.$fullPath ;
        // return "https://storage.googleapis.com/{$bucket}/{$fullPath}";
    }

    public function temporaryUrl($path, $expiration, array $options = [])
    {
        return $this->url($path);
    }

    public function setVisibility($path, $visibility)
    {
        return true;
    }

    public function getVisibility($path)
    {
        return 'public';
    }

    public function makeDirectory($path)
    {
        $placeholder = rtrim($path, '/') . '/.gitkeep';
        
        try {
            $this->put($placeholder, '');
            return true;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function deleteDirectory($directory)
    {
        try {
            return parent::deleteDirectory($directory);
        } catch (\Exception $e) {
            return true;
        }
    }

    public function exists($path)
    {
        try {
            if ($this->fileExists($path)) {
                return true;
            }
            
            try {
                $listing = $this->listContents($path, false);
                $contents = iterator_to_array($listing, false);
                return count($contents) > 0;
            } catch (\Exception $e) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function fileExists($path)
    {
        try {
            return parent::fileExists($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function directoryExists($path)
    {
        try {
            $listing = $this->listContents($path, false);
            $contents = iterator_to_array($listing, false);
            return count($contents) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function files($directory = null, $recursive = false)
    {
        try {
            return parent::files($directory, $recursive);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function directories($directory = null, $recursive = false)
    {
        try {
            $listing = $this->listContents($directory ?: '', $recursive);
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
            return [];
        }
    }

    /**
     * IMPORTANT: Mime type detection WITHOUT finfo
     */
    public function mimeType($path)
    {
        // Agar finfo available hai to use karo
        if (class_exists('finfo')) {
            try {
                return parent::mimeType($path);
            } catch (\Exception $e) {
                // Continue to fallback
            }
        }
        
        // Fallback: extension se detect karo
        return $this->guessMimeTypeFromExtension($path);
    }

    /**
     * Guess mime type from file extension
     */
    protected function guessMimeTypeFromExtension($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon',
            
            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            
            // Text
            'txt' => 'text/plain',
            'html' => 'text/html',
            'htm' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'csv' => 'text/csv',
            
            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
            
            // Audio
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',
            
            // Video
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
            'wmv' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv',
            'webm' => 'video/webm',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}