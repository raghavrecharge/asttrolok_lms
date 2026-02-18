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
                            <option value="expensive" @if(request()->get('sort', null) == 'expensive') selected="selected" @endif>Highest Price</option>
                            <option value="inexpensive" @if(request()->get('sort', null) == 'inexpensive') selected="selected" @endif>Lowest Price</option>

                            <option value="top_sale" @if(request()->get('sort', null) == 'top_sale') selected="selected" @endif>Best Sellers</option>
                            <option value="top_rate" @if(request()->get('sort', null) == 'top_rate') selected="selected" @endif>{{ trans('public.best_rates') }}</option>
                        </select>
                    </div>
                       <div class="categories">

 @foreach($blogCategories as $category)
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
        <div class="col-lg-12  align-items-center">
            <div class="checkbox-button primary-selected  ">
                        <div class="shadow-lg border border-gray300 rounded-sm search-input bg-white p-10 flex-grow-1">
                            <form action="/search" method="get">
                                <div class="form-group d-flex align-items-center m-0">
                                    <img loading="lazy"  width="25" height="25" src="https://img.icons8.com/sf-ultralight/25/000000/search.png" alt="search"/>
                                    <input type="text" name="search" class="form-control border-0" placeholder="Search ..."/>

                                    <a href="{{$page}}" ><img loading="lazy"  width="25" height="25" src="/public/assets/default/img/icon/close.png" alt="search"/></a>
                                </div>
                            </form>
                        </div>
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
