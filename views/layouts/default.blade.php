<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('laramanager.site_title') }} | @yield('title')</title>

    <link href="{{ asset("vendor/laramanager/styles.css") }}" rel="stylesheet" media="screen">

    @yield('head')
</head>
<body>

@include('laramanager::navigations.primary.index')

@yield('content')

</body>
</html>