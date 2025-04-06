<!DOCTYPE html>
<html lang="{{ cookie('locale', 'fr') }}">


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('/img/web/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
 <x-layouts.app.app-nav-welcome />


    <div x-data="{ hash: window.location.hash || '#welcome' }" class="animate-fade" x-init="window.addEventListener('hashchange', () => hash = window.location.hash || '#welcome')">

        <div class="relative w-full h-screen overflow-hidden">
            <!-- Welcome Page -->
            <div x-show="hash != '#about' && hash != '#features'"
                x-transition:enter="transform transition ease-out duration-500"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-300"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="absolute inset-0 w-full h-full bg-cover bg-center"
                style="background-image: url('/img/web/bg2.jpg');">
            </div>

            <!-- About Page -->
            <div x-show="hash === '#about'" x-transition:enter="transform transition ease-out duration-500"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-300"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="absolute inset-0 w-full h-full bg-gray-200 flex items-center justify-center text-4xl">
                About Page Content
            </div>

            <!-- Features Page -->
            <div x-show="hash === '#features'" x-transition:enter="transform transition ease-out duration-500"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-300"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="absolute inset-0 w-full h-full bg-blue-200 flex items-center justify-center text-4xl">
                Features Page Content
            </div>
        </div>

    </div>
</body>
</html>
