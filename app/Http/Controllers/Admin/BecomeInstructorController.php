<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Accounting;
use App\Models\BecomeInstructor;
use App\Models\Order;
use App\Models\RegistrationPackage;
use App\Models\Role;
use App\Models\Sale;

class BecomeInstructorController extends Controller
{
    public function index($page)
    {
        try {
            $this->authorize('admin_become_instructors_list');

            if ($page == 'organizations') {
                $role = Role::$organization;
            } else {
                $role = Role::$teacher;
            }

            $query = BecomeInstructor::where('role', $role);

            if (getRegistrationPackagesGeneralSettings('force_user_to_select_a_package')) {
                $query->whereNotNull('package_id');
            }

            $becomeInstructors = $query->with(['user', 'registrationPackage'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('admin/main.become_instructors_list'),
                'becomeInstructors' => $becomeInstructors
            ];

            return view('admin.users.become_instructors.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function reject($id)
    {
        try {
            $this->authorize('admin_become_instructors_reject');

            $becomeInstructors = BecomeInstructor::findOrFail($id);

            $this->handleRefundPackage($becomeInstructors);

            $becomeInstructors->update([
                'status' => 'reject'
            ]);

            $becomeInstructors->sendNotificationToUser('reject');

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('reject error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->authorize('admin_become_instructors_delete');

            $becomeInstructors = BecomeInstructor::findOrFail($id);

            $this->handleRefundPackage($becomeInstructors);

            $becomeInstructors->delete();

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('delete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleRefundPackage($becomeInstructors)
    {
        if (!empty($becomeInstructors->package_id)) {
            $sale = Sale::where('buyer_id', $becomeInstructors->user_id)
                ->where('type', Sale::$registrationPackage)
                ->where('registration_package_id', $becomeInstructors->package_id)
                ->whereNull('refund_at')
                ->first();

            if (!empty($sale)) {
                if (!empty($sale->total_amount)) {

                    Accounting::refundAccounting($sale);
                }

                $sale->update(['refund_at' => time()]);
            }
        }
    }
}
