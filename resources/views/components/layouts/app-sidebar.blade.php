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

<body class="bg-accent_grey h-screen min-h-[400px]">
    <flux:sidebar class="bg-accent_blue" sticky stashable>
        <flux:sidebar.toggle class="text-right! text-white! lg:hidden" icon="x-mark" />
        <div>
            <a href="/" class="flex flex-row items-center gap-x-2">
                <img src="{{ url('/img/web/logo.png') }}" width="30" height="30" />
                <p class="text-base">{{ config('app.name') }}</p>
            </a>
        </div>

        <flux:navlist variant="outline" x-data="{ path: window.location.pathname, admin: new URL('{{ route('admin') }}').pathname, aplication: new URL('{{ route(name: 'admin.aplication') }}').pathname, default_avatar: new URL('{{ route('admin.avatar') }}').pathname }">
            <template x-if="path == admin">
                <div>
                    <flux:navlist.item icon="user" current class="text-secondary_blue!">{{ __('admin.users') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="document-text" href="{{ route('admin.aplication') }}"
                        class="text-white! transition-opacity hover:opacity-50">{{ __('admin.aplication_letter') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="user-circle" href="{{ route('admin.avatar') }}"
                        class="text-white! transition-opacity hover:opacity-50">{{ __('admin.default_avatar') }}
                    </flux:navlist.item>
                </div>
            </template>
            <template x-if="path == aplication">
                <div>
                    <flux:navlist.item icon="user" href="/admin"
                        class="text-white! transition-opacity hover:opacity-50">{{ __('admin.users') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="document-text" href="/admin/aplication" current
                        class="text-secondary_blue!">{{ __('admin.aplication_letter') }}</flux:navlist.item>
                    <flux:navlist.item icon="user-circle" href="{{ route('admin.avatar') }}"
                        class="text-white! transition-opacity hover:opacity-50">{{ __('admin.default_avatar') }}
                    </flux:navlist.item>
                </div>
            </template>
            <template x-if="path == default_avatar">
                <div>
                    <flux:navlist.item icon="user" href="/admin" class="text-white! transition-opacity hover:opacity-50">
                        {{ __('admin.users') }}</flux:navlist.item>
                    <flux:navlist.item icon="document-text" href="/admin/aplication"
                        class="text-white! transition-opacity hover:opacity-50">{{ __('admin.aplication_letter') }}
                    </flux:navlist.item>
                    <flux:navlist.item current icon="user-circle" href="{{ route(name: 'admin.avatar') }}"
                        class=" text-secondary_blue!">{{ __('admin.default_avatar') }}
                    </flux:navlist.item>
                </div>
            </template>
        </flux:navlist>

        <flux:spacer />

        <div x-data="init" class="z-10">
            <div class="relative">
                <div x-show="showBar" @click.away="showBar = false" class="top absolute -bottom-2 left-0 w-full"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-y-1 scale-95">
                    <ul class="bg-primary_white text-secondary_blue min-w-[150px] rounded-xl p-2 shadow-xl">
                        <li class="w-full">
                            <a href="{{ route('settings.profile') }}"
                                class="hover:bg-accent_grey inline-flex w-full rounded-lg px-2 py-1 transition-colors hover:cursor-pointer">
                                {{ __('welcome.profile') }}
                            </a>
                        </li>
                        <li class="w-full">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="hover:bg-accent_grey inline-flex w-full rounded-lg px-2 py-1 transition-colors hover:cursor-pointer">
                                    {{ __('welcome.logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="flex w-full flex-row items-center justify-around rounded-md bg-white p-2">
                <div
                    class="border-secondary_blue h-[40px] w-[40px] cursor-pointer overflow-hidden rounded-full border p-[2px] transition-opacity duration-300 hover:opacity-50">
                    <img src="{{ asset(auth()->user()->profile_photo_path) }}" alt="Profile Photo" loading="lazy">
                </div>
                <p class="text-secondary_blue w-[110px] truncate overflow-ellipsis whitespace-nowrap text-sm font-bold">
                    {{ auth()->user()->name }}</p>
                <div @click="showBar = true"
                    class="hover:bg-secondary_black/20 cursor-pointer rounded-full p-2 transition-colors">
                    <svg class="w-[22px]" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" fill="#2867A4">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <title>arrow_down [#272]</title>
                            <desc>Created with Sketch.</desc>
                            <defs> </defs>
                            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g id="Dribbble-Light-Preview" transform="translate(-60.000000, -6959.000000)"
                                    fill="#2867A4">
                                    <g id="icons" transform="translate(56.000000, 160.000000)">
                                        <path
                                            d="M22,6802 C22,6801.448 21.552,6801 21,6801 L7,6801 C6.448,6801 6,6801.448 6,6802 L6,6804 C6,6804.552 6.448,6805 7,6805 L21,6805 C21.552,6805 22,6804.552 22,6804 L22,6802 Z M24,6801 L24,6805 C24,6806.105 23.104,6807 22,6807 L6,6807 C4.895,6807 4,6806.105 4,6805 L4,6801 C4,6799.895 4.895,6799 6,6799 L22,6799 C23.104,6799 24,6799.895 24,6801 L24,6801 Z M18.949,6813.464 C19.34,6813.854 19.34,6814.488 18.95,6814.879 C16.099,6817.729 15.979,6817.849 15.413,6818.415 C14.633,6819.195 13.367,6819.195 12.587,6818.415 L9.05,6814.878 C8.659,6814.488 8.659,6813.855 9.05,6813.464 C9.44,6813.074 10.073,6813.074 10.464,6813.464 L12.146,6815.147 C12.461,6815.462 13,6815.239 13,6814.793 L13,6809.657 C13,6809.104 13.448,6808.657 14,6808.657 C14.552,6808.657 15,6809.104 15,6809.657 L15,6814.791 C15,6815.237 15.539,6815.46 15.854,6815.145 L17.535,6813.464 C17.926,6813.073 18.559,6813.073 18.949,6813.464 L18.949,6813.464 Z">
                                        </path>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </svg>

                </div>
            </div>
        </div>
    </flux:sidebar>
    {{ $slot }}

    <script>
        function init() {
            return {
                showBar: false,
            }
        }
    </script>

    @persist('data-notifikasi')
        <livewire:component.data-notifikasi />
    @endpersist
</body>
@fluxScripts

</html>
