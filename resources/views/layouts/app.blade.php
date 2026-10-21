<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1F2522">
    <title>@yield('title', 'Clientes') · EIYA Estrategia</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/symbol-1.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>
<body class="@yield('body-class')">
    <a class="skip-link" href="#main">Saltar al contenido</a>
    @yield('content')
</body>
</html>
