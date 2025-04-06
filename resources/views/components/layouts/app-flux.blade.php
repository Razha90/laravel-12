<!DOCTYPE html>
<html lang="{{ Cookie::get('locale', 'fr') }}">

<head>
    @include('partials.new-head')
    @fluxAppearance
</head>
<style>
    *::-webkit-scrollbar {
        height: 12px;
        width: 12px;
    }

    *::-webkit-scrollbar-track {
        border-radius: 9px;
        background-color: #DFE9EB;
        border: 3px solid #FFFFFF;
    }

    *::-webkit-scrollbar-track:hover {
        background-color: #C2C2C2;
    }

    *::-webkit-scrollbar-track:active {
        background-color: #C2C2C2;
    }

    *::-webkit-scrollbar-thumb {
        border-radius: 11px;
        background-color: #4F78FF;
    }

    *::-webkit-scrollbar-thumb:hover {
        background-color: #6647FF;
    }

    *::-webkit-scrollbar-thumb:active {
        background-color: #6647FF;
    }
</style>

<body class="bg-accent_grey">

    @if (empty(auth()->user()->email_verified_at))
        <x-layouts.app.app-nav-welcome />
    @else
        <livewire:component.nav-auth />
    @endif

    {{ $slot }}

    @persist('data-notifikasi')
        <livewire:component.data-notifikasi />
    @endpersist
</body>
@fluxScripts

</html>
