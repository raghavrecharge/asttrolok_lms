<style>
    .comm-title {
    display: block;
    font-size: 20px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 18px;
}

/* row */
.comm-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 18px;
}

/* left icon box */
.comm-icon {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
}

.comm-icon-edu  { background: #FFE7F0; }
.comm-icon-exp  { background: #E4F5FF; }
.comm-icon-skill{ background: #FFF0D9; }

.comm-icon img {
    width: 30px;
    height: 30px;
}

/* right text */
.comm-content {
    flex: 1;
}

.comm-heading {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
}

.comm-subtext {
    font-size: 13px;
    line-height: 1.4;
    color: #4B5563;
}

.comm-subtext span + span {
    margin-left: 12px;
}

/* skills tags */
.comm-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.comm-tag {
    padding: 3px 12px;
    border-radius: 999px;
    border: 1px solid #D1D5DB;
    font-size: 12px;
    color: #4B5563;
    background-color: #F9FAFB;
}

/* mobile */
@media (max-width: 768px) {
    .comm-item {
        align-items: center;
    }
    .comm-icon {
        width: 52px;
        height: 52px;
        margin-right: 12px;
    }
}

</style>

@if($user->offline)
    <div class="user-offline-alert d-flex mt-40">
        <div class="p-15">
            <h3 class="font-16 text-dark-blue">{{ trans('public.instructor_is_not_available') }}</h3>
            <p class="font-14 font-weight-500 text-gray mt-15">{{ $user->offline_message }}</p>
        </div>

        <div class="offline-icon offline-icon-right ml-auto d-flex align-items-stretch">
            <div class="d-flex align-items-center">
                <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/profile/time-icon.png" alt="offline">
            </div>
        </div>
    </div>
@endif

@if((!empty($educations) and !$educations->isEmpty()) or (!empty($experiences) and !$experiences->isEmpty()) or (!empty($occupations) and !$occupations->isEmpty()) or !empty($user->about))
    @if(!empty($educations) and !$educations->isEmpty())
     <!-- <div class="mt-40">
            <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.education') }}</h3>

            <ul class="list-group-custom">
                @foreach($educations as $education)
                    <li class="mt-15 text-gray">{{ $education->value }}</li>
                @endforeach
            </ul>
        </div> -->
    @endif


    @if(!empty($user->about))
        <div class="mt-40">
            <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.about') }}</h3>

            <div class="mt-30">
                {!! nl2br($user->about) !!}
            </div>
        </div>
    @endif


        <span class="comm-title mt-30">Communication</span>
        @if(!empty($educations) and !$educations->isEmpty())
        <div class="comm-item">
            <div class="comm-icon comm-icon-edu">
                <img src="/assets/design_1/img/instructors/public2/vector1076-nzk.svg" alt="Education">
            </div>
            <div class="comm-content">
                <div class="comm-heading">{{ trans('site.education') }}</div>
                <div class="comm-subtext">
                     <ul class="list-group-custom">
                @foreach($educations as $education)
                    <li class="mt-15 text-gray">{{ $education->value }}</li>
                @endforeach
            </ul>
                </div>
            </div>
        </div>
        @endif
        <div class="comm-item">
            <div class="comm-icon comm-icon-exp">
                <img src="/assets/design_1/img/instructors/public2/vector1076-67bb.svg" alt="Experiences">
            </div>
            <div class="comm-content">
                <div class="comm-heading">Experiences</div>
                <div class="comm-subtext">
                  {{$user["bio"]}}
                </div>
            </div>
        </div>
       
        <div class="comm-item">
            <div class="comm-icon comm-icon-skill">
                <img src="/assets/design_1/img/instructors/public2/vector1076-rr2.svg" alt="Skills">
            </div>
            <div class="comm-content">
                <div class="comm-heading">{{ trans('site.occupations') }}</div>
                <div class="comm-tags">
                    @foreach($occupations as $occupation)
                    <span class="comm-tag">{{ $occupation->category->title }}</span>
                    
                    @endforeach
                </div>
            </div>
        </div>
      
@else

    @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'bio.png',
        'title' => trans('site.not_create_bio'),
        'hint' => '',
    ])

@endif



