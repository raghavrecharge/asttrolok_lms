@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')

@endpush

@section('content')

    <section>
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="section-title">{{ trans('panel.favorite_live_classes') }}</h2>
        </div>

        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/webinars/favorites" method="get">
                <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px;">

                    {{-- Search --}}
                    <div style="flex:1 1 200px;min-width:180px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="search" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.search') }}
                        </label>
                        <div style="position:relative;">
                            <input type="text" name="search" value="{{ request()->get('search') }}"
                                   class="form-control"
                                   placeholder="{{ trans('public.search_anything') }}"
                                   style="height:40px;padding-left:12px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"/>
                        </div>
                    </div>

                    {{-- Category --}}
                    <div style="flex:1 1 200px;min-width:180px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="grid" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.category') }}
                        </label>
                        <div style="position:relative;">
                            <select name="category_id" class="select2" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;">
                                <option value="all">{{ trans('public.all') }}</option>
                                @foreach($categories as $category)
                                    @if(!empty($category->subCategories) and count($category->subCategories))
                                        <optgroup label="{{ $category->title }}">
                                            @foreach($category->subCategories as $subCategory)
                                                <option value="{{ $subCategory->id }}" @if(request()->get('category_id') == $subCategory->id) selected @endif>{{ $subCategory->title }}</option>
                                            @endforeach
                                        </optgroup>
                                    @else
                                        <option value="{{ $category->id }}" @if(request()->get('category_id') == $category->id) selected @endif>{{ $category->title }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                                <i data-feather="chevron-down" width="13" height="13"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Instructor --}}
                    <div style="flex:1 1 200px;min-width:180px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="user" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.instructor') }}
                        </label>
                        <div style="position:relative;">
                            <select name="instructor_id" class="select2" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;">
                                <option value="all">{{ trans('public.all') }}</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" @if(request()->get('instructor_id') == $instructor->id) selected @endif>{{ $instructor->full_name }}</option>
                                @endforeach
                            </select>
                            <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                                <i data-feather="chevron-down" width="13" height="13"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div style="flex:0 0 auto;">
                        <button type="submit" style="height:40px;background:linear-gradient(135deg,#43d477 0%,#2ecc71 100%);border:none;border-radius:9px;color:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 14px rgba(67,212,119,0.25);white-space:nowrap;padding:0 20px;transition:all .2s;" onmouseover="this.style.boxShadow='0 6px 18px rgba(67,212,119,0.35)'" onmouseout="this.style.boxShadow='0 4px 14px rgba(67,212,119,0.25)'">
                            <i data-feather="search" width="13" height="13"></i>
                            {{ trans('public.show_results') }}
                        </button>
                    </div>

                </div>
            </form>
        </div>

        @if(!empty($favorites) and !$favorites->isEmpty())
<div class="row mt-30">
            @foreach($favorites as $favorite)

                    <div class="col-lg-6">
                        <div class="webinar-card webinar-list d-flex">
                            <div class="image-box" style="height:auto !important;">
                                <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $favorite->webinar->getImage() }}"  class="img-cover" alt="">

                                @if($favorite->webinar->type == 'webinar')
                                    <div class="progress">
                                        <span class="progress-bar" style="width: {{ $favorite->webinar->getProgress() }}%"></span>
                                    </div>
                                @endif
                            </div>

                            <div class="webinar-card-body w-100 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="{{ $favorite->webinar->getUrl() }}" target="_blank">
                                        <h3 class="font-16 text-dark-blue font-weight-bold">{{ $favorite->webinar->title }}</h3>
                                    </a>

                                    <div class="btn-group dropdown table-actions">
                                        <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i data-feather="more-vertical" height="20"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="/panel/webinars/favorites/{{ $favorite->id }}/delete" class="webinar-actions d-block delete-action">{{ trans('public.remove') }}</a>
                                        </div>
                                    </div>
                                </div>

                                @include(getTemplate() . '.includes.webinar.rate',['rate' => $favorite->webinar->getRate()])

                                <div class="webinar-price-box mt-15">
                                    @if($favorite->webinar->bestTicket() < $favorite->webinar->price)
                                        <span class="real">{{ handlePrice($favorite->webinar->bestTicket(), true, true, false, null, true) }}</span>
                                        <span class="off ml-10">{{ handlePrice($favorite->webinar->price, true, true, false, null, true) }}</span>
                                    @else
                                        <span class="real">{{ handlePrice($favorite->webinar->price, true, true, false, null, true) }}</span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center justify-content-between flex-wrap mt-auto">
                                    <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class="stat-title">{{ trans('public.item_id') }}:</span>
                                        <span class="stat-value">{{ $favorite->webinar->id }}</span>
                                    </div>

                                    <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class="stat-title">{{ trans('public.category') }}:</span>
                                        <span class="stat-value">{{ !empty($favorite->webinar->category_id) ? $favorite->webinar->category->title : '' }}</span>
                                    </div>

                                    <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class="stat-title">{{ trans('public.duration') }}:</span>
                                        <span class="stat-value">{{ convertMinutesToHourAndMinute($favorite->webinar->duration) }} {{ trans('home.hours') }}</span>
                                    </div>

                                    <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                        @if($favorite->webinar->isWebinar())
                                            <span class="stat-title">{{ trans('public.start_date') }}:</span>
                                        @else
                                            <span class="stat-title">{{ trans('public.created_at') }}:</span>
                                        @endif
                                        <span class="stat-value">{{ dateTimeFormat(!empty($favorite->webinar->start_date) ? $favorite->webinar->start_date : $favorite->webinar->created_at,'j M Y') }}</span>
                                    </div>

                                    <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class="stat-title">{{ trans('public.instructor') }}:</span>
                                        <span class="stat-value">{{ $favorite->webinar->teacher->full_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

            @endforeach
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'student.png',
                'title' => trans('panel.no_result_favorites'),
                'hint' =>  trans('panel.no_result_favorites_hint') ,
            ])
        @endif

    </section>

    <div class="my-30">
        {{ $favorites->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection
