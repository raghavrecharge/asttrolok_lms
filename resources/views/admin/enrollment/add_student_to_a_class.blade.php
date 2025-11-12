@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">


            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <form action="{{ getAdminPanelUrl() }}/enrollments/store" method="Post">
                                        {{ csrf_field() }}
                                        
                                        <!-- add new import section -->
                                        <div class="form-group">
                                     <div class="card-header">
                                            <!--@can('admin_users_export_excel')-->
                                            <!--    <a href="{{ getAdminPanelUrl() }}/students/excel?{{ http_build_query(request()->all()) }}" class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>-->
                                            <!--@endcan-->
                                     <button type="button" data-toggle="modal" data-target="#import" class=" course-content-btns btn btn-sm btn-primary not-login-toast">
                                                            Import Excel
                                                        </button>
                                    <div class="h-10"></div>
                                        
                                         </div>
                                         </div>
                                        <input type="number" value="1" hidden name="option">

                                        <div class="form-group">
                                            <label class="input-label">{{trans('admin/main.class')}}</label>
                                            <select name="webinar_id" class="form-control search-webinar-select2"
                                                    data-placeholder="Search classes">

                                            </select>

                                            @error('webinar_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="input-label d-block">{{ trans('admin/main.user') }}</label>
                                            <select name="user_id" class="form-control search-user-select2" data-placeholder="{{ trans('public.search_user') }}">

                                            </select>
                                            @error('user_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class=" mt-4">
                                            <button type="submit" class="btn btn-primary">{{ trans('admin/main.add') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="import" tabindex="-1" aria-labelledby="import" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content py-20">
            <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25"></i>
                </button>
            </div>

            <div class="mt-25 position-relative">
                

                

                <div class="modal-video-lists mt-15">
                                   <section class="card">
            <div class="card-body">
                 <h1 class="font-20 font-weight-bold">Import Course Excel</h1>

                    <form action="{{ getAdminPanelUrl() }}/enrollments/import" method="POST" name="importform"
	  enctype="multipart/form-data">
		@csrf
		<div class="form-group">
			<label for="file">File:</label>
			<input id="file" type="file" name="file" class="form-control">
		</div>
	 
		<button class="btn btn-success">Import File</button>
	</form>
            </div>
        </section>
                                        
                          
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

