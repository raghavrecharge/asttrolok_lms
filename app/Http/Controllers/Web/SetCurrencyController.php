<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Mixins\Financial\MultiCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class SetCurrencyController extends Controller
{
    public function setCurrency(Request $request)
    {
        try {
            $this->validate($request, [
                'currency' => 'required'
            ]);

            $currency = $request->get('currency');

            $multiCurrency = new MultiCurrency();
            $currencies = $multiCurrency->getCurrencies();
            $signs = $currencies->pluck('currency')->toArray();

            if (in_array($currency, $signs)) {
                if (auth()->check()) {
                    $user = auth()->user();
                    $user->update([
                        'currency' => $currency
                    ]);
                } else {
                    Cookie::queue('user_currency', $currency, 30 * 24 * 60,'/',
                    '.asttrolok.com',
                    true,
                    true,
                    false,
                    'None'
                    );
                }
            }

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('setCurrency error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
