<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="DONA ÓRGANOS PANAMÁ: información y registro de voluntad para la donación de órganos y tejidos.">
    <title>@yield('title', 'DONA ÓRGANOS PANAMÁ')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body_class')">
    @yield('content')
    @stack('scripts')
</body>
</html>
