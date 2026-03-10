@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries?v=1.0.2"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap&v=1.0.2" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap&v=1.0.2" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#16A34A",
                        "primary-light": "#DCFCE7",
                        "border": "#E5E7EB",
                        "text-dark": "#111827",
                        "text-light": "#6B7280"
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    spacing: {
                        "8": "8px",
                        "12": "12px", 
                        "16": "16px",
                        "20": "20px",
                        "24": "24px"
                    }
                },
            },
        }
    </script>
    <style>
        .page-container { font-family: 'Inter', sans-serif; }
        .page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        
        /* Hide old admin layout elements */
        .section-header, .section-body > .card:first-child, .section-body > section.card, .section-filters { 
            display: none !important; 
        }
        
        /* Page Container */
        .page-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        
        /* Tab Navigation - Horizontal Design */
        .tab-nav {
            display: flex;
            align-items: center;
            gap: 32px;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 12px;
            margin-bottom: 24px;
            background: #FFFFFF;
            overflow-x: auto;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            .tab-nav {
                gap: 24px;
                padding-bottom: 8px;
            }
        }
        
        .tab-button {
            font-size: 16px;
            font-weight: 500;
            color: #64748B;
            cursor: pointer;
            padding-bottom: 8px;
            position: relative;
            transition: color 0.2s ease;
            background: none;
            border: none;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .tab-button:hover {
            color: #1F2937;
        }
        
        .tab-button.active {
            color: #16A34A;
            font-weight: 600;
            border-bottom: 3px solid #16A34A;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            padding: 0;
            box-shadow: none;
            max-width: 100%;
        }
        
        /* Update card styling for tab content */
        .tab-content .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .tab-content .form-field {
            margin-bottom: 20px;
        }
        
        .tab-content .form-grid {
            margin-bottom: 20px;
        }
        
        /* Tab Container */
        .tab-container {
            background: #ffffff;
            border: none;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            max-width: 100%;
        }
        
        /* Card Component */
        .card {
            background: #ffffff;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .card-title {
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: #111827;
        }
        
        /* Form Fields */
        .form-field {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
        }
        
        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #16A34A;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
        }
        
        .form-input:disabled {
            background: #F9FAFB;
            color: #6B7280;
        }
        
        /* Two Column Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        /* Product Item */
        .product-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            border-radius: 8px;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .product-details {
            display: flex;
            flex-direction: column;
        }
        
        .product-title {
            font-weight: 500;
            color: #111827;
        }
        
        .product-price {
            font-size: 14px;
            color: #6B7280;
        }
        
        /* Upfront Payment */
        .upfront-input {
            background: #DCFCE7;
            border: 1px solid #86EFAC;
            border-radius: 6px;
            padding: 10px;
        }
        
        .upfront-helper {
            color: #6B7280;
            font-size: 14px;
        }
        
        /* Installment Row */
        .installment-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .installment-number {
            width: 20px;
            font-weight: 500;
            color: #111827;
        }
        
        .installment-amount {
            width: 100px;
            padding: 6px;
            border: 1px solid #D1D5DB;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .installment-due {
            color: #6B7280;
            font-size: 14px;
        }
        
        /* Button Styles */
        .btn-add {
            color: #16A34A;
            font-weight: 500;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .btn-delete {
            color: #EF4444;
            cursor: pointer;
            background: none;
            border: none;
            padding: 4px;
        }
        
        /* Header Row */
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
@endpush

@section('content')
<div class="page-wrapper">

    <form method="post" action="{{ getAdminPanelUrl('/financial/installments/'. (!empty($installment) ? $installment->id.'/update' : 'store')) }}" id="installmentForm">
        {{ csrf_field() }}

        <!-- Tabbed Interface -->
        <div class="tab-container">
            <!-- Tab Navigation -->
            <div class="tab-nav">
                <button type="button" class="tab-button active" onclick="switchTab('basic')">
                    Basic Information
                </button>
                <button type="button" class="tab-button" onclick="switchTab('settings')">
                    Plan Settings
                </button>
                <button type="button" class="tab-button" onclick="switchTab('products')">
                    Target Products
                </button>
                <button type="button" class="tab-button" onclick="switchTab('payment')">
                    Payment Configuration
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content active" id="basic-tab">
                <h2 class="card-title">
                    <span class="material-symbols-outlined">info</span>
                    Basic Information
                </h2>
                
                <div class="form-field">
                    <label class="form-label">PLAN ID / TITLE</label>
                    <input type="text" class="form-input" value="{{ !empty($installment) ? 'INST-' . str_pad($installment->id, 3, '0', STR_PAD_LEFT) : 'INST-NEW' }}" disabled>
                </div>
                
                <div class="form-field">
                    <label class="form-label">MAIN DISPLAY TITLE</label>
                    <input type="text" name="title" class="form-input" value="{{ !empty($installment) ? $installment->title : old('title') }}" placeholder="Enter public title">
                    @error('title')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-field">
                    <label class="form-label">MAIN TITLE</label>
                    <input type="text" name="main_title" class="form-input" value="{{ !empty($installment) ? $installment->main_title : old('main_title') }}" placeholder="Enter main title">
                    @error('main_title')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-field">
                    <label class="form-label">DESCRIPTION</label>
                    <textarea name="description" class="form-input" rows="3" placeholder="Internal or public notes about the plan...">{{ !empty($installment) ? $installment->description : old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="tab-content" id="settings-tab">
                <h2 class="card-title">
                    <span class="material-symbols-outlined">settings</span>
                    Plan Settings
                </h2>
                
                <div class="form-grid">
                    <div class="form-field">
                        <label class="form-label">CAPACITY</label>
                        <input type="number" name="capacity" class="form-input" value="{{ !empty($installment) ? $installment->capacity : old('capacity') }}" placeholder="Unlimited">
                        @error('capacity')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="form-field">
                        <label class="form-label">USER GROUP</label>
                        <select name="group_ids[]" class="form-input" multiple>
                            <option value="">Select Groups</option>
                            @if(isset($userGroups))
                                @foreach($userGroups as $group)
                                    <option value="{{ $group->id }}" 
                                        @if(!empty($installment) && !empty($installment->userGroups) && $installment->userGroups->contains('id', $group->id)) selected @endif>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('group_ids')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-field">
                        <label class="form-label">START DATE</label>
                        <input type="date" name="start_date" class="form-input" 
                            value="{{ !empty($installment) && !empty($installment->start_date) ? date('Y-m-d', $installment->start_date) : old('start_date') }}" 
                            placeholder="mm/dd/yyyy">
                        @error('start_date')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="form-field">
                        <label class="form-label">END DATE</label>
                        <input type="date" name="end_date" class="form-input" 
                            value="{{ !empty($installment) && !empty($installment->end_date) ? date('Y-m-d', $installment->end_date) : old('end_date') }}" 
                            placeholder="mm/dd/yyyy">
                        @error('end_date')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Additional Settings -->
                <div class="form-grid">
                    <div class="form-field">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="enable" value="1" 
                                @if(!empty($installment) && $installment->enable) checked @endif
                                @if(old('enable')) checked @endif>
                            <span class="form-label" style="margin: 0;">Enable Plan</span>
                        </label>
                    </div>
                    
                    <div class="form-field">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="verification" value="1" 
                                @if(!empty($installment) && $installment->verification) checked @endif
                                @if(old('verification')) checked @endif>
                            <span class="form-label" style="margin: 0;">Require Verification</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="products-tab">
                <div class="header-row">
                    <h2 class="card-title">
                        <span class="material-symbols-outlined">shopping_bag</span>
                        Target Products
                    </h2>
                    <button type="button" class="btn-add" onclick="addTargetProduct()">
                        <span class="material-symbols-outlined">add</span>
                        Add Product
                    </button>
                </div>
                
                <!-- Target Type Selection -->
                <div class="form-field">
                    <label class="form-label">TARGET TYPE</label>
                    <select name="target_type" id="targetType" class="form-input" onchange="updateTargetOptions()">
                        <option value="">Select Target Type</option>
                        <option value="all" {{ !empty($installment) && $installment->target_type == 'all' ? 'selected' : '' }}>All Products</option>
                        <option value="specific_courses" {{ !empty($installment) && $installment->target_type == 'specific_courses' ? 'selected' : '' }}>Specific Courses</option>
                        <option value="specific_products" {{ !empty($installment) && $installment->target_type == 'specific_products' ? 'selected' : '' }}>Specific Products</option>
                        <option value="specific_bundles" {{ !empty($installment) && $installment->target_type == 'specific_bundles' ? 'selected' : '' }}>Specific Bundles</option>
                        <option value="specific_packages" {{ !empty($installment) && $installment->target_type == 'specific_packages' ? 'selected' : '' }}>Specific Packages</option>
                    </select>
                    @error('target_type')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Dynamic Product List -->
                <div id="productList">
                    @if(!empty($installment) && !empty($installment->specificationItems))
                        @foreach($installment->specificationItems as $item)
                            <div class="product-item">
                                <div class="product-info">
                                    <span class="material-symbols-outlined">
                                        @if($item->webinar_id) video_library
                                        @elseif($item->product_id) shopping_bag
                                        @elseif($item->bundle_id) inventory_2
                                        @elseif($item->subscribe_id) card_membership
                                        @else book
                                        @endif
                                    </span>
                                    <div class="product-details">
                                        <div class="product-title">
                                            @if($item->webinar_id) {{ $item->webinar->title ?? 'Course' }}
                                            @elseif($item->product_id) {{ $item->product->title ?? 'Product' }}
                                            @elseif($item->bundle_id) {{ $item->bundle->title ?? 'Bundle' }}
                                            @elseif($item->subscribe_id) {{ $item->subscribe->title ?? 'Package' }}
                                            @else Unknown Item
                                            @endif
                                        </div>
                                        <div class="product-price">
                                            @if($item->webinar_id) ${{ number_format($item->webinar->price ?? 0, 2) }}
                                            @elseif($item->product_id) ${{ number_format($item->product->price ?? 0, 2) }}
                                            @elseif($item->bundle_id) ${{ number_format($item->bundle->price ?? 0, 2) }}
                                            @elseif($item->subscribe_id) ${{ number_format($item->subscribe->price ?? 0, 2) }}
                                            @else $0.00
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-delete" onclick="removeProduct(this)">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="product-item">
                            <div class="product-info">
                                <span class="material-symbols-outlined">book</span>
                                <div class="product-details">
                                    <div class="product-title">Vedic Astrology 101</div>
                                    <div class="product-price">$1,200.00</div>
                                </div>
                            </div>
                            <button type="button" class="btn-delete" onclick="removeProduct(this)">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    @endif
                </div>
                
                <!-- Hidden inputs for form submission -->
                <div id="hiddenProductInputs">
                    @if(!empty($installment) && !empty($installment->specificationItems))
                        @foreach($installment->specificationItems as $item)
                            @if($item->webinar_id)
                                <input type="hidden" name="webinar_ids[]" value="{{ $item->webinar_id }}">
                            @elseif($item->product_id)
                                <input type="hidden" name="product_ids[]" value="{{ $item->product_id }}">
                            @elseif($item->bundle_id)
                                <input type="hidden" name="bundle_ids[]" value="{{ $item->bundle_id }}">
                            @elseif($item->subscribe_id)
                                <input type="hidden" name="subscribe_ids[]" value="{{ $item->subscribe_id }}">
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="tab-content" id="payment-tab">
                <h2 class="card-title">
                    <span class="material-symbols-outlined">payments</span>
                    Payment Configuration
                </h2>
                
                <!-- Upfront Payment Section -->
                <div>
                    <label class="form-label">UPFRONT PAYMENT</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="number" name="upfront" class="form-input upfront-input" 
                            value="{{ !empty($installment) ? $installment->upfront : old('upfront') }}" 
                            placeholder="0.00" step="0.01">
                        <span class="upfront-helper" id="upfrontHelper">33.3% of total</span>
                    </div>
                    @error('upfront')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Upfront Type -->
                <div class="form-field">
                    <label class="form-label">UPFRONT TYPE</label>
                    <select name="upfront_type" class="form-input">
                        <option value="fixed_amount" {{ !empty($installment) && $installment->upfront_type == 'fixed_amount' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="percent" {{ !empty($installment) && $installment->upfront_type == 'percent' ? 'selected' : '' }}>Percentage</option>
                    </select>
                    @error('upfront_type')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Installments Section -->
                <div>
                    <div class="header-row">
                        <label class="form-label">PAYMENT STEPS (INSTALLMENTS)</label>
                        <button type="button" class="btn-add" onclick="addInstallmentStep()">
                            <span class="material-symbols-outlined">add</span>
                            Add Step
                        </button>
                    </div>
                    
                    <!-- Dynamic Installment Rows -->
                    <div id="installmentStepsList">
                        @if(!empty($installment) && !empty($installment->steps))
                            @foreach($installment->steps as $index => $step)
                                <div class="installment-row" data-step-id="{{ $step->id }}">
                                    <span class="installment-number">{{ $index + 1 }}</span>
                                    <input type="number" name="steps[{{ $step->id }}][amount]" 
                                        class="installment-amount" value="{{ $step->amount }}" 
                                        placeholder="0.00" step="0.01">
                                    <select name="steps[{{ $step->id }}][amount_type]" class="form-input" style="width: 100px; padding: 4px;">
                                        <option value="fixed_amount" {{ $step->amount_type == 'fixed_amount' ? 'selected' : '' }}>Fixed</option>
                                        <option value="percent" {{ $step->amount_type == 'percent' ? 'selected' : '' }}>Percent</option>
                                    </select>
                                    <input type="number" name="steps[{{ $step->id }}][deadline]" 
                                        class="installment-amount" value="{{ $step->deadline }}" 
                                        placeholder="Days" style="width: 80px;">
                                    <span class="installment-due">Due in {{ $step->deadline }} days</span>
                                    <button type="button" class="btn-delete" onclick="removeInstallmentStep(this)">
                                        <span class="material-symbols-outlined">close</span>
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <!-- Default installment steps for new plans -->
                            <div class="installment-row">
                                <span class="installment-number">1</span>
                                <input type="number" name="steps[record][amount][]" 
                                    class="installment-amount" value="400.00" placeholder="0.00" step="0.01">
                                <select name="steps[record][amount_type][]" class="form-input" style="width: 100px; padding: 4px;">
                                    <option value="fixed_amount" selected>Fixed</option>
                                    <option value="percent">Percent</option>
                                </select>
                                <input type="number" name="steps[record][deadline][]" 
                                    class="installment-amount" value="30" placeholder="Days" style="width: 80px;">
                                <span class="installment-due">Due in 30 days</span>
                                <button type="button" class="btn-delete" onclick="removeInstallmentStep(this)">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            
                            <div class="installment-row">
                                <span class="installment-number">2</span>
                                <input type="number" name="steps[record][amount][]" 
                                    class="installment-amount" value="400.00" placeholder="0.00" step="0.01">
                                <select name="steps[record][amount_type][]" class="form-input" style="width: 100px; padding: 4px;">
                                    <option value="fixed_amount" selected>Fixed</option>
                                    <option value="percent">Percent</option>
                                </select>
                                <input type="number" name="steps[record][deadline][]" 
                                    class="installment-amount" value="60" placeholder="Days" style="width: 80px;">
                                <span class="installment-due">Due in 60 days</span>
                                <button type="button" class="btn-delete" onclick="removeInstallmentStep(this)">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            
                            <div class="installment-row">
                                <span class="installment-number">3</span>
                                <input type="number" name="steps[record][amount][]" 
                                    class="installment-amount" value="400.00" placeholder="0.00" step="0.01">
                                <select name="steps[record][amount_type][]" class="form-input" style="width: 100px; padding: 4px;">
                                    <option value="fixed_amount" selected>Fixed</option>
                                    <option value="percent">Percent</option>
                                </select>
                                <input type="number" name="steps[record][deadline][]" 
                                    class="installment-amount" value="90" placeholder="Days" style="width: 80px;">
                                <span class="installment-due">Due in 90 days</span>
                                <button type="button" class="btn-delete" onclick="removeInstallmentStep(this)">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div style="margin-top: 32px; text-align: center;">
            <button type="submit" style="background: #16A34A; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                Save Installment Plan
            </button>
        </div>

    </form>

</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/default/js/admin/create_installment.min.js"></script>
    
    <script>
        // Tab Switching Function
        function switchTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            const selectedTab = document.getElementById(tabName + '-tab');
            if (selectedTab) {
                selectedTab.classList.add('active');
            }
            
            // Add active class to clicked button
            const clickedButton = document.querySelector(`[onclick="switchTab('${tabName}')"]`);
            if (clickedButton) {
                clickedButton.classList.add('active');
            }
        }
        
        // Dynamic Product Management
        let productCounter = 0;
        
        function addTargetProduct() {
            const productList = document.getElementById('productList');
            const productItem = document.createElement('div');
            productItem.className = 'product-item';
            productItem.innerHTML = `
                <div class="product-info">
                    <span class="material-symbols-outlined">book</span>
                    <div class="product-details">
                        <div class="product-title">New Product ${++productCounter}</div>
                        <div class="product-price">$0.00</div>
                    </div>
                </div>
                <button type="button" class="btn-delete" onclick="removeProduct(this)">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            `;
            productList.appendChild(productItem);
        }
        
        function removeProduct(button) {
            button.closest('.product-item').remove();
        }
        
        // Dynamic Installment Step Management
        let installmentCounter = 3;
        
        function addInstallmentStep() {
            const stepsList = document.getElementById('installmentStepsList');
            const stepRow = document.createElement('div');
            stepRow.className = 'installment-row';
            
            const newCounter = ++installmentCounter;
            stepRow.innerHTML = `
                <span class="installment-number">${newCounter}</span>
                <input type="number" name="steps[record][amount][]" 
                    class="installment-amount" value="0.00" placeholder="0.00" step="0.01">
                <select name="steps[record][amount_type][]" class="form-input" style="width: 100px; padding: 4px;">
                    <option value="fixed_amount" selected>Fixed</option>
                    <option value="percent">Percent</option>
                </select>
                <input type="number" name="steps[record][deadline][]" 
                    class="installment-amount" value="${newCounter * 30}" placeholder="Days" style="width: 80px;">
                <span class="installment-due">Due in ${newCounter * 30} days</span>
                <button type="button" class="btn-delete" onclick="removeInstallmentStep(this)">
                    <span class="material-symbols-outlined">close</span>
                </button>
            `;
            stepsList.appendChild(stepRow);
            renumberInstallmentSteps();
        }
        
        function removeInstallmentStep(button) {
            button.closest('.installment-row').remove();
            renumberInstallmentSteps();
        }
        
        function renumberInstallmentSteps() {
            const steps = document.querySelectorAll('#installmentStepsList .installment-row');
            steps.forEach((step, index) => {
                const numberElement = step.querySelector('.installment-number');
                const dueElement = step.querySelector('.installment-due');
                const deadlineInput = step.querySelector('input[name*="deadline"]');
                
                if (numberElement) numberElement.textContent = index + 1;
                if (deadlineInput && dueElement) {
                    const days = deadlineInput.value || ((index + 1) * 30);
                    dueElement.textContent = `Due in ${days} days`;
                }
            });
        }
        
        // Update installment due dates when deadline changes
        document.addEventListener('input', function(e) {
            if (e.target.name && e.target.name.includes('deadline')) {
                const stepRow = e.target.closest('.installment-row');
                const dueElement = stepRow.querySelector('.installment-due');
                if (dueElement) {
                    dueElement.textContent = `Due in ${e.target.value} days`;
                }
            }
        });
        
        // Update upfront helper text
        document.addEventListener('input', function(e) {
            if (e.target.name === 'upfront' || e.target.name === 'upfront_type') {
                updateUpfrontHelper();
            }
        });
        
        function updateUpfrontHelper() {
            const upfrontInput = document.querySelector('input[name="upfront"]');
            const upfrontType = document.querySelector('select[name="upfront_type"]');
            const helperElement = document.getElementById('upfrontHelper');
            
            if (upfrontInput && upfrontType && helperElement) {
                const value = upfrontInput.value || 0;
                const type = upfrontType.value;
                
                if (type === 'percent') {
                    helperElement.textContent = `${value}% of total`;
                } else {
                    helperElement.textContent = `$${parseFloat(value).toFixed(2)} fixed amount`;
                }
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateUpfrontHelper();
            
            // Add event listeners to existing deadline inputs
            const deadlineInputs = document.querySelectorAll('input[name*="deadline"]');
            deadlineInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const stepRow = this.closest('.installment-row');
                    const dueElement = stepRow.querySelector('.installment-due');
                    if (dueElement) {
                        dueElement.textContent = `Due in ${this.value} days`;
                    }
                });
            });
        });
        
        // Target type change handler
        function updateTargetOptions() {
            const targetType = document.getElementById('targetType').value;
            const productList = document.getElementById('productList');
            
            // This would typically load different products based on target type
            // For now, we'll just show a message
            if (targetType === 'all') {
                productList.innerHTML = '<div class="product-item"><div class="product-info"><span class="material-symbols-outlined">apps</span><div class="product-details"><div class="product-title">All Products Available</div><div class="product-price">No restrictions</div></div></div></div>';
            }
        }
    </script>
@endpush
