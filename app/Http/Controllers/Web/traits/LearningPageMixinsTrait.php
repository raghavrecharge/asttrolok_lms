<?php

namespace App\Http\Controllers\Web\traits;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Models\Webinar;
use App\Models\WebinarChapter;
use Illuminate\Support\Facades\Request;

trait LearningPageMixinsTrait
{
    public function getCourse($slug, $user = null, $relation = null, $relationWith = null)
    {
        try {
            if (empty($user)) {
                $user = auth()->user();
            }

            $query = Webinar::where('slug', $slug)
                ->where('status', 'active');

            if (!empty($relation)) {
                $query->with([
                    "{$relation}" => function ($query) use ($relation, $relationWith) {
                        if ($relation == 'forums') {
                            $query->orderBy('pin', 'desc');
                        }

                        $query->orderBy('created_at', 'desc');

                        if (!empty($relationWith)) {
                            $query->with($relationWith);
                        }
                    }
                ])->withCount([
                    "{$relation}"
                ]);
            }

            $query->with([
                'chapters' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive);
                    $query->orderBy('order', 'asc');

                    $query->with([
                        'chapterItems' => function ($query) {
                            $query->orderBy('order', 'asc');
                        }
                    ]);
                }
            ]);

            $course = $query->first();

            if (!empty($course) and ($course->checkUserHasBought($user) or !empty($course->getInstallmentOrder()))) {
                $isPrivate = $course->private;
                $hasBought = $course->checkUserHasBought($user);

                if (!empty($user) and ($user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin() or $hasBought)) {
                    $isPrivate = false;
                }

                if ($isPrivate) {
                    return 'not_access';
                }

                return $course;
            }

            return 'not_access';
        } catch (\Exception $e) {
            \Log::error('getCourse error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
