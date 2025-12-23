<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Faq extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'faqs';
    public $timestamps = false;
    protected $guarded = ['id'];

    public $translatedAttributes = ['title', 'answer'];

    // YE CONSTANTS ADD KAREIN - IMPORTANT!
    const TYPE_FAQ = 'faq';
    const TYPE_WHY_CHOOSE_US = 'why_choose_us';

    // Scopes
    public function scopeFaqType($query)
    {
        return $query->where('type', self::TYPE_FAQ);
    }

    public function scopeWhyChooseUsType($query)
    {
        return $query->where('type', self::TYPE_WHY_CHOOSE_US);
    }

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getAnswerAttribute()
    {
        return getTranslateAttributeValue($this, 'answer');
    }
}