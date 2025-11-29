<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadFileManager extends Controller
{

      public function private_folder_name(){
        try {
            return apiAuth()->id ;
        } catch (\Exception $e) {
            \Log::error('private_folder_name error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

      public function base_directory(){
        try {
            return config('lfm.base_directory') ;
        } catch (\Exception $e) {
            \Log::error('base_directory error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

      public function path(){
        try {
            return    $this->private_folder_name() ;
        } catch (\Exception $e) {
            \Log::error('path error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

     public function __construct($file,$sub_directory=null)
     {
         $fileName = $file->getClientOriginalName() ;
         $path=$this->path() .'/'.$sub_directory;
         $storage_path= $file->storeAs($path
             , $fileName);
         $this->storage_path='store/' . $storage_path ;
     }

    public function __invoke(Request $request)
    {
        try {
            dd('dd') ;
        } catch (\Exception $e) {
            \Log::error('__invoke error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
