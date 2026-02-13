<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentsController extends Controller
{
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'item_id' => 'required',
                'item_name' => ['required', Rule::in(['blog', 'webinar'])],
                'comment' => 'required|string',
            ]);

            $user = auth()->user();
            $item_name = $request->input('item_name');
            $item_id = $request->input('item_id');
            if ($item_name == 'webinar') {

            } elseif ($item_name == 'blog') {
                $rules['item_id'] = 'required|exists:blog';

            }
            $item_name = $item_name . '_id';

            Comment::create([
                $item_name => $item_id,
                'user_id' => $user->id,
                'comment' => $request->input('comment'),
                'reply_id' => $request->input('reply_id'),
                'status' => $request->input('status') ?? Comment::$pending,
                'created_at' => time()
            ]);

            if ($item_name == 'webinar_id') {
                $webinar = Webinar::FindOrFail($item_id);
                $notifyOptions = [
                    '[c.title]' => $webinar->title,
                    '[u.name]' => $user->full_name
                ];
                sendNotification('new_comment', $notifyOptions, 1);
            }

            return apiResponse2(1, 'stored', trans('public.stored'));
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeReply(Request $request)
    {
        try {
            $this->validate($request, [
                'item_id' => 'required',
                'reply' => 'required|string',
            ]);

            $item_name = $request->get('item_name');
            $item_id = $request->get('item_id');

            Comment::create([
                $item_name => $item_id,
                'user_id' => auth()->user()->id,
                'comment' => $request->input('reply'),
                'reply_id' => $request->input('comment_id'),
                'status' => $request->input('status') ?? Comment::$pending,
                'created_at' => time()
            ]);

            $toastData = [
                'title' => trans('product.comment_success_store'),
                'msg' => trans('product.comment_success_store_msg'),
                'status' => 'success'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('storeReply error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();

            $this->validate($request, [
                'webinar_id' => 'required',
                'comment' => 'nullable',
            ]);

            $comment = Comment::where('id', $id)
                ->where('user_id', $user->id)
                ->first();
            if (!empty($comment)) {
                $comment->update([
                    'webinar_id' => $request->input('webinar_id'),
                    'user_id' => $user->id,
                    'comment' => $request->input('comment'),
                    'reply_id' => $request->input('reply_id'),
                    'status' => $request->input('status') ?? Comment::$pending,
                    'created_at' => time()
                ]);

                return redirect()->back();
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $comment = Comment::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!empty($comment)) {
                $comment->delete();
            }

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function report(Request $request, $id)
    {
        try {
            $comment = comment::findOrFail($id);

            $this->validate($request, [
                'item_id' => 'required',
                'message' => 'required',
            ]);

            $item_name = $request->get('item_name');
            $item_id = $request->get('item_id');
            $data = $request->all();

            CommentReport::create([
                $item_name => $item_id,
                'user_id' => auth()->id(),
                'comment_id' => $comment->id,
                'message' => $data['message'],
                'created_at' => time()
            ]);

            return response()->json([
                'code' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('report error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
