<?php

namespace App\Handlers;

use UniSharp\LaravelFilemanager\Handlers\ConfigHandler;

class LfmConfigHandler extends ConfigHandler
{
    public function userField()
    {
           $user = auth()->user();

       
        if ($user && $user->role_name === 'admin') {
            return 'store/1';
        }
       
        return 'store/' . $user->id;
    }
}

