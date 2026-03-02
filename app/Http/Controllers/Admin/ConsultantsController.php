<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Exports\ConsultantsExport;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\Models\Sale;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\BackgroundExportTrait;

class ConsultantsController extends Controller
{
    use BackgroundExportTrait;
    public function index(Request $request, $exportExcel = false)
    {
        try {
            $this->authorize('admin_consultants_lists');

            $query = User::whereIn('role_name', [Role::$teacher, Role::$organization])
                ->join('meetings', 'meetings.creator_id', '=', 'users.id')
                ->select('users.*', 'meetings.amount', 'meetings.discount', 'meetings.disabled')
                ->groupBy('users.id');

            $totalConsultants = User::whereHas('meeting')->get();

            $availableConsultants = User::whereHas('meeting', function ($query) {
                $query->where('disabled', false);
            })->count();

            $unavailableConsultants = User::whereHas('meeting', function ($query) {
                $query->where('disabled', true);
            })->count();

            $consultantsWithoutAppointment = 0;
            foreach ($totalConsultants as $consultant) {
                $checkConsultantsMeetingSale = Sale::whereNull('refund_at')
                    ->where('seller_id', $consultant->id)
                    ->whereNotNull('meeting_id')
                    ->count();

                if ($checkConsultantsMeetingSale < 1) {
                    $consultantsWithoutAppointment += 1;
                }
            }

            $organizations = User::select('id', 'full_name', 'created_at')
                ->where('role_name', Role::$organization)
                ->orderBy('created_at', 'desc')
                ->get();

            $userGroups = Group::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            $query = $this->filters($query, $request);

            if ($exportExcel) {
                return $query->with([
                    'userGroup'
                ])->get();
            }

            $consultants = $query->with([
                'userGroup',
            ])->paginate(10);

            $consultants = $this->addUsersExtraInfo($consultants);

            $data = [
                'pageTitle' => trans('admin/main.consultants_list_title'),
                'totalConsultants' => count($totalConsultants),
                'availableConsultants' => $availableConsultants,
                'unavailableConsultants' => $unavailableConsultants,
                'consultantsWithoutAppointment' => $consultantsWithoutAppointment,
                'organizations' => $organizations,
                'userGroups' => $userGroups,
                'consultants' => $consultants,
            ];

            return view('admin.consultants.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function addUsersExtraInfo($users)
    {
        foreach ($users as $user) {
            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');
            $reserveMeetingsQuery = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                ->where(function ($query) {
                    $query->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    });

                    $query->orWhere(function ($query) {
                        $query->whereIn('status', ['canceled']);
                        $query->whereHas('sale');
                    });
                });

            $user->meetingsSalesCount = deepClone($reserveMeetingsQuery)->count();
            $user->meetingsSalesSum = deepClone($reserveMeetingsQuery)->sum('paid_amount');
            $user->pendingAppointments = deepClone($reserveMeetingsQuery)->where('status', 'pending')->count();

        }

        return $users;
    }

    private function filters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $search = $request->get('search', null);
        $sort = $request->get('sort', null);
        $organization_id = $request->get('organization_id', null);
        $group_id = $request->get('group_id', null);
        $disabled = $request->get('disabled', null);

        $query = fromAndToDateFilter($from, $to, $query, 'users.created_at');

        if (!empty($search)) {
            $query->where('users.full_name', 'like', "%$search%");
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'appointments_asc':
                    $query->orderBy('sales_count', 'asc');
                    break;
                case 'appointments_desc':
                    $query->orderBy('sales_count', 'desc');
                    break;
                case 'appointments_income_asc':
                    $query->orderBy('totalIncome', 'asc');
                    break;
                case 'appointments_income_desc':
                    $query->orderBy('totalIncome', 'desc');
                    break;
                case 'pending_appointments_asc':
                    $query->orderBy('pendingAppointments', 'asc');
                    break;
                case 'pending_appointments_desc':
                    $query->orderBy('pendingAppointments', 'desc');
                    break;
                case 'created_at_asc':
                    $query->orderBy('users.created_at', 'asc');
                    break;
                case 'created_at_desc':
                    $query->orderBy('users.created_at', 'desc');
                    break;
            }
        }

        if (!empty($organization_id)) {
            $query->where('organ_id', $organization_id);
        }

        if (!empty($group_id)) {
            $query->where('group_id', $group_id);
        }

        if (isset($disabled)) {
            $query->where('disabled', ($disabled == '1') ? 1 : 0);
        }

        return $query;
    }

    public function exportExcel(Request $request)
    {
        try {
            $this->authorize('admin_consultants_export_excel');

            $consultants = $this->index($request, true);

            $exports = new ConsultantsExport($consultants);

            return $this->dispatchBackgroundExport($exports, 'consultants_' . date('Y-m-d_H-i-s') . '.xlsx', 'Consultants Export');
        } catch (\Exception $e) {
            \Log::error('exportExcel error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
