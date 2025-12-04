<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebinarExtraDetails extends Model
{
    protected $table = 'webinar_extra_details';
    
    protected $fillable = [
        'webinar_id',
        'plan_type',
        'plan_badge',
        'plan_price',
        'price_suffix',
        'plan_duration_option',
        'plan_cancel_text',
        'comparison_text',
        'plan_icon',
        'is_featured',
        'heading_main',
        'heading_sub',
        'heading_extra',
        'additional_description',
        'extra_description',
        'subtitle',
        'subdescription',
        'material_text',
        'material_icon',
        'learn_text',
        'price_icon',
        'plan_movie',
        'learn_title',
        'learn_description',
        'learn_icon',
        'bonus_heading',
        'bonus_icon',
        'ad_title',
        'ad_subtitle',
        'ad_description',
        'ad_img',
        'certification_time',
        'certification_focus',
        'certification_outcome',
        'rate_title',
        'rate_options',
        'rate_icon',
    ];

    protected $casts = [
        'material_text' => 'array',
        'learn_text' => 'array',
        'certification_time' => 'array',
        'certification_focus' => 'array',
        'certification_outcome' => 'array',
        'rate_options' => 'array',
    ];

    public function webinar()
    {
        return $this->belongsTo(Webinar::class);
    }
}