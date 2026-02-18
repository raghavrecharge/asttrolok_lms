@if(!empty($data))
<section class="home-categories-section mt-10">
    @foreach($data as $category)
    <div>
        <a href="/categories{{ $category['link'] ?? '#' }}" style="display: flex;flex-direction: column;align-items: center;">
            @if(isset($category['frame']))
                <div class="home-group813">
                    <img src="{{ $category['frame'] }}" alt="{{ $category['name'] ?? '' }}" class="home-ellipse11" />
                </div>
            @else
                <div class="home-group813">
                    <img src="{{ $category['ellipse'] ?? '' }}" alt="Ellipse" class="home-ellipse11" />
                    <img src="{{ $category['vector'] ?? '' }}" alt="Vector" class="home-vector11" />
                </div>
            @endif
            <span class="home-text101">{{ $category['name'] ?? 'Category Missing' }}</span>
        </a>
    </div>
    @endforeach
</section>
@else
<div class="alert alert-warning p-3 rounded">
    <strong>⚠️ Categories Missing</strong><br>
    Controller: $categories_mobile = collect([['name', 'link', 'ellipse', 'vector']]);
</div>
@endif
