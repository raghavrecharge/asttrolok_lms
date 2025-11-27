<div class="modal fade" id="textpop" tabindex="-1" aria-labelledby="textpop" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content py-20">
            <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25"></i>
                </button>
            </div>

            <div class="mt-25 position-relative">

                <div class="modal-video-lists mt-15">

                    <div class="mt-20 text-center">
                        <span>Don't have an account?</span>
                        <a href="/register" class="text-secondary font-weight-bold">Signup</a>
                    </div>

                                        <div class="accordion-content-wrapper mt-15" id="videosAccordion" role="tablist" aria-multiselectable="true">
                                     <div class="login-card">
                    <h2 class="font-20 font-weight-bold">Log in to your account</h2>

                    <form method="Post" action="/login" class="mt-35">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                         <input type="hidden" name="rd" value="{{ url()->current() }}">
                        <div class="form-group">
                            <label class="input-label" for="username">Email or Phone:</label>
                            <input name="username" type="text" class="form-control " id="username" value="" aria-describedby="emailHelp">
                                                    </div>

                        <div class="form-group">
                            <label class="input-label" for="password">Password:</label>
                            <input name="password" type="password" class="form-control " id="password" aria-describedby="passwordHelp">

                                                    </div>

                        <button type="submit" class="btn btn-primary btn-block mt-20">Login</button>
                    </form>

                    <div class="mt-30 text-center">
                        <a href="/forget-password" >Forgot your password?</a>
                    </div>

                    <div class="mt-20 text-center">
                        <span>Don't have an account?</span>
                        <a href="/register" class="text-secondary font-weight-bold">Signup</a>
                    </div>
                </div>
                                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
