<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $settings['site-name'] }} Admin | @yield('title')</title>

    <link href="{{ asset("vendor/laramanager/css/styles.css") }}" rel="stylesheet" media="screen">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Raleway:300,500' rel='stylesheet' type='text/css'>

    <script type="text/javascript" charset="utf-8">
        var SITE_URL = "{{ url() }}";
        var csrf = "{{ csrf_token() }}";
    </script>

    @yield('head')
</head>
<body>

    @include('laramanager::navigations.top.index')

    <div class="uk-grid uk-grid-collapse">
        <div class="uk-width-1-6 uk-contrast uk-height-viewport uk-hidden-small" id="sidebar">
            @include('laramanager::navigations.primary.index')
        </div>
        <div class="uk-width-1-1 uk-width-medium-5-6" id="primary-content-area">

            <div class="title-bar uk-container">
                <div class="uk-grid uk-grid-collapse uk-flex-middle">
                    <div class="uk-width-1-2">
                        <span class="title">@yield('title')</span>
                    </div>
                    <div class="uk-width-1-2">
                        @yield('actions')
                    </div>
                </div>
            </div>

            <div class="uk-container" id="secondary-content-area">
                @include('laramanager::partials.alerts.default')

                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/laramanager/js/scripts.js') }}"></script>

    @yield('scripts')

</body>
</html>