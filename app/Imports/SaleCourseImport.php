<?php

namespace App\Imports;

use App\Models\Sale;
use App\Models\ProductOrder;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;
use App\Models\Webinar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SaleCourseImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // V-10 FIX: Validate required fields exist
        if (empty($row['user_id']) || empty($row['webinar_id'])) {
            Log::warning('SaleCourseImport: Skipping row — missing user_id or webinar_id', $row);
            return null;
        }

        $userId = (int) $row['user_id'];
        $webinarId = (int) $row['webinar_id'];

        // V-10 FIX: Validate user and webinar exist
        $user = User::find($userId);
        if (!$user) {
            Log::warning('SaleCourseImport: Skipping row — user not found', ['user_id' => $userId]);
            return null;
        }

        $webinar = Webinar::find($webinarId);
        if (!$webinar) {
            Log::warning('SaleCourseImport: Skipping row — webinar not found', ['webinar_id' => $webinarId]);
            return null;
        }

        // V-10 FIX: Check duplicate — user already has active access
        $existingSale = Sale::where('webinar_id', $webinarId)
            ->where('buyer_id', $userId)
            ->whereNull('refund_at')
            ->where('access_to_purchased_item', 1)
            ->first();

        if ($existingSale) {
            Log::info('SaleCourseImport: Skipping row — user already has access', [
                'user_id' => $userId,
                'webinar_id' => $webinarId,
            ]);
            return null;
        }

        // V-10 FIX: Log the import with admin audit trail
        Log::info('SaleCourseImport: Granting access via bulk import', [
            'user_id' => $userId,
            'webinar_id' => $webinarId,
            'imported_by' => Auth::id(),
        ]);

        return new Sale([
            'buyer_id' => $userId,
            'seller_id' => $webinar->creator_id,
            'webinar_id' => $webinarId,
            'type' => Sale::$webinar,
            'manual_added' => true,
            'payment_method' => Sale::$credit,
            'amount' => 0,
            'total_amount' => 0,
            'access_to_purchased_item' => 1,
            'granted_by_admin_id' => Auth::id(),
            'created_at' => time(),
        ]);
    }
}
