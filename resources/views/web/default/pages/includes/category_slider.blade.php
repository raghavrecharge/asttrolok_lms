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
</style>

            <div id="topFilters" class="mt-25 shadow-lg border border-gray300 rounded-sm p-10 p-md-20 d-none">
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
                <div class="row align-items-center">
                    <div class="col-lg-9 d-block d-md-flex align-items-center justify-content-start my-25 my-lg-0">
                        <div class="d-flex align-items-center justify-content-between justify-content-md-center">
                            <label class="mb-0 mr-10 cursor-pointer" for="available_for_meetings">{{ trans('public.available_for_meetings') }}</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="available_for_meetings" class="custom-control-input" id="available_for_meetings" @if(request()->get('available_for_meetings',null) == 'on') checked="checked" @endif>
                                <label class="custom-control-label" for="available_for_meetings"></label>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between justify-content-md-center mx-0 mx-md-20 my-20 my-md-0">
                            <label class="mb-0 mr-10 cursor-pointer" for="free_meetings">{{ trans('public.free_meetings') }}</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="free_meetings" class="custom-control-input" id="free_meetings" @if(request()->get('free_meetings',null) == 'on') checked="checked" @endif>
                                <label class="custom-control-label" for="free_meetings"></label>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between justify-content-md-center">
                            <label class="mb-0 mr-10 cursor-pointer" for="discount">{{ trans('public.discount') }}</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="discount" class="custom-control-input" id="discount" @if(request()->get('discount',null) == 'on') checked="checked" @endif>
                                <label class="custom-control-label" for="discount"></label>
                            </div>
                        </div>

                    </div>

                </div>
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
