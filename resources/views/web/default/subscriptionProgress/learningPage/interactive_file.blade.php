<html>
<head>
    <title>{{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

    <!-- General CSS File -->
    <link href="{{ config('app.js_css_url') }}/assets/default/css/font.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/app.css">
</head>
<body class="play-iframe-page">
@if(!empty($iframe))
    {!! $iframe !!}
@else
    <iframe src="{{ $path }}" frameborder="0"  class="interactive-file-iframe" allowfullscreen></iframe>
@endif
</body>
</html>
