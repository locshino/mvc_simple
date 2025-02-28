<!-- Views/layouts/error.blade.php -->
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
</head>

<body>
    @yield('content')
    @include('_flash')
    <script src="/js/bootstrap.min.js"></script>
    @stack('scripts')
</body>

</html>
