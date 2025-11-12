@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Add Consultation SEO</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-body">
                <form action="{{ getAdminPanelUrl() }}/consultation-seo/store" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>User</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">-- Select User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ ($selectedUserId == $user->id) ? 'selected' : '' }}>
                                    {{ $user->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" maxlength="500">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>H1</label>
                        <input type="text" name="h1" class="form-control" maxlength="1200">
                    </div>

                    <div class="form-group">
                        <label>Keyword</label>
                        <input type="text" name="keyword" class="form-control" maxlength="500">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Save SEO</button>
                    <a href="{{ getAdminPanelUrl() }}/consultation-seo" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
