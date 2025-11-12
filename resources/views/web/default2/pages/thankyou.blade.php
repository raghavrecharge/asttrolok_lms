@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.css">
@endpush

@section('content')
 

    <div class="container">
        <div class="row login-container1">
            <!--<div class="col-12 col-md-6 pl-0"  style="display: flex;flex-wrap: nowrap;align-items: center;">-->
            <!--    <img src="{{ getPageBackgroundSettings('register') }}"  style="height:auto;" class="img-cover" alt="Login">-->
            
            <!--</div>-->
            
            
            <div class="col-12 col-md-12">
                <div class="login-card">
                    <h1 class="font-20 font-weight-bold" style="font-size:50px;">Thank You!</h1>
                    
                    <p > Thank you for contacting us! <br> We've received your message and will be in touch shortly. We appreciate your interest.
                    </p>
                        

                    

                  
                        
                    

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.js"></script>
@endpush
