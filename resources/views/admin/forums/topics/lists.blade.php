@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item "><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a></div>
                <div class="breadcrumb-item "><a href="{{ getAdminPanelUrl() }}/forums">{{trans('update.forums')}}</a></div>
                <div class="breadcrumb-item active">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th class="text-left">{{ trans('admin/main.title') }}</th>
                                        <th>{{ trans('admin/main.creator') }}</th>
                                        <th>{{ trans('site.posts') }}</th>
                                        <th>{{ trans('admin/main.status') }}</th>
                                        <th>{{ trans('admin/main.created_at') }}</th>
                                        <th>{{ trans('admin/main.updated_at') }}</th>
                                        <th>Topic Status</th>
                                        <th>Approval Status</th>
                                        <th>{{ trans('admin/main.action') }}</th>
                                    </tr>
                                    @foreach($topics as $topic)

                                        <tr>
                                            <td class="text-left">
                                                <a href="{{ getAdminPanelUrl() }}/forums/{{ $topic->forum_id }}/topics/{{ $topic->id }}/posts">
                                                    {{ $topic->title }}
                                                </a>
                                            </td>
                                            <td>{{ $topic->creator->full_name }}</td>
                                            <td>{{ $topic->posts_count }}</td>
                                            <td>
                                                @if($topic->close)
                                                    <span class="text-danger">{{ trans('admin/main.close') }}</span>
                                                @else
                                                    <span class="text-success">{{ trans('admin/main.open') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ dateTimeFormat($topic->created_at,'j M Y | H:i') }}</td>
                                            <td class="text-center">{{ dateTimeFormat($topic->updated_at,'j M Y | H:i') }}</td>
                                            <td class="text-center"><input data-id="{{$topic->id}}" class="toggle-class" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" {{ $topic->status ? 'checked' : '' }}></td>
                                           <td>
                                            @if($topic->status)
                                            <span class="text-success">Approval</span>
                                            @else
                                            <span class="text-danger">UnApproval</span>
                                        @endif
                                            </td>
                                           <td>
                                                @can('admin_forum_topics_lists')
                                                    @if(!$topic->close)
                                                        @include('admin.includes.delete_button',[
                                                            'url' => "/admin/forums/{$topic->forum_id}/topics/{$topic->id}/close",
                                                            'tooltip' => trans('public.close'),
                                                            'btnClass' => 'mr-1',
                                                            'btnIcon' => 'fa-lock'
                                                        ])
                                                    @else
                                                        @include('admin.includes.delete_button',[
                                                            'url' => "/admin/forums/{$topic->forum_id}/topics/{$topic->id}/open",
                                                            'tooltip' => trans('public.open'),
                                                            'btnClass' => 'mr-1',
                                                            'btnIcon' => 'fa-unlock'
                                                        ])
                                                    @endif
                                                @endcan

                                                @can('admin_forum_topics_posts')
                                                    <a href="{{ getAdminPanelUrl() }}/forums/{{ $topic->forum_id }}/topics/{{ $topic->id }}/posts"
                                                       class="btn-transparent btn-sm text-primary mr-1"
                                                       data-toggle="tooltip" data-placement="top" title="{{ trans('site.posts') }}"
                                                    >
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                @endcan

                                                @can('admin_forum_topics_delete')
                                                    @include('admin.includes.delete_button', [
                                                            'url' => getAdminPanelUrl().'/forums/'.$topic->forum_id.'/topics/'.$topic->id.'/delete',
                                                            'btnClass' => 'btn-sm'
                                                        ])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $topics->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" ></script>

    <script>
        $(function() {
          $('.toggle-class').change(function() {
              var status = $(this).prop('checked') == true ? 1 : 0; 
              var user_id = $(this).data('id'); 
               console.log(status);
              $.ajax({
                  type: "POST",
                  dataType: "json",
                  url: '{{ getAdminPanelUrl() }}/forums/topics/status',
                  data: {'status': status, 'topic_id': user_id},
                  success: function(data){
                    console.log(data.success)
                    location.reload();

                  }
              });
          })
        })
      </script>
@endsection
