<!DOCTYPE html>
<html lang="{{ Cookie::get('locale', 'fr') }}">

<head>
    @include('partials.new-head')
</head>

<body class="bg-accent_grey">
    <livewire:component.nav-auth />
    {{ $slot }}
    @persist('data-notifikasi')
        <livewire:component.data-notifikasi />
    @endpersist
</body>

</html>
