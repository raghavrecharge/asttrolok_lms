<style>
   .categories {
    display: flex;
    flex-wrap: nowrap !important;
    overflow-x: auto !important;
     min-height: 60px !important;
}

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

    flex-direction: row;

}
.sortby1 {

    border-top: 1px solid #cbcaca;
    position: relative;
    top: 10px;
    font-size: 14px;

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
                            <option value="max_price" @if(request()->get('sort', null) == 'max_price') selected="selected" @endif>High Price</option>
                            <option value="min_price" @if(request()->get('sort', null) == 'min_price') selected="selected" @endif>Low Price</option>

                            <option value="top_sale" @if(request()->get('sort', null) == 'top_sale') selected="selected" @endif>Best Sellers</option>
                            <option value="top_rate" @if(request()->get('sort', null) == 'top_rate') selected="selected" @endif>{{ trans('public.best_rates') }}</option>
                        </select>
                    </div>
                       <div class="categories">

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

                                    <a href="{{$page}}" ><img loading="lazy"  width="25" height="25" src="/assets/default/img/icons/close.png" alt="search"/></a>
                                </div>
                            </form>
                        </div>
                    </div>

        @if (empty(request()->get('card')))

          <div class="checkbox-button primary-selected ml-10">
                <input type="radio" name="card" id="listView" value="list" >
                <label for="listView" class="bg-white border-0 mb-0">
                    <i data-feather="grid" width="25" height="25" class="@if(empty(request()->get('card')) or request()->get('card') == 'grid') text-primary @endif"></i>
                </label>
            </div>

            @else

       @if(request()->get('card')=='list')

              <div class="checkbox-button primary-selected ml-10">
            <input type="radio" name="card" id="gridView" value="grid" @if(!empty(request()->get('card')) and request()->get('card') == 'grid') checked="checked" @endif>
            <label for="gridView" class="bg-white border-0 mb-0">
                <i data-feather="list" width="25" height="25" class="{{ (!empty(request()->get('card')) and request()->get('card') == 'grid') ? 'text-primary' : '' }}"></i>

            </label>

        </div>
            @else

                <div class="checkbox-button primary-selected ml-10">
                <input type="radio" name="card" id="listView" value="list" @if(empty(request()->get('card')) or request()->get('card') == 'list') checked="checked" @endif>
                <label for="listView" class="bg-white border-0 mb-0">

                    <i data-feather="grid" width="25" height="25" class="@if(empty(request()->get('card')) or request()->get('card') == 'grid') text-primary @endif"></i>
                </label>
            </div>

               @endif
            @endif
            <div class=" primary-selected ml-10">
                 <label  class="bg-white border-0 mb-0">

                    <div class=" primary-selected">
                        <div class="ellipsis-icon" onclick="toggleDropdown()">

                            <img loading="lazy"  width="20" height="20" src="https://img.icons8.com/color/48/menu-2.png" alt="menu-2"/>
                        </div>
                        <div class="dropdown-menu webinars-lists-dropdown " id="sortDropdown" x-placement="bottom-start" >

                            <a  class=" d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 sortby">

                            <span class="ml-2">Sort By</span>
                            <i class="fas fa-chart-line"></i>
                            </a>

                            <a data-val="max_price" class=" select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 ">

                                <span class="ml-2">High Price</span>
                            </a>
                            <a data-val="min_price" class=" select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 ">

                                <span class="ml-2">Low Price</span>
                            </a>
                            <a data-val="top_sale" class=" select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 ">

                                <span class="ml-2">Best Sellers</span>
                             </a>
                             <a data-val="top_rate" class=" select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 ">

                                <span class="ml-2">{{ trans('public.best_rates') }}</span>
                             </a>
                             <a href="/consult-with-astrologers" class=" d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 sortby1">

                                <span class="ml-2">Reset All</span>
                                <i class="fas fa-chart-line"></i>
                            </a>
                            </div>

                    </div>
                </label>
            </div>
        </div>

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
