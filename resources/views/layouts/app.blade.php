<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Multivendor Marketplace')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .navbar-nav .nav-link {
            margin-right: 1rem;
        }
        .footer {
            text-align: center;
            padding: 1rem;
            background-color: #f8f9fa;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
        main {
            padding-bottom: 3rem; /* Space for the fixed footer */
        }
    </style>
</head>
<body>
    @include('layouts.header')

    <main class="container mt-5">
        @yield('content')
    </main>

    @include('layouts.footer')
</body>
</html>
