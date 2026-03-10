@extends('admin.layouts.app')

@section('content')
    <section class="section text-left">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none text-left">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Financial / Promotions / Configuration</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ getAdminPanelUrl() }}/financial/promotions" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-gray-50 transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-rounded text-xl text-gray-400">arrow_back</span>
                    {{ trans('admin/main.back') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ getAdminPanelUrl() }}/financial/promotions/{{ !empty($promotion) ? $promotion->id.'/update' : 'store' }}" method="Post">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Configuration -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-50 flex items-center gap-3 bg-gray-50/50">
                                <div class="w-8 h-8 rounded-xl bg-gray-900/5 flex items-center justify-center text-gray-900">
                                    <span class="material-symbols-rounded text-lg">edit_note</span>
                                </div>
                                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">{{ trans('admin/main.basic_information') }}</h3>
                            </div>
                            <div class="p-8 space-y-6">
                                @if(!empty(getGeneralSettings('content_translate')))
                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('auth.language') }}</label>
                                        <div class="relative group">
                                            <select name="locale" class="w-full pl-4 pr-10 py-3 bg-gray-50 border-none rounded-2xl text-sm font-bold appearance-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer {{ !empty($promotion) ? 'js-edit-content-locale' : '' }}">
                                                @foreach($userLanguages as $lang => $language)
                                                    <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none group-hover:text-primary transition-colors">unfold_more</span>
                                        </div>
                                    </div>
                                @else
                                    <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                @endif

                                <div class="form-group space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.title') }}</label>
                                    <div class="relative">
                                        <input type="text" name="title" value="{{ !empty($promotion) ? $promotion->title : old('title') }}" 
                                               class="w-full px-5 py-3 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all placeholder:text-gray-300 italic @error('title') is-invalid @enderror"
                                               placeholder="e.g., EARLY BIRD SPECIAL">
                                    </div>
                                    @error('title')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('public.description') }}</label>
                                    <textarea name="description" rows="6" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all resize-none placeholder:text-gray-300 italic @error('description') is-invalid @enderror" placeholder="Provide detailed information about this promotional campaign...">{{ !empty($promotion) ? $promotion->description : old('description') }}</textarea>
                                    @error('description')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Side Configuration -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                            <div class="p-6 border-b border-gray-50 flex items-center gap-3 bg-gray-50/50">
                                <div class="w-8 h-8 rounded-xl bg-gray-900/5 flex items-center justify-center text-gray-900">
                                    <span class="material-symbols-rounded text-lg">settings</span>
                                </div>
                                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">{{ trans('admin/main.settings') }}</h3>
                            </div>
                            <div class="p-8 space-y-6">
                                <div class="form-group space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('public.days') }}</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors leading-none">calendar_month</span>
                                        <input type="number" name="days" value="{{ !empty($promotion) ? $promotion->days : old('days') }}" 
                                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-primary/20 transition-all @error('days') is-invalid @enderror">
                                    </div>
                                    @error('days')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.price') }} ({{ $currency }})</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors leading-none">payments</span>
                                        <input type="number" name="price" value="{{ !empty($promotion) ? $promotion->price : old('price') }}" 
                                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-primary/20 transition-all @error('price') is-invalid @enderror">
                                    </div>
                                    @error('price')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group space-y-3">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.icon') }}</label>
                                    <div class="flex flex-col gap-4">
                                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100 border-dashed group hover:border-primary transition-all cursor-pointer admin-file-manager" data-input="icon" data-preview="holder">
                                            <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center text-gray-300 group-hover:text-primary border border-gray-100 overflow-hidden shadow-sm" id="holder">
                                                @if(!empty($promotion->icon))
                                                    <img src="{{ $promotion->icon }}" class="w-full h-full object-contain">
                                                @else
                                                    <span class="material-symbols-rounded text-3xl">add_photo_alternate</span>
                                                @endif
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black text-gray-900 uppercase tracking-tight leading-none mb-1">Upload Icon</span>
                                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest leading-none italic">PNG, SVG or URL</p>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input type="text" name="icon" id="icon" value="{{ !empty($promotion->icon) ? $promotion->icon : old('icon') }}" 
                                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-2xl text-[10px] font-mono font-bold focus:ring-2 focus:ring-primary/20 transition-all @error('icon') is-invalid @enderror"
                                                   placeholder="Icon Path...">
                                            <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors admin-file-view" data-input="icon">
                                                <span class="material-symbols-rounded text-lg leading-none">open_in_new</span>
                                            </button>
                                        </div>
                                    </div>
                                    @error('icon')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group pt-2">
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 group transition-all hover:bg-white hover:border-amber-500/20 shadow-sm border-dashed">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-gray-400 group-hover:text-amber-500 shadow-sm border border-gray-100 transition-colors">
                                                <span class="material-symbols-rounded text-xl">stars</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <label class="text-xs font-black text-gray-900 leading-none uppercase tracking-tight cursor-pointer mb-1" for="isPopular">Mark Popular</label>
                                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest leading-none italic">Display Popular Badge</p>
                                            </div>
                                        </div>
                                        <div class="relative flex items-center">
                                            <input type="hidden" name="is_popular" value="0">
                                            <input type="checkbox" name="is_popular" id="isPopular" value="1" {{ (!empty($promotion) and $promotion->is_popular) ? 'checked' : '' }} class="hidden peer">
                                            <label for="isPopular" class="w-11 h-6 bg-gray-200 peer-checked:bg-amber-500 rounded-full relative cursor-pointer transition-all duration-300 shadow-inner">
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5 shadow-sm"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8 bg-gray-50 border-t border-gray-100 space-y-4">
                                <button type="submit" class="w-full bg-gray-900 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] flex items-center justify-center gap-2 hover:bg-gray-800 transition-all shadow-xl active:scale-95">
                                    <span class="material-symbols-rounded text-xl leading-none">task_alt</span>
                                    {{ trans('admin/main.submit') }}
                                </button>
                                <a href="{{ getAdminPanelUrl() }}/financial/promotions" class="w-full bg-white text-gray-400 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-gray-200 flex items-center justify-center hover:bg-gray-100 transition-all italic">
                                    {{ trans('admin/main.cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

