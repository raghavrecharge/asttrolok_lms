<section class="mt-45">
    <h3 class="section-title">{{ trans('update.shipping_and_delivery') }}</h3>
    <div class="rounded-sm shadow mt-20 py-25 px-20">
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="input-label font-weight-500">{{ trans('update.country') }}</label>

                    <select id="country" name="country_id" class="form-control @error('country_id')  is-invalid @enderror">
                        <option value="">{{ trans('update.select_country') }}</option>

                        @if(!empty($countries))
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ (!empty($user) and $user->country_id == $country->id) ? 'selected' : '' }}>{{ $country->title }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('country_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="input-label font-weight-500">{{ trans('update.province') }}</label>

                    <select id="state" name="province_id" class="form-control @error('province_id')  is-invalid @enderror" {{ (!empty($user) and $user->province_id) ? '' : 'disabled' }}>
                        <option value="">{{ trans('update.select_province') }}</option>

                        @if(!empty($provinces))
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ (!empty($user) and $user->province_id == $province->id) ? 'selected' : '' }}>{{ $province->title }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('province_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="input-label font-weight-500">{{ trans('update.city') }}</label>

                    <select id="city" name="city_id" class="form-control @error('city_id')  is-invalid @enderror" {{ (!empty($user) and $user->city_id) ? '' : 'disabled' }}>
                        <option value="">{{ trans('update.select_city') }}</option>

                        @if(!empty($cities))
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ (!empty($user) and $user->city_id == $city->id) ? 'selected' : '' }}>{{ $city->title }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('city_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="input-label font-weight-500">{{ trans('update.address') }}</label>

                    <textarea name="address" rows="6" class="form-control @error('address')  is-invalid @enderror">{{ !empty($user) ? $user->address : '' }}</textarea>

                    @error('address')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="input-label font-weight-500">{{ trans('update.message_to_seller') }}</label>

                    <textarea name="message_to_seller" rows="8" class="form-control @error('message_to_seller')  is-invalid @enderror"></textarea>

                    @error('message_to_seller')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</section>

@if(!empty($deliveryEstimateTime))
    <div class="d-flex align-items-center mt-30 rounded-lg border px-10 py-5">
        <div class="appointment-timezone-icon">
            <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/icons/timezone.svg" alt="appointment timezone">
        </div>
        <div class="ml-15">
            <div class="font-16 font-weight-bold text-dark-blue">{{ trans('update.cart_order_estimated_delivery_time') }}</div>
            <p class="font-14 font-weight-500 text-gray">{{ trans('update.cart_order_estimated_delivery_time_hint',['days' => $deliveryEstimateTime]) }}</p>
        </div>
    </div>
@endif
@push('scripts_bottom')
<script  >
    var selectCountryText = "{{ trans('update.select_country') }}";
    var selectProvinceText = "{{ trans('update.select_province') }}";
    var selectCityText = "{{ trans('update.select_city') }}";
    var selectDistrictText = "{{ trans('update.select_district') }}";
</script>
<script  >
function findById(array, id) {
    if (!Array.isArray(array)) return null;
    return array.find(item => parseInt(item.id) === parseInt(id));
}

// Load JSON
$.getJSON("{{ asset('json/countries_states_cities.json') }}", function(countriesData) {
    console.log("JSON Loaded:", countriesData);

    // Populate Country dropdown
    $.each(countriesData, function(index, country) {
        $('#country').append('<option value="' + country.id + '">' + country.name + '</option>');
    });

    // Country change → populate province (states)
    $('#country').on('change', function() {
        var countryId = $(this).val();
        var country = findById(countriesData, countryId);

        // Reset dependent dropdowns
        $('#state').html('<option value="">' + selectProvinceText + '</option>').prop('disabled', false);
        $('#city').html('<option value="">' + selectCityText + '</option>').prop('disabled', true);
        $('#district').html('<option value="">' + selectDistrictText + '</option>').prop('disabled', true);
        console.log('country', country);
        // Populate province dropdown using 'states' from JSON
        if (country && country.states && country.states.length) {
            country.states.forEach(function(state) {
                $('#state').append('<option value="' + state.id + '">' + state.name + '</option>');
                console.log('state', state.name);
            });
        } else {
            console.log('No states found for this country.');
        }
            });
    // Province change → populate city
    $('#state').on('change', function() {
        var countryId = $('#country').val();
        var stateId = $('#state').val();
        var country = findById(countriesData, countryId);
        var state = country ? findById(country.states, stateId) : null;
       console.log('Selected state:', state); // <-- check

        // Reset city & district
        $('#city').html('<option value="">{{ trans("update.select_city") }}</option>').prop('disabled', true);
        $('#district').html('<option value="">{{ trans("update.select_district") }}</option>').prop('disabled', true);

        // Populate cities
        if (state && state.cities && state.cities.length > 0) {
            $.each(state.cities, function(index, city) {
                $('#city').append('<option value="' + city.id + '">' + city.name + '</option>');
            });
            $('#city').prop('disabled', false);
        }
    });

    // City change → populate district
    $('#city').on('change', function() {
        var countryId = $('#country').val();
        var stateId = $('#state').val();
        var cityId = $(this).val();
        var country = findById(countriesData, countryId);
        var state = country ? findById(country.states, stateId) : null;
        var city = state ? findById(state.cities, cityId) : null;

        // Reset district
        $('#district').html('<option value="">{{ trans("update.select_district") }}</option>').prop('disabled', true);

        if (city && city.districts && city.districts.length > 0) {
            $.each(city.districts, function(index, district) {
                $('#district').append('<option value="' + district.id + '">' + district.name + '</option>');
            });
            $('#district').prop('disabled', false);
        }
    });
});
</script>

@endpush
