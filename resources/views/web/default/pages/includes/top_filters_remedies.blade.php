<style>
   .categories {
    display: flex;
    flex-wrap: nowrap !important;
    overflow-x: auto !important;
     min-height: 60px !important; 
} 
/*a.active-clr {*/
/*          color: white;*/
/*      }*/
/*       .active-cat {*/
/*          background: linear-gradient(180deg, #43d477 -0%, #29ae59 100%);*/
/*          color: white;*/
/*      }*/
/*      .frame-690-7jm {*/
/*    align-items: center;*/
    /* background: linear-gradient(180deg, #43d477 -0%, #29ae59 100%); */
/*    border-radius: 2rem;*/
/*    color: #ffffff;*/
/*    display: flex;*/
/*    flex-shrink: 0;*/
    /* font-family: Work Sans, 'Source Sans Pro'; */
/*    font-size: 1rem;*/
/*    font-weight: 600;*/
/*    height: 100% !important;*/
/*    justify-content: center;*/
/*    letter-spacing: 0.016rem;*/
/*    line-height: 1.5;*/
/*    white-space: nowrap;*/
/*    border: solid 0.07rem #b3b3b3;*/
/*    width: 6rem !important;*/
/*}*/
.checkbox-button label {
    cursor: pointer;
    font-size: 0.875rem;
    padding: 5px 10px;
    border-radius: 20px;
    border: solid 2px #f1f1f1;
    background-color: #f1f1f1;
    color: #3f3f3f;
    transition: all 0.3s ease;
}
.sortby {
    
    border-bottom: 1px solid #cbcaca;
    position: relative;
    top: -4px;
    font-size: 14px;
    /* display: flex; */
    flex-direction: row;
   
}
.sortby1 {
    
    border-top: 1px solid #cbcaca;
    position: relative;
    top: 10px;
    font-size: 14px;
    /* display: flex; */
    flex-direction: row;
 
}
.dropdown-menu {
    padding: 5px 4px 13px 10px;
}

</style>
<div id="topFilters" class=" p-10 p-md-20 homehide1">
     <div class="col-lg-3 d-flex align-items-center">
                        <select name="sort" id="sort-by" class="form-control d-none">
                            <option disabled selected>{{ trans('public.sort_by') }}</option>
                            <option value="">{{ trans('public.all') }}</option>
                            <option value="experience" @if(request()->get('sort', null) == 'experience') selected="selected" @endif>Experience</option>
                            <option value="expensive" @if(request()->get('sort', null) == 'expensive') selected="selected" @endif>Highest Price</option>
                            <option value="inexpensive" @if(request()->get('sort', null) == 'inexpensive') selected="selected" @endif>Lowest Price</option>
                            <!--<option value="inexpensive" @if(request()->get('sort', null) == 'inexpensive') selected="selected" @endif>{{ trans('public.inexpensive') }}</option>-->
                            <option value="top_sale" @if(request()->get('sort', null) == 'top_sale') selected="selected" @endif>Best Sellers</option>
                            <option value="top_rate" @if(request()->get('sort', null) == 'top_rate') selected="selected" @endif>{{ trans('public.best_rates') }}</option>
                        </select>
                    </div>
                       <div class="categories">
                <!--<h3 class="category-filter-title font-20 font-weight-bold">{{ trans('categories.categories') }}</h3>-->
 @foreach($categories as $category)
                        @if(!empty($category->subCategories) and count($category->subCategories))
                            @foreach($category->subCategories as $subCategory)
                                <div class="checkbox-button bordered-200 mr-5">
                                    <input type="checkbox" name="categories[]" id="checkbox{{ $subCategory->id }}" value="{{ $subCategory->id }}" @if(in_array($subCategory->id,request()->get('categories',[]))) checked="checked" @endif>
                                    <label for="checkbox{{ $subCategory->id }}">{{ $subCategory->title }}</label>
                                </div>
                            @endforeach
                        @else
                        @if($category->title !='Uncategories')
                            <div class="checkbox-button bordered-200 mr-5">
                                <input type="checkbox" name="categories[]" id="checkbox{{ $category->id }}" value="{{ $category->id }}" @if(in_array($category->id,request()->get('categories',[]))) checked="checked" @endif>
                                <label for="checkbox{{ $category->id }}">{{ $category->title }}</label>
                            </div>
                        @endif
                        @endif
                    @endforeach
               
            </div>
    <div class="row align-items-center">
        <div class="col-lg-12 d-flex align-items-center" style="justify-content: space-between;">
            <div class="checkbox-button primary-selected  ">
                        <div class="shadow-lg border border-gray300 rounded-sm search-input bg-white p-10 flex-grow-1">
                            <form action="/search" method="get">
                                <div class="form-group d-flex align-items-center m-0">
                                    <img loading="lazy"  width="25" height="25" src="https://img.icons8.com/sf-ultralight/25/000000/search.png" alt="search"/>
                                    <input type="text" name="search" class="form-control border-0" placeholder="Search ..."/>
                                    <!--<button type="submit" class="btn btn-primary rounded-pill">{{ trans('home.find') }}</button>-->
                                    <a href="{{$page}}" ><img loading="lazy"  width="25" height="25" src="/assets/default/img/icons/close.png" alt="search"/></a>
                                </div>
                            </form>
                        </div>
                    </div>

            
        @if (empty(request()->get('card')))
        
          <div class="checkbox-button primary-selected ml-10">
                <input type="radio" name="card" id="listView" value="list" >
                <label for="listView" class="bg-white border-0 mb-0">
                    <i data-feather="list" width="25" height="25" class="list text-primary "></i>
                </label>
            </div>
       <!--<div class="checkbox-button primary-selected ml-10">-->
       <!--     <input type="radio" name="card" id="listView" value="grid" @if(!empty(request()->get('card')) and request()->get('card') == 'grid') checked="checked" @endif>-->
       <!--     <label for="listView" class="bg-white border-0 mb-0">-->
       <!--         <i data-feather="list" width="25" height="25" class="{{ (!empty(request()->get('card')) and request()->get('card') == 'grid') ? 'text-primary' : '' }}"></i>-->
       <!--         {{-- <i data-feather="list" width="25" height="25" class="@if(empty(request()->get('card')) or request()->get('card') == 'list') text-primary @endif"></i> --}}-->
       <!--     </label>-->
            
       <!-- </div>-->
           
            @else

       @if(request()->get('card')=='list')
            <!--<a href="/{{ $page }}?card=list">  <div class="checkbox-button primary-selected ml-10">-->
            <!--<label for="gridView" class="bg-white border-0 mb-0">-->
            <!--        <i data-feather="grid" width="25" height="25" class="@if(empty(request()->get('card')) or request()->get('card') == 'grid') text-primary @endif"></i>-->
            <!--    </label>-->
            <!--</div></a>-->
              <div class="checkbox-button primary-selected ml-10">
            <input type="radio" name="card" id="gridView" value="grid" @if(!empty(request()->get('card')) and request()->get('card') == 'grid') checked="checked" @endif>
            <label for="gridView" class="bg-white border-0 mb-0">
                <!--<i data-feather="list" width="25" height="25" class="{{ (!empty(request()->get('card')) and request()->get('card') == 'grid') ? 'text-primary' : '' }}"></i>-->
                <i data-feather="grid" width="25" height="25" class="@if(empty(request()->get('card')) or request()->get('card') == 'grid') text-primary @endif"></i> 
            </label>
            
        </div>
            @else
            
            <!--<a href="/{{ $page }}?card=list">  <div class="checkbox-button primary-selected ml-10">-->
            <!--<label for="gridView" class="bg-white border-0 mb-0">-->
            <!--       <i data-feather="grid" width="25" height="25" class="@if(empty(request()->get('card')) or request()->get('card') == 'list') text-primary @endif"></i>-->
            <!--    </label>-->
            <!--</div></a>-->
            
                <div class="checkbox-button primary-selected ml-10">
                <input type="radio" name="card" id="listView" value="list" @if(empty(request()->get('card')) or request()->get('card') == 'list') checked="checked" @endif>
                <label for="listView" class="bg-white border-0 mb-0">
                    <i data-feather="list" width="25" height="25" class="@if(empty(request()->get('card')) or request()->get('card') == 'list') text-primary @endif"></i>
                </label>
            </div>
               
           
               @endif
            @endif
            <!--<div class=" primary-selected ml-10">-->
            <!--     <label for="" class="bg-white border-0 mb-0">-->
                   
            <!--        <div class=" primary-selected">-->
            <!--            <div class="ellipsis-icon" onclick="toggleDropdown()">-->
                            <!-- Insert your three dots icon/image here -->
            <!--              <img loading="lazy"  width="20" height="20" src="https://img.icons8.com/color/48/menu-2.png" alt="menu-2"/> -->
            <!--            </div>-->
            <!--            <div class="dropdown-menu webinars-lists-dropdown " id="sortDropdown" x-placement="bottom-start" >-->

            <!--                <a  class=" d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 sortby">-->
            
            <!--                <span class="ml-2">Sort By</span>-->
            <!--                <i class="fas fa-chart-line"></i>-->
            <!--                </a>-->
                            <!--<a data-val="experience" class=" select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 ">-->
            
                            <!--<span class="ml-2">Experience</span>-->
                            <!--</a>-->
            <!--                <a data-val="expensive" class=" select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 ">-->
                
            <!--                    <span class="ml-2">Highest Price</span>-->
            <!--                </a>-->
            <!--                <a data-val="inexpensive" class=" select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 ">-->
                
            <!--                    <span class="ml-2">Lowest Price</span>-->
            <!--                </a>-->
            <!--                <a data-val="top_sale" class=" select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 ">-->
                
               
            <!--                    <span class="ml-2">Best Sellers</span>-->
            <!--                 </a>-->
            <!--                 <a data-val="top_rate" class=" select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 ">-->
                
            <!--                    <span class="ml-2">{{ trans('public.best_rates') }}</span>-->
            <!--                 </a>-->
            <!--                <a href="/{{$page}}" class=" d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 sortby1">-->
            
            <!--                    <span class="ml-2">Reset All</span>-->
            <!--                    <i class="fas fa-chart-line"></i>-->
            <!--                </a>-->
            <!--                </div>-->
            <!--            {{-- <select name="sort" class="form-control font-14" id="sort-by" >-->
            <!--                <option disabled selected>{{ trans('public.sort_by') }}</option>-->
            <!--                <option value="">{{ trans('public.all') }}</option>-->
            <!--                <option value="newest" @if(request()->get('sort', null) == 'newest') selected="selected" @endif>{{ trans('public.newest') }}</option>-->
            <!--                <option value="expensive" @if(request()->get('sort', null) == 'expensive') selected="selected" @endif>{{ trans('public.expensive') }}</option>-->
            <!--                <option value="inexpensive" @if(request()->get('sort', null) == 'inexpensive') selected="selected" @endif>{{ trans('public.inexpensive') }}</option>-->
            <!--                <option value="bestsellers" @if(request()->get('sort', null) == 'bestsellers') selected="selected" @endif>{{ trans('public.bestsellers') }}</option>-->
            <!--                <option value="best_rates" @if(request()->get('sort', null) == 'best_rates') selected="selected" @endif>{{ trans('public.best_rates') }}</option>-->
            <!--            </select> --}}-->
            <!--        </div>-->
            <!--    </label>-->
            <!--</div>-->
        </div>
        

        {{-- <div class="col-lg-6 d-block d-md-flex align-items-center justify-content-end my-25 my-lg-0">
            <div class="d-flex align-items-center justify-content-between justify-content-md-center mx-0 mx-md-20 my-20 my-md-0">
                <label class="mb-0 mr-10 cursor-pointer" for="upcoming">{{ trans('panel.upcoming') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="upcoming" class="custom-control-input" id="upcoming" @if(request()->get('upcoming', null) == 'on') checked="checked" @endif>
                    <label class="custom-control-label" for="upcoming"></label>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between justify-content-md-center">
                <label class="mb-0 mr-10 cursor-pointer" for="free">{{ trans('public.free') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="free" class="custom-control-input" id="free" @if(request()->get('free', null) == 'on') checked="checked" @endif>
                    <label class="custom-control-label" for="free"></label>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between justify-content-md-center mx-0 mx-md-20 my-20 my-md-0">
                <label class="mb-0 mr-10 cursor-pointer" for="discount">{{ trans('public.discount') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="discount" class="custom-control-input" id="discount" @if(request()->get('discount', null) == 'on') checked="checked" @endif>
                    <label class="custom-control-label" for="discount"></label>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between justify-content-md-center">
                <label class="mb-0 mr-10 cursor-pointer" for="download">{{ trans('home.download') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="downloadable" class="custom-control-input" id="download" @if(request()->get('downloadable', null) == 'on') checked="checked" @endif>
                    <label class="custom-control-label" for="download"></label>
                </div>
            </div>
        </div> --}}

        {{-- <div class="col-lg-3 d-flex align-items-right">
            <select name="sort" class="form-control font-14">
                <option disabled selected>{{ trans('public.sort_by') }}</option>
                <option value="">{{ trans('public.all') }}</option>
                <option value="newest" @if(request()->get('sort', null) == 'newest') selected="selected" @endif>{{ trans('public.newest') }}</option>
                <option value="expensive" @if(request()->get('sort', null) == 'expensive') selected="selected" @endif>{{ trans('public.expensive') }}</option>
                <option value="inexpensive" @if(request()->get('sort', null) == 'inexpensive') selected="selected" @endif>{{ trans('public.inexpensive') }}</option>
                <option value="bestsellers" @if(request()->get('sort', null) == 'bestsellers') selected="selected" @endif>{{ trans('public.bestsellers') }}</option>
                <option value="best_rates" @if(request()->get('sort', null) == 'best_rates') selected="selected" @endif>{{ trans('public.best_rates') }}</option>
            </select>
        </div> --}}

    </div>
</div>
<script   src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script  >
    function toggleDropdown() {
        var dropdown = $("#sortDropdown");
        dropdown.toggle();
    }
    $('.select-change').click(function(){ 
    $('#sort-by').val($(this).data('val')).trigger('change');
})
</script>