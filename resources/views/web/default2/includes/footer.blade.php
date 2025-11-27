@php
    $generalSettings = getGeneralSettings();
    $socials = getSocials();
    if (!empty($socials) and count($socials)) {
        $socials = collect($socials)->sortBy('order')->toArray();
    }

    $footerColumns = getFooterColumns();
@endphp

<style>
    .footer .footer-social img {
  width: 24px;
  min-width: 24px;
  max-width: 24px;
  height: 24px;
}

.footer .footer-logo {
  width: 170px;
  height: 50px;
}

.footer .border-blue {
  border-top: 1px solid #305995;
}

.footer .footer-copyright-card {
  position: relative;
  background-color: transparent;
}

.footer .footer-copyright-card .container {
  position: relative;
  z-index: 2;
  background-color: transparent;
}

.footer .footer-copyright-card:before {
  content: "";
  position: absolute;
  inset: 0;
  opacity: 0.15;
  background-color: #000;
  z-index: 1;
}

.footer .footer-subscribe {
    position: relative;
    top: -80px;
    height: 120px;
    background-image: url(/assets/default/img/footer/pattern.png);
    background-color: var
#0170ff
(--primary);
    border-radius: 15px;
    padding: 25px;
}
</style>
<div class="footermobile">

    </div>
    <footer class="footer bg-secondary position-relative user-select-none">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                <div class=" footer-subscribe d-block d-md-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <strong>{{ trans('footer.join_us_today') }}</strong>
                        <span class="d-block mt-5 text-white">{{ trans('footer.subscribe_content') }}</span>
                    </div>
                    <div class="subscribe-input bg-white p-10 flex-grow-1 mt-30 mt-md-0">
                        <form action="/newsletters" method="post">
                            {{ csrf_field() }}

                            <div class="form-group d-flex align-items-center m-0">
                                <div class="w-100">
                                    <input type="text" maxlength="60" name="newsletter_email" class="form-control border-0 @error('newsletter_email') is-invalid @enderror" placeholder="{{ trans('footer.enter_email_here') }}"/>
                                    @error('newsletter_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary rounded-pill">{{ trans('footer.join') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
    $columns = ['first_column','second_column','third_column','forth_column'];
    @endphp

    <div class="container">
        @if(isset($_GET['ad']))
        <div class="row">

            @foreach($columns as $column)
            @if($column=='first_column')
            <div class=" col-md-5 mt-10">
                @elseif($column=='third_column')
                <div class=" col-md-3 mt-10">
                    @else
                    <div class=" col-md-2 mt-10">
                        @endif
                        @if(!empty($footerColumns[$column]))
                        @if(!empty($footerColumns[$column]['title']))
                        <span class="header d-block text-white font-weight-bold d-none" style="font-size: 20px !important;">{{ $footerColumns[$column]['title'] }}</span>
                        @endif

                        @if(!empty($footerColumns[$column]['value']))
                        @if($column=='first_column')
                        <div class="">
                            @else
                            <div class="mt-20">
                                @endif

                                {!! $footerColumns[$column]['value'] !!}
                            </div>
                            @endif
                            @endif
                        </div>
                        @endforeach

                    </div>
                    @else

                    <div class="row">

                        <div class=" col-md-5 mt-10">

                        <div class="">

                            <div class="footer-logo" style="width: 215px; height: 57px;">
                                <a href="{{isset($_GET['ad'])?'#':'/'}}"><img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Asttolok-White-Logo.webp" class="img-cover" alt="footer logo"></a>
                            </div>
                            <br>
                            <p>
                                <span style="font-family: Montserrat, sans-serif; font-size: 15px; letter-spacing: 0.04695px; text-align: justify;">
                                    <font color="#ffffff">
                                        Welcome to Asttrolok, recognized among India's top 3 astrology institutes, offering courses in astrology, numerology, palmistry, vastu, &amp; ayurveda. Our goal:
                                        guide to enlightenment, share wisdom globally.
                                    </font>
                                </span>
                            </p>
                            <div class="align-items-center justify-content-between mt-15">
                                <div class="footer-social">
                                    <a href="https://www.instagram.com/asttrolok"> <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}/store/1/default_images/social/social-icons/Instagram1.webp" alt="Instagram" class="mr-15"></a>
                <a href="//api.whatsapp.com/send?phone=919174822333&amp;text= "><img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}/store/1/default_images/social/social-icons/Whatsapp1.webp" alt="Whatsapp" class="mr-15"></a>
                <a href="https://twitter.com/asttrolok"> <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/store/1/default_images/social/Twitter.png" alt="Twitter" class="mr-15"> </a>
                <a href="https://www.facebook.com/asttrolok"><img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}/store/1/default_images/social/social-icons/FB1.webp" alt="Facebook" class="mr-15"></a>

                <a href="https://in.pinterest.com/asttrolok/"><img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}/store/1/default_images/social/social-icons/pinterest2.webp" alt="pinterest" class="mr-15"></a>
            </div>
        </div>
                            </div>
                        </div>
                        <div class=" col-md-2 mt-10 {{isset($_GET['ad'])?'d-none':''}}">
                            <span class="header d-block text-white font-weight-bold d-none" style="font-size: 20px !important;">Quick Links</span>

                            <div class="mt-20">

                                <ul class="ml-10">
                                    <li style="list-style: outside;color: #ffffff;">

                                        <p>
                            <a href="#"><font color="#ffffff">Career &amp; Placement</font></a>
                        </p>
                    </li>
                    <li style="list-style: outside;color: #ffffff;">
                        <p>
                            <font color="#ffffff">
                                <a href="{{isset($_GET['ad'])?'#':'https://asttroveda.asttrolok.com/asttrolok/personalizedkundali'}}"><font color="#ffffff">Kundali Reports</font></a><br>
                            </font>
                        </p>
                    </li>

                    <li style="list-style: outside;color: #ffffff;">
                        <p>
                            <a href="{{isset($_GET['ad'])?'#':'/consult-with-astrologers'}}"><font color="#ffffff">Astrologers</font></a>
                        </p>
                    </li>
                    <li style="list-style: outside;color: #ffffff;">
                        <p>
                            <a href="{{isset($_GET['ad'])?'#':'/classes'}}"><font color="#ffffff">Courses</font></a>
                        </p>
                    </li>
                    <li style="list-style: outside;color: #ffffff;">
                        <p>
                            <font color="#ffffff">
                                <a href="{{isset($_GET['ad'])?'#':'/blog'}}"><font color="#ffffff">Blog</font></a><br>
                            </font>
                        </p>
                    </li>
                    <li style="list-style: outside;color: #ffffff;">
                        <p>
                            <font color="#ffffff">
                                <a href="{{isset($_GET['ad'])?'#':'/tutorial-guide'}}"><font color="#ffffff">Tutorial Guide</font></a><br>
                            </font>
                        </p>
                    </li>

                </ul>
            </div>
                </div>
                <div class=" col-md-3 mt-10">
                    <span class="header d-block text-white font-weight-bold d-none" style="font-size: 20px !important;">Get In Touch</span>

                    <div class="mt-20">

                        <p class="mt-5" style="font-size: 15px !important;">
                            <font color="#ffffff"><a href="tel:09174822333" style="color:#fff; margin-right:10px;" target="_blank">

                                <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}/store/1/default_images/social/Call Icon.svg" width="15" height="15" alt="Instagram">
                                09174822333
                            </a></font>
                        </p>
                        <p class="mt-5" style="font-size: 15px !important;">

                        <font color="#ffffff">
                            <a href="mailto:astrolok.vedic@gmail.com" style="color:#fff" target="_blank">
                                <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}/store/1/default_images/social/mail_4314565 1.svg" width="15" height="15" alt="Instagram">

                                astrolok.vedic@gmail.com
                        </a>
                    </font>
                    </p>
                    <p class="mt-5" style="font-size: 15px !important;">
                        <a href="https://maps.app.goo.gl/SogMK8SxiX9eHbRc9" target="_blank">
                            <font color="#ffffff" style="display: flex;justify-content: space-between;flex-direction: row;">
                                <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}/store/1/default_images/social/Location Icon.svg" width="15" height="15" alt="Instagram">
                                <span style="padding-left: 5px;"> 312, 3rd Floor, Vikram Urbane, 25-A Mechanic Nagar Extn. Sch# 54, Indore(MP) 452010 </span>
                            </font>
                        </a>
                    </p>
                </div>
            </div>
            <div class=" col-md-2 mt-10">
                <span class="header d-block text-white font-weight-bold d-none" style="font-size: 20px !important;">Subscribe Now</span>

                <div class="mt-20">

                    <p style="font-family: Montserrat, sans-serif; font-size: 15px; letter-spacing: 0.04695px;">
                        <font color="#ffffff">
                            Join a global astrology network with <b>100K</b> diverse members.
                        </font>
                    </p>

                    <a target="_blank" href="https://www.youtube.com/@ASTTROLOKChannel?sub_confirmation=1">
                        <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}/store/1/default_images/social/youtube.webp" alt="youtube" width="100">
                    </a>
                            </div>
                        </div>

                    </div>

                    @endif

        <div class="mt-40 border-blue py-25 d-flex align-items-center justify-content-between">
            <div class="">
                <p style="font-family: Montserrat, sans-serif; font-size: 15px; letter-spacing: 0.04695px; text-align: justify;color:#fff">© All copyrights reserved 2023 Asttrolok.com  |
                    <font color="#ffffff">
                        <a href="{{ config('app.manual_base_url') }}/pages/privacy-policy"><font color="#ffffff">Privacy Policy</font></a>
                    </font> | <a href="{{ config('app.manual_base_url') }}/pages/terms"><font color="#ffffff">Terms &amp; Conditions</font></a> | <a href="{{ config('app.manual_base_url') }}/pages/cancellation-and-refund-policy"><font color="#ffffff">Cancellation &amp; Refund Policy</font></a>
                </p>
            </div>
        </div>

    </div>
</div>

@if(getOthersPersonalizationSettings('platform_phone_and_email_position') == 'footer')
<div class="footer-copyright-card">
    <div class="container d-flex align-items-center justify-content-between py-15">
        <div class="font-14 text-white">© All copyrights reserved 2023 Asttrolok.com</div>

        <div class="d-flex align-items-center justify-content-center">
            @if(!empty($generalSettings['site_phone']))
            <div class="d-flex align-items-center text-white font-14">
                <i data-feather="phone" width="20" height="20" class="mr-10"></i>
                {{ $generalSettings['site_phone'] }}
            </div>
            @endif

            @if(!empty($generalSettings['site_email']))
            <div class="border-left mx-5 mx-lg-15 h-100"></div>

            <div class="d-flex align-items-center text-white font-14">
                <i data-feather="mail" width="20" height="20" class="mr-10"></i>
                {{ $generalSettings['site_email'] }}
            </div>
            @endif
        </div>
    </div>
</div>
@endif

</footer>

<script defer>
    //     jivo_api.open = function() {
        //     return false;
        // };

    </script>
