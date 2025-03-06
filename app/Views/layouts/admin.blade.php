<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="/css/bootstrap-5.3.3/bootstrap.min.css" rel="stylesheet">
    <title>@yield('title', 'Admin Dashboard')</title>

    <style>
        /* Định dạng sidebar */
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: #fff;
        }

        .sidebar a {
            color: #ddd;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #495057;
            color: #fff;
        }

        .active-link {
            background-color: #495057;
            font-weight: bold;
        }

        /* Định dạng phần login và header */
        .header-right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .header-right .dropdown-menu {
            min-width: 200px;
        }

        .header-right .icon {
            margin-left: 20px;
            cursor: pointer;
        }

        .icon:hover {
            color: #007bff;
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            @include('components.sidebar', [
                'title' => 'Admin Panel',
                'links' => [
                    'Dashboard' => '/admin',
                    'Users' => '/admin/users',
                    'Products' => '/admin/products',
                    'Categories' => '/admin/categories',
                    'Orders' => '/admin/orders',
                ],
            ])

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Header -->
                <header class="d-flex justify-content-between align-items-center py-3 mb-3 border-bottom">
                    <h1 class="h2">Admin Panel</h1>
                </header>

                <!-- Nội dung chính -->
                <div class="content">
                    @yield('content')
                </div>

                <footer class="text-center py-3">
                    <span>FPT POLYTECHNIC</span>
                </footer>
            </main>
        </div>
    </div>

    <script src="/js/bootstrap-5.3.3/bootstrap.min.js"></script>
    @stack('scripts')
</body>

</html>
