<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
class ConsultationSeo extends Model
{
    protected $table = 'consultation_seo';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'h1',
        'status',
        'keyword'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
