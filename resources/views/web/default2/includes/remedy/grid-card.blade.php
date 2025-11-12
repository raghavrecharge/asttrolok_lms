<style>
    .webinar-card .image-box {
    height: 350px !important;
}
</style>

<div class="webinar-card">
    <figure>
        <div class="image-box">
           
           
            <a href="{{ $remedy->getUrl() }}">
                <img src="{{ config('app.img_dynamic_url') }}{{ $remedy->getImage() }}" class="img-cover" alt="{{ $remedy->title }}">
            </a>

           
        </div>

        <figcaption class="webinar-card-body">
            
            <a href="{{ $remedy->getUrl() }}">
                <h3 class="mt-5 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($remedy->title,'title') }}</h3>
            </a>

            <div class="d-flex justify-content-between mt-5">
                <div class="d-flex align-items-center">
                    <i data-feather="film" width="15" height="15" class="webinar-icon"></i>
                    <span class="duration font-14 ml-5">{{ $remedy->files->where('file_type','video')->count() }} Videos</span>
                </div>

                <div class="d-flex align-items-center">
                    <i data-feather="file" width="15" height="15" class="webinar-icon"></i>
                    <span class="date-published font-14 ml-5">{{ $remedy->files->where('file_type','pdf')->count()+$remedy->files->where('file_type','powerpoint')->count()+$remedy->files->where('file_type','image')->count()+$remedy->files->where('file_type','document')->count() }} PDFs</span>
                </div>
                
                
            </div>

            
        </figcaption>
    </figure>
</div>
