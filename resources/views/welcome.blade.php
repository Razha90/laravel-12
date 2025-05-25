<!DOCTYPE html>
<html lang="{{ cookie('locale', 'fr') }}">


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/web/icon.svg') }}">


    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <x-layouts.app.app-nav-welcome />
    <div x-data="{ hash: window.location.hash || '#welcome' }" class="animate-fade" x-init="window.addEventListener('hashchange', () => hash = window.location.hash || '#welcome')">
        <div class="bg-secondary_blue/15 relative h-screen min-h-[900px] w-full overflow-hidden">
            <!-- Welcome Page -->
            <div x-show="hash != '#about' && hash != '#features'"
                x-transition:enter="transform transition ease-out duration-500"
                x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-300"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="absolute inset-0 mx-auto h-full w-full max-w-7xl overflow-y-auto bg-cover bg-center">
                <div class="lg:h-50 welcome:h-40 nav-1:h-30 h-20"></div>
                <div class="welcome:flex-row mx-auto flex flex-col-reverse items-center justify-center">
                    <div class="animate-fade-right animate-delay-200 welcome:w-[45%] nav-1:pl-10 w-full px-5">
                        <h1
                            class="welcome:text-left text-secondary_blue font-poetsen lg:leading-15 text-center text-4xl leading-10 lg:text-5xl">
                            {{ __('welcome.title') }}</h1>
                        <p
                            class="text-secondary_blue/80 welcome:text-left text-md mt-3 text-center font-bold lg:text-xl">
                            {{ __('welcome.description') }}</p>
                        <button
                            class="welcome:mx-0 text-primary_white bg-secondary_blue border-secondary_blue hover:bg-secondary_blue/5 hover:text-secondary_blue active:bg-secondary_blue/5 active:text-secondary_blue mx-auto mt-5 flex cursor-pointer flex-row items-center gap-x-2 rounded-xl border-2 p-3 transition-all">
                            <p class="text-xl font-bold lg:text-2xl">{{ __('welcome.explore_now') }}</p>
                            <svg class="h-[30px] w-[30px] lg:h-[35px] lg:w-[35px]" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path
                                        d="M11 16L15 12M15 12L11 8M15 12H3M4.51555 17C6.13007 19.412 8.87958 21 12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C8.87958 3 6.13007 4.58803 4.51555 7"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </g>
                            </svg>
                        </button>
                    </div>
                    <div class="animate-fade-left animate-delay-200 welcome:w-[55%] w-full pr-5">
                        <img class="-translate-x-1/12 w-full" src="{{ asset('img/web/bg-welcome-1.png') }}" />
                    </div>
                </div>
                <div
                    class="welcome:mt-0 animate-fade-up animate-delay-200 bg-primary_white mx-auto mb-10 mt-10 flex w-[80%] flex-row flex-wrap justify-around gap-y-5 rounded-xl p-5">
                    <div class="flex w-[300px] flex-row items-start gap-x-2 rounded-xl bg-gray-100 p-3 px-3">
                        <div class="text-secondary_blue">
                            <svg class="h-[35px] w-[35px]" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2C16.714 2 19.0711 2 20.5355 3.46447C22 4.92893 22 7.28595 22 12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12ZM13.75 12C13.75 10.253 15.2834 8.75 17.2857 8.75C17.7974 8.75 18.281 8.84961 18.7168 9.02731C19.1003 9.18372 19.5381 8.99958 19.6945 8.61604C19.8509 8.23249 19.6667 7.79477 19.2832 7.63836C18.669 7.38791 17.9931 7.25 17.2857 7.25C14.5541 7.25 12.25 9.32875 12.25 12C12.25 14.6712 14.5541 16.75 17.2857 16.75C17.9931 16.75 18.669 16.6121 19.2832 16.3616C19.6667 16.2052 19.8509 15.7675 19.6945 15.384C19.5381 15.0004 19.1003 14.8163 18.7168 14.9727C18.281 15.1504 17.7974 15.25 17.2857 15.25C15.2834 15.25 13.75 13.747 13.75 12ZM9.28571 8.75C7.28342 8.75 5.75 10.253 5.75 12C5.75 13.747 7.28342 15.25 9.28571 15.25C9.79735 15.25 10.281 15.1504 10.7168 14.9727C11.1003 14.8163 11.5381 15.0004 11.6945 15.384C11.8509 15.7675 11.6667 16.2052 11.2832 16.3616C10.669 16.6121 9.99311 16.75 9.28571 16.75C6.55414 16.75 4.25 14.6712 4.25 12C4.25 9.32875 6.55414 7.25 9.28571 7.25C9.99311 7.25 10.669 7.38791 11.2832 7.63836C11.6667 7.79477 11.8509 8.23249 11.6945 8.61604C11.5381 8.99958 11.1003 9.18372 10.7168 9.02731C10.281 8.84961 9.79735 8.75 9.28571 8.75Z"
                                        fill="currentColor"></path>
                                </g>
                            </svg>
                        </div>
                        <div>
                            <p class="text-secondary_blue text-base font-bold">{{ __('welcome.free') }}</p>
                            <p class="text-secondary_blue/50 text-sm">{{ __('welcome.free.detail') }}</p>
                        </div>
                    </div>
                    <div class="flex w-[300px] flex-row items-start gap-x-2 rounded-xl bg-gray-100 p-3 px-3">
                        <div class="text-yellow-400">
                            <svg class="h-[35px] w-[35px]" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path d="M12 3V16M12 16L16 11.625M12 16L8 11.625" stroke="currentColor"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path
                                        d="M15 21H9C6.17157 21 4.75736 21 3.87868 20.1213C3 19.2426 3 17.8284 3 15M21 15C21 17.8284 21 19.2426 20.1213 20.1213C19.8215 20.4211 19.4594 20.6186 19 20.7487"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </g>
                            </svg>
                        </div>
                        <div>
                            <p class="text-secondary_blue text-base font-bold">{{ __('welcome.install') }}</p>
                            <p class="text-secondary_blue/50 text-sm">{{ __('welcome.install.detail') }}</p>
                        </div>
                    </div>
                    <div class="flex w-[300px] flex-row items-start gap-x-2 rounded-xl bg-gray-100 p-3 px-3">
                        <div class="text-red-400">
                            <svg class="h-[35px] w-[35px]" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M11.5376 3.9477C11.7275 3.78498 12.0083 3.78751 12.1952 3.95363L19.702 10.6263C20.0456 10.9317 19.8295 11.5 19.3698 11.5H18.5C17.6716 11.5 17 12.1716 17 13V20C17 20.2761 16.7761 20.5 16.5 20.5H7.24999C6.97385 20.5 6.74999 20.2761 6.74999 20V13.125C6.74999 12.8489 6.52613 12.625 6.24999 12.625C5.97385 12.625 5.74999 12.8489 5.74999 13.125V20C5.74999 20.8284 6.42156 21.5 7.24999 21.5H16.5C17.3284 21.5 18 20.8284 18 20V13C18 12.7239 18.2238 12.5 18.5 12.5H19.3698C20.7491 12.5 21.3972 10.7952 20.3663 9.87889L12.8596 3.20622C12.2989 2.70788 11.4564 2.70028 10.8868 3.18845L3.67459 9.37038C3.46493 9.55009 3.44065 9.86574 3.62036 10.0754C3.80007 10.2851 4.11572 10.3093 4.32538 10.1296L11.5376 3.9477Z"
                                        fill="currentColor"></path>
                                </g>
                            </svg>
                        </div>
                        <div>
                            <p class="text-secondary_blue text-base font-bold">{{ __('welcome.install') }}</p>
                            <p class="text-secondary_blue/50 text-sm">{{ __('welcome.install.detail') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About Page -->
            <div x-show="hash === '#about'" x-transition:enter="transform transition ease-out duration-500"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-300"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="absolute inset-0 mx-auto h-full w-full max-w-7xl overflow-y-auto bg-cover bg-center"
                x-data="{ loaded: false }">
                <div class="welcome:h-40 nav-1:h-35 h-25"></div>
                <div class="nav-1:px-10 relative mx-auto max-w-7xl">
                    <div
                        class="nav-1:aspect-[5/1] nav-1:rounded-[70px] relative mx-auto aspect-[5/2] w-full overflow-hidden">
                        <img x-ref="bgs" @load="loaded = true" x-show="loaded" x-init="if ($refs.bgs.complete) loaded = true"
                            class="animate-fade h-full w-full object-cover" src="{{ asset('img/web/about.png') }}" />

                        <div class="absolute left-0 top-0 -z-10 h-full w-full animate-pulse bg-gray-200"></div>
                    </div>
                    <div
                        class="animate-fade-up text-secondary_blue welcome-1:px-6 welcome-1:py-4 welcome:text-5xl welcome-1:text-2xl absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 rounded-[30px] bg-white px-3 py-1 text-xl font-bold shadow">
                        {{ __('welcome.get_to_know_use') }}
                    </div>
                </div>
                <div
                    class="text-secondary_blue font-merriweather welcome:mt-30 welcome-2:mt-20 welcome-1:mt-15 welcome:flex-row welcome:gap-y-1 welcome-2:gap-y-5 mx-auto mt-10 flex w-full max-w-7xl flex-col gap-y-2 px-10">
                    <h2
                        class="underline-yellow-500 animate-fade-right welcome-2:text-4xl welcome:w-[40%] w-full text-2xl underline">
                        {{ __('welcome.a_litte_bit_about_us') }}</h2>
                    <div class="welcome-1:text-md welcome:w-[60%] w-full text-base">
                        <p class="animate-fade-left indent-12 leading-8 tracking-wide">{{ config('app.name') }}
                            {{ __('welcome.description_1') }}</p>
                        <p class="animate-fade-left mt-5 indent-12 leading-8 tracking-wide">
                            {{ __('welcome.description_2') }}</p>
                    </div>
                </div>
            </div>

            <!-- Features Page -->
            <div x-show="hash === '#features'" x-transition:enter="transform transition ease-out duration-500"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-300"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="absolute inset-0 mx-auto h-full w-full max-w-7xl overflow-y-auto bg-cover bg-center">
                <div class="welcome:h-40 nav-1:h-35 h-25"></div>
                <div class="welcome-1:px-5 flex flex-col gap-x-[5%] gap-y-10 px-1 lg:flex-row lg:pl-10">
                    <div class="animate-fade-right w-full lg:w-[55%] xl:w-[40%]">
                        <h2 class="text-secondary_blue welcome-1:text-4xl text-center text-2xl font-bold lg:text-left">
                            {{ __('welcome.check_interest_feature') }}
                        </h2>
                        <p class="text-secondary_blue welcome-1:text-lg mt-3 text-center text-base lg:text-left">
                            {{ __('welcome.check_interest_detail') }}</p>
                        <div x-data="{ cool: false }"
                            class="welcome-1:rounded-[40px] welcome-1:shadow-[10px_10px_0_rgba(0,0,0,1)] relative mt-3 aspect-[3/1] cursor-pointer overflow-hidden transition-all hover:shadow-[-10px_-10px_0_rgba(0,0,0,1)] active:shadow-[-10px_-10px_0_rgba(0,0,0,1)] lg:aspect-[3/3]">
                            <img x-ref="bg" @load="cool = true" x-show="cool" x-init="if ($refs.bg.complete) cool = true"
                                class="animate-fade h-full w-full object-cover object-top"
                                src="{{ asset('img/web/chat-bg.png') }}" />
                            <div class="absolute left-0 top-0 -z-10 h-full w-full animate-pulse bg-gray-200"></div>
                        </div>
                    </div>
                    <div
                        class="animate-fade-left flex w-auto flex-row flex-wrap justify-center gap-x-10 gap-y-10 pr-10 pr-[10px] lg:flex-col">
                        <div
                            class="bg-accent_yellow flex w-[300px] flex-row items-start gap-x-2 rounded-xl p-3 shadow-[10px_10px_0_rgba(0,0,0,1)] transition-all hover:bg-red-100 hover:shadow-[-10px_-10px_0_rgba(0,0,0,1)] active:shadow-[-10px_-10px_0_rgba(0,0,0,1)] lg:w-auto">
                            <div
                                class="text-secondary_blue bg-accent_yellow border-secondary_blue rounded-full border-2 p-2">
                                <svg class="h-[35px] w-[35px]" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z"
                                            fill="currentColor"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M12 3C12.5523 3 13 3.44772 13 4V5.07089C16.0657 5.5094 18.4906 7.93431 18.9291 11H20C20.5523 11 21 11.4477 21 12C21 12.5523 20.5523 13 20 13H18.9291C18.4906 16.0657 16.0657 18.4906 13 18.9291V20C13 20.5523 12.5523 21 12 21C11.4477 21 11 20.5523 11 20V18.9291C7.93431 18.4906 5.5094 16.0657 5.07089 13H4C3.44772 13 3 12.5523 3 12C3 11.4477 3.44772 11 4 11H5.07089C5.5094 7.93431 7.93431 5.5094 11 5.07089V4C11 3.44772 11.4477 3 12 3ZM7 12C7 9.23858 9.23858 7 12 7C14.7614 7 17 9.23858 17 12C17 14.7614 14.7614 17 12 17C9.23858 17 7 14.7614 7 12Z"
                                            fill="currentColor"></path>
                                    </g>
                                </svg>
                            </div>
                            <div>
                                <p class="text-secondary_blue text-lg font-bold">{{ __('welcome.feature_1') }}</p>
                                <p class="text-secondary_blue text-base">{{ __('welcome.feature_1.detail') }}</p>
                            </div>
                        </div>
                        <div
                            class="bg-accent_yellow flex w-[300px] flex-row items-start gap-x-2 rounded-xl p-3 shadow-[10px_10px_0_rgba(0,0,0,1)] transition-all hover:bg-red-100 hover:shadow-[-10px_-10px_0_rgba(0,0,0,1)] active:shadow-[-10px_-10px_0_rgba(0,0,0,1)] lg:w-auto">
                            <div
                                class="text-secondary_blue bg-accent_yellow border-secondary_blue rounded-full border-2 p-2">
                                <svg class="h-[35px] w-[35px]" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M17 3.33782C15.5291 2.48697 13.8214 2 12 2C6.47715 2 2 6.47715 2 12C2 13.5997 2.37562 15.1116 3.04346 16.4525C3.22094 16.8088 3.28001 17.2161 3.17712 17.6006L2.58151 19.8267C2.32295 20.793 3.20701 21.677 4.17335 21.4185L6.39939 20.8229C6.78393 20.72 7.19121 20.7791 7.54753 20.9565C8.88837 21.6244 10.4003 22 12 22C17.5228 22 22 17.5228 22 12C22 10.1786 21.513 8.47087 20.6622 7"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    </g>
                                </svg>
                            </div>
                            <div>
                                <p class="text-secondary_blue text-lg font-bold">{{ __('welcome.feature_2') }}</p>
                                <p class="text-secondary_blue text-base">{{ __('welcome.features_2.detail') }}</p>
                            </div>
                        </div>
                        <div
                            class="bg-accent_yellow flex w-[300px] flex-row items-start gap-x-2 rounded-xl p-3 shadow-[10px_10px_0_rgba(0,0,0,1)] transition-all hover:bg-red-100 hover:shadow-[-10px_-10px_0_rgba(0,0,0,1)] active:shadow-[-10px_-10px_0_rgba(0,0,0,1)] lg:w-auto">
                            <div
                                class="text-secondary_blue bg-accent_yellow border-secondary_blue rounded-full border-2 p-2">
                                <svg class="h-[35px] w-[35px]" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"
                                    fill="currentColor">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M11.707 1.712l-4 4L4.419 9l1.29 1.29a1 1 0 0 1 .21 1.09A.987.987 0 0 1 5 12H1a1 1 0 0 1-1-1V7a.987.987 0 0 1 .62-.92 1 1 0 0 1 1.09.21L3 7.581l3.289-3.289 4-4a1.003 1.003 0 0 1 1.418 1.42zM6.293 16.288l4-4L13.581 9l-1.29-1.29a1 1 0 0 1-.21-1.09A.987.987 0 0 1 13 6h4a1 1 0 0 1 1 1v4a.987.987 0 0 1-.62.92 1 1 0 0 1-1.09-.21L15 10.419l-3.289 3.289-4 4a1.003 1.003 0 0 1-1.418-1.42z"
                                            fill="currentColor" fill-rule="evenodd"></path>
                                    </g>
                                </svg>
                            </div>
                            <div>
                                <p class="text-secondary_blue text-lg font-bold">{{ __('welcome.feature_3') }}</p>
                                <p class="text-secondary_blue text-base">{{ __('welcome.feature_3.detail') }}</p>
                            </div>
                        </div>
                        <div
                            class="bg-accent_yellow flex w-[300px] flex-row items-start gap-x-2 rounded-xl p-3 shadow-[10px_10px_0_rgba(0,0,0,1)] transition-all hover:bg-red-100 hover:shadow-[-10px_-10px_0_rgba(0,0,0,1)] active:shadow-[-10px_-10px_0_rgba(0,0,0,1)] lg:w-auto">
                            <div
                                class="text-secondary_blue bg-accent_yellow border-secondary_blue rounded-full border-2 p-2">
                                <svg class="h-[35px] w-[35px]" viewBox="0 -1.5 21 21" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    fill="currentColor">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <title>content / 18 - content, folder, open folder, data, file, storage icon
                                        </title>
                                        <g id="Free-Icons" stroke="none" stroke-width="1" fill="none"
                                            fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round">
                                            <g transform="translate(-229.000000, -305.000000)" id="Group"
                                                stroke="currentColor" stroke-width="2">
                                                <g transform="translate(227.000000, 302.000000)" id="Shape">
                                                    <path
                                                        d="M6.99588205,13 L22,13 L18.8443072,18.4941815 C18.3092927,19.4256622 17.3170763,20 16.2428803,20 L3,20 L6.99588205,13 Z">
                                                    </path>
                                                    <path
                                                        d="M3,20 L3,5 C3,4.44771525 3.44771525,4 4,4 L9.49749189,4 C9.81366031,4 10.1112053,4.14951876 10.2998908,4.40321197 L12.7001092,7.63037563 C12.8887947,7.88406884 13.1863397,8.0335876 13.5025081,8.0335876 L18,8.0335876 C18.5522847,8.0335876 19,8.48130285 19,9.0335876 L19,12 L19,12">
                                                    </path>
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                            <div>
                                <p class="text-secondary_blue text-lg font-bold">{{ __('welcome.feature_4') }}</p>
                                <p class="text-secondary_blue text-base">{{ __('welcome.feature_4.detail') }}</p>
                            </div>
                        </div>
                        <div
                            class="bg-accent_yellow flex w-[300px] flex-row items-start gap-x-2 rounded-xl p-3 shadow-[10px_10px_0_rgba(0,0,0,1)] transition-all hover:bg-red-100 hover:shadow-[-10px_-10px_0_rgba(0,0,0,1)] active:shadow-[-10px_-10px_0_rgba(0,0,0,1)] lg:w-auto">
                            <div
                                class="text-secondary_blue bg-accent_yellow border-secondary_blue rounded-full border-2 p-2">
                                <svg class="h-[35px] w-[35px]" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M4 21C4 17.4735 6.60771 14.5561 10 14.0709M19.8726 15.2038C19.8044 15.2079 19.7357 15.21 19.6667 15.21C18.6422 15.21 17.7077 14.7524 17 14C16.2923 14.7524 15.3578 15.2099 14.3333 15.2099C14.2643 15.2099 14.1956 15.2078 14.1274 15.2037C14.0442 15.5853 14 15.9855 14 16.3979C14 18.6121 15.2748 20.4725 17 21C18.7252 20.4725 20 18.6121 20 16.3979C20 15.9855 19.9558 15.5853 19.8726 15.2038ZM15 7C15 9.20914 13.2091 11 11 11C8.79086 11 7 9.20914 7 7C7 4.79086 8.79086 3 11 3C13.2091 3 15 4.79086 15 7Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                    </g>
                                </svg>
                            </div>
                            <div>
                                <p class="text-secondary_blue text-lg font-bold">{{ __('welcome.feature_5') }}</p>
                                <p class="text-secondary_blue text-base">{{ __('welcome.feature_5.detail') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="h-[100px]"></div>
            </div>
        </div>

    </div>
</body>

</html>
