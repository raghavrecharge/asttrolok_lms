@php
    $userCurrency = $globalCurrency ?? currency();
    $globalPricingCurrencies = config('global_pricing.currencies', []);

    // Merge: legacy $currencies from DB + global_pricing config currencies
    $allCurrencyCodes = [];
    $mergedCurrencies = collect();

    // Add legacy currencies first
    if (!empty($currencies) && count($currencies)) {
        foreach ($currencies as $c) {
            if (!in_array($c->currency, $allCurrencyCodes)) {
                $allCurrencyCodes[] = $c->currency;
                $mergedCurrencies->push($c);
            }
        }
    }

    // Add global_pricing currencies that aren't already in legacy list
    foreach ($globalPricingCurrencies as $code => $meta) {
        if (!in_array($code, $allCurrencyCodes)) {
            $allCurrencyCodes[] = $code;
            $obj = new \stdClass();
            $obj->currency = $code;
            $obj->currency_position = 'left';
            $obj->currency_separator = 'dot';
            $obj->currency_decimal = 0;
            $obj->exchange_rate = null;
            $mergedCurrencies->push($obj);
        }
    }
@endphp

@if($mergedCurrencies->count() > 0)
<style>
    .custom-dropdown-body__item {
        background-color: #ffffff !important;
    }
    .custom-dropdown-body__item span,
    .custom-dropdown-body__item div {
        color: #000000 !important;
    }
    .custom-dropdown-body__item.active span,
    .custom-dropdown-body__item.active div {
        color: #43d477 !important;
    }
    .custom-dropdown-body__item:hover span,
    .custom-dropdown-body__item:hover div {
        color: #43d477 !important;
    }
    .custom-dropdown .custom-dropdown-body__item.active {
        border-left: 2px solid #43d477 !important;
        color: #43d477 !important;
    }
</style>
    <div class="js-currency-select custom-dropdown position-relative">
        <form action="/set-currency" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="currency" value="{{ $userCurrency }}">

            @foreach($mergedCurrencies as $currencyItem)
                @if($userCurrency == $currencyItem->currency)
                    <div class="custom-dropdown-toggle d-flex align-items-center cursor-pointer">
                        <div class="mr-5 text-white">
                            <span class="js-lang-title font-14">{{ $currencyItem->currency }} ({{ $globalPricingCurrencies[$currencyItem->currency]['symbol'] ?? currencySign($currencyItem->currency) }})</span>
                        </div>
                        <i data-feather="chevron-down" class="icons" width="14px" height="14px" style="stroke:#fff;"></i>
                    </div>
                @endif
            @endforeach
        </form>

        <div class="custom-dropdown-body py-10">
            @foreach($mergedCurrencies as $currencyItem)
                @php
                    $symbol = $globalPricingCurrencies[$currencyItem->currency]['symbol'] ?? currencySign($currencyItem->currency);
                    $name = $globalPricingCurrencies[$currencyItem->currency]['name'] ?? currenciesLists($currencyItem->currency);
                @endphp
                <div class="js-currency-dropdown-item custom-dropdown-body__item cursor-pointer {{ ($userCurrency == $currencyItem->currency) ? 'active' : '' }}" data-value="{{ $currencyItem->currency }}" data-title="{{ $currencyItem->currency }} ({{ $symbol }})">
                    <div class="d-flex align-items-center w-100 px-15 py-5 text-gray bg-transparent">
                        <div class="size-32 position-relative d-flex-center bg-gray100 rounded-sm">
                            {{ $symbol }}
                        </div>
                        <span class="ml-5 font-14">{{ $name }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">
@endif
