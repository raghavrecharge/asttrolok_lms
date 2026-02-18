 @php
    $learningMaterialsExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','learning_materials') : null;
    $companyLogosExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','company_logos') : null;
    $requirementsExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','requirements') : null;
@endphp
 <style>
   
.frame427322615-border4 {
    height: auto !important;
}


.frame427322615-frame427322558 {
    height: auto !important;
}


.frame427322615-frame427322557,
.frame427322615-frame427322556 {
    height: auto !important;
    display: block;
}


.frame427322615-horizontal-border3 {
    height: auto !important;
}


.frame427322615-frame427322549 {
    display: flex;
    align-items: flex-start;
    flex-wrap: wrap;
}

 </style>
 @if(!empty($requirementsExtraDescription) and count($requirementsExtraDescription))
  <div class="frame427322615-border4">
            <div class="frame427322615-frame427322558">
              <div class="frame427322615-frame427322545">
                <img
                  src="{{ asset($course->extraDetails->bonus_icon) }}"
                  alt="image22871"
                  class="frame427322615-image26"
                />
                <span class="frame427322615-text239">
                  {{$course->extraDetails->bonus_heading	}}
                </span>
              </div>
            
              <div class="frame427322615-frame427322557">
                <div class="frame427322615-frame427322556">
                  @foreach($requirementsExtraDescription as $requirementExtraDescription)
                      <div class="frame427322615-horizontal-border3">
                    <div class="frame427322615-frame427322549">
                      <div class="frame427322615-container13">
                        <img src="{{ config('app.img_dynamic_url') }}{{ $requirementExtraDescription->img }}" alt="SVG2871" class="frame427322615svg21">
                      </div>
                      <div class="frame427322615-frame427322548">
                        <span class="frame427322615-text242">
                        {{ $requirementExtraDescription->value }}
                        </span>
                        <span class="frame427322615-text243">
                         {{ $requirementExtraDescription->description }}.
                        </span>
                      </div>
                    </div>
                  </div>
               @endforeach
                </div>
                    
              </div>
           
            </div>
            
            <form action="/cart/store" method="post" style="flex:1 1 0;">
                      @csrf
                      <input type="hidden" name="item_id" value="{{ $course->id }}">
                      <input type="hidden" name="item_name" value="webinar_id">
                      @if($hasBought or !empty($course->getInstallmentOrder()))
    <button type="button" 
            class="frame427322615-button1 btn-success" 
            onclick="window.location.href='{{ $course->getLearningPageUrl1() }}'">
        <span class="frame427322615-text250">
          Start Learning
        </span>
    </button>
@else
    <button type="button" class="frame427322615-button1 btn-success buy_now js-course-direct-payment">
        <span class="frame427322615-text250">
            {{ $course->extraDetails->cta_text }}
        </span>
    </button>
@endif
                  </form>
            </div>
          
@endif
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/comment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/video_player_helpers.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/webinar_show.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/time-counter-down.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/barrating/jquery.barrating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
    