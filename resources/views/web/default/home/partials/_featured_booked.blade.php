{{-- SECTION 10: FEATURED BOOK --}}
@if($featuredBook)
<section class="home-featured-book-section mt-20">
  <div class="home-frame1000001641">

    <img src="{{ asset($featuredBook->bg_image_1) }}"
         alt="Background"
         class="home-rectangle415" />

    <img src="{{ asset($featuredBook->bg_image_2) }}"
         alt="Background"
         class="home-rectangle17" />

    <div class="home-mainimage"
         style="background-image:url('{{ asset($featuredBook->main_image) }}')">
    </div>

    <div class="home-frame1000001640">
      <div class="home-frame1000001636">
        <span class="home-text155">Featured Book:</span>
      </div>

      <span class="home-text156">{{ $featuredBook->subtitle }}</span>
      <span class="home-text157">{{ $featuredBook->title }}</span>

      <span class="home-text158">
        {{ $featuredBook->pages }}+ Pages • {{ $featuredBook->copies_sold }} Copy Sold
      </span>
    </div>

  </div>
</section>
@endif
