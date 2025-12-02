<!DOCTYPE html>
<html lang="{{ Cookie::get('locale', 'fr') }}">

<head>
    @include('partials.head')
</head>

<body class="dark:bg-linear-to-b min-h-screen bg-white antialiased dark:from-neutral-950 dark:to-neutral-900">
    <x-layouts.app.app-nav-welcome />
    <div x-data="{ path: window.location.pathname }" class="bg-background flex h-svh min-h-[700px] flex-col items-center justify-center gap-6"
        x-bind:class="path == '/register' ? 'pt-[260px] nav-1:pt-[300px] md:pt-[150px]' : 'p-6 md:p-10'">
        <div class="flex w-full max-w-sm flex-col gap-2">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                <span class="mb-1 flex h-12 w-12 items-center justify-center rounded-md">
                    <x-app-logo-icon class="size-9 fill-current text-black" />
                </span>
                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>
            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>
    <div x-show="path == '/register'" class="h-[100px]"></div>
    @fluxScripts
</body>

</html>
