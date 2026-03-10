@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Advertising / Banner Configuration</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ getAdminPanelUrl() }}/advertising/banners" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all border border-gray-200 shadow-sm text-sm">
                    <span class="material-symbols-rounded text-xl text-gray-400">arrow_back</span>
                    {{ trans('admin/main.back') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <form action="{{ getAdminPanelUrl() }}/advertising/banners/{{ !empty($banner) ? $banner->id.'/update' : 'store' }}" method="Post" class="max-w-4xl">
                        {{ csrf_field() }}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                            <!-- Left Column: Basic Info -->
                            <div class="space-y-8 text-left">
                                <div class="space-y-6">
                                    <h3 class="text-xs font-bold text-primary uppercase tracking-widest flex items-center gap-2">
                                        <span class="material-symbols-rounded text-lg">info</span>
                                        General Information
                                    </h3>

                                    @if(!empty(getGeneralSettings('content_translate')))
                                        <div class="form-group space-y-2">
                                            <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest">{{ trans('auth.language') }}</label>
                                            <div class="relative">
                                                <select name="locale" class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none {{ !empty($banner) ? 'js-edit-content-locale' : '' }}">
                                                    @foreach($userLanguages as $lang => $language)
                                                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                                            </div>
                                            @error('locale')
                                                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @else
                                        <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                    @endif

                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest">{{ trans('admin/main.title') }}</label>
                                        <input type="text" name="title"
                                               class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all font-bold placeholder:font-medium @error('title') ring-2 ring-rose-100 @enderror"
                                               value="{{ !empty($banner) ? $banner->title : old('title') }}" placeholder="Banner Title"/>
                                        @error('title')
                                            <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest">{{ trans('admin/main.position') }}</label>
                                        <div class="relative">
                                            <select name="position" class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none font-bold @error('position') ring-2 ring-rose-100 @enderror">
                                                <option selected disabled>{{ trans('admin/main.position') }}</option>
                                                @foreach(\App\Models\AdvertisingBanner::$positions as $position)
                                                    <option value="{{ $position }}" @if(!empty($banner) and $banner->position == $position) selected @endif>{{ $position }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">place</span>
                                        </div>
                                        @error('position')
                                            <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest">{{ trans('admin/main.link') }}</label>
                                        <div class="relative">
                                            <input type="text" name="link"
                                                   class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all font-mono font-bold @error('link') ring-2 ring-rose-100 @enderror"
                                                   value="{{ !empty($banner) ? $banner->link : old('link') }}" placeholder="https://..."/>
                                            <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg">link</span>
                                        </div>
                                        @error('link')
                                            <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Media & Status -->
                            <div class="space-y-8 border-l border-gray-50 pl-12 text-left">
                                <div class="space-y-6">
                                    <h3 class="text-xs font-bold text-primary uppercase tracking-widest flex items-center gap-2">
                                        <span class="material-symbols-rounded text-lg">image</span>
                                        Media & Style
                                    </h3>

                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest">{{ trans('admin/main.image') }}</label>
                                        <div class="flex gap-2">
                                            <div class="relative flex-1">
                                                <input type="text" name="image" id="image" value="{{ !empty($banner->image) ? $banner->image : old('image') }}" 
                                                       class="w-full pl-4 pr-11 py-3 bg-gray-50 border-none rounded-2xl text-xs font-mono font-bold focus:ring-2 focus:ring-primary/20 transition-all @error('image') ring-2 ring-rose-100 @enderror"
                                                       placeholder="Select from media manager..."/>
                                                <button type="button" class="admin-file-manager absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center hover:bg-primary transition-all hover:text-white" data-input="image" data-preview="holder">
                                                    <span class="material-symbols-rounded text-lg">upload</span>
                                                </button>
                                            </div>
                                            <button type="button" class="admin-file-view w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center hover:bg-emerald-50 hover:text-emerald-600 transition-all" data-input="image">
                                                <span class="material-symbols-rounded">visibility</span>
                                            </button>
                                        </div>
                                        @error('image')
                                            <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest">{{ trans('admin/main.banner_size') }}</label>
                                        <div class="relative border-none">
                                            <select name="size" class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none font-bold @error('size') ring-2 ring-rose-100 @enderror">
                                                <option selected disabled>{{ trans('admin/main.banner_size') }}</option>
                                                @foreach(\App\Models\AdvertisingBanner::$size as $size => $value)
                                                    <option value="{{ $size }}" @if(!empty($banner) and $banner->size == $size) selected @endif>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">aspect_ratio</span>
                                        </div>
                                        @error('size')
                                            <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group pt-4">
                                        <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-dashed border-gray-200 group">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-gray-400 shadow-sm transition-colors group-hover:text-emerald-600 border border-gray-100">
                                                    <span class="material-symbols-rounded text-xl">publish</span>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-black text-gray-900 leading-none uppercase tracking-tight">Status</p>
                                                    <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold font-mono">Published to platform</p>
                                                </div>
                                            </div>
                                            <div class="relative flex items-center">
                                                <input type="hidden" name="published" value="0">
                                                <input type="checkbox" name="published" id="published" value="1" {{ (!empty($banner) and $banner->published) ? 'checked' : '' }} class="hidden peer"/>
                                                <label for="published" class="w-12 h-6 bg-gray-200 peer-checked:bg-emerald-500 rounded-full relative cursor-pointer transition-all duration-300 shadow-inner">
                                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-6 shadow-sm"></div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mt-12 pt-8 border-t border-gray-50">
                            <button type="submit" class="flex items-center gap-2 px-8 py-3 bg-gray-900 text-white rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-gray-800 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:scale-95">
                                <span class="material-symbols-rounded text-xl leading-none">check_circle</span>
                                {{ trans('admin/main.submit') }}
                            </button>
                            <a href="{{ getAdminPanelUrl() }}/advertising/banners" class="px-8 py-3 bg-gray-100 text-gray-600 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-gray-200 transition-all active:scale-95">
                                {{ trans('admin/main.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
