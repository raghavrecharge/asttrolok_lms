
@php
    $checkSequenceContent = $file->checkSequenceContent();
    $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));

@endphp

@if( $file->file_type != "video")

<div id="mob1" class="col-6 col-md-6 col-lg-3 pt-30 " >
    <div class="webinar-card ">
    <a href="{{ $file->file }}" target="_blank">
    <figure>
        <div class="image-box">

                       @php
print_r('mayank');
@endphp
            <a href="{{ $file-> file }}" >
                <img src="https://play-lh.googleusercontent.com/1EgLFchHT9oQb3KME8rzIab7LrOIBfC14DSfcK_Uzo4vuK-WYFs9dhI-1kDI7J0ZNTDr=w240-h480-rw" class=" img-cover" alt="{{ $file->title }}">
            </a>

        </div>

        <figcaption class="webinar-card-body">

            <a href="{{ $file->file }}"  class="viewfile1" >
                <h3 class="mt-5 webinar-title font-weight-bold font-16 text-dark-blue " >{{  $file->title }}</h3>
            </a>

        </figcaption>
    </figure>

</a>
</div></div>
@endIf
<div  class="col-6 col-md-6 col-lg-2 pt-30 {{ $file->file_type }}" onclick="viewfile('{{ $file->file }}','{{ $file->id }}')" >
    <div class="webinar-card ">
    <figure>
        <div class="image-box">

            <span src="{{ $file->file }}"   >
                <img src="{{ ($file->file_type == 'video')?'https://media.istockphoto.com/id/1266094665/vector/white-online-play-video-icon-isolated-with-long-shadow-film-strip-with-play-sign-red-circle.jpg?s=170667a&w=0&k=20&c=TbML_QZ2oG9c6qqJKSoJERkMVjbAANFnatTlcqOAnsY=':'https://play-lh.googleusercontent.com/1EgLFchHT9oQb3KME8rzIab7LrOIBfC14DSfcK_Uzo4vuK-WYFs9dhI-1kDI7J0ZNTDr=w240-h480-rw' }}" class=" img-cover" alt="{{ $file->title }}">
            </span>

        </div>

        <figcaption class="webinar-card-body">

            <span src="{{ $file->file }}"  class="viewfile1" >
                <h3 class="mt-5 webinar-title font-weight-bold font-16 text-dark-blue " >{{  $file->title }}</h3>
            </span>

        </figcaption>
    </figure>
</div>
</div>

