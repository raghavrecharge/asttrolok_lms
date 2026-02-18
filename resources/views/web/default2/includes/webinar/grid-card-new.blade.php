<style>
     .custom-border {
    border-top: 2px solid #ececec !important;
}
 </style>

 <div class="col-12 col-md-6 col-lg-3 mt-24 ">
        <a href="{{ $webinar->getUrl() }}" class="text-decoration-none d-block">
    </a><div class="course-grid-card-1 position-relative"><a href="{{ $webinar->getUrl() }}" class="text-decoration-none d-block">
    <div class="course-grid-card-1__mask"></div>

    </a><div class="position-relative z-index-2"><a href="{{ $webinar->getUrl() }}" class="text-decoration-none d-block">
            <div class="course-grid-card-1__image bg-gray-200">
                                <img src="{{ config('app.img_dynamic_url') }}{{ $webinar->getImage() }}" class="img-cover" alt="{{ $webinar->title }}">
            </div>

        </a><div class="course-grid-card-1__body d-flex flex-column py-12"><a href="{{ $webinar->getUrl() }}" class="text-decoration-none d-block">
            </a><div class="d-flex flex-column px-12 w-100"><a href="{{ $webinar->getUrl() }}" class="text-decoration-none d-block">
                    <h3 class="course-title font-16 font-weight-bold text-dark">{{ clean($webinar->title,'title') }}</h3>

                <div class="stars-card d-flex align-items-center mt-12">

                        <span class="stars-card__item active">
                <svg width="14px" height="14px" class="icons" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
  <path d="M5.74 16c.11-.49-.09-1.19-.44-1.54l-2.43-2.43c-.76-.76-1.06-1.57-.84-2.27.23-.7.94-1.18 2-1.36l3.12-.52c.45-.08 1-.48 1.21-.89l1.72-3.45C10.58 2.55 11.26 2 12 2s1.42.55 1.92 1.54l1.72 3.45c.13.26.4.51.69.68L5.56 18.44c-.14.14-.38.01-.34-.19L5.74 16zM18.7 14.462c-.36.36-.56 1.05-.44 1.54l.69 3.01c.29 1.25.11 2.19-.51 2.64a1.5 1.5 0 01-.9.27c-.51 0-1.11-.19-1.77-.58l-2.93-1.74c-.46-.27-1.22-.27-1.68 0l-2.93 1.74c-1.11.65-2.06.76-2.67.31-.23-.17-.4-.4-.51-.7l12.16-12.16c.46-.46 1.11-.67 1.74-.56l1.01.17c1.06.18 1.77.66 2 1.36.22.7-.08 1.51-.84 2.27l-2.42 2.43z"></path>
</svg>            </span>
                    <span class="stars-card__item active">
                <svg width="14px" height="14px" class="icons" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
  <path d="M5.74 16c.11-.49-.09-1.19-.44-1.54l-2.43-2.43c-.76-.76-1.06-1.57-.84-2.27.23-.7.94-1.18 2-1.36l3.12-.52c.45-.08 1-.48 1.21-.89l1.72-3.45C10.58 2.55 11.26 2 12 2s1.42.55 1.92 1.54l1.72 3.45c.13.26.4.51.69.68L5.56 18.44c-.14.14-.38.01-.34-.19L5.74 16zM18.7 14.462c-.36.36-.56 1.05-.44 1.54l.69 3.01c.29 1.25.11 2.19-.51 2.64a1.5 1.5 0 01-.9.27c-.51 0-1.11-.19-1.77-.58l-2.93-1.74c-.46-.27-1.22-.27-1.68 0l-2.93 1.74c-1.11.65-2.06.76-2.67.31-.23-.17-.4-.4-.51-.7l12.16-12.16c.46-.46 1.11-.67 1.74-.56l1.01.17c1.06.18 1.77.66 2 1.36.22.7-.08 1.51-.84 2.27l-2.42 2.43z"></path>
</svg>            </span>
                    <span class="stars-card__item active">
                <svg width="14px" height="14px" class="icons" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
  <path d="M5.74 16c.11-.49-.09-1.19-.44-1.54l-2.43-2.43c-.76-.76-1.06-1.57-.84-2.27.23-.7.94-1.18 2-1.36l3.12-.52c.45-.08 1-.48 1.21-.89l1.72-3.45C10.58 2.55 11.26 2 12 2s1.42.55 1.92 1.54l1.72 3.45c.13.26.4.51.69.68L5.56 18.44c-.14.14-.38.01-.34-.19L5.74 16zM18.7 14.462c-.36.36-.56 1.05-.44 1.54l.69 3.01c.29 1.25.11 2.19-.51 2.64a1.5 1.5 0 01-.9.27c-.51 0-1.11-.19-1.77-.58l-2.93-1.74c-.46-.27-1.22-.27-1.68 0l-2.93 1.74c-1.11.65-2.06.76-2.67.31-.23-.17-.4-.4-.51-.7l12.16-12.16c.46-.46 1.11-.67 1.74-.56l1.01.17c1.06.18 1.77.66 2 1.36.22.7-.08 1.51-.84 2.27l-2.42 2.43z"></path>
</svg>            </span>
                    <span class="stars-card__item active">
                <svg width="14px" height="14px" class="icons" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
  <path d="M5.74 16c.11-.49-.09-1.19-.44-1.54l-2.43-2.43c-.76-.76-1.06-1.57-.84-2.27.23-.7.94-1.18 2-1.36l3.12-.52c.45-.08 1-.48 1.21-.89l1.72-3.45C10.58 2.55 11.26 2 12 2s1.42.55 1.92 1.54l1.72 3.45c.13.26.4.51.69.68L5.56 18.44c-.14.14-.38.01-.34-.19L5.74 16zM18.7 14.462c-.36.36-.56 1.05-.44 1.54l.69 3.01c.29 1.25.11 2.19-.51 2.64a1.5 1.5 0 01-.9.27c-.51 0-1.11-.19-1.77-.58l-2.93-1.74c-.46-.27-1.22-.27-1.68 0l-2.93 1.74c-1.11.65-2.06.76-2.67.31-.23-.17-.4-.4-.51-.7l12.16-12.16c.46-.46 1.11-.67 1.74-.56l1.01.17c1.06.18 1.77.66 2 1.36.22.7-.08 1.51-.84 2.27l-2.42 2.43z"></path>
</svg>            </span>
 <span class="stars-card__item active">
                <svg width="14px" height="14px" class="icons" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
  <path d="M5.74 16c.11-.49-.09-1.19-.44-1.54l-2.43-2.43c-.76-.76-1.06-1.57-.84-2.27.23-.7.94-1.18 2-1.36l3.12-.52c.45-.08 1-.48 1.21-.89l1.72-3.45C10.58 2.55 11.26 2 12 2s1.42.55 1.92 1.54l1.72 3.45c.13.26.4.51.69.68L5.56 18.44c-.14.14-.38.01-.34-.19L5.74 16zM18.7 14.462c-.36.36-.56 1.05-.44 1.54l.69 3.01c.29 1.25.11 2.19-.51 2.64a1.5 1.5 0 01-.9.27c-.51 0-1.11-.19-1.77-.58l-2.93-1.74c-.46-.27-1.22-.27-1.68 0l-2.93 1.74c-1.11.65-2.06.76-2.67.31-.23-.17-.4-.4-.51-.7l12.16-12.16c.46-.46 1.11-.67 1.74-.56l1.01.17c1.06.18 1.77.66 2 1.36.22.7-.08 1.51-.84 2.27l-2.42 2.43z"></path>
</svg>            </span>

<span class="ml-2 text-gray-500 font-14">(5)</span>
</span>

            </div>

                    </a><div class="d-flex align-items-center my-16"><a href="{{ $webinar->teacher->getProfileUrl() }}" class="text-decoration-none d-block">
                        </a><a href="{{ $webinar->teacher->getProfileUrl() }}"  class="size-32 rounded-circle">
                        <img src="{{ config('app.img_dynamic_url') }}{{ $webinar->teacher->getAvatar() }}" class="img-cover rounded-circle" alt="{{ $webinar->teacher->full_name }}">
                        </a>

                    <div class="d-flex flex-column ml-2">
                            <a href="{{ $webinar->teacher->getProfileUrl() }}"  class="font-14 font-weight-bold text-dark" >{{ $webinar->teacher->full_name }}</a>

                                            </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-auto pt-12 border-top-gray-100 px-12 custom-border">

                <div class="d-flex align-items-center font-16 font-weight-bold text-primary">
                        <a href="{{ $webinar->getUrl() }}" class="text-decoration-none " style="color:#32A128">
                @if(!empty($isRewardCourses) and !empty($webinar->points))
                    <span class="text-warning real font-14">{{ $webinar->points }} {{ trans('update.points') }}</span>
                @elseif(!empty($webinar->price) and $webinar->price > 0)
                    @if($webinar->bestTicket() < $webinar->price)
                        <span class="real">{{ handlePrice($webinar->bestTicket(), true, true, false, null, true) }}</span>

                    @else
                        <span class="real">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>
                    @endif
                @else
                    <span class="real font-14">{{ trans('public.free') }}</span>
                @endif

                                                </a>
                </div>

                </div>
            </div>
        </div>
    </div>

    </div>
