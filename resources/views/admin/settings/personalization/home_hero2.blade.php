<div class=" mt-3 ">
    <div class="row">
        <div class="col-12 col-md-6">
            <form action="{{ getAdminPanelUrl() }}/settings/main" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="name" value="home_hero2">
                <input type="hidden" name="page" value="personalization">
@php 
if (!empty($itemValue) and $itemValue['home_slider']) {
    $itemValue1 = $itemValue;
}else {
    $itemValue1 = [];
}
@endphp
                @if(!empty(getGeneralSettings('content_translate')))
                    <div class="form-group">
                        <label class="input-label">{{ trans('auth.language') }}</label>
                        <select name="locale" class="form-control js-edit-content-locale">
                            @foreach($userLanguages as $lang => $language)
                                <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', (!empty($itemValue1) and !empty($itemValue1['locale'])) ? $itemValue1['locale'] : app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                            @endforeach
                        </select>
                        @error('locale')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                @else
                    <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                @endif
                <input type="hidden" name="slider_id" value="{{ (!empty($itemValue1) and !empty($itemValue1['id'])) ? $itemValue1['id'] : old('id') }}">
                <div class="form-group">
                    <label>{{ trans('admin/main.title') }}</label>
                    <input type="text" name="title" required value="{{ (!empty($itemValue1) and !empty($itemValue1['title'])) ? $itemValue1['title'] : old('title') }}" class="form-control "/>
                </div>

                <div class="form-group">
                    <label>{{ trans('public.description') }}</label>
                    <textarea type="text" name="description" required rows="5" class="form-control ">{{ (!empty($itemValue1) and !empty($itemValue1['description'])) ? $itemValue1['description'] : old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Button Text</label>
                    <input type="text" name="button_text" required value="{{ (!empty($itemValue1) and !empty($itemValue1['button_text'])) ? $itemValue1['button_text'] : old('button_text') }}" class="form-control "/>
                </div>
                <div class="form-group">
                    <label>Button Url</label>
                    <input type="text" name="button_url" required value="{{ (!empty($itemValue1) and !empty($itemValue1['button_url'])) ? $itemValue1['button_url'] : old('button_url') }}" class="form-control "/>
                </div>
                <div class="form-group">
                    <label>Button color</label>
                    <input type="text" name="button_color" required value="{{ (!empty($itemValue1) and !empty($itemValue1['button_color'])) ? $itemValue1['button_color'] : old('button_color') }}" class="form-control "/>
                </div>
                <div class="form-group">
                    <label class="input-label">{{ trans('admin/main.hero_background') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="hero_background" data-preview="holder">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="hero_background" required id="hero_background" value="{{ (!empty($itemValue1) and !empty($itemValue1['hero_background'])) ? $itemValue1['hero_background'] : old('hero_background') }}" class="form-control"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="input-label">{{ trans('admin/main.hero_vector') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="hero_vector" data-preview="holder">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="hero_vector" required id="hero_vector" value="{{ (!empty($itemValue1) and !empty($itemValue1['hero_vector'])) ? $itemValue1['hero_vector'] : old('hero_vector') }}" class="form-control"/>
                    </div>
                </div>

                <div class="form-group custom-switches-stacked">
                    <label class="custom-switch pl-0">
                        <input type="hidden" name="has_lottie" value="0">
                        <input type="checkbox" name="has_lottie" id="hasLottie" value="1" {{ (!empty($itemValue1) and !empty($itemValue1['has_lottie']) and $itemValue1['has_lottie']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                        <span class="custom-switch-indicator"></span>
                        <label class="custom-switch-description mb-0 cursor-pointer" for="hasLottie">{{ trans('admin/main.has_lottie') }}</label>
                    </label>
                    <div class="text-muted text-small mt-1">{{ trans('admin/main.has_lottie_hint') }}</div>

                </div>

                <button type="submit" class="btn btn-success">{{ trans('admin/main.save') }}</button>
            </form>
        </div>
    </div>
</div>

@if(!empty($all_data))
<div class="table-responsive mt-5">
    <table class="table table-striped font-14">
        <tr>
            <th>{{ trans('admin/main.title') }}</th>
            <th>{{ trans('public.description') }}</th>
            <th>{{ trans('admin/main.hero_background') }}</th>
            <th>Hero image</th>
            <th>Button Text</th>
            <th>Button Url</th>
            <th>Button color</th>
            <th>{{ trans('admin/main.actions') }}</th>
        </tr>

        @foreach($all_data as $key => $row)
            <tr>
                <td>{{ $row->title }}</td>
                <td>{{ $row->description }}</td>
                <td>{{ $row->hero_background }}</td>
                <td>{{ $row->hero_vector }}</td>
                <td>{{ $row->button_text }}</td>
                <td>{{ $row->button_url }}</td>
                <td>{{ $row->button_color }}</td>
               
                <td>
                    @can('admin_settings_personalization')
                        <a href="{{ getAdminPanelUrl() }}/settings/home_slider/{{ $row['id'] }}/edit" class="btn-sm" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                            <i class="fa fa-edit"></i>
                        </a>
                    @endcan

                    @can('admin_settings_personalization')
                        @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/settings/home_slider/'. $row['id'] .'/delete' , 'btnClass' => 'btn-sm'])
                    @endcan
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endif