<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ config('app.name') }}</title>
<meta name="csrf-token" content="{{ csrf_token() }}">


<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
<link rel="icon" type="image/png" href="{{ asset('/img/web/logo.png') }}">

@livewireScripts
@livewireStyles
@vite(['resources/css/app.css', 'resources/js/echo.js'])
@fluxAppearance
