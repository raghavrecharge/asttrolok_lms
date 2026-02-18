{{-- ========== SECTION 2: SEARCH BAR ========== --}}
<style>
    .home-search-section{
      
          /* width: calc(100% - 20px) !important;
    margin: 10px !important; */
        
        border-radius: 16px;
   
        background-size: cover;
        background-position: center;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
  
    }
</style>


<section class="home-search-section mt-15">
   <form action="/search" method="GET" class="home-frame5 responsive-search">
    <input 
        type="text" 
        name="search" 
        class="home-search-input" 
        placeholder="Search for courses, astrologers, topics..."
        required
    />

    <button type="submit" class="home-search-button">
        <img src="/assets/design_1/img/home_mobile_image/public/vectori117-rqig.svg" alt="Search" />
    </button>
</form>
</section>
{{-- ========== END SECTION 2: SEARCH BAR ========== --}}