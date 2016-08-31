<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>elFinder 2.0</title>

    <!-- jQuery and jQuery UI (REQUIRED) -->
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css"/>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>

    <!-- elFinder CSS (REQUIRED) -->
    <link rel="stylesheet" type="text/css" href="{{ asset_module('plugins/elfinder/css/elfinder.css', 'filemanager') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset_module('plugins/elfinder/css/theme.css', 'filemanager') }}">

    <!-- elFinder JS (REQUIRED) -->
    <script src="{{ asset_module('plugins/elfinder/js/elfinder.js', 'filemanager') }}"></script>

    @if($locale)
        <script src="{{ asset_module('plugins/elfinder/js/i18n/elfinder.' . $locale . '.js', 'filemanager') }}"></script>
    @endif

    <!-- elFinder initialization (REQUIRED) -->
    <script type="text/javascript" charset="utf-8">
        $().ready(function () {
            $('#elfinder').elfinder({
                @if($locale)
                lang: '{{ $locale }}',
                @endif
                customData: {
                    _token: '{{ csrf_token() }}'
                },
                resizable: false,
                url: '{{ route('backend.filemanager.elfinder.connector') }}'
            });
        });
    </script>
</head>
<body>
{{-- //soundPath: json_encode(URL::assetFrom($dir, 'sounds')), --}}

<!-- Element where elFinder will be created (REQUIRED) -->
<div id="elfinder"></div>

</body>
</html>
