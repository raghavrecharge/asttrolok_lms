<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\PaymentChannel;
use Illuminate\Http\Request;

class PaymentChannelController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_payment_channel_list');

            $paymentChannels = PaymentChannel::orderBy('created_at', 'desc')->paginate(10);

            $data = [
                'pageTitle' => trans('admin/pages/paymentChannels.payment_channels'),
                'paymentChannels' => $paymentChannels
            ];

            return view('admin.settings.financial.payment_channel.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit($id)
    {
        try {
            $this->authorize('admin_payment_channel_edit');

            $paymentChannel = PaymentChannel::findOrFail($id);

            $data = [
                'pageTitle' => trans('admin/pages/paymentChannels.payment_channel_edit'),
                'paymentChannel' => $paymentChannel
            ];

            return view('admin.settings.financial.payment_channel.create', $data);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
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
            $this->authorize('admin_payment_channel_edit');

            $this->validate($request, [
                'title' => 'required',
            ]);

            $data = $request->all();
            $paymentChannel = PaymentChannel::findOrFail($id);

            $paymentChannel->update([
                'title' => $data['title'],
                'image' => $data['image'],
                'status' => $data['status'],
                'currencies' => !empty($data['currencies']) ? json_encode($data['currencies']) : null,
            ]);

            return redirect(getAdminPanelUrl() . '/settings/financial?page=payment_channels');
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function toggleStatus($id)
    {
        try {
            $this->authorize('admin_payment_channel_toggle_status');

            $channel = PaymentChannel::findOrFail($id);

            $channel->update([
                'status' => ($channel->status == 'active') ? 'inactive' : 'active'
            ]);

            return redirect(getAdminPanelUrl() . '/settings/financial');
        } catch (\Exception $e) {
            \Log::error('toggleStatus error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
