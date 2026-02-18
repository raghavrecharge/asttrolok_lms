@php
   // $checkSequenceContent = $file->checkSequenceContent();
   // $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));
//print_r($file->id);
//die();
@endphp

   @if($free_video <= 3 && $file->getIconByType() == 'film')

    <div class="col-12 col-md-6 col-lg-4 mt-24">
        <div class="course-grid-card-1 position-relative">
            <div class="course-grid-card-1__mask"></div>
                <div class="position-relative z-index-2">
                        <div class="course-grid-card-1__image bg-gray-200">
                             <img src="/store/1/default_images/courses/course3.jpg" class="img-cover" alt="UX Research and User Testing">
                        </div>
                        <div class="course-grid-card-1__body d-flex flex-column py-12" style="    border: 1px solid #dcdcdc;height: 70px;">
                                <div class="d-flex flex-column px-12 w-100">
                                        <h3 class="course-titlex font-16 font-weight-bold text-dark" >{{ $file->title }}</h3>
                                </div>
                        </div>
                </div>
            </div>
        </div>

 @endif
