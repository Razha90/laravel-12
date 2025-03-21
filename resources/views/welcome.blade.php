<!DOCTYPE html>
<html lang="{{ Session::get('locale', 'fr') }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('/img/web/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav>
        <div class="fixed top-8 w-full flex justify-center z-30 select-none">
            <div
                class="bg-primary_white min-w-[600px] h-[80px] w-[80%] shadow-xl rounded-full animate-fade-down flex justify-between items-center p-4 max-960:min-w-[90%] max-w-[900px]">
                <div>
                    <a href="{{ route('home') }}" class="hover:cursor-pointer hover:opacity-50 transition-opacity">
                        <img src="{{ url('/img/web/logo.png') }}" width="100" height="100" />
                    </a>
                </div>
                <div>
                    <ul x-data="{ hash: window.location.hash }" x-init="if (!hash || (hash !== '#welcome' && hash !== '#about' && hash !== '#features')) {
                        hash = '#welcome';
                        window.location.hash = '#welcome';
                    }
                    window.addEventListener('hashchange', () => hash = window.location.hash);"
                        class="flex flex-row text-xl text-accent_blue gap-x-6">
                        <li>
                            <a href="#welcome" @click="hash = '#welcome'"
                                x-bind:class="hash != '#about' && hash != '#features' ? 'font-bold text-secondary_blue' : 'opacity-70'">
                                {{ __('welcome.home') }}
                            </a>
                        </li>
                        <li>
                            <a href="#about" @click="hash = '#about'"
                                x-bind:class="hash === '#about' ? 'font-bold text-secondary_blue' : 'opacity-70'">
                                {{ __('welcome.about') }}
                            </a>
                        </li>
                        <li>
                            <a href="#features" @click="hash = '#features'"
                                x-bind:class="hash === '#features' ? 'font-bold text-secondary_blue' : 'opacity-70'">
                                {{ __('welcome.features') }}
                            </a>
                        </li>
                        @auth
                            <li class="scale-75 ml-[-20px]">
                                <a href="{{ route('my-app') }}" x-data="{ clicked: false }"
                                    :class="{ 'pointer-events-none opacity-50': clicked }" @click="clicked = true"
                                    class="text-secondary_blue px-4 py-2 bg-primary_white border border-secondary_blue rounded-full hover:bg-secondary_blue transition-colors hover:border-primary_white hover:text-primary_white">
                                    {{ __('welcome.dashboard') }}
                                </a>
                            </li>
                        @endauth
                    </ul>

                </div>
                <div class="flex flex-row gap-x-6">
                    <div x-data="{ open: false }" class="relative flex items-center">
                        <div @click="open = !open"
                            class="flex flex-row items-center gap-x-2 py-1 px-5 shadow-xl rounded-full border border-secondary_blue cursor-pointer">
                            <div
                                class="w-[30px] h-[30px] rounded-full overflow-hidden children:rounded-full border border-secondary_blue">
                                <svg x-show=" `{{ session()->get('locale', 'fr') }}` == `en`" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    viewBox="0 0 130 120" enable-background="new 0 0 130 120" xml:space="preserve"
                                    fill="#000000">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <g id="Infos">
                                            <rect id="BG" x="-200" y="-1350" fill="#D8D8D8" width="2180"
                                                height="1700"></rect>
                                        </g>
                                        <g id="Others">
                                            <g>
                                                <rect y="0" fill="#DC4437" width="130" height="13.3"></rect>
                                                <rect y="26.7" fill="#DC4437" width="130" height="13.3"></rect>
                                                <rect y="80" fill="#DC4437" width="130" height="13.3"></rect>
                                                <rect y="106.7" fill="#DC4437" width="130" height="13.3"></rect>
                                                <rect y="53.3" fill="#DC4437" width="130" height="13.3"></rect>
                                                <rect y="13.3" fill="#FFFFFF" width="130" height="13.3"></rect>
                                                <rect y="40" fill="#FFFFFF" width="130" height="13.3"></rect>
                                                <rect y="93.3" fill="#FFFFFF" width="130" height="13.3"></rect>
                                                <rect y="66.7" fill="#FFFFFF" width="130" height="13.3"></rect>
                                                <rect y="0" fill="#2A66B7" width="70" height="66.7"></rect>
                                                <polygon fill="#FFFFFF"
                                                    points="13.5,4 15.8,8.9 21,9.7 17.2,13.6 18.1,19 13.5,16.4 8.9,19 9.8,13.6 6,9.7 11.2,8.9 ">
                                                </polygon>
                                                <polygon fill="#FFFFFF"
                                                    points="34,4 36.3,8.9 41.5,9.7 37.8,13.6 38.6,19 34,16.4 29.4,19 30.2,13.6 26.5,9.7 31.7,8.9 ">
                                                </polygon>
                                                <polygon fill="#FFFFFF"
                                                    points="54.5,4 56.8,8.9 62,9.7 58.2,13.6 59.1,19 54.5,16.4 49.9,19 50.8,13.6 47,9.7 52.2,8.9 ">
                                                </polygon>
                                                <polygon fill="#FFFFFF"
                                                    points="24,24 26.3,28.9 31.5,29.7 27.8,33.6 28.6,39 24,36.4 19.4,39 20.2,33.6 16.5,29.7 21.7,28.9 ">
                                                </polygon>
                                                <polygon fill="#FFFFFF"
                                                    points="44.5,24 46.8,28.9 52,29.7 48.2,33.6 49.1,39 44.5,36.4 39.9,39 40.8,33.6 37,29.7 42.2,28.9 ">
                                                </polygon>
                                                <polygon fill="#FFFFFF"
                                                    points="13.5,45.2 15.8,50.1 21,50.9 17.2,54.7 18.1,60.2 13.5,57.6 8.9,60.2 9.8,54.7 6,50.9 11.2,50.1 ">
                                                </polygon>
                                                <polygon fill="#FFFFFF"
                                                    points="34,45.2 36.3,50.1 41.5,50.9 37.8,54.7 38.6,60.2 34,57.6 29.4,60.2 30.2,54.7 26.5,50.9 31.7,50.1 ">
                                                </polygon>
                                                <polygon fill="#FFFFFF"
                                                    points="54.5,45.2 56.8,50.1 62,50.9 58.2,54.7 59.1,60.2 54.5,57.6 49.9,60.2 50.8,54.7 47,50.9 52.2,50.1 ">
                                                </polygon>
                                            </g>
                                        </g>
                                        <g id="Europe">
                                            <g id="Row_5"> </g>
                                            <g id="Row_4"> </g>
                                            <g id="Row_3"> </g>
                                            <g id="Row_2"> </g>
                                            <g id="Row_1"> </g>
                                        </g>
                                    </g>
                                </svg>
                                <svg x-show="`{{ session()->get('locale', 'fr') }}` == `fr`" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    viewBox="0 0 130 120" enable-background="new 0 0 130 120" xml:space="preserve"
                                    fill="#000000">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <g id="Infos">
                                            <rect id="BG" x="-650" y="-740" fill="#D8D8D8" width="2180"
                                                height="1700"></rect>
                                        </g>
                                        <g id="Others"> </g>
                                        <g id="Europe">
                                            <g id="Row_5"> </g>
                                            <g id="Row_4"> </g>
                                            <g id="Row_3"> </g>
                                            <g id="Row_2">
                                                <g>
                                                    <rect x="87" fill="#DB3A49" width="43" height="120"></rect>
                                                    <rect x="43" fill="#FFFFFF" width="44" height="120"></rect>
                                                    <rect fill="#2A66B7" width="43" height="120"></rect>
                                                </g>
                                            </g>
                                            <g id="Row_1"> </g>
                                        </g>
                                    </g>
                                </svg>
                                <svg x-show="`{{ session()->get('locale', 'id') }}` == `id`" viewBox="0 0 36 36"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    aria-hidden="true" role="img" class="iconify iconify--twemoji"
                                    preserveAspectRatio="xMidYMid meet" fill="#000000">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path fill="#DC1F26" d="M32 5H4a4 4 0 0 0-4 4v9h36V9a4 4 0 0 0-4-4z"></path>
                                        <path fill="#EEE" d="M36 27a4 4 0 0 1-4 4H4a4 4 0 0 1-4-4v-9h36v9z"></path>
                                    </g>
                                </svg>
                            </div>
                            <p class="font-koho text-secondary_blue text-2xl">
                                {{ session()->get('locale', 'fr') }}
                            </p>
                        </div>
                        <div x-show="open" @click.away="open = false"
                            class="absolute top-12 rounded-2xl shadow-xl bg-primary_white"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-5 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 -translate-y-5 scale-95">
                            <ul
                                class="p-5 rounded-xl shadow-xl flex gap-4 flex-col">
                                <li @click="window.location.href = `{{ route('change.lang', ['lang' => 'fr']) }}`"
                                    class="hover:bg-accent_grey flex flex-row items-center gap-2 transition-colors hover:cursor-pointer px-2 rounded-lg py-1">
                                    <div
                                        class="w-[30px] h-[30px] rounded-full overflow-hidden border border-accent_blue">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 130 120"
                                            enable-background="new 0 0 130 120" xml:space="preserve" fill="#000000">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <g id="Infos">
                                                    <rect id="BG" x="-200" y="-1350" fill="#D8D8D8"
                                                        width="2180" height="1700">
                                                    </rect>
                                                </g>
                                                <g id="Others">
                                                    <g>
                                                        <rect y="0" fill="#DC4437" width="130" height="13.3">
                                                        </rect>
                                                        <rect y="26.7" fill="#DC4437" width="130" height="13.3">
                                                        </rect>
                                                        <rect y="80" fill="#DC4437" width="130" height="13.3">
                                                        </rect>
                                                        <rect y="106.7" fill="#DC4437" width="130" height="13.3">
                                                        </rect>
                                                        <rect y="53.3" fill="#DC4437" width="130" height="13.3">
                                                        </rect>
                                                        <rect y="13.3" fill="#FFFFFF" width="130" height="13.3">
                                                        </rect>
                                                        <rect y="40" fill="#FFFFFF" width="130" height="13.3">
                                                        </rect>
                                                        <rect y="93.3" fill="#FFFFFF" width="130" height="13.3">
                                                        </rect>
                                                        <rect y="66.7" fill="#FFFFFF" width="130" height="13.3">
                                                        </rect>
                                                        <rect y="0" fill="#2A66B7" width="70" height="66.7">
                                                        </rect>
                                                        <polygon fill="#FFFFFF"
                                                            points="13.5,4 15.8,8.9 21,9.7 17.2,13.6 18.1,19 13.5,16.4 8.9,19 9.8,13.6 6,9.7 11.2,8.9 ">
                                                        </polygon>
                                                        <polygon fill="#FFFFFF"
                                                            points="34,4 36.3,8.9 41.5,9.7 37.8,13.6 38.6,19 34,16.4 29.4,19 30.2,13.6 26.5,9.7 31.7,8.9 ">
                                                        </polygon>
                                                        <polygon fill="#FFFFFF"
                                                            points="54.5,4 56.8,8.9 62,9.7 58.2,13.6 59.1,19 54.5,16.4 49.9,19 50.8,13.6 47,9.7 52.2,8.9 ">
                                                        </polygon>
                                                        <polygon fill="#FFFFFF"
                                                            points="24,24 26.3,28.9 31.5,29.7 27.8,33.6 28.6,39 24,36.4 19.4,39 20.2,33.6 16.5,29.7 21.7,28.9 ">
                                                        </polygon>
                                                        <polygon fill="#FFFFFF"
                                                            points="44.5,24 46.8,28.9 52,29.7 48.2,33.6 49.1,39 44.5,36.4 39.9,39 40.8,33.6 37,29.7 42.2,28.9 ">
                                                        </polygon>
                                                        <polygon fill="#FFFFFF"
                                                            points="13.5,45.2 15.8,50.1 21,50.9 17.2,54.7 18.1,60.2 13.5,57.6 8.9,60.2 9.8,54.7 6,50.9 11.2,50.1 ">
                                                        </polygon>
                                                        <polygon fill="#FFFFFF"
                                                            points="34,45.2 36.3,50.1 41.5,50.9 37.8,54.7 38.6,60.2 34,57.6 29.4,60.2 30.2,54.7 26.5,50.9 31.7,50.1 ">
                                                        </polygon>
                                                        <polygon fill="#FFFFFF"
                                                            points="54.5,45.2 56.8,50.1 62,50.9 58.2,54.7 59.1,60.2 54.5,57.6 49.9,60.2 50.8,54.7 47,50.9 52.2,50.1 ">
                                                        </polygon>
                                                    </g>
                                                </g>
                                                <g id="Europe">
                                                    <g id="Row_5"> </g>
                                                    <g id="Row_4"> </g>
                                                    <g id="Row_3"> </g>
                                                    <g id="Row_2"> </g>
                                                    <g id="Row_1"> </g>
                                                </g>
                                            </g>
                                        </svg>
                                    </div>
                                    <p class="font-koho text-secondary_blue text-xl">
                                        France
                                    </p>
                                </li>
                                <li @click="window.location.href = `{{ route('change.lang', ['lang' => 'en']) }}`"
                                    class="hover:bg-accent_grey flex flex-row items-center gap-2 transition-colors hover:cursor-pointer px-2 rounded-lg py-1">
                                    <div
                                        class="w-[30px] h-[30px] rounded-full overflow-hidden border border-accent_blue">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 130 120"
                                            enable-background="new 0 0 130 120" xml:space="preserve" fill="#000000">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <g id="Infos">
                                                    <rect id="BG" x="-650" y="-740" fill="#D8D8D8"
                                                        width="2180" height="1700">
                                                    </rect>
                                                </g>
                                                <g id="Others"> </g>
                                                <g id="Europe">
                                                    <g id="Row_5"> </g>
                                                    <g id="Row_4"> </g>
                                                    <g id="Row_3"> </g>
                                                    <g id="Row_2">
                                                        <g>
                                                            <rect x="87" fill="#DB3A49" width="43"
                                                                height="120">
                                                            </rect>
                                                            <rect x="43" fill="#FFFFFF" width="44"
                                                                height="120">
                                                            </rect>
                                                            <rect fill="#2A66B7" width="43" height="120">
                                                            </rect>
                                                        </g>
                                                    </g>
                                                    <g id="Row_1"> </g>
                                                </g>
                                            </g>
                                        </svg>

                                    </div>
                                    <p class="font-koho text-secondary_blue text-xl">
                                        English
                                    </p>
                                </li>
                                <li @click="window.location.href = `{{ route('change.lang', ['lang' => 'id']) }}`"
                                    class="hover:bg-accent_grey flex flex-row items-center gap-2 transition-colors hover:cursor-pointer px-2 rounded-lg py-1">
                                    <div
                                        class="w-[30px] h-[30px] rounded-full overflow-hidden border border-accent_blue">
                                        <svg viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                                            role="img" class="iconify iconify--twemoji"
                                            preserveAspectRatio="xMidYMid meet" fill="#000000">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path fill="#DC1F26" d="M32 5H4a4 4 0 0 0-4 4v9h36V9a4 4 0 0 0-4-4z">
                                                </path>
                                                <path fill="#EEE" d="M36 27a4 4 0 0 1-4 4H4a4 4 0 0 1-4-4v-9h36v9z">
                                                </path>
                                            </g>
                                        </svg>

                                    </div>
                                    <p class="font-koho text-secondary_blue text-xl">
                                        Indonesia
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @auth
                        <a href="{{ route('settings.profile') }}" x-data="{ clicked: false }"
                            @click.prevent="if (!clicked) { clicked = true; window.location.href='{{ route('settings.profile') }}' }"
                            :class="{ 'opacity-50 pointer-events-none': clicked }"
                            class="rounded-full w-[40px] h-[40px] overflow-hidden border border-secondary_blue p-[2px] transition-opacity duration-300 hover:opacity-50 cursor-pointer">
                            <img src="{{ asset('/img/profile/' . auth()->user()->profile_photo_path) }}"
                                alt="Profile Photo">
                        </a>
                    @else
                        <a href="{{ route('login') }}" x-data="{ clicked: false }" @click="clicked = true">
                            <button type="button"
                                class="bg-accent_blue text-primary_white px-7 py-3 rounded-full font-koho font-bold text-lg"
                                :disabled="clicked" :class="{ 'opacity-50 cursor-not-allowed': clicked }">
                                {{ __('welcome.login') }}
                            </button>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
        <div></div>
    </nav>
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
