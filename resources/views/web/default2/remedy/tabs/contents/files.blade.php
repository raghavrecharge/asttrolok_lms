@php
    $checkSequenceContent = $file->checkSequenceContent();
    $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));

@endphp

@if($file->file_type == "pdf")
     <div class="d-flex-center flex-column text-center px-16">

                            <div class="profile-avatar-card size-150  mt-32 " style="width:248px;height:218px;">
                                <img src="{{ config('app.js_css_url') }}/assets/default/img/pdf.png" alt="Ricardo Dave" class="img-cover " style="border-radius: 7px;">
                            </div>

                            <h4 class="mt-16 font-18 font-weight-bold">{{ $file->title }}</h4>
                                    <a href="{{ url('/free-download') }}?url={{ urlencode(config('app.img_dynamic_url') . $file->file) }}&title={{ urlencode($file->title . '.pdf') }}"
   class="btn bookbtn btn-primary my-20 ">
   <span class="ml-5" style="font-family: 'Inter', sans-serif !important;">Download Now</span>
</a>
                        </div>

<script >
function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.target = '_blank'; // optional
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

@endif
