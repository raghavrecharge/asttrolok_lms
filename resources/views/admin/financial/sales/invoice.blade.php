<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $pageTitle ?? '' }} </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#32A128",
                        "accent": "#eab308",
                        "background-light": "#F7F9FC",
                        "background-dark": "#112210",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.75rem",
                        "xl": "1rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-container { padding: 0 !important; max-width: 100% !important; }
            .shadow-sm { shadow: none !important; }
            .border { border: 1px solid #e2e8f0 !important; }
        }
    </style>
</head>
<body class="bg-background-light text-slate-900 font-display min-h-screen">
<div class="layout-container flex flex-col items-center print-container">
    <div class="w-full max-w-[1000px] px-4 md:px-8 py-6">
        
        <!-- Top Action Bar (No Print) -->
        <header class="flex items-center justify-between no-print border-b border-slate-200 bg-white rounded-xl px-6 py-4 shadow-sm mb-6">
            <div class="flex items-center gap-4">
                <div class="size-8 flex items-center justify-center bg-primary/10 rounded-lg">
                    <span class="material-symbols-outlined text-primary text-[20px]">receipt_long</span>
                </div>
                <h2 class="text-slate-900 text-lg font-bold leading-tight tracking-tight">Invoice Details</h2>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 shadow-md shadow-primary/20 transition-all">
                    <span class="material-symbols-outlined text-sm">print</span>
                    Print Invoice
                </button>
            </div>
        </header>

        <!-- Main Invoice Content -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            
            <!-- Branding & Status Header -->
            <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-slate-50/30">
                <div class="flex flex-col gap-4">
                    <img src="https://storage.googleapis.com/astrolok/store/1/Home/asttroloklogo-min_converted.webp" class="h-10 w-auto object-contain" alt="Asttrolok Logo">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">TAX INVOICE</h1>
                        <p class="text-sm font-bold text-slate-400 mt-1 uppercase tracking-widest">Transaction #{{ $sale->id }}</p>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-primary/10 text-primary text-sm font-black uppercase tracking-wider">
                        <span class="size-2 bg-primary rounded-full mr-2"></span>
                        PAID
                    </span>
                    <p class="text-xs font-semibold text-slate-500">{{ dateTimeFormat($sale->created_at,'j F Y, H:i') }}</p>
                </div>
            </div>

            <!-- Billing Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 p-8 border-b border-slate-50 text-sm">
                <div class="space-y-4">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2">Billed To</h3>
                    <div class="space-y-1">
                        <p class="text-lg font-black text-slate-800">{{ !empty($sale->gift_recipient) ? $sale->gift_recipient : $sale->buyer->full_name }}</p>
                        <p class="text-slate-500 font-medium">Student ID: #{{ $sale->buyer->id }}</p>
                        @if(!empty($orderAddress))
                            <p class="text-slate-500 leading-relaxed mt-2">
                                {{ $orderAddress->Address ?? $orderAddress->StreetAddress . ', ' . $orderAddress->City . ', ' . $orderAddress->StateProvince . ' - ' . $orderAddress->PostalCode . ', ' . $orderAddress->Country }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="space-y-4 text-right">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2">Platform Details</h3>
                    <div class="space-y-1">
                        <p class="text-lg font-black text-primary">Asttrolok</p>
                        <p class="text-slate-500 font-medium">Empowering Enlightenment</p>
                        <div class="text-slate-500 leading-relaxed mt-2">
                            {!! nl2br(getContactPageSettings('address')) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="p-8 pb-0">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Order Summary</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b-2 border-slate-50">
                                <th class="pb-4 pt-2">Description</th>
                                <th class="pb-4 pt-2 text-center">Type</th>
                                <th class="pb-4 pt-2 text-right">Unit Price</th>
                                <th class="pb-4 pt-2 text-right">Discount</th>
                                <th class="pb-4 pt-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr class="border-b border-slate-50 relative group">
                                <td class="py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                                            @if(!empty($webinar)) <span class="material-symbols-outlined text-[20px]">movie</span>
                                            @elseif(!empty($subscription)) <span class="material-symbols-outlined text-[20px]">loyalty</span>
                                            @else <span class="material-symbols-outlined text-[20px]">package_2</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-black text-slate-800 text-base">
                                                @if(!empty($webinar)) {{ $webinar->title ?? $webinar->slug ?? '-' }}
                                                @elseif(!empty($subscription)) {{ $subscription->title ?? $subscription->slug ?? '-' }}
                                                @elseif(!empty($bundle)) {{ $bundle->title ?? $bundle->slug ?? '-' }}
                                                @else - @endif
                                            </span>
                                            @if(!empty($webinar))
                                                <span class="text-xs font-bold text-slate-400 mt-0.5">Instructor: {{ !empty($webinar->teacher) ? $webinar->teacher->full_name : '-' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-widest">
                                        @if(!empty($webinar)) Webinar @elseif(!empty($subscription)) Subscription @elseif(!empty($bundle)) Bundle @else Item @endif
                                    </span>
                                </td>
                                <td class="py-6 text-right font-medium text-slate-600">
                                    @if(!empty($webinar)) {{ handlePrice($webinar->price ?? 0) }}
                                    @elseif(!empty($subscription)) {{ handlePrice($subscription->price ?? 0) }}
                                    @elseif(!empty($bundle)) {{ handlePrice($bundle->price ?? 0) }}
                                    @else - @endif
                                </td>
                                <td class="py-6 text-right font-medium text-slate-400">
                                    {{ handlePrice($sale->discount ?? 0) }}
                                </td>
                                <td class="py-6 text-right font-black text-slate-900 text-base">
                                    {{ handlePrice($sale->total_amount ?? 0) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Financial Totals -->
            <div class="p-8 pt-0 flex flex-col md:flex-row justify-between items-start gap-8">
                <div class="md:w-1/2 p-6 rounded-2xl bg-slate-50 border border-slate-100 mt-8">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">info</span>
                        Important Notice
                    </h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                        This is a computer-generated invoice and does not require a physical signature. Returns or refunds are subject to our standard terms and conditions. For any billing queries, please contact Asttrolok Support with your Transaction ID #{{ $sale->id }}.
                    </p>
                </div>
                
                <div class="md:w-1/3 w-full space-y-3 mt-8">
                    <div class="flex justify-between items-center text-sm font-bold text-slate-500">
                        <span>Subtotal</span>
                        <span>{{ handlePrice($sale->amount) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm font-bold text-slate-500">
                        <span>Tax ({{ (int)getFinancialSettings('tax') }}%)</span>
                        <span class="text-slate-800">{{ $sale->tax ? handlePrice($sale->tax) : '-' }}</span>
                    </div>
                    @if(!empty($sale->discount))
                        <div class="flex justify-between items-center text-sm font-bold text-accent">
                            <span>Total Discount</span>
                            <span>-{{ handlePrice($sale->discount) }}</span>
                        </div>
                    @endif
                    <div class="pt-4 border-t-2 border-slate-100 flex justify-between items-center">
                        <span class="text-base font-black text-slate-900 uppercase tracking-tight">Net Amount</span>
                        <span class="text-2xl font-black text-primary">{{ handlePrice($sale->total_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer Branding -->
            <div class="p-8 bg-slate-900 flex flex-col items-center gap-2 text-center">
                <p class="text-white font-black text-sm tracking-widest uppercase">Thank you for choosing Asttrolok</p>
                <p class="text-slate-500 text-[10px] font-medium tracking-tight">A step towards spiritual and celestial wisdom.</p>
            </div>
        </div>

        <footer class="flex items-center justify-center py-8 text-slate-400 text-[10px] font-bold uppercase tracking-widest no-print">
            Asttrolok Administrative Suite • Secured Transaction
        </footer>
    </div>
</div>
</body>
</html>
