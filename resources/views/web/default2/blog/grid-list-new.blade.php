      <div class="col-6 mt-24">
                                    <a href="{{ $post->getUrl() }}" class="text-decoration-none d-block">
    <div class="blog-section__post-card position-relative rounded-24 four-small-col">
        <div class="position-relative">
            <img src="{{ config('app.img_dynamic_url') }}{{ $post->image }}" alt="{{ $post->title }}" class="blog-section__post-card-img img-cover rounded-24">
        </div>

        <div class="blog-section__post-card-footer p-16">
            <div class="d-flex flex-column justify-content-end w-100 h-100">
                <h3 class="font-16 text-white d-none">{{ $post->title }}</h3>

                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between mt-12">
                    <div class="d-flex align-items-center">
                        <div class="size-36 rounded-circle bg-gray-100 d-none">
                            <img src="{{ $post->author->image }}" alt="{{ $post->author->full_name }}" class="img-cover rounded-circle">
                        </div>
                        <div class="ml-4 d-none">
                             @if(!empty($post->author->full_name))
               <h5 class="font-14 text-white">{{ $post->author->full_name }}</h5>
                 @endif
                            
                            <p class="font-12 text-white mt-2 d-none">{{ $post->comments_count }}</p>
                        </div>
                    </div>

                                    </div>
            </div>
        </div>
    </div>
</a>
                                </div>