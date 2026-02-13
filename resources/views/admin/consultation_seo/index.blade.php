@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Consultation SEO</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
            <div class="breadcrumb-item">Consultation SEO</div>
        </div>
    </div>

    <div class="section-body">

        <!-- 🔎 Filter -->
        <section class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ getAdminPanelUrl() }}/consultation-seo" class="mb-0">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="input-label">Filter by User</label>
                            <select name="user_id" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Select User --</option>
                                @foreach($users as $userOption)
                                    <option value="{{ $userOption->id }}" {{ request('user_id') == $userOption->id ? 'selected' : '' }}>
                                        {{ $userOption->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ getAdminPanelUrl() }}/consultation-seo" class="btn btn-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- 📊 SEO Data Table -->
        <div class="card">
            <div class="card-header">
                <h4>SEO Records</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive text-center">
                    <table class="table table-striped font-14">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Fullname</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>H1</th>
                                <th>Keyword</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="160">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                @forelse($user->consultationSeos as $seo)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->full_name }}</td>
                                        <td>{{ Str::limit($seo->title, 40) }}</td>
                                        <td>{{ Str::limit($seo->description, 60) }}</td>
                                        <td>{{ Str::limit($seo->h1, 40) }}</td>
                                        <td>{{ Str::limit($seo->keyword, 40) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $seo->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($seo->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $seo->created_at->format('d M Y') }}</td>
                                        <td class="text-center">
                                            <a href="{{ getAdminPanelUrl() }}/consultation-seo/{{ $seo->id }}/edit"
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <!--<form action="{{ getAdminPanelUrl() }}/consultation-seo/{{ $seo->id }}/delete"-->
                                            <!--      method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure to delete this SEO record?');">-->
                                            <!--    @csrf-->
                                            <!--    @method('DELETE')-->
                                            <!--    <button type="submit" class="btn btn-sm btn-danger">-->
                                            <!--        <i class="fa fa-trash"></i>-->
                                            <!--    </button>-->
                                            <!--</form>-->
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->full_name }}</td>
                                        <td colspan="6" class="text-center">No SEO record</td>
                                        <td class="text-center">
                                            <a href="{{ getAdminPanelUrl() }}/consultation-seo/create?user_id={{ $user->id }}"
                                               class="btn btn-sm btn-success" title="Add SEO">
                                                <i class="fa fa-plus"></i> Add SEO
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Pagination -->
            <div class="card-footer text-center">
                {{ $users->appends(request()->input())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</section>
@endsection
