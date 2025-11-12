<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refile;
use App\Models\Quiz;
use App\Models\Session;
use App\Models\TextLesson;
use App\Models\Translation\RemedyChapterTranslation;
use App\Models\Remedy;
// use App\Models\WebinarAssignment;
use App\Models\RemedyChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RechapterController extends Controller
{
    public function getChapter(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

        $chapter = RemedyChapter::where('id', $id)
            ->first();

        $locale = $request->get('locale', app()->getLocale());

        if (!empty($chapter)) {
            foreach ($chapter->translatedAttributes as $attribute) {
                try {
                    $chapter->$attribute = $chapter->translate(mb_strtolower($locale))->$attribute;
                } catch (\Exception $e) {
                    $chapter->$attribute = null;
                }
            }

            $data = [
                'chapter' => $chapter
            ];

            return response()->json($data, 200);
        }

        abort(403);
    }

    public function getAllByRemedyId($remedy_id)
    {
        $this->authorize('admin_remedies_edit');

        $remedy = Remedy::find($remedy_id);

        if (!empty($remedy)) {

            $chapters = $remedy->chapters->where('status', RemedyChapter::$chapterActive);

            $data = [
                'chapters' => [],
            ];

            if (!empty($chapters) and count($chapters)) {
                // for translate send on array of data

                foreach ($chapters as $chapter) {
                    $data['chapters'][] = [
                        'user_id' => $chapter->user_id,
                        'remedy_id' => $chapter->remedy_id,
                        'id' => $chapter->id,
                        'order' => $chapter->order,
                        'status' => $chapter->status,
                        'title' => $chapter->title,
                        'type' => $chapter->type,
                        'created_at' => $chapter->created_at,
                    ];
                }
            }

            return response()->json($data, 200);
        }

        abort(403);
    }

    public function store(Request $request)
    {
        
        $this->authorize('admin_remedies_edit');

        $data = $request->get('ajax')['chapter'];

        $validator = Validator::make($data, [
            'remedy_id' => 'required',
            //'type' => 'required|' . Rule::in(WebinarChapter::$chapterTypes),
            'title' => 'required|max:255',
        ]);
// echo "<script>console.log('PHP: " . $data . "');</script>";
        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!empty($data['remedy_id'])) {
            $remedy = Remedy::where('id', $data['remedy_id'])->first();

            if (!empty($remedy)) {
                $teacher = $remedy->creator;
                $status = (!empty($data['status']) and $data['status'] == 'on') ? RemedyChapter::$chapterActive : RemedyChapter::$chapterInactive;

                $chapter = RemedyChapter::create([
                    'user_id' => $teacher->id,
                    'remedy_id' => $remedy->id,
                    // 'type' => $data['type'],
                    'title' => $data['title'],
                    'status' => $status,
                    'check_all_contents_pass' => (!empty($data['check_all_contents_pass']) and $data['check_all_contents_pass'] == 'on'),
                    'created_at' => time(),
                ]);

                if (!empty($chapter)) {
                    RemedyChapterTranslation::updateOrCreate([
                        'remedy_chapter_id' => $chapter->id,
                        'locale' => mb_strtolower($data['locale']),
                    ], [
                        'title' => $data['title'],
                    ]);
                }


                return response()->json([
                    'code' => 200,
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

        $chapter = RemedyChapter::where('id', $id)->first();

        if (!empty($chapter)) {
            $locale = $request->get('locale', app()->getLocale());
            if (empty($locale)) {
                $locale = app()->getLocale();
            }
            storeContentLocale($locale, $chapter->getTable(), $chapter->id);

            $chapter->title = $chapter->getTitleAttribute();
            $chapter->locale = mb_strtoupper($locale);

            return response()->json([
                'chapter' => $chapter
            ], 200);
        }

        return response()->json([], 422);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

        $data = $request->get('ajax')['chapter'];

        $validator = Validator::make($data, [
            'remedy_id' => 'required',
            //'type' => 'required|' . Rule::in(RemedyChapter::$chapterTypes),
            'title' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $chapter = RemedyChapter::where('id', $id)->first();

        if (!empty($chapter)) {
            $remedy = Remedy::where('id', $data['remedy_id'])->first();

            if (!empty($remedy)) {
                $status = (!empty($data['status']) and $data['status'] == 'on') ? RemedyChapter::$chapterActive : RemedyChapter::$chapterInactive;

                $chapter->update([
                    'check_all_contents_pass' => (!empty($data['check_all_contents_pass']) and $data['check_all_contents_pass'] == 'on'),
                    'status' => $status,
                ]);

                if (!empty($chapter)) {
                    RemedyChapterTranslation::updateOrCreate([
                        'remedy_chapter_id' => $chapter->id,
                        'locale' => mb_strtolower($data['locale']),
                    ], [
                        'title' => $data['title'],
                    ]);
                }

                removeContentLocale();

                return response()->json([
                    'code' => 200,
                ], 200);
            }
        }

        removeContentLocale();

        return response()->json([], 422);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

        $chapter = RemedyChapter::where('id', $id)->first();

        if (!empty($chapter)) {
            $chapter->delete();
        }

        return response()->json([
            'code' => 200
        ], 200);
    }

    public function change(Request $request)
    {
        $data = $request->get('ajax');

        $validator = Validator::make($data, [
            'item_id' => 'required',
            'item_type' => 'required',
            'chapter_id' => 'required',
            'remedy_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = null;

        $remedy = Remedy::find($data['remedy_id']);

        if (!empty($remedy)) {

            switch ($data['item_type']) {
                case RemedyChapterItem::$chapterSession:
                    $item = Session::where('id', $data['item_id'])
                        ->where('remedy_id', $data['remedy_id'])
                        ->first();
                    break;

                case RemedyChapterItem::$chapterFile:
                    $item = Refile::where('id', $data['item_id'])
                        ->where('remedy_id', $data['remedy_id'])
                        ->first();
                    break;

                case RemedyChapterItem::$chapterTextLesson:
                    $item = TextLesson::where('id', $data['item_id'])
                        ->where('remedy_id', $data['remedy_id'])
                        ->first();
                    break;

                case RemedyChapterItem::$chapterQuiz:
                    $item = Quiz::where('id', $data['item_id'])
                        ->where('remedy_id', $data['remedy_id'])
                        ->first();
                    break;

                case RemedyChapterItem::$chapterAssignment:
                    $item = RemedyAssignment::where('id', $data['item_id'])
                        ->where('remedy_id', $data['remedy_id'])
                        ->first();
                    break;
            }
        }

        if (!empty($item)) {
            $item->update([
                'chapter_id' => !empty($data['chapter_id']) ? $data['chapter_id'] : null
            ]);

            RemedyChapterItem::where('item_id', $item->id)
                ->where('type', $data['item_type'])
                ->delete();

            if (!empty($data['chapter_id'])) {
                RemedyChapterItem::makeItem($item->creator_id, $data['chapter_id'], $item->id, $data['item_type']);
            }
        }

        return response()->json([
            'code' => 200
        ], 200);
    }
}
