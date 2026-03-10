<div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
    <form method="get" class="mb-0">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4 items-end">
            <div class="form-group space-y-2 lg:col-span-2">
                <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.search')}}</label>
                <div class="relative group">
                    <input name="title" type="text" class="w-full pl-11 pr-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" value="{{ request()->get('title') }}" placeholder="Search by title...">
                    <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors text-xl">search</span>
                </div>
            </div>

            <div class="form-group space-y-2">
                <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.start_date')}}</label>
                <div class="relative">
                    <input type="date" name="from" value="{{ request()->get('from') }}" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                </div>
            </div>

            <div class="form-group space-y-2">
                <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.end_date')}}</label>
                <div class="relative">
                    <input type="date" name="to" value="{{ request()->get('to') }}" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                </div>
            </div>

            @php
                $filters = ['amount_asc', 'amount_desc', 'paid_amount_asc', 'paid_amount_desc', 'date_asc', 'date_desc'];
            @endphp
            <div class="form-group space-y-2">
                <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.filters')}}</label>
                <div class="relative">
                    <select name="sort" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                        <option value="">{{trans('admin/main.all')}}</option>
                        @foreach($filters as $filter)
                            <option value="{{ $filter }}" @if(request()->get('sort') == $filter) selected @endif>{{trans('update.'.$filter)}}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">sort</span>
                </div>
            </div>

            <div class="form-group space-y-2">
                <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.type')}}</label>
                <div class="relative">
                    <select name="target_type" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                        <option value="">{{trans('admin/main.all')}}</option>
                        @foreach(\App\Models\CashbackRule::$targetTypes as $type)
                            <option value="{{ $type }}" @if(request()->get('target_type') == $type) selected @endif>{{ trans('update.target_types_'.$type) }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">category</span>
                </div>
            </div>

            <div class="form-group space-y-2">
                <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.status')}}</label>
                <div class="relative">
                    <select name="status" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                        <option value="">{{trans('admin/main.all')}}</option>
                        <option value="active" {{ (request()->get('status') == 'active') ? 'selected' : '' }}>{{ trans('admin/main.active') }}</option>
                        <option value="inactive" {{ (request()->get('status') == 'inactive') ? 'selected' : '' }}>{{ trans('admin/main.inactive') }}</option>
                    </select>
                    <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">settings</span>
                </div>
            </div>

            <div class="form-group lg:col-span-full xl:col-span-1">
                <button type="submit" class="w-full py-2.5 bg-gray-900 text-white rounded-2xl font-bold hover:bg-gray-800 transition-all shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-rounded text-xl">filter_list</span>
                    {{trans('admin/main.show_results')}}
                </button>
            </div>
        </div>
    </form>
</div>
