<?php

namespace App\Http\Controllers\Admin\traits;

use Illuminate\Support\Facades\Log;
use Exception;

trait WebinarChangeCreator
{
    private function webinarChangedCreator($webinar)
    {

        $webinar->chapters()->update([
            'user_id' => $webinar->creator_id
        ]);

        $webinar->sessions()->update([
            'creator_id' => $webinar->creator_id
        ]);

        $webinar->faqs()->update([
            'creator_id' => $webinar->creator_id
        ]);

        $webinar->files()->update([
            'creator_id' => $webinar->creator_id
        ]);

        $webinar->textLessons()->update([
            'creator_id' => $webinar->creator_id
        ]);

        $webinar->quizzes()->update([
            'creator_id' => $webinar->creator_id
        ]);

        $webinar->assignments()->update([
            'creator_id' => $webinar->creator_id
        ]);

        $webinar->webinarExtraDescription()->update([
            'creator_id' => $webinar->creator_id
        ]);

        $webinar->tickets()->update([
            'creator_id' => $webinar->creator_id
        ]);

    }
}
