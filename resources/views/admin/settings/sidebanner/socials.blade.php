<div class="tab-pane mt-3 fade  show active" id="socials" role="tabpanel" aria-labelledby="socials-tab">
    <div class="row">
        <div class="col-12 col-md-8 col-lg-6">
          
            <form action="{{ getAdminPanelUrl() }}/settings/sidebanner/store" method="post">
                {{ csrf_field() }}

                <input type="hidden" name="page" value="general">
                <input type="hidden" name="sidebanner" value="{{ !empty($sidebannerKey) ? $sidebannerKey : 'newSidebanner' }}">

                <div class="form-group">
                    <label>{{ trans('admin/main.title') }}</label>
                    <input type="text" name="value[title]" value="{{ (!empty($sidebanner)) ? $sidebanner->title : old('value.title') }}" class="form-control  @error('value.title') is-invalid @enderror" readonly requied/>
                    @error('value.title')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="input-label">{{ trans('admin/main.icon') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="image" data-preview="holder">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="value[image]" id="image" value="{{ (!empty($sidebanner)) ? $sidebanner->image : old('value.image') }}" class="form-control @error('value.image')  is-invalid @enderror"/>
                        @error('value.image')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ trans('public.link') }}</label>
                    <input type="text" name="value[link]" value="{{ (!empty($sidebanner)) ? $sidebanner->link : old('value.link') }}" class="form-control  @error('value.link') is-invalid @enderror"/>
                    @error('value.link')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group" style="display:none;">
                    <label>{{ trans('admin/main.order') }}</label>
                    <input type="number" name="value[order]" value="{{ (!empty($sidebanner)) ? $sidebanner->order : old('value.order') }}" class="form-control  @error('value.order') is-invalid @enderror" />
                    @error('value.order')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success mt-3">{{ trans('admin/main.submit') }}</button>
            </form>
        </div>
    </div>

    <div class="table-responsive mt-5">
        <table class="table table-striped font-14">
            <tr>
                <th>{{ trans('admin/main.icon') }}</th>
                <th>{{ trans('public.title') }}</th>
                <th>{{ trans('public.link') }}</th>
                <th>{{ trans('public.controls') }}</th>
            </tr>
            @if(!empty($itemValue))
                @php
                    if (!is_array($itemValue)) {
                        $itemValue = json_decode($itemValue, true);
                    }
                @endphp

                @if(!empty($itemValue) and is_array($itemValue))
                    @foreach($itemValue as $key => $val)
                        <tr>
                            <td>
                                <img src="{{ $val['image'] }}" width="240"/>
                            </td>
                            <td>{{ $val['title'] }}</td>
                            <td><a href="{{ $val['link'] }}" target="_blank">{{ trans('public.view') }}</a></td>
                            <td>
                                <a href="{{ getAdminPanelUrl() }}/settings/sidebanner/{{ $key }}/edit" class="btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                    <i class="fa fa-edit"></i>
                                </a>

                              {{--  @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/settings/sidebanner/'. $key .'/delete','btnClass' => '']) --}}
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endif
        </table>
    </div>
</div>
