<?php

namespace App\Traits;

use App\Models\GroupUser;
use Illuminate\Support\Facades\Schema;

trait StudentFiltersTrait
{
    protected function studentsListsFilters($subscription, $query, $filters)
    {
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $full_name = $filters['full_name'] ?? null;
        $sort = $filters['sort'] ?? null;
        $group_id = $filters['group_id'] ?? null;
        $role_id = $filters['role_id'] ?? null;
        $status = $filters['status'] ?? null;

        $query = fromAndToDateFilter($from, $to, $query, 'sales.created_at');

        if (!empty($full_name)) {
            $query->where('users.full_name', 'like', "%$full_name%");
        }

        if (!empty($sort)) {
            if ($sort == 'rate_asc') {
                $query->orderBy('webinar_reviews.rates', 'asc');
            }

            if ($sort == 'rate_desc') {
                $query->orderBy('webinar_reviews.rates', 'desc');
            }
        }

        if (!empty($group_id)) {
            $userIds = GroupUser::where('group_id', $group_id)->pluck('user_id')->toArray();
            $query->whereIn('users.id', $userIds);
        }

        if (!empty($role_id)) {
            $query->where('users.role_id', $role_id);
        }

        if (!empty($status)) {
            if ($status == 'expire' and !empty($subscription->access_days)) {
                $accessTimestamp = $subscription->access_days * 24 * 60 * 60;
                $query->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()]);
            }
        }

        return $query;
    }
}
