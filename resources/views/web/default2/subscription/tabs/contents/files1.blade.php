@php
   // $checkSequenceContent = $file->checkSequenceContent();
   // $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));
//print_r($file->id);
//die();
$k=3+$i;
@endphp

   @if($free_video <= 3 && $file->getIconByType() == 'film')



                


 @else

  <div class="course-card">
            <div class="study-material-tag">Study Material 1</div>
            <div class="coursex-title">{{ $file->title }}</div>
            <div class="lock-icon-wrapper">
                <i class="fas fa-lock lock-icon"></i>
            </div>
            <div class="course-actions">
                <span>Preview</span>
                <span>Watch Video</span>
            </div>
        </div>


  @endif