<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:border-primary/20 transition-all text-decoration-none">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
            <span class="material-symbols-rounded text-2xl">receipt_long</span>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest leading-none">{{ trans('update.total_rules') }}</p>
            <h3 class="text-xl font-bold text-gray-900 mt-1.5 tracking-tight">{{ $totalRules }}</h3>
        </div>
    </div>

    <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:border-emerald-500/20 transition-all text-decoration-none">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
            <span class="material-symbols-rounded text-2xl">check_circle</span>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest leading-none">{{ trans('update.active_rules') }}</p>
            <h3 class="text-xl font-bold text-gray-900 mt-1.5 tracking-tight">{{ $activeRules }}</h3>
        </div>
    </div>

    <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:border-rose-500/20 transition-all text-decoration-none">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 group-hover:scale-110 transition-transform">
            <span class="material-symbols-rounded text-2xl">cancel</span>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest leading-none">{{ trans('update.disabled_rules') }}</p>
            <h3 class="text-xl font-bold text-gray-900 mt-1.5 tracking-tight">{{ $disabledRules }}</h3>
        </div>
    </div>
</div>
