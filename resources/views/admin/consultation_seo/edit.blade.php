@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Consultation SEO</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-body">
                <form action="{{ getAdminPanelUrl() }}/consultation-seo/{{ $seo->id }}/update" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>User</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">-- Select User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ ($seo->user_id == $user->id) ? 'selected' : '' }}>
                                    {{ $user->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" value="{{ $seo->title }}" maxlength="500">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control">{{ $seo->description }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>H1</label>
                        <input type="text" name="h1" class="form-control" value="{{ $seo->h1 }}" maxlength="1200">
                    </div>

                    <div class="form-group">
                        <label>Keyword</label>
                        <input type="text" name="keyword" class="form-control" value="{{ $seo->keyword }}" maxlength="500">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active" {{ $seo->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $seo->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Update SEO</button>
                    <a href="{{ getAdminPanelUrl() }}/consultation-seo" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
