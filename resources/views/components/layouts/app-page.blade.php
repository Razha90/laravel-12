<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.new-head')
</head>

<body class="bg-accent_grey">
    {{ $slot }}
    @fluxScripts
</body>

</html>
