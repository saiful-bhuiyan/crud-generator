<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ getGeneralSetting('site_title') ?? 'Admin Dashboard' }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">

    <link rel="stylesheet" href="{{ static_asset('assets/css/bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ static_asset('assets/css/animate.css') }}">

    <link rel="stylesheet" href="{{ static_asset('assets/css/dataTables.bootstrap4.min.css') }}">

    <link rel="stylesheet" href="{{ static_asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/plugins/fontawesome/css/all.min.css') }}">

    <link rel="stylesheet" href="{{ static_asset('assets/css/style.css') }}">

    <link rel="stylesheet" href="{{ static_asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/plugins/daterangepicker/daterangepicker.css') }}"></link>
    <link rel="stylesheet" href="{{ static_asset('assets/plugins/summernote/summernote-bs4.min.css') }}"></link>
    <link rel="stylesheet" href="{{ static_asset('assets/plugins/daterangepicker/daterangepicker.css') }}"></link>


    @yield('style')
</head>

<body>
    <!-- <div id="global-loader">
        <div class="whirly-loader"> </div>
    </div> -->

    <div class="main-wrapper">

        @include('admin.layouts.topbar')

        @include('admin.layouts.sidebar')

        <div class="page-wrapper">
        @yield('body')
        </div>


        <script src="{{ static_asset('assets/js/jquery-3.6.0.min.js') }}"></script>

        <script src="{{ static_asset('assets/js/feather.min.js') }}"></script>

        <script src="{{ static_asset('assets/js/jquery.slimscroll.min.js') }}"></script>

        <script src="{{ static_asset('assets/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ static_asset('assets/js/dataTables.bootstrap4.min.js') }}"></script>

        <script src="{{ static_asset('assets/js/bootstrap.bundle.min.js') }}"></script>

        <script src="{{ static_asset('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
        <script src="{{ static_asset('assets/plugins/apexchart/chart-data.js') }}"></script>

        <script src="{{ static_asset('assets/js/script.js') }}"></script>

        <script src="{{ static_asset('assets/plugins/select2/js/select2.min.js') }}"></script>

        <script src="{{ static_asset('assets/plugins/daterangepicker/moment.min.js') }}"></script>
        <script src="{{ static_asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
        <script src="{{ static_asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
        <script type="text/javascript" src="{{ static_asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>


        <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        </script>

        <script>
            $(function() {
                $('.daterange').daterangepicker({
                    opens: 'left',
                    autoUpdateInput: false,  // prevent auto-filling
                    locale: {
                        cancelLabel: 'Clear' // optional: label for cancel button
                    }
                });

                // When user selects a date range
                $('.daterange').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(
                        picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD')
                    );
                });

                // When user cancels / clears
                $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val(''); // clear the input
                });
            });


            $(document).ready(function () {
                    // Initialize Summernote on all elements with class 'html-editor'
                    $('.html-editor').summernote({
                        height: 250,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link', 'picture', 'video']],
                            ['view', ['codeview', 'help']]
                        ]
                    });
                });
        </script>

        @yield('script')
</body>

</html>