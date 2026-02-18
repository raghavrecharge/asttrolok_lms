<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\User;

class Talk extends Model
{
    protected $table = 'talks';

    protected $fillable = [
        'speaker_id',
        'topic',
        'description',
        'date_time',
        'city',
        'location',
        'thumbnail',
        'slug',
        'status',
    ];
    public function hasRegistered($userId): bool
{
 
    return $this->registrations()->where('user_id', $userId)->exists();
}

    /**
     * Relation: Talk ke registrations
     */
    public function registrations()
    {
        return $this->hasMany(TalkRegistration::class, 'talk_id');
    }

    /**
     * Relation: Talk ka speaker (User model)
     */
    public function speaker()
    {
        return $this->belongsTo(User::class, 'speaker_id', 'id');
    }

    /**
     * Talk ka URL
     */
    public function getUrl()
    {
        return url('/talks/' . $this->slug);
    }

    /**
     * Talk ka thumbnail image full path
     */
public function getImage()
{
    // If admin uploaded
    if (!empty($this->thumbnail)) {
        return $this->thumbnail;
    }

    // If teacher uploaded
    if (!empty($this->image)) {
        return $this->image;
    }

    // Fallback
    return '/assets/default/img/default-image.jpg';
}


public function users()
{
    return $this->belongsToMany(User::class, 'talk_registrations', 'talk_id', 'user_id')
                ->withTimestamps();
}

    /**
     * Model boot method: automatic slug generation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($talk) {
            $talk->slug = Str::slug($talk->topic);
        });

        static::updating(function ($talk) {
            if ($talk->isDirty('topic')) {
                $talk->slug = Str::slug($talk->topic);
            }
        });
    }
}