<?php

namespace App\Http\Controllers\Api\Config;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Api\Request;
use App\Http\Controllers\Controller;
use App\Models\PaymentChannel;

class ConfigController extends Controller
{

    public function list(Request $request)
    {
        try {
            return self::get();
        } catch (\Exception $e) {
            \Log::error('list error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public static function get()
    {
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';

        $userLanguages = getGeneralSettings('user_languages');
        if (!empty($userLanguages) and is_array($userLanguages)) {
            $userLanguages = getLanguages($userLanguages);
        } else {
            $userLanguages = [];
        }
        $paymentChannels = PaymentChannel::all()->groupBy('status');
        $getFinancialSettings = getFinancialSettings() ['minimum_payout'];
        $currency = [
            'sign' => currencySign(),
            'name' => currency()
        ];

        $data = [
            'register_method' => $registerMethod,
            'offline_bank_account' => getOfflineBanksTitle() ?? null,
            'user_language' => $userLanguages,
            'payment_channels' => $paymentChannels,
            'minimum_payout_amount' => $getFinancialSettings,
            'currency' => $currency,
            'price_display' => getFinancialSettings('price_display') ?? 'only_price',
            'currency_position' => getFinancialSettings('currency_position')
        ];
        return $data;

    }

}
