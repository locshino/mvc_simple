<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/img/icons/app-32.png">
    <title>@yield('title')</title>

    @stack('meta')
    <link href="/css/bootstrap-5.3.3/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
</head>

<body>
    @yield('content')

    <script src="/js/bootstrap-5.3.3/bootstrap.min.js"></script>
    @stack('scripts')
</body>

</html>
