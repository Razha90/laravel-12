<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name') }}</title>
<link rel="icon" type="image/png" href="{{ asset('/img/web/logo.png') }}">
@livewireScripts
@livewireStyles
@vite(['resources/css/app.css', 'resources/js/echo.js'])

