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

    border: 1.8px solid #97A7BF;
    background-image: url(/assets/default/img/footer/pattern.png);

    background: #32A128 ;

    border-radius: 15px;
    padding: 25px;
    border: 1px solid #dedede;
}
</style>
<div class="footermobile">
    
    </div>
    <footer class="footer bg-secondary position-relative user-select-none" style="background-color: var(--secondary); background-image: url(/public/public/footer_background_7gn.png); ">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                <div class="footer-subscribe  d-block d-md-flex align-items-center justify-content-between" style="background-color:#f8f9fa;">
                    <div class="flex-grow-1" style="color: #000;">
                        <strong style="color: #000;">Subscribe to Our Newsletter</strong>
                        <span class="d-block mt-5 "style="color: #97A7BF;">Receive expert insights, course updates, and learning resources directly in your inbox and get notified</span>
                    </div>
                    <div class="subscribe-input bg-white p-10 flex-grow-1 mt-30 mt-md-0" style="border-radius: 20px;">
                        <form action="/newsletters" method="post">
                            {{ csrf_field() }}
                            
                            <div class="form-group d-flex align-items-center m-0">
                                <div class="w-100">
                                    <img src="/public/public/email.svg" 
         alt="email"
         style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width:18px; height:18px;">

                                    <input type="text" maxlength="60" name="newsletter_email" class="form-control border-0 @error('newsletter_email') is-invalid @enderror" placeholder="Enter your email address here" style="margin-left:40px;font-size: 0.95rem"/>
                                    @error('newsletter_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-success " style="background-color:#32A128; border:1px solid #32A128;border-radius: 8px;">{{ trans('footer.join') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
   
    
    <div class="container">
       
                    <div class="row">
                        
                        <div class=" col-md-5 mt-10">
                     <div class="d-inline-flex-center gap-8 border-2 border-white rounded-32 bg-white-10 text-white px-16 py-12">

                                                                            <span class="">Let’s get started now!</span>
                                                                    </div>

                                                                    <h3 class="mt-16 font-44 text-white mr-0 mr-lg-48">Take the First Step Towards Cosmic</h3>
                                
                                                                <div class="footer-logo mt-10" style="width: 215px; height: 57px;">
                        <a href="{{ config('app.manual_base_url') }}/"><img loading="lazy" decoding="async" src="/public/public/Asttolok-White-Logo 1.png" class="img-cover" alt="footer logo" style="
    border-radius: 0;
"/></a>
                    </div>
                        </div>
                        <div class=" col-md-2 mt-10 {{isset($_GET['ad'])?'d-none':''}}"> 
                            <span class="header d-block text-white font-weight-bold d-none" style="font-size: 20px !important;">Home</span>
                            
                            <div class="mt-20"> 
                               @guest
    {{-- Login/Register buttons for guests --}}
    <a href="{{ config('app.manual_base_url') }}/login" target="_blank" class="d-block font-16 text-white opacity-70 mt-16" data-text="Login">
        <span class="btn-flip-effect__text">Login</span>
    </a>
    
    <a href="{{ config('app.manual_base_url') }}/register" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Register">
        <span class="btn-flip-effect__text">Register</span>
    </a>
@else
    {{-- Logout or Profile link for logged-in users --}}
    <a href="{{ url('/panel') }}" class="d-block font-16 text-white opacity-70 mt-16" data-text="Dashboard">
        <span class="btn-flip-effect__text">Dashboard</span>
    </a>
    
    <a href="{{ url('/logout') }}" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Logout">
        <span class="btn-flip-effect__text">Logout</span>
    </a>
@endguest
                                                                                                                                                <a href="{{ config('app.manual_base_url') }}/contact" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Contact">
                                            <span class="btn-flip-effect__text">Contact</span>
                                        </a>
                                                                                                                                              
                                            
                                                                                                                                                <a href="{{ config('app.manual_base_url') }}/about" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="About">
                                            <span class="btn-flip-effect__text">About</span>
                                        </a>
                                                                                                                                                <a href="{{ config('app.manual_base_url') }}/pages/terms" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Terms and Policies">
                                            <span class="btn-flip-effect__text">Terms and Conditions</span>
                                        </a>
                                                                                                                                            <a href="{{ config('app.manual_base_url') }}/pages/cancellation-and-refund-policy" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Become Instructor">
                                            <span class="btn-flip-effect__text">Cancellation &amp; Refund Policy</span>
                                        </a>
                                         <a href="{{ config('app.manual_base_url') }}/tutorial-guide" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Become Instructor">
                                            <span class="btn-flip-effect__text">Tutorial Guide</span>
                                        </a>
                                       
            </div>
                </div>
               
            <div class=" col-md-2 mt-10"> 
                <span class="header d-block text-white font-weight-bold d-none" style="font-size: 20px !important;">Popular Categories</span>
                
                     <a href="{{ config('app.manual_base_url') }}/categories/astrology" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-16" data-text="Development">
                                            <span class="btn-flip-effect__text">Astrology</span>
                                        </a>
                                                                                                                                                <a href="{{ config('app.manual_base_url') }}/categories/ayurveda" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Business">
                                            <span class="btn-flip-effect__text">Ayurveda</span>
                                        </a>
                                                                                                                                                <a href="{{ config('app.manual_base_url') }}/categories/numerology" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Marketing">
                                            <span class="btn-flip-effect__text">Numerology</span>
                                        </a>
                                                                                                                                                <a href="{{ config('app.manual_base_url') }}/categories/palmistry" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Lifestyle">
                                            <span class="btn-flip-effect__text">Palmistry</span>
                                        </a>
                                                                                                                                                <a href="{{ config('app.manual_base_url') }}/categories/vastu" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Health">
                                            <span class="btn-flip-effect__text">Vastu</span>
                                        </a>
                                                                                                                                                <a href="{{ config('app.manual_base_url') }}/classes?hindi=on" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Academics">
                                            <span class="btn-flip-effect__text">Hindi</span>
                                        </a>
                                                                                                                                                <a href="{{ config('app.manual_base_url') }}/classes?english=on" target="_blank" class="text-decoration-none d-block font-16 text-white opacity-70 mt-12" data-text="Design">
                                            <span class="btn-flip-effect__text">English</span>
                                        </a>
                        </div>
                      <div class=" col-md-3 mt-10">  
                    <span class="header d-block text-white font-weight-bold d-none" style="font-size: 20px !important;">Contact US</span>
                    
                     <div class="d-flex align-items-start gap-8 mt-20">
                                        <div class="size-24">
                                            <svg width="24px" height="24px" class="text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
  <path stroke-width="1.5" d="M12 13.43a3.12 3.12 0 100-6.24 3.12 3.12 0 000 6.24z"></path>
  <path stroke-width="1.5" d="M3.62 8.49c1.97-8.66 14.8-8.65 16.76.01 1.15 5.08-2.01 9.38-4.78 12.04a5.193 5.193 0 01-7.21 0c-2.76-2.66-5.92-6.97-4.77-12.05z"></path>
</svg>                                        </div>
                                        <span class="font-16 text-white opacity-70">312, Vikram Urbane, Scheme No 54, Indore, Madhya Pradesh 452011</span>
                                    </div>
                                
                                                                    <div class="d-flex align-items-start gap-8 mt-16">
                                        <div class="size-24">
                                            <svg width="24px" height="24px" class="text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
  <path stroke-miterlimit="10" stroke-width="1.5" d="M21.97 18.33c0 .36-.08.73-.25 1.09-.17.36-.39.7-.68 1.02-.49.54-1.03.93-1.64 1.18-.6.25-1.25.38-1.95.38-1.02 0-2.11-.24-3.26-.73s-2.3-1.15-3.44-1.98a28.75 28.75 0 01-3.28-2.8 28.414 28.414 0 01-2.79-3.27c-.82-1.14-1.48-2.28-1.96-3.41C2.24 8.67 2 7.58 2 6.54c0-.68.12-1.33.36-1.93.24-.61.62-1.17 1.15-1.67C4.15 2.31 4.85 2 5.59 2c.28 0 .56.06.81.18.26.12.49.3.67.56l2.32 3.27c.18.25.31.48.4.7.09.21.14.42.14.61 0 .24-.07.48-.21.71-.13.23-.32.47-.56.71l-.76.79c-.11.11-.16.24-.16.4 0 .08.01.15.03.23.03.08.06.14.08.2.18.33.49.76.93 1.28.45.52.93 1.05 1.45 1.58.54.53 1.06 1.02 1.59 1.47.52.44.95.74 1.29.92.05.02.11.05.18.08.08.03.16.04.25.04.17 0 .3-.06.41-.17l.76-.75c.25-.25.49-.44.72-.56.23-.14.46-.21.71-.21.19 0 .39.04.61.13.22.09.45.22.7.39l3.31 2.35c.26.18.44.39.55.64.1.25.16.5.16.78z"></path>
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.5 9c0-.6-.47-1.52-1.17-2.27-.64-.69-1.49-1.23-2.33-1.23M22 9c0-3.87-3.13-7-7-7"></path>
</svg>                                        </div>
                                        <a href="tel:+919174822333" class="font-16 text-white opacity-70" style="text-decoration: none;">
    +91 91748-22333
</a>
                                    </div>
                                
                                                               
                                
                                                                    <div class="d-flex align-items-start gap-8 mt-16">
                                        <div class="size-24">
                                            <svg width="24px" height="24px" class="text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5" d="M17 20.5H7c-3 0-5-1.5-5-5v-7c0-3.5 2-5 5-5h10c3 0 5 1.5 5 5v7c0 3.5-2 5-5 5z"></path>
  <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5" d="M17 9l-3.13 2.5c-1.03.82-2.72.82-3.75 0L7 9"></path>
</svg>                                        </div>
                                        <a href="mailto:info@asttrolok.com" class="font-16 text-white opacity-70 email-link">
    <i class="fas fa-envelope me-2"></i>
    info@asttrolok.com
</a>
                                    </div>
            </div>
                    </div>
                    
                 
                    
                    

        <div class="mt-40 border-blue py-25 d-flex align-items-center justify-content-between">
            <div class="font-14 text-white opacity-70"> All copyrights reserved 2023 Asttrolok.com.</div>
                    
                    <div class="d-flex align-items-center justify-content-center gap-16 gap-lg-24">
                                                    
                                                                                                                                        <a href="https://www.instagram.com/asttrolok" target="_blank" rel="nofollow" title="Instagram" class="d-flex-center size-24">
                                            <img src="/public/public/instagram.svg" alt="Instagram" class="img-cover">
                                        </a>
                                                                                                                                                                                                            <a href="//api.whatsapp.com/send?phone=919174822333&text= " target="_blank" rel="nofollow" title="Whatsapp" class="d-flex-center size-24">
                                            <img src="/public/public/whatsapp.svg" alt="Whatsapp" class="img-cover">
                                        </a>
                                                                                                                                                                                                            <a href="https://www.linkedin.com/company/asttrolok/" target="_blank" rel="nofollow" title="Linkedin" class="d-flex-center size-24">
                                            <img src="/public/public/linkedin.svg" alt="linkedin" class="img-cover">
                                        </a>
                                                                                                                                                                                                            <a href="https://www.facebook.com/asttrolok" target="_blank" rel="nofollow" title="Facebook" class="d-flex-center size-24">
                                            <img src="/public/public/Facebook.svg" alt="Facebook" class="img-cover">
                                        </a>
                                                                                                                                                                                                       <a href="https://in.pinterest.com/asttrolok/" target="_blank" rel="nofollow" title="Pinterest" class="d-flex-center size-24">
                                            <img src="/public/public/pinterest.svg" alt="Pinterest" class="img-cover">
                                        </a>

                                          <a href="hhttps://youtube.com/@asttrolokchannel" target="_blank" rel="nofollow" title="Youtube" class="d-flex-center size-24">
                                            <img src="/public/public/youtube.svg" alt="Youtube"  class="img-cover">
                                            
                                        </a>
                                                                                                                        

                    </div>
        </div>
        
    </div>
</div>



</footer>

