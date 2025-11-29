<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Controller;
use App\Models\Notification;
use App\Models\NotificationStatus;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function list(Request $request)
    {
        try {
            $status = $request->input('status');
            if ($status == 'unread') {
                $notifications = $this->unRead();
            } elseif ($status == 'read') {
                $notifications = $this->read();
            }else{
                $notifications=$this->all() ;
            }
            $notifications = self::brief($notifications);
            return apiResponse2(1, 'retrieved', trans('public.retrieved'), $notifications);
        } catch (\Exception $e) {
            \Log::error('list error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public static function brief($notifications)
    {
        $notifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'status' => ($notification->notificationStatus) ? 'read' : 'unread',
                'created_at'=>$notification->created_at
            ];
        });
        return [
            'count' => count($notifications),
            'notifications' => $notifications,
        ];

    }

    public function seen($id)
    {
        try {
            $user = apiAuth();
            $notification = Notification::where('id', $id)->first();
            if (!$notification) {
                abort(404);
            }
            $unReadNotifications = $user->getUnReadNotifications();

            if (!empty($unReadNotifications) and !$unReadNotifications->isEmpty()) {
                $notification = $unReadNotifications->where('id', $id)->first();

                if (!empty($notification)) {
                    $status = NotificationStatus::where('user_id', $user->id)
                        ->where('notification_id', $notification->id)
                        ->first();

                    if (empty($status)) {
                        NotificationStatus::create([
                            'user_id' => $user->id,
                            'notification_id' => $notification->id,
                            'seen_at' => time()
                        ]);
                        return apiResponse2(1, 'seen', trans('api.notification.seen'));

                    }
                }

                return apiResponse2(0, 'already_seen', trans('api.notification.already_seen'));
            }

            return apiResponse2(0, 'already_seen', trans('api.notification.already_seen'));
        } catch (\Exception $e) {
            \Log::error('seen error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function unRead()
    {
        try {
            $user = apiAuth();
            $unReadNotifications = $user->getUnReadNotifications();
            return $unReadNotifications;
        } catch (\Exception $e) {
            \Log::error('unRead error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function read()
    {
        try {
            return $this->all()->diff($this->unRead());
        } catch (\Exception $e) {
            \Log::error('read error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function all()
    {
        try {
            $user = apiAuth();
            $notifications = Notification::where(function ($query) use ($user) {
                $query->where('notifications.user_id', $user->id)
                    ->where('notifications.type', 'single');
            })->orWhere(function ($query) use ($user) {
                if (!$user->isAdmin()) {
                    $query->whereNull('notifications.user_id')
                        ->whereNull('notifications.group_id')
                        ->where('notifications.type', 'all_users');
                }
            });

            $userGroup = $user->userGroup()->first();
            if (!empty($userGroup)) {
                $notifications->orWhere(function ($query) use ($userGroup) {
                    $query->where('notifications.group_id', $userGroup->group_id)
                        ->where('notifications.type', 'group');
                });
            }

            $notifications->orWhere(function ($query) use ($user) {
                $query->whereNull('notifications.user_id')
                    ->whereNull('notifications.group_id')
                    ->where(function ($query) use ($user) {
                        if ($user->isUser()) {
                            $query->where('notifications.type', 'students');
                        } elseif ($user->isTeacher()) {
                            $query->where('notifications.type', 'instructors');
                        } elseif ($user->isOrganization()) {
                            $query->where('notifications.type', 'organizations');
                        }
                    });
            });

            $notifications = $notifications->orderBy('notifications.created_at', 'DESC')->get();
            return $notifications;
        } catch (\Exception $e) {
            \Log::error('all error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
