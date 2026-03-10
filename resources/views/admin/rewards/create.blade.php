@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none text-left">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ trans('update.rewards') }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Rewards / Condition Settings</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_rewards_items')
                    <button type="button" class="js-add-new-reward flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 transition-all shadow-sm active:scale-95 group">
                        <span class="material-symbols-rounded text-xl group-hover:rotate-90 transition-transform">add</span>
                        {{ trans('update.new_condition') }}
                    </button>
                @endcan
            </div>
        </div>

        <div class="section-body">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden text-left">
                <div class="table-responsive text-decoration-none">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-left">{{ trans('admin/main.title') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.score') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.status') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.created_at') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @if(!empty($rewards))
                                @foreach($rewards as $reward)
                                    <tr class="group hover:bg-gray-50/50 transition-all">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-lg bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm border border-primary/10">
                                                    <span class="material-symbols-rounded text-lg">military_tech</span>
                                                </div>
                                                <span class="font-bold text-gray-900 tracking-tight group-hover:text-primary transition-colors italic uppercase text-xs">{{ trans('update.reward_type_'.$reward->type) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 rounded-full border border-amber-100 font-black text-xs">
                                                <span class="material-symbols-rounded text-sm">stars</span>
                                                {{ $reward->score }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($reward->status == 'active')
                                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100 italic">{{ trans('admin/main.active') }}</span>
                                            @else
                                                <span class="px-3 py-1 bg-gray-50 text-gray-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-gray-100 italic">{{ trans('admin/main.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-xs font-bold text-gray-700">{{ dateTimeFormat($reward->created_at,'j M Y') }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right pr-8">
                                            <div class="flex justify-end items-center gap-2">
                                                @can('admin_rewards_items')
                                                    <button type="button" class="js-edit-reward p-2 text-gray-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all" data-id="{{ $reward->id }}" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                        <span class="material-symbols-rounded text-xl leading-none">edit</span>
                                                    </button>
                                                @endcan

                                                @can('admin_rewards_item_delete')
                                                    @include('admin.includes.delete_button',[
                                                        'url' => getAdminPanelUrl().'/rewards/items/'.$reward->id.'/delete',
                                                        'btnClass' => 'p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all'
                                                    ])
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- Reward Setting Modal --}}
    <div class="modal fade" id="rewardSettingModal" tabindex="-1" aria-labelledby="contactMessageLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content overflow-hidden border-none rounded-3xl shadow-2xl">
                <div class="modal-header bg-gray-900 p-6">
                    <div class="flex items-center gap-3 text-left">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white border border-white/10 shrink-0">
                            <span class="material-symbols-rounded">rewarded_ads</span>
                        </div>
                        <h5 class="modal-title text-sm font-black text-white uppercase tracking-widest">{{ trans('update.new_condition') }}</h5>
                    </div>
                    <button type="button" class="text-white/40 hover:text-white transition-colors p-2" data-dismiss="modal" aria-label="Close">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <div class="modal-body p-8 text-left">
                    <form class="space-y-6">
                        <div class="form-group space-y-2">
                            <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('update.condition') }}</label>
                            <div class="relative group">
                                <select name="type" class="w-full pl-4 pr-10 py-3 bg-gray-50 border-none rounded-2xl text-sm font-bold appearance-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                                    <option selected disabled>-- Select Reward Type --</option>
                                    @foreach(\App\Models\Reward::getTypesLists() as $type)
                                        <option value="{{ $type }}">{{ trans('update.reward_type_'.$type) }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none group-hover:text-primary transition-colors">unfold_more</span>
                            </div>
                            <div class="invalid-feedback text-[10px] font-bold text-rose-500 uppercase tracking-widest mt-1 ml-1"></div>
                        </div>

                        <div class="js-score-input form-group space-y-2">
                            <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('update.score') }}</label>
                            <div class="relative">
                                <input type="number" name="score" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all placeholder:text-gray-300" placeholder="0"/>
                                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg transition-colors leading-none">stars</span>
                            </div>
                            <div class="invalid-feedback text-[10px] font-bold text-rose-500 uppercase tracking-widest mt-1 ml-1"></div>
                        </div>

                        <div class="js-condition-input form-group space-y-2 hidden">
                            <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('update.value') }}</label>
                            <div class="relative text-left">
                                <input type="text" name="condition" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-mono font-bold focus:ring-2 focus:ring-primary/20 transition-all placeholder:text-gray-300" placeholder="Condition value..."/>
                                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg transition-colors leading-none">terminal</span>
                            </div>
                            <div class="invalid-feedback text-[10px] font-bold text-rose-500 uppercase tracking-widest mt-1 ml-1"></div>
                        </div>

                        <div class="form-group">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 group transition-all hover:bg-white hover:border-emerald-500/20 shadow-sm border-dashed">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-gray-400 group-hover:text-emerald-600 shadow-sm border border-gray-100 transition-colors">
                                        <span class="material-symbols-rounded text-xl">power_settings_new</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="text-xs font-black text-gray-900 leading-none uppercase tracking-tight cursor-pointer mb-1" for="statusSwitch">Condition Status</label>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest leading-none italic">Enable Reward Condition</p>
                                    </div>
                                </div>
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="status" id="statusSwitch" class="hidden peer" checked>
                                    <label for="statusSwitch" class="w-11 h-6 bg-gray-200 peer-checked:bg-emerald-500 rounded-full relative cursor-pointer transition-all duration-300 shadow-inner">
                                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5 shadow-sm"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer bg-gray-50 p-6 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" class="px-6 py-2 bg-white text-gray-600 text-[10px] font-black uppercase tracking-widest rounded-xl border border-gray-200 hover:bg-gray-100 transition-all active:scale-95" data-dismiss="modal">{{ trans('admin/main.close') }}</button>
                    <button type="button" class="js-save-reward px-8 py-2 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-gray-800 transition-all shadow-md active:scale-95">{{ trans('admin/main.save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
    </script>
    <script src="/assets/default/js/admin/rewards_items.min.js"></script>
@endpush
