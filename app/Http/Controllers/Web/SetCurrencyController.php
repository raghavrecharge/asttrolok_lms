<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mixins\Financial\MultiCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class SetCurrencyController extends Controller
{
    public function setCurrency(Request $request)
    {
        $this->validate($request, [
            'currency' => 'required'
        ]);

        $currency = $request->get('currency');
        // Session::put('selectbysuer',true);
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
                Cookie::queue('user_currency', $currency, 30 * 24 * 60,'/',            // path
                '.asttrolok.com', // domain
                true,           // secure
                true,           // httpOnly
                false,          // raw
                'None'          // SameSite=None
                );
            }
        }

        return redirect()->back();
    }
    
    // public function setCurrency(Request $request)
    // {
    //     $this->validate($request, [
    //         'currency' => 'required'
    //     ]);

    //     $currency = $request->get('currency');
    //     Session::put('selectbysuer',true);
    //     $multiCurrency = new MultiCurrency();
    //     $currencies = $multiCurrency->getCurrencies();
    //     $signs = $currencies->pluck('currency')->toArray();

    //     if (in_array($currency, $signs)) {
    //         if (auth()->check()) {
    //             $user = auth()->user();
    //             $user->update([
    //                 'currency' => $currency
    //             ]);
    //         } else {
    //             Cookie::queue('user_currency', $currency, 30 * 24 * 60);
    //         }
    //     }

    //     return redirect()->back();
    // }
}
