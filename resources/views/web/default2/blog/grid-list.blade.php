<div class="blog-grid-card">
     <a href="{{ $post->getUrl() }}">
    <div class="blog-grid-image">
        <img src="{{ config('app.img_dynamic_url') }}{{ $post->image }}" class="img-cover" alt="{{ $post->title }}">

        <span class="badge created-at d-flex align-items-center">
            <i data-feather="calendar" width="20" height="20" class="mr-5"></i>
            <span>{{ dateTimeFormat($post->created_at, 'j M Y') }}</span>
        </span>
    </div>

    <div class="blog-grid-detail">
        <a href="{{ $post->getUrl() }}">
            <h3 class="blog-grid-title mt-10">{{ $post->title }}</h3>
        </a>

        <div class="mt-20 blog-grid-desc">{!! truncate(strip_tags($post->description), 160) !!}</div>

        <div class="blog-grid-footer d-flex align-items-center justify-content-between mt-1">
            <span>
                <i data-feather="user" width="15" height="15" class=""></i>
                 @if(!empty($post->author->full_name))
                <span class="ml-5">{{ $post->author->full_name }}</span>
                 @endif
              </span>

            <span class="d-flex align-items-center">
                <i data-feather="message-square" width="15" height="15" class=""></i>
                <span class="ml-5">{{ $post->comments_count }}</span>
            </span>
        </div>
    </div></a>
</div>
