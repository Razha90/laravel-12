<nav class="fixed top-8 w-full flex justify-center z-30 items-center">
    <div
        class="max-w-[1200px] w-[1200px] px-4 py-2 select-none flex flex-row justify-between items-center bg-primary_white rounded-full shadow-xl animate-fade-down h-[60px]">
        <div>
            <a href="{{ route('my-app') }}" class="hover:cursor-pointer hover:opacity-50 transition-opacity">
                <img src="{{ url('/img/web/logo.png') }}" width="100" height="100" />
            </a>
        </div>
        <div x-data="{
            currentPath: '',
            isNavigating: false,
            init() {
                this.currentPath = window.location.href;
            }
        }" x-cloak>
            <ul class="flex flex-row gap-x-8 font-sans">
                <li>
                    <a href="{{ route('my-app') }}"
                    class="text-secondary_black"
                        :class="{
                            'text-secondary_blue  border-secondary_blue font-bold pointer-events-none': currentPath === '{{ route('my-app') }}',
                            'opacity-50 cursor-not-allowed': isNavigating
                        }"
                        @click.prevent="if (currentPath !== '{{ route('my-app') }}') { isNavigating = true; setTimeout(() => { window.location.href = '{{ route('my-app') }}'; }, 100); }">
                        {{ __('app.dashboard') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('classroom') }}"
                        class="text-secondary_black"

                        :class="{
                            'text-secondary_blue border-secondary_blue font-bold pointer-events-none': currentPath === '{{ route('classroom') }}',
                            'opacity-50 cursor-not-allowed': isNavigating
                        }"
                        @click.prevent="if (currentPath !== '{{ route('classroom') }}') { isNavigating = true; setTimeout(() => { window.location.href = '{{ route('classroom') }}'; }, 100); }">
                        {{ __('app.my_classrooms') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('chat') }}"
                        class="text-secondary_black"
                        :class="{
                            'text-secondary_blue border-secondary_blue font-bold pointer-events-none': currentPath === '{{ route('chat') }}',
                            'opacity-50 cursor-not-allowed': isNavigating
                        }"
                        @click.prevent="if (currentPath !== '{{ route('chat') }}') { isNavigating = true; setTimeout(() => { window.location.href = '{{ route('chat') }}'; }, 100); }">
                        {{ __('app.chat') }}
                    </a>
                </li>
            </ul>
        </div>
        <div class="flex flex-row gap-x-4">
            <div x-data="{ open: false }" x-cloak class="relative flex items-center">
                <div @click="open = !open"
                    class="flex flex-row items-center gap-x-2 py-1 px-5 shadow-xl rounded-full border border-secondary_blue cursor-pointer">
                    <div
                        class="w-[30px] h-[30px] rounded-full overflow-hidden border border-secondary_blue">
                        <svg x-show=" `{{ Cookie::get('locale', 'fr') }}` == `en`" version="1.1"
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
                        <svg x-show="`{{ Cookie::get('locale', 'fr') }}` == `fr`" version="1.1"
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
                        <svg x-show="`{{ Cookie::get('locale', 'id') }}` == `id`" viewBox="0 0 36 36"
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
                        {{ Cookie::get('locale', 'fr') }}
                    </p>
                </div>
                <div x-show="open" @click.away="open = false" x-cloak
                    class="absolute top-12 rounded-2xl shadow-xl bg-primary_white"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-5 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-y-5 scale-95">
                    <ul class="p-5 rounded-xl shadow-xl flex gap-4 flex-col">
                        <li @click="window.location.href = `{{ route('change.lang', ['lang' => 'fr']) }}`"
                            class="hover:bg-accent_grey flex flex-row items-center gap-2 transition-colors hover:cursor-pointer px-2 rounded-lg py-1">
                            <div class="w-[30px] h-[30px] rounded-full overflow-hidden border border-accent_blue">
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 130 120"
                                    enable-background="new 0 0 130 120" xml:space="preserve" fill="#000000">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                    </g>
                                    <g id="SVGRepo_iconCarrier">
                                        <g id="Infos">
                                            <rect id="BG" x="-200" y="-1350" fill="#D8D8D8" width="2180"
                                                height="1700">
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
                            <div class="w-[30px] h-[30px] rounded-full overflow-hidden border border-accent_blue">
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 130 120"
                                    enable-background="new 0 0 130 120" xml:space="preserve" fill="#000000">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                    </g>
                                    <g id="SVGRepo_iconCarrier">
                                        <g id="Infos">
                                            <rect id="BG" x="-650" y="-740" fill="#D8D8D8" width="2180"
                                                height="1700">
                                            </rect>
                                        </g>
                                        <g id="Others"> </g>
                                        <g id="Europe">
                                            <g id="Row_5"> </g>
                                            <g id="Row_4"> </g>
                                            <g id="Row_3"> </g>
                                            <g id="Row_2">
                                                <g>
                                                    <rect x="87" fill="#DB3A49" width="43" height="120">
                                                    </rect>
                                                    <rect x="43" fill="#FFFFFF" width="44" height="120">
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
                            <div class="w-[30px] h-[30px] rounded-full overflow-hidden border border-accent_blue">
                                <svg viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img"
                                    class="iconify iconify--twemoji" preserveAspectRatio="xMidYMid meet"
                                    fill="#000000">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
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
            <!-- <a href="{{ route('settings.profile') }}"
                class="w-[40px] h-[40px] overflow-hidden rounded-full border border-secondary_blue p-1  hover:opacity-50 transition-opacity"
                x-data="{ isClicked: false }"
                @click.prevent="if (!isClicked) { isClicked = true; window.location.href = '{{ route('settings.profile') }}'; }"
                :class="{ 'pointer-events-none opacity-50': isClicked }">
                <img src="{{ asset(auth()->user()->profile_photo_path) }}" alt="Profile Photo">
            </a> -->
            <div x-data="{open:false}" class="relative">
                            <div class="rounded-full w-[40px] h-[40px] overflow-hidden border border-secondary_blue p-[2px] transition-opacity duration-300 hover:opacity-50 cursor-pointer" @click="open = !open">
                                <img src="{{ asset(auth()->user()->profile_photo_path) }}"
                                alt="Profile Photo">
                            </div>
                            <div x-show="open" @click.away="open = false" class="absolute top-14 right-0"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                            >
                                <ul class=" bg-primary_white rounded-xl shadow-xl p-2 text-secondary_blue min-w-[150px]">
                                    <li class="w-full">
                                        <a href="{{ route('settings.profile') }}"
                                            class="hover:bg-accent_grey rounded-lg px-2 py-1 transition-colors hover:cursor-pointer inline-flex w-full">
                                            {{ __('welcome.profile') }}
                                        </a>
                                    </li>
                                    <li class="w-full">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit"
                                                class="hover:bg-accent_grey rounded-lg px-2 py-1 transition-colors hover:cursor-pointer inline-flex w-full">
                                                {{ __('welcome.logout') }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
        </div>
    </div>
</nav>
