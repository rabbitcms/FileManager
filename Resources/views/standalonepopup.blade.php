<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ trans('filemanager::common.file_manager') }}</title>

    <!-- jQuery and jQuery UI (REQUIRED) -->
    <link rel="stylesheet" type="text/css" href="{{ asset_module('backend/plugins/jquery-ui/jquery-ui.min.css', 'backend') }}">
    <script src="{{ asset_module('backend/plugins/jquery.min.js', 'backend') }}"></script>
    <script src="{{ asset_module('backend/plugins/jquery-ui/jquery-ui.min.js', 'backend') }}"></script>

    <!-- elFinder CSS (REQUIRED) -->
    <link rel="stylesheet" type="text/css" href="{{ asset_module('plugins/elfinder/css/elfinder.css', 'filemanager') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset_module('plugins/elfinder/css/theme.css', 'filemanager') }}">

    <!-- elFinder JS (REQUIRED) -->
    <script src="{{ asset_module('plugins/elfinder/js/elfinder.js', 'filemanager') }}"></script>

    @if($locale)
        <script src="{{ asset_module('plugins/elfinder/js/i18n/elfinder.' . $locale . '.js', 'filemanager') }}"></script>
    @endif

    <script type="text/javascript">
        $().ready(function () {
            var elf = $('#elfinder').elfinder({
                @if($locale)
                lang: '{{ $locale }}',
                @endif
                customData: {
                    _token: '{{ csrf_token() }}'
                },
                url: '{{ route('backend.filemanager.elfinder.connector') }}',
                dialog: {
                    width: 900,
                    modal: true,
                    title: 'Select a file'
                },
                resizable: false,
                commandsOptions: {
                    getfile: {
                        oncomplete: 'destroy'
                    }
                },
                height: $(window).height() - 25,
                getFileCallback: function (file) {
                    window.parent.processSelectedFile(file.path, file);
                    parent.jQuery.colorbox.close();
                }
            }).elfinder('instance');
        });
    </script>
</head>
<body>

<!-- Element where elFinder will be created (REQUIRED) -->
<div id="elfinder"></div>

</body>
</html>
