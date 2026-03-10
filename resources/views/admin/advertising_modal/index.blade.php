@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Advertising / Modal Configuration</p>
            </div>
        </div>

        <div class="section-body">
                                                <div class="col-6">
                                                    <label>{{ trans('admin/main.link') }}</label>
                                                    <input type="text" name="value[button2][link]" value="{{ (!empty($value) and !empty($value['button2'])) ? $value['button2']['link'] : '' }}" class="form-control "/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group custom-switches-stacked">
                                            <label class="custom-switch pl-0 d-flex align-items-center">
                                                <input type="hidden" name="value[status]" value="0">
                                                <input type="checkbox" name="value[status]" id="advertiseModalStatusSwitch" value="1" {{ (!empty($value) and !empty($value['status']) and $value['status']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                <span class="custom-switch-indicator"></span>
                                                <label class="custom-switch-description mb-0 cursor-pointer" for="advertiseModalStatusSwitch">{{ trans('admin/main.active') }}</label>
                                            </label>
                                            <div class="text-muted text-small mt-1">{{ trans('update.advertising_modal_status_hint') }}</div>
                                        </div>

                                    </div>
                                </div>

                                <div class="">
                                    <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                                    <button type="button" class="js-preview-modal btn btn-warning ml-2">{{ trans('update.preview') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/js/admin/advertising_modal.min.js"></script>
@endpush
