<div class=" mt-20  mx-20 course-teacher-card d-flex align-items-center flex-column">

    @if(!empty($webinarPartnerTeacher))
        <span class="user-select-none px-15 py-10 bg-gray200 off-label text-gray text-white font-12 rounded-sm ml-auto">{{ trans('public.invited') }}</span>
    @endif
<div class="row rounded-lg p-5 shadow-sm "><div class="col-3">
    <div class="teacher-avatar mt-5">
        <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}{{ $courseTeacher->getAvatar(100) }}" class="img-cover" alt="{{ $courseTeacher->full_name }}">

    </div></div><div class="col-9 pl-10"  style="display: flex;
    flex-wrap: wrap;
    flex-direction: column;
    justify-content: center;
    align-content: flex-end;">
    <h3 class="ml-5 font-16 font-weight-bold text-primary">{{ $courseTeacher->full_name }}</h3>
    <span class="ml-5 mt-5 font-14 font-weight-500 text-gray">{{ $courseTeacher->bio }}</span>
</div></div>

    @php
        $hasMeeting = !empty($courseTeacher->hasMeeting());
    @endphp

</div>
