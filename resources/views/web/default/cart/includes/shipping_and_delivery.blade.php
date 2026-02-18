<section class="mt-45">
    <h3 class="section-title">{{ trans('update.shipping_and_delivery') }}</h3>
    <div class="rounded-sm shadow mt-20 py-25 px-20">
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="input-label font-weight-3000">{{ trans('update.country') }}</label>
                    <select id="country" name="country_id" class="form-control @error('country_id') is-invalid @enderror" required>
                        <option value="">{{ trans('update.select_country') }}</option>

                    </select>
                    @error('country_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

               <div class="form-group">
                    <label class="input-label font-weight-3000">{{ trans('update.province') }}</label>
                    <select id="state" name="province_id" class="form-control @error('province_id') is-invalid @enderror" disabled required>
                        <option value="">{{ trans('update.select_province') }}</option>
                    </select>
                    @error('province_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                 <div class="form-group">
                    <label class="input-label font-weight-3000">{{ trans('update.city') }}</label>
                    <select id="city" name="city_id" class="form-control @error('city_id') is-invalid @enderror" disabled required>
                        <option value="">{{ trans('update.select_city') }}</option>
                    </select>
                    @error('city_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                 <div class="form-group">
                    <label class="input-label font-weight-3000">{{ trans('update.pin_code') }}</label>
                    <input type="text" id="pin_code" name="pin_code" class="form-control @error('pin_code') is-invalid @enderror" placeholder="">
                    @error('pin_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="input-label font-weight-3000">{{ trans('update.address') }}</label>

                    <textarea name="address" rows="6" class="form-control @error('address')  is-invalid @enderror">{{ !empty($user) ? $user->address : '' }}</textarea>

                    @error('address')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="message" class="input-label font-weight-3000">
                        {{ trans('update.message') }}
                    </label>
                    <textarea name="message" id="message" class="form-control @error('message') is-invalid @enderror" rows="3">{{ old('message') }}</textarea>
                    
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
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
<script >
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
    // console.log("JSON Loaded:", countriesData);
let selectedCountry = sessionStorage.getItem("cty");

console.log('selectedCountry', sessionStorage.getItem("cty"));
if(selectedCountry){
    
    $.each(countriesData, function(index, country) {
        // console.log('country.name', country.name);
        let selected = (selectedCountry == country.id) ? 'selected' : '';
        $('#country').append('<option value="' + country.id + '" ' + selected + '>' + country.name + '</option>');
    });
    
    // var countryId = $(this).val();
        // var country = selectedCountry;
        var country = findById(countriesData, selectedCountry);
        
        // console.log('country', country);

        // Reset dependent dropdowns
        $('#state').html('<option value="">' + selectProvinceText + '</option>').prop('disabled', false);
        $('#city').html('<option value="">' + selectCityText + '</option>').prop('disabled', true);
        $('#district').html('<option value="">' + selectDistrictText + '</option>').prop('disabled', true);
        // console.log('country', country);
        // Populate province dropdown using 'states' from JSON
        if (country && country.states && country.states.length) {
            country.states.forEach(function(state) {
                $('#state').append('<option value="' + state.id + '">' + state.name + '</option>');
                // console.log('state', state.name);
            });
        }
    
}else{
    
    console.log('selectedCountry1', sessionStorage.getItem("cty"));
    // Populate Country dropdown
    $.each(countriesData, function(index, country) {
        $('#country').append('<option value="' + country.id + '">' + country.name + '</option>');
    });
}
    // Country change → populate province (states)
    $('#country').on('change', function() {
        var countryId = $(this).val();
        var country = findById(countriesData, countryId);
        
        // Reset dependent dropdowns
        $('#state').html('<option value="">' + selectProvinceText + '</option>').prop('disabled', false);
        $('#city').html('<option value="">' + selectCityText + '</option>').prop('disabled', true);
        $('#district').html('<option value="">' + selectDistrictText + '</option>').prop('disabled', true);
        // console.log('country', country);
        // Populate province dropdown using 'states' from JSON
        if (country && country.states && country.states.length) {
            country.states.forEach(function(state) {
                $('#state').append('<option value="' + state.id + '">' + state.name + '</option>');
                // console.log('state', state.name);
            });
        } else {
            // console.log('No states found for this country.');
        }
        
        // console.log("Selected Country ID:", countryId);
        // console.log("Selected Country Name:", country ? country.name : "Not Found");
        
         if (country && !( country.name.toLowerCase() === "india")) {
           handleNonIndiaCountry(country);
        }else{
            fetch("/unset-session", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log("Session unset successfully");
                }
            });

            // ek key hatane ke liye
        sessionStorage.setItem("cty", 101);
        // window.location.reload();
        document.body.classList.add('disabled-page');
            document.getElementById('loader').style.display = 'block';
            document.documentElement.style.overflow = 'hidden';
        setTimeout(function() {
            window.location.reload();
        }, 3000);
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
    function handleNonIndiaCountry(country) {
        // yaha aap apna logic likho
        console.log("Function Triggered! Country:", country.id);
        let carts = @json($carts);
        let tax_international = carts[0].product_order.product.tax_international;
        let delivery_fee_international = carts[0].product_order.product.delivery_fee_international;
        // console.log("Function Triggered! Country:", carts);
        // console.log("tax for india:", carts[0].product_order.product.tax);
        console.log("tax for international:", delivery_fee_international);
        // console.log("delivery fee for india:", carts[0].product_order.product.delivery_fee);
        console.log("delivery fee for international:", delivery_fee_international);
        console.log("delivery fee for international:", country.id);
        
        sessionStorage.setItem("cty", country.id)
        
    
        // if (sessionStorage.getItem("cty")) {
           
        // }
    
        if (delivery_fee_international) {
            fetch("{{ route('set.session') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    tax_international: tax_international,
                    delivery_fee_international: delivery_fee_international
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log("Session set:", data);
            });
        }
     
        // window.location.href = "/cart";
        // window.location.reload();
        document.body.classList.add('disabled-page');
            document.getElementById('loader').style.display = 'block';
            document.documentElement.style.overflow = 'hidden';
        setTimeout(function() {
            window.location.reload();
        }, 3000);
        


    
        
    }
</script>

@endpush