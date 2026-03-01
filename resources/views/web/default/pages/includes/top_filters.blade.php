<style>
/* Categories container - Horizontal scroll WITHOUT scrollbar */
.categories {
    display: flex;
    flex-wrap: nowrap !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    min-height: 50px !important;
    width: 100%;
    align-items: center;
    
    /* Hide scrollbar */
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

/* Hide scrollbar for Chrome, Safari, Opera */
.categories::-webkit-scrollbar {
    display: none;
}

/* Reduced gap between categories */
.categories .checkbox-button {
    margin-right: 12px !important;
    flex-shrink: 0;
}

/* Right side icons INSIDE categories */
.categories .view-controls {
    margin-left: auto !important;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

/* icon button styling */
.view-controls .checkbox-button label,
.view-controls label {
    background: #fff !important;
    border: 0 !important;
    cursor: pointer;
}

/* Checkbox label styling */
.checkbox-button label {
    cursor: pointer;
    font-size: 0.875rem;
    padding: 5px 12px;
    border-color: rgba(193, 193, 193, 1) !important;
    border-style: solid !important;
    border-width: 1px !important; 
    border-radius: 50px !important;
    background-color: #f1f1f1;
    color: rgba(0, 0, 0, 1);
    transition: all 0.3s ease;
    white-space: nowrap;
}

/* Active state */
.checkbox-button input:checked + label {
    background: linear-gradient(180deg, #43d477 0%, #29ae59 100%);
    color: white;
    border-color: #29ae59;
}

/* Hide checkbox inputs */
.checkbox-button input[type="checkbox"],
.checkbox-button input[type="radio"] {
    display: none;
}

/* Dropdown positioning - Right side */
.primary-selected {
    position: relative;
}

.webinars-lists-dropdown {
    position: absolute;
    top: 100%;
    right: 0 !important;
    left: auto !important;
    min-width: 200px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    padding: 8px 0;
    margin-top: 5px;
    display: none;
    z-index: 1000;
}

.webinars-lists-dropdown a {
    padding: 8px 16px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: background 0.2s;
}

.webinars-lists-dropdown a:hover {
    background: #f5f5f5;
}

.sortby {
    font-weight: 600;
    color: #333 !important;
    border-bottom: 1px solid #cbcaca;
    margin-bottom: 4px;
    cursor: default !important;
}

.sortby:hover {
    background: transparent !important;
}

.sortby1 {
    border-top: 1px solid #cbcaca;
    margin-top: 4px;
    padding-top: 12px;
    color: #d32f2f !important;
}

.ellipsis-icon {
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: background 0.2s;
}

.ellipsis-icon:hover {
    background: #f5f5f5;
}

.dropdown-menu {
    padding: 5px 4px 13px 10px;
}
</style>

<div id="topFilters" class="p-md-20 homehide1">
    
    <!-- Hidden sort select -->
    <div class="col-lg-3 d-flex align-items-center d-none">
        <select name="sort" id="sort-by" class="form-control d-none">
            <option disabled selected>{{ trans('public.sort_by') }}</option>
            <option value="">{{ trans('public.all') }}</option>
            <option value="experience" @if(request()->get('sort') == 'experience') selected @endif>Experience</option>
            <option value="expensive" @if(request()->get('sort') == 'expensive') selected @endif>Highest Price</option>
            <option value="inexpensive" @if(request()->get('sort') == 'inexpensive') selected @endif>Lowest Price</option>
            <option value="top_sale" @if(request()->get('sort') == 'top_sale') selected @endif>Best Sellers</option>
            <option value="top_rate" @if(request()->get('sort') == 'top_rate') selected @endif>{{ trans('public.best_rates') }}</option>
        </select>
    </div>

    <!-- Categories horizontal scroll -->
    <div class="categories">
        
        {{-- ✅ All Categories (hidden on category pages) --}}
        @if(empty($hideCategoryFilter))
        @foreach($categories as $category)
            @if(!empty($category->subCategories) && count($category->subCategories))
                @foreach($category->subCategories as $subCategory)
                    <div class="checkbox-button bordered-200 mr-5">
                        <input type="checkbox" name="categories[]" id="checkbox{{ $subCategory->id }}"
                            value="{{ $subCategory->id }}"
                            @if(in_array($subCategory->id, request()->get('categories',[]))) checked @endif>
                        <label for="checkbox{{ $subCategory->id }}">{{ $subCategory->title }}</label>
                    </div>
                @endforeach
            @else
                @if($category->title != 'Uncategories')
                    <div class="checkbox-button bordered-200 mr-5">
                        <input type="checkbox" name="categories[]" id="checkbox{{ $category->id }}"
                            value="{{ $category->id }}"
                            @if(in_array($category->id, request()->get('categories',[]))) checked @endif>
                        <label for="checkbox{{ $category->id }}">{{ $category->title }}</label>
                    </div>
                @endif
            @endif
        @endforeach
        @endif

        {{-- ✅ HINDI FILTER - Only show for webinars/bundles --}}
@if(!isset($hideLanguageFilter) || !$hideLanguageFilter)
    <div class="checkbox-button bordered-200 mr-5">
        <input type="checkbox" name="hindi" id="hindiFilter" value="on"
            @if(request()->get('hindi') == 'on') checked @endif>
        <label for="hindiFilter"> Hindi</label>
    </div>

    {{-- ✅ ENGLISH FILTER - Only show for webinars/bundles --}}
    <div class="checkbox-button bordered-200 mr-5">
        <input type="checkbox" name="english" id="englishFilter" value="on"
            @if(request()->get('english') == 'on') checked @endif>
        <label for="englishFilter"> English</label>
    </div>
@endif
 {{-- ✅ HINDI FILTER - Only show for webinars/bundles --}}
@if(!isset($hideLanguageFilter) || !$hideLanguageFilter)
    <div class="checkbox-button bordered-200 mr-5">
        <input type="checkbox" name="recordedclasses" id="recordedclasses" value="on"
            @if(request()->get('recordedclasses') == 'on') checked @endif>
        <label for="recordedclasses"> Recorded Courses </label>
    </div>

    {{-- ✅ LIVE COURSES FILTER --}}
    <div class="checkbox-button bordered-200 mr-5">
        <input type="checkbox" name="liveClasses" id="liveClasses" value="on"
            @if(request()->get('liveClasses') == 'on') checked @endif>
        <label for="liveClasses"> Live Courses </label>
    </div>

    {{-- ✅ SUBSCRIPTION FILTER --}}
    <div class="checkbox-button bordered-200 mr-5">
        <input type="checkbox" name="subscription" id="subscriptionFilter" value="on"
            @if(request()->get('subscription') == 'on') checked @endif>
        <label for="subscriptionFilter"> Subscription </label>
    </div>

    {{-- ✅ UPCOMING FILTER --}}
    <div class="checkbox-button bordered-200 mr-5">
        <input type="checkbox" name="upcomingFilter" id="upcomingFilter" value="on"
            @if(request()->get('upcomingFilter') == 'on') checked @endif>
        <label for="upcomingFilter"> Upcoming </label>
    </div>
@endif

        {{-- ✅ FREE FILTER 
        <div class="checkbox-button bordered-200 mr-5">
            <input type="checkbox" name="free" id="freeFilter" value="on"
                @if(request()->get('free') == 'on') checked @endif>
            <label for="freeFilter">💰 Free</label>
        </div>--}}

        {{-- ✅ DISCOUNT FILTER 
        <div class="checkbox-button bordered-200 mr-5">
            <input type="checkbox" name="discount" id="discountFilter" value="on"
                @if(request()->get('discount') == 'on') checked @endif>
            <label for="discountFilter">🏷️ Discount</label>
        </div>--}}

        {{-- ✅ RIGHT SIDE CONTROLS --}}
        <div class="view-controls">
            
            {{-- List/Grid Toggle --}}
            @if (empty(request()->get('card')))
                {{--<div class="checkbox-button primary-selected ml-10">
                    <input type="radio" name="card" id="listView" value="list">
                    <label for="listView" class="bg-white border-0 mb-0">
                        <i data-feather="list" width="25" height="25" class="list text-primary"></i>
                    </label>
                </div>--}}
            @else
                @if(request()->get('card')=='list')
                    {{--<div class="checkbox-button primary-selected ml-10">
                        <input type="radio" name="card" id="gridView" value="grid">
                        <label for="gridView" class="bg-white border-0 mb-0">
                            <i data-feather="grid" width="25" height="25"
                                class="@if(request()->get('card')=='grid') text-primary @endif"></i>
                        </label>
                    </div>--}}
                @else
                    <div class="checkbox-button primary-selected ml-10">
                        <input type="radio" name="card" id="listView" value="list"
                            @if(request()->get('card')=='list') checked @endif>
                        <label for="listView" class="bg-white border-0 mb-0">
                            <i data-feather="list" width="25" height="25"
                                class="@if(request()->get('card')=='list') text-primary @endif"></i>
                        </label>
                    </div>
                @endif
            @endif

            {{-- Sort Menu --}}
            <div class="primary-selected ml-10">
                <label class="bg-white border-0 mb-0">
                    <div class="primary-selected">
                        {{--<div class="ellipsis-icon" onclick="toggleDropdown()">
                            <img loading="lazy" width="20" height="20"
                                src="https://img.icons8.com/color/48/menu-2.png" alt="menu-2"/>
                        </div>--}}

                        <div class="dropdown-menu webinars-lists-dropdown" id="sortDropdown">
                            <a class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 sortby">
                                <span class="ml-2">Sort By</span>
                            </a>

                            <a data-val="expensive" class="select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1">
                                <span class="ml-2">Highest Price</span>
                            </a>

                            <a data-val="inexpensive" class="select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1">
                                <span class="ml-2">Lowest Price</span>
                            </a>

                            <a data-val="top_sale" class="select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1">
                                <span class="ml-2">Best Sellers</span>
                            </a>

                            <a data-val="top_rate" class="select-change d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1">
                                <span class="ml-2">{{ trans('public.best_rates') }}</span>
                            </a>

                            <a href="/{{ $page ?? 'classes' }}" class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm1 mt-1 sortby1">
                                <span class="ml-2">Reset All</span>
                            </a>
                        </div>
                    </div>
                </label>
            </div>
            
        </div> <!-- END view-controls -->
        
    </div> <!-- END categories -->

</div> <!-- END topFilters -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Dropdown toggle
    function toggleDropdown() {
        $("#sortDropdown").toggle();
    }

    $(document).ready(function() {
        // ✅ Any filter change -> submit the parent form
        $('#filtersForm').on('change', 'input[name="categories[]"], #hindiFilter, #englishFilter, #freeFilter, #discountFilter, #recordedclasses, #liveClasses, #subscriptionFilter, #upcomingFilter, #sort-by, input[name="card"]', function() {
            $('#filtersForm').submit();
        });

        // ✅ Close dropdown on outside click
        $(document).click(function(e) {
            if (!$(e.target).closest('.ellipsis-icon, #sortDropdown').length) {
                $("#sortDropdown").hide();
            }
        });

        // ✅ Prevent dropdown close on inside click
        $('#sortDropdown').click(function(e) {
            e.stopPropagation();
        });
    });

    // ✅ Sort dropdown click
    $('.select-change').click(function(e) {
        e.preventDefault();
        $('#sort-by').val($(this).data('val'));
        $('#filtersForm').submit();
    });
</script>
