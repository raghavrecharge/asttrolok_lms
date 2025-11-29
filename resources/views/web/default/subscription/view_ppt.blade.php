@extends('web.default.layouts.app')

@section('content')
<div class="container py-30">
    <h3>{{ $file->title }}</h3>
    <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($filePath) }}"
            width="100%" height="600px" frameborder="0">
    </iframe>
</div>
@endsection
