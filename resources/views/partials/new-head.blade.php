<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name') }}</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('img/web/icon.svg') }}">

@livewireScripts
@livewireStyles
@vite(['resources/css/app.css', 'resources/js/echo.js'])

