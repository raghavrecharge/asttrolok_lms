<?php
namespace App\Models;

use App\User;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportUsers implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
         $organizations = User::select('id', 'full_name', 'created_at')
            ->where('email', $row[1])
            ->first();
            if(!$organizations){
                $organizations1 = User::select('id', 'full_name', 'created_at')
            ->where('mobile', $row[2])
            ->first();
            if(!$organizations1){
        return new User([
            'full_name' => $row[0],
            'role_name' => 'user',
            'role_id' => '1',
            'consultant' => '0',
            'password' => User::generatePassword('123456'),
            'status' => 'active',
            'affiliate' => '0',
            'verified' => true,
            'created_at' => time(),
            'mobile'     => $row[2],
            'email'    => $row[1],
        ]);
            }
            }
    }
}
