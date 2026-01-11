<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>@yield('title', 'Document')</title>
    <link rel="stylesheet" href="{{ asset('pos/assets/css/invoice.css') }}" />
</head>

<body>
    <div class="inv-screen">
        @yield('content')
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autoprint') === '1') {
            setTimeout(() => window.print(), 300);
        }
    </script>
</body>

</html>