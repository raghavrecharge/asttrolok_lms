<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Channel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'channels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'link',
        'icon',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

   
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('channels_active');
            Cache::forget('channels_all');
        });

        static::deleted(function () {
            Cache::forget('channels_active');
            Cache::forget('channels_all');
        });
    }

    public static function getActiveChannels()
    {
        return  self::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
    }

    
    public static function getAllChannels()
    {
        return  self::orderBy('sort_order')
                ->orderBy('name')
                ->get();
    }

   
    public static function getChannelById($id)
    {
        return self::find($id);
    }

 
    public static function getChannelsByName($name)
    {
        return self::where('name', 'like', "%{$name}%")->get();
    }

    
    public static function createChannel(array $data)
    {
        return self::create($data);
    }

  
    public function updateChannel(array $data)
    {
        return $this->update($data);
    }

   
    public function deleteChannel()
    {
        return $this->delete();
    }

    
    public function toggleActive()
    {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

   
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

  
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the icon URL (accessor)
     *
     * @return string
     */
    public function getIconUrlAttribute()
    {
        return asset($this->icon);
    }

  
    public function isActive()
    {
        return $this->is_active === true;
    }
}