<!DOCTYPE html>
<html>
<head>
    <title></title>
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/hart/architect/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/hart/architect/css/bootstrap-theme.min.css') }}">
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
    <script src="{{ asset('packages/hart/architect/js/jquery.min.js') }}"></script>
    <script src="{{ asset('packages/hart/architect/js/bootstrap.min.js') }}"></script>
</body>
</html>