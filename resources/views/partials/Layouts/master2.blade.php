<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', ' | Pump 360 Admin Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Pump 360 - Fuel Station Management System" name="description" />
    <meta content="Pump 360" name="author" />

    <!-- layout setup -->
    <script type="module" src="{{ asset('assets/js/layout-setup.js') }}"></script>

    <!-- App favicon -->
    <link rel="shortcut icon" href="https://png.pngtree.com/png-vector/20230410/ourmid/pngtree-petroal-pump-vector-png-image_6689654.png">

    @yield('css')
    @include('partials.head-css')
</head>

<body>

    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-wrapper">
        <div class="container-fluid">
            @include('partials.page-title')

            @yield('content')

            @include('partials.switcher')
            @include('partials.scroll-to-top')
            @include('partials.footer')

            @include('partials.vendor-scripts')

            @yield('js')

</body>

</html>