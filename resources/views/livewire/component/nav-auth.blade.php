<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Volt\Component;

new class extends Component {}; ?>

<nav class="nav-2:top-5 fixed top-0 z-30 flex w-full items-center justify-center" x-data="init()"
    x-init="firstInit">
    <div aria-hidden="true" x-show="open" x-cloak
        class="animate-fade fixed left-0 right-0 top-0 z-50 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-black/20 backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-2xl rounded-xl bg-white p-2" @click.away = "open = false">
            <div class="flex flex-row items-center justify-between">
                <div class="flex flex-row items-center gap-x-3 bg-white p-4">
                    <svg class="w-[30px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M3 12V15.8C3 16.9201 3 17.4802 3.21799 17.908C3.40973 18.2843 3.71569 18.5903 4.09202 18.782C4.51984 19 5.0799 19 6.2 19H17.8C18.9201 19 19.4802 19 19.908 18.782C20.2843 18.5903 20.5903 18.2843 20.782 17.908C21 17.4802 21 16.9201 21 15.8V12M3 12H6.67452C7.16369 12 7.40829 12 7.63846 12.0553C7.84254 12.1043 8.03763 12.1851 8.21657 12.2947C8.4184 12.4184 8.59136 12.5914 8.93726 12.9373L9.06274 13.0627C9.40865 13.4086 9.5816 13.5816 9.78343 13.7053C9.96237 13.8149 10.1575 13.8957 10.3615 13.9447C10.5917 14 10.8363 14 11.3255 14H12.6745C13.1637 14 13.4083 14 13.6385 13.9447C13.8425 13.8957 14.0376 13.8149 14.2166 13.7053C14.4184 13.5816 14.5914 13.4086 14.9373 13.0627L15.0627 12.9373C15.4086 12.5914 15.5816 12.4184 15.7834 12.2947C15.9624 12.1851 16.1575 12.1043 16.3615 12.0553C16.5917 12 16.8363 12 17.3255 12H21M3 12L5.32639 6.83025C5.78752 5.8055 6.0181 5.29312 6.38026 4.91755C6.70041 4.58556 7.09278 4.33186 7.52691 4.17615C8.01802 4 8.57988 4 9.70361 4H14.2964C15.4201 4 15.982 4 16.4731 4.17615C16.9072 4.33186 17.2996 4.58556 17.6197 4.91755C17.9819 5.29312 18.2125 5.8055 18.6736 6.83025L21 12"
                                stroke="#2867A4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </g>
                    </svg>
                    <p class="text-secondary_blue">{{ __('nav.inbox') }}</p>
                </div>
                <button type="button" @click="open = false"
                    class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                    data-modal-hide="default-modal">
                    <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <div class="flex flex-row gap-x-[2%]">
                <div class="flex w-[13%] flex-col gap-y-[15px]">
                    <div @click="activeLatter = false"
                        class="hover:bg-secondary_blue/25 flex cursor-pointer items-center justify-between rounded-xl px-3 py-1"
                        x-bind:class="{
                            'bg-secondary_blue/10 border border-bg-white': !
                                activeLatter,
                            'border border-secondary_blue/10': activeLatter
                        }">
                        <svg class="w-[35px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M7.81269 11.805L8.48873 12.1298C8.53741 12.0284 8.56269 11.9174 8.56269 11.805H7.81269ZM6.82501 13.861L6.14897 13.5362C6.08961 13.6598 6.06525 13.7973 6.07855 13.9338L6.82501 13.861ZM8.24851 15.73L8.56851 15.0517C8.54848 15.0422 8.52805 15.0337 8.50727 15.0261L8.24851 15.73ZM11.7644 16.53L11.7601 17.28L11.7686 17.28L11.7644 16.53ZM15.2802 15.73L14.9707 15.0468L14.9602 15.0517L15.2802 15.73ZM16.3264 14.916L16.9113 15.3855C16.9407 15.3489 16.9666 15.3096 16.9886 15.2681L16.3264 14.916ZM16.2094 12.833L15.5231 13.1354C15.542 13.1783 15.5649 13.2194 15.5914 13.258L16.2094 12.833ZM15.6449 10.664L16.3915 10.593C16.3907 10.5849 16.3898 10.5768 16.3888 10.5687L15.6449 10.664ZM11.7644 19L11.754 19.7499C11.7609 19.75 11.7678 19.75 11.7747 19.7499L11.7644 19ZM11.7634 5.897C10.1522 5.897 8.93413 6.59555 8.14766 7.73144C7.38524 8.8326 7.06269 10.2897 7.06269 11.805H8.56269C8.56269 10.4733 8.85049 9.3514 9.38091 8.58531C9.88729 7.85395 10.6446 7.397 11.7634 7.397V5.897ZM7.13665 11.4802L6.14897 13.5362L7.50105 14.1858L8.48873 12.1298L7.13665 11.4802ZM6.07855 13.9338C6.18878 15.0647 6.92546 16.0427 7.98975 16.4339L8.50727 15.0261C7.99879 14.8391 7.62744 14.3624 7.57147 13.7882L6.07855 13.9338ZM7.92852 16.4083C9.12988 16.9751 10.4363 17.2725 11.7601 17.28L11.7686 15.78C10.6642 15.7738 9.57324 15.5257 8.56851 15.0517L7.92852 16.4083ZM11.7686 17.28C13.0925 17.2725 14.3988 16.9751 15.6002 16.4083L14.9602 15.0517C13.9555 15.5257 12.8645 15.7738 11.7601 15.78L11.7686 17.28ZM15.5897 16.4132C16.1041 16.1801 16.5564 15.8276 16.9113 15.3855L15.7415 14.4465C15.5324 14.7071 15.2681 14.9121 14.9707 15.0468L15.5897 16.4132ZM16.9886 15.2681C17.4707 14.3614 17.4092 13.254 16.8273 12.408L15.5914 13.258C15.8552 13.6415 15.8844 14.1496 15.6642 14.5639L16.9886 15.2681ZM16.8957 12.5306C16.6257 11.9178 16.4553 11.2632 16.3915 10.593L14.8982 10.735C14.9771 11.5644 15.1881 12.3752 15.5231 13.1354L16.8957 12.5306ZM16.3888 10.5687C16.2291 9.32169 15.8062 8.15798 15.0356 7.29412C14.247 6.41001 13.1429 5.897 11.7634 5.897V7.397C12.7365 7.397 13.426 7.74299 13.9163 8.29263C14.4246 8.86252 14.7662 9.70731 14.9009 10.7593L16.3888 10.5687ZM12.5134 6.647V5H11.0134V6.647H12.5134ZM8.85395 18.7845C9.70153 19.3981 10.7125 19.7355 11.754 19.7499L11.7747 18.2501C11.0449 18.24 10.3332 18.0036 9.73348 17.5695L8.85395 18.7845ZM11.7747 19.7499C12.8162 19.7355 13.8272 19.3981 14.6748 18.7845L13.7952 17.5695C13.1955 18.0036 12.4839 18.24 11.754 18.2501L11.7747 19.7499Z"
                                    fill="#2867A4"></path>
                            </g>
                        </svg>
                    </div>
                    <div @click="activeLatter = true"
                        class="hover:bg-secondary_blue/25 flex cursor-pointer items-center justify-between rounded-xl px-3 py-1"
                        x-bind:class="{
                            'bg-secondary_blue/10 border border-bg-white': activeLatter,
                            'border border-secondary_blue/10':
                                !activeLatter
                        }">
                        <svg class="w-[30px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M22 12C22 15.7712 22 17.6569 20.8284 18.8284C19.6569 20 17.7712 20 14 20H10C6.22876 20 4.34315 20 3.17157 18.8284C2 17.6569 2 15.7712 2 12C2 8.22876 2 6.34315 3.17157 5.17157C4.34315 4 6.22876 4 10 4H14C17.7712 4 19.6569 4 20.8284 5.17157C21.4816 5.82475 21.7706 6.69989 21.8985 8"
                                    stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"></path>
                                <path
                                    d="M18 8L15.8411 9.79908C14.0045 11.3296 13.0861 12.0949 12 12.0949C11.3507 12.0949 10.7614 11.8214 10 11.2744M6 8L6.9 8.75L7.8 9.5"
                                    stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"></path>
                            </g>
                        </svg>
                    </div>
                </div>
                <div x-show="!activeLatter"
                    class="flex h-[400px] max-h-[400px] w-[85%] flex-col gap-y-3 overflow-auto pr-2">
                    <div x-show="dataNotification?.data?.length > 0" @click="askOpen = true"
                        class="cursor-pointer text-xs text-red-500 transition-all hover:underline">
                        {{ __('nav.delete_all') }}
                    </div>
                    <template x-if="dataNotification?.data?.length > 0">
                        <template x-for="(content, index) in dataNotification?.data" :key="content.id">
                            <div x-bind:class="{
                                'bg-yellow-500/10 border-yellow-300': content.read_at ==
                                    null,
                                'bg-secondary_blue/10  border-secondary_blue/70': content.read_at != null
                            }"
                                @click="if (content.read_at == null) { readNotification(content.id) }"
                                x-data="{ show: false }" class="relative rounded-xl border p-2" x-init="">
                                <h3 x-text="content.data.title" class="text-secondary_blue font-bold"></h3>

                                <div x-html="content.data.message" class="text-secondary_blue max-h-auto mt-3"></div>
                                <div x-transition x-text="changeDate(content.created_at)"
                                    class="text-secondary_blue text-right text-sm opacity-50"></div>
                                <div x-show="content.read == '0'" x-transition
                                    class="absolute left-0 top-0 h-[8px] w-[8px] animate-pulse rounded-full bg-red-300">
                                </div>
                                <div class="absolute right-2 top-2 cursor-pointer"
                                    @click="deleteNotification(content.id)">
                                    <svg class="w-[20px]" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path
                                                d="M6 5H18M9 5V5C10.5769 3.16026 13.4231 3.16026 15 5V5M9 20H15C16.1046 20 17 19.1046 17 18V9C17 8.44772 16.5523 8 16 8H8C7.44772 8 7 8.44772 7 9V18C7 19.1046 7.89543 20 9 20Z"
                                                stroke="#727D73" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                        </template>
                    </template>
                    <div x-show="dataNotification?.data?.length == 0" class="text-secondary_blue text-center">
                        {{ __('nav.null_notification') }}
                    </div>
                </div>
                <div x-show="activeLatter"
                    class="flex h-[400px] max-h-[400px] w-[85%] flex-col gap-y-3 overflow-auto pr-2">
                    <template x-if="dataAplicationLetter?.data.length > 0">
                        <template x-for="(content, index) in dataAplicationLetter?.data" :key="index">
                            <div class="relative flex flex-col gap-y-2 rounded-xl p-2"
                                x-bind:class="{
                                    'bg-yellow-300/20 border border-yellow-500/25': content && content.status ==
                                        'pending',
                                    'bg-green-300/20 border border-green-500/25': content && content.status ==
                                        'approved',
                                    'bg-red-300/20 border border-red-500/25': content && content.status ==
                                        'rejected'
                                }">
                                <h3 x-show="content.role == 'teacher'"
                                    class="text-secondary_blue text-base font-bold">
                                    {{ __('nav.letter_teacher') }}</h3>
                                <h3 x-show="content.role == 'guest'" class="text-secondary_blue text-base font-bold">
                                    {{ __('nav.letter_guest') }}</h3>
                                <div>
                                    <p class="text-secondary_blue text-sm font-bold">{{ __('nav.name') }}</p>
                                    <p class="text-secondary_blue text-sm" x-text="content.full_name"></p>
                                </div>
                                <div>
                                    <p class="text-secondary_blue text-sm font-bold">{{ __('nav.origin') }}</p>
                                    <p class="text-secondary_blue text-sm" x-text="content.origin"></p>
                                </div>
                                <div>
                                    <p class="text-secondary_blue text-sm font-bold">{{ __('nav.message') }}</p>
                                    <p class="text-secondary_blue text-sm" x-text="content.message"></p>
                                </div>
                                <div class="absolute right-3 top-3 animate-pulse">
                                    <p x-show="content && content.status == 'pending'"
                                        class="text-sm text-yellow-500">
                                        {{ __('nav.pending') }}</p>
                                    <p x-show="content.status == 'approved'" class="text-sm text-green-500">
                                        {{ __('nav.approved') }}</p>
                                    <p x-show="content.status == 'rejected'" class="text-sm text-red-500">
                                        {{ __('nav.rejected') }}</p>
                                </div>
                                <div class="text-center">
                                    <template x-if="content.status == 'pending'">
                                        <button @click="deleteAplicationLetter(content.id)"
                                            class="my-2 cursor-pointer rounded-md bg-red-500 px-4 py-1 text-sm text-white transition-colors hover:bg-red-400">
                                            {{ __('nav.cancel') }}
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </template>
                    <div x-show="dataAplicationLetter?.data?.length == 0" class="text-secondary_blue text-center">
                        {{ __('nav.null_letter') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="askOpen" x-cloak
        class="animate-fade bg-secondary_black/20 fixed left-0 right-0 top-0 z-50 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden backdrop-blur-sm">
        <div class="relative max-h-full w-full max-w-md p-4">
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <button type="button" @click="askOpen = false"
                    class="absolute end-2.5 top-3 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                    data-modal-hide="popup-modal">
                    <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="p-4 text-center md:p-5"
                    @click.away="askOpen=false; setTimeout(() => { open=true; }, 100)">
                    <svg class="mx-auto mb-4 h-12 w-12 text-gray-400 dark:text-gray-200" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">{{ __('nav.ask_delete') }}?
                    </h3>
                    <button data-modal-hide="popup-modal" type="button"
                        @click="deleteAllNotification(); askOpen = false; setTimeout(() => { open=true; }, 100)"
                        class="inline-flex items-center rounded-lg bg-red-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 dark:focus:ring-red-800">
                        {{ __('nav.yes_sure') }}
                    </button>
                    <button data-modal-hide="popup-modal" type="button"
                        @click="askOpen = false; setTimeout(() => { open=true; }, 100)"
                        class="ms-3 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700">{{ __('nav.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div
        class="bg-primary_white animate-fade-down nav-2:mx-5 nav-2:rounded-full mx-0 flex w-[1200px] max-w-[1200px] select-none flex-row items-center justify-between rounded-none px-4 py-2 shadow-xl">
        <div>
            <!-- <a href="{{ route('my-app') }}" class="transition-opacity hover:cursor-pointer hover:opacity-50">
                <img src="{{ url('/img/web/logo.png') }}" width="100" height="100" loading="lazy" />
            </a> -->
            <a href="{{ route('my-app') }}" class="transition-opacity hover:cursor-pointer hover:opacity-50">
                <!-- <img src="{{ url('/img/web/logo.png') }}" width="100" height="100" loading="lazy" /> -->
                <span class="mb-1 flex h-9 w-9 items-center justify-center rounded-md">
                    <x-app-logo-icon class="size-9 fill-current text-black" />
                </span>
            </a>
        </div>

        <div x-data="{
            currentPath: '',
            isNavigating: false,
            init() {
                this.currentPath = window.location.href;
            }
        }" x-cloak class="nav-2:block hidden">
            <ul class="flex flex-row gap-x-8 font-sans text-xl">
                <li>
                    <a href="{{ route('my-app') }}" class="text-secondary_black"
                        :class="{
                            'text-secondary_blue  border-secondary_blue font-bold pointer-events-none': currentPath === '{{ route('my-app') }}',
                            'opacity-50 cursor-not-allowed': isNavigating,
                            'text-secondary_blue/70 hover:text-orange-500 transition-all': currentPath !=
                                '{{ route('my-app') }}'
                        }"
                        @click.prevent="if (currentPath !== '{{ route('my-app') }}') { isNavigating = true; setTimeout(() => { window.location.href = '{{ route('my-app') }}'; }, 100); }">
                        {{ __('app.dashboard') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('classroom') }}" class="text-secondary_black"
                        :class="{
                            'text-secondary_blue border-secondary_blue font-bold pointer-events-none': currentPath === '{{ route('classroom') }}',
                            'opacity-50 cursor-not-allowed': isNavigating,
                            'text-secondary_blue/70 hover:text-orange-500 transition-all': currentPath !=
                                '{{ route('classroom') }}'
                        }"
                        @click.prevent="if (currentPath !== '{{ route('classroom') }}') { isNavigating = true; setTimeout(() => { window.location.href = '{{ route('classroom') }}'; }, 100); }">
                        {{ __('app.my_classrooms') }}
                    </a>
                </li>
            </ul>
        </div>

        <div class="nav-2:flex hidden flex-row items-center gap-x-4">
            <div x-data="{ open: false }" x-cloak class="relative flex items-center">
                <div @click="open = !open"
                    class="border-secondary_blue flex cursor-pointer flex-row items-center gap-x-2 rounded-full border px-5 py-1 shadow-xl">
                    <div class="border-secondary_blue h-[30px] w-[30px] overflow-hidden rounded-full border">
                        <svg x-show=" `{{ Cookie::get('locale', 'en') }}` == `en`" version="1.1"
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
                    class="bg-primary_white absolute top-12 rounded-2xl shadow-xl"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-5 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-y-5 scale-95">
                    <ul class="flex flex-col gap-4 rounded-xl px-4 py-2 shadow-xl" x-data="{ currentLang: '{{ Cookie::get('locale', 'fr') }}' }">
                        <li @click="if (currentLang !== 'fr') window.location.href = '{{ route('change.lang', ['lang' => 'fr']) }}'"
                            class="flex items-center gap-2 rounded-lg px-2 py-1 transition-colors"
                            :class="currentLang === 'fr'
                                ?
                                'bg-accent_grey text-gray-400 cursor-not-allowed' :
                                'hover:bg-accent_grey cursor-pointer'">
                            <div class="border-accent_blue h-[30px] w-[30px] overflow-hidden rounded-full border">
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
                                France
                            </p>
                        </li>
                        <li @click="if (currentLang !== 'en') window.location.href = '{{ route('change.lang', ['lang' => 'en']) }}'"
                            class="flex items-center gap-2 rounded-lg px-2 py-1 transition-colors"
                            :class="currentLang === 'en'
                                ?
                                'bg-accent_grey text-gray-400 cursor-not-allowed' :
                                'hover:bg-accent_grey cursor-pointer'">
                            <div class="border-accent_blue h-[30px] w-[30px] overflow-hidden rounded-full border">
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
                                English
                            </p>
                        </li>
                        <li @click="if (currentLang !== 'id') window.location.href = '{{ route('change.lang', ['lang' => 'id']) }}'"
                            class="flex items-center gap-2 rounded-lg px-2 py-1 transition-colors"
                            :class="currentLang === 'id'
                                ?
                                'bg-accent_grey text-gray-400 cursor-not-allowed' :
                                'hover:bg-accent_grey cursor-pointer'">
                            <div class="border-accent_blue h-[30px] w-[30px] overflow-hidden rounded-full border">
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

            <div @click="open = true"
                class="relative mx-[5px] flex h-[40px] w-[40px] cursor-pointer items-center justify-center rounded-full p-[4px] transition-colors hover:bg-black/10">
                <svg class="w-[30px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path
                            d="M3 12V15.8C3 16.9201 3 17.4802 3.21799 17.908C3.40973 18.2843 3.71569 18.5903 4.09202 18.782C4.51984 19 5.0799 19 6.2 19H17.8C18.9201 19 19.4802 19 19.908 18.782C20.2843 18.5903 20.5903 18.2843 20.782 17.908C21 17.4802 21 16.9201 21 15.8V12M3 12H6.67452C7.16369 12 7.40829 12 7.63846 12.0553C7.84254 12.1043 8.03763 12.1851 8.21657 12.2947C8.4184 12.4184 8.59136 12.5914 8.93726 12.9373L9.06274 13.0627C9.40865 13.4086 9.5816 13.5816 9.78343 13.7053C9.96237 13.8149 10.1575 13.8957 10.3615 13.9447C10.5917 14 10.8363 14 11.3255 14H12.6745C13.1637 14 13.4083 14 13.6385 13.9447C13.8425 13.8957 14.0376 13.8149 14.2166 13.7053C14.4184 13.5816 14.5914 13.4086 14.9373 13.0627L15.0627 12.9373C15.4086 12.5914 15.5816 12.4184 15.7834 12.2947C15.9624 12.1851 16.1575 12.1043 16.3615 12.0553C16.5917 12 16.8363 12 17.3255 12H21M3 12L5.32639 6.83025C5.78752 5.8055 6.0181 5.29312 6.38026 4.91755C6.70041 4.58556 7.09278 4.33186 7.52691 4.17615C8.01802 4 8.57988 4 9.70361 4H14.2964C15.4201 4 15.982 4 16.4731 4.17615C16.9072 4.33186 17.2996 4.58556 17.6197 4.91755C17.9819 5.29312 18.2125 5.8055 18.6736 6.83025L21 12"
                            stroke="#2867A4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </g>
                </svg>
                <div x-show="dataNotification?.data?.filter(item => item.read_at == null).length > 0"
                    class="absolute right-1 top-1 z-30 h-[10px] w-[10px] animate-pulse rounded-full bg-red-500"></div>
            </div>
            <div x-cloak x-data="{ open: false }" class="relative">
                <div class="border-secondary_blue h-[40px] w-[40px] cursor-pointer overflow-hidden rounded-full border p-[2px] transition-opacity duration-300 hover:opacity-50"
                    @click="open = !open">
                    <img src="{{ asset(auth()->user()->profile_photo_path) }}" alt="Profile Photo" loading="lazy">
                </div>
                <div x-show="open" @click.away="open = false" class="absolute right-0 top-14"
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
                        @auth
                            @if (auth()->user()->role === 'admin')
                                <li class="w-full">
                                    <a href="{{ route('admin') }}"
                                        class="hover:bg-accent_grey inline-flex w-full rounded-lg px-2 py-1 transition-colors hover:cursor-pointer">
                                        {{ __('nav.admin') }}
                                    </a>
                                </li>
                            @endif
                        @endauth
                    </ul>
                </div>
            </div>
        </div>

        <div x-data="{
            showingNav() {
                this.$dispatch('nav-mobile')
            }
        }" @click="showingNav"
            class="animate-fade-left nav-2:hidden text-secondary_blue bg-secondary_blue/10 group block cursor-pointer rounded-md p-2 transition-all hover:text-orange-500 active:text-orange-500">
            <svg class="h-[35px] w-[35px] active:scale-110 group-hover:scale-110" viewBox="-0.5 0 25 25"
                fill="none" xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path
                        d="M6.5 10.32C8.433 10.32 10 8.753 10 6.82001C10 4.88701 8.433 3.32001 6.5 3.32001C4.567 3.32001 3 4.88701 3 6.82001C3 8.753 4.567 10.32 6.5 10.32Z"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                    <path
                        d="M17.5 10.32C19.433 10.32 21 8.753 21 6.82001C21 4.88701 19.433 3.32001 17.5 3.32001C15.567 3.32001 14 4.88701 14 6.82001C14 8.753 15.567 10.32 17.5 10.32Z"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                    <path
                        d="M6.5 21.32C8.433 21.32 10 19.753 10 17.82C10 15.887 8.433 14.32 6.5 14.32C4.567 14.32 3 15.887 3 17.82C3 19.753 4.567 21.32 6.5 21.32Z"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                    <path
                        d="M17.5 21.32C19.433 21.32 21 19.753 21 17.82C21 15.887 19.433 14.32 17.5 14.32C15.567 14.32 14 15.887 14 17.82C14 19.753 15.567 21.32 17.5 21.32Z"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                </g>
            </svg>
        </div>
    </div>

    <div x-show="showen" x-cloak
        class="bg-secondary_blue/20 fixed right-0 top-0 z-50 flex h-screen min-h-[300px] w-screen justify-end"
        x-data="{ showen: false }" x-on:nav-mobile.window="()=>{
        showen = true;
    }"
        x-transition:enter="transform transition-transform ease-out duration-300"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition-transform ease-in duration-200"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
        <div class="bg-primary_white border-secondary_blue relative flex h-full w-[300px] max-w-[300px] flex-col border-l-2"
            x-on:click.away="showen=false">
            <div class="text-secondary_blue bg-secondary_blue/15 group absolute right-2 top-2 rounded-md p-2">
                <svg class="h-[25px] w-[25px] transition-all group-active:rotate-180"
                    @click="setTimeout(()=>{showen = false},(100))" viewBox="0 0 1024 1024"
                    xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path fill="currentColor"
                            d="M195.2 195.2a64 64 0 0 1 90.496 0L512 421.504 738.304 195.2a64 64 0 0 1 90.496 90.496L602.496 512 828.8 738.304a64 64 0 0 1-90.496 90.496L512 602.496 285.696 828.8a64 64 0 0 1-90.496-90.496L421.504 512 195.2 285.696a64 64 0 0 1 0-90.496z">
                        </path>
                    </g>
                </svg>
            </div>
            <div class="mt-16 px-5">
                <ul x-data="{ path: window.location.pathname }" class="text-accent_blue flex w-full flex-col gap-y-3 text-xl">
                    <li class="w-full">
                        <a href="{{ route('my-app') }}"
                            @click.prevent="
                                    if (window.location.pathname !== '/app') {
                                        window.location.href = '{{ route('my-app') }}';
                                    }
                                "
                            class="block w-full rounded-md border-b px-3 py-2"
                            x-bind:class="path == '/app' ? 'font-bold text-secondary_blue bg-secondary_blue/10' :
                                'opacity-70 border-secondary_blue'">
                            {{ __('app.dashboard') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('classroom') }}"
                        @click.prevent="
                                    if (window.location.pathname !== '/classroom') {
                                        window.location.href = '{{ route('classroom') }}';
                                    }
                                "
                            class="block w-full rounded-md border-b px-3 py-2"
                            x-bind:class="path == '/classroom' ? 'font-bold text-secondary_blue bg-secondary_blue/10' :
                                'opacity-70 border-secondary_blue'">
                            {{ __('app.my_classrooms') }}
                        </a>
                    </li>
                </ul>
            </div>
            <div @click="open = true; showen = false"
                class="relative mx-5 mt-5 flex h-[40px] w-[40px] cursor-pointer items-center justify-center rounded-full p-[4px] transition-colors hover:bg-black/10">
                <svg class="w-[30px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path
                            d="M3 12V15.8C3 16.9201 3 17.4802 3.21799 17.908C3.40973 18.2843 3.71569 18.5903 4.09202 18.782C4.51984 19 5.0799 19 6.2 19H17.8C18.9201 19 19.4802 19 19.908 18.782C20.2843 18.5903 20.5903 18.2843 20.782 17.908C21 17.4802 21 16.9201 21 15.8V12M3 12H6.67452C7.16369 12 7.40829 12 7.63846 12.0553C7.84254 12.1043 8.03763 12.1851 8.21657 12.2947C8.4184 12.4184 8.59136 12.5914 8.93726 12.9373L9.06274 13.0627C9.40865 13.4086 9.5816 13.5816 9.78343 13.7053C9.96237 13.8149 10.1575 13.8957 10.3615 13.9447C10.5917 14 10.8363 14 11.3255 14H12.6745C13.1637 14 13.4083 14 13.6385 13.9447C13.8425 13.8957 14.0376 13.8149 14.2166 13.7053C14.4184 13.5816 14.5914 13.4086 14.9373 13.0627L15.0627 12.9373C15.4086 12.5914 15.5816 12.4184 15.7834 12.2947C15.9624 12.1851 16.1575 12.1043 16.3615 12.0553C16.5917 12 16.8363 12 17.3255 12H21M3 12L5.32639 6.83025C5.78752 5.8055 6.0181 5.29312 6.38026 4.91755C6.70041 4.58556 7.09278 4.33186 7.52691 4.17615C8.01802 4 8.57988 4 9.70361 4H14.2964C15.4201 4 15.982 4 16.4731 4.17615C16.9072 4.33186 17.2996 4.58556 17.6197 4.91755C17.9819 5.29312 18.2125 5.8055 18.6736 6.83025L21 12"
                            stroke="#2867A4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </g>
                </svg>
                <div x-show="dataNotification?.data?.filter(item => item.read_at == null).length > 0"
                    class="absolute right-1 top-1 z-30 h-[10px] w-[10px] animate-pulse rounded-full bg-red-500"></div>
            </div>
            <div x-data="{ open: false }" class="relative mt-10 flex items-center justify-center">
                <div @click="open = !open"
                    class="border-secondary_blue flex cursor-pointer flex-row items-center gap-x-2 rounded-full border px-5 py-1 shadow-xl">
                    <div
                        class="children:rounded-full border-secondary_blue h-[30px] w-[30px] overflow-hidden rounded-full border">
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
                <div x-cloak x-show="open" @click.away="open = false"
                    class="bg-primary_white absolute top-12 rounded-2xl shadow-xl" x-transition>
                    <ul class="flex flex-col gap-4 rounded-xl px-4 py-2 shadow-xl" x-data="{ currentLang: '{{ Cookie::get('locale', 'fr') }}' }">
                        <li @click="if (currentLang !== 'fr') window.location.href = '{{ route('change.lang', ['lang' => 'fr']) }}'"
                            class="flex items-center gap-2 rounded-lg px-2 py-1 transition-colors"
                            :class="currentLang === 'fr'
                                ?
                                'bg-accent_grey text-gray-400 cursor-not-allowed' :
                                'hover:bg-accent_grey cursor-pointer'">
                            <div class="border-accent_blue h-[30px] w-[30px] overflow-hidden rounded-full border">
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
                                France
                            </p>
                        </li>
                        <li @click="if (currentLang !== 'en') window.location.href = '{{ route('change.lang', ['lang' => 'en']) }}'"
                            class="flex items-center gap-2 rounded-lg px-2 py-1 transition-colors"
                            :class="currentLang === 'en'
                                ?
                                'bg-accent_grey text-gray-400 cursor-not-allowed' :
                                'hover:bg-accent_grey cursor-pointer'">
                            <div class="border-accent_blue h-[30px] w-[30px] overflow-hidden rounded-full border">
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
                                English
                            </p>
                        </li>
                        <li @click="if (currentLang !== 'id') window.location.href = '{{ route('change.lang', ['lang' => 'id']) }}'"
                            class="flex items-center gap-2 rounded-lg px-2 py-1 transition-colors"
                            :class="currentLang === 'id'
                                ?
                                'bg-accent_grey text-gray-400 cursor-not-allowed' :
                                'hover:bg-accent_grey cursor-pointer'">
                            <div class="border-accent_blue h-[30px] w-[30px] overflow-hidden rounded-full border">
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
            <div class="flex-1"></div>
            <div class="mb-10 flex flex-row justify-center px-10">
                @auth
                    <div x-cloak x-data="{ open: false }" class="relative">
                        <div class="flex flex-row items-center gap-x-2" @click="open = !open">
                            <div
                                class="border-secondary_blue h-[40px] w-[40px] cursor-pointer overflow-hidden rounded-full border p-[2px] transition-opacity duration-300 hover:opacity-50">
                                <img src="{{ asset(auth()->user()->profile_photo_path) }}" alt="Profile Photo"
                                    loading="lazy">
                            </div>
                            <p class="text-secondary_blue line-clamp-1 text-base">{{ auth()->user()->name }}</p>
                        </div>
                        <div x-cloak x-show="open" @click.away="open = false" class="absolute bottom-[120%]"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-1 scale-95">
                            <ul class="bg-primary_white text-secondary_blue min-w-[150px] rounded-xl p-2 shadow-xl">
                                <li class="w-full">
                                    <a wire:navigate href="{{ route('settings.profile') }}"
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
                                @auth
                                    @if (auth()->user()->role === 'admin')
                                        <li class="w-full">
                                            <a href="{{ route('admin') }}"
                                                class="hover:bg-accent_grey inline-flex w-full rounded-lg px-2 py-1 transition-colors hover:cursor-pointer">
                                                {{ __('nav.admin') }}
                                            </a>
                                        </li>
                                    @endif
                                @endauth
                            </ul>
                        </div>
                    </div>
                @else
                    <a wire:navigate href="{{ route('login') }}" x-data="{ clicked: false, url: ['/login'], show: 'false' }" @click="clicked = true"
                        x-show="show" x-init="if (url.includes(window.location.pathname)) show = false;">
                        <button type="button"
                            class="bg-accent_blue text-primary_white font-koho rounded-full px-7 py-3 text-lg font-bold"
                            :disabled="clicked" :class="{ 'opacity-50 cursor-not-allowed': clicked }">
                            {{ __('welcome.login') }}
                        </button>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<script>
    function init() {
        return {
            open: false,
            dataNotification: {},
            stopCreateListener: false,
            activeLatter: false,
            askOpen: false,
            dataAplicationLetter: {},
            checkOverflow(el) {
                this.$nextTick(() => {
                    const lineHeight = parseFloat(getComputedStyle(el).lineHeight);
                    const totalHeight = el.scrollHeight;
                    const maxLines = 2;
                    this.isOverflowing = totalHeight > (lineHeight * maxLines);
                });
            },
            firstInit() {
                this.createListener();
                this.dataNotification = this.dataNotifications;
                this.dataAplicationLetter = this.dataAplicationLetters;

            },
            createListener() {
                if (this.stopCreateListener) return;
                this.stopCreateListener = true;
                window.addEventListener('notifications', (event) => {
                    const data = JSON.parse(localStorage.getItem('notifications'));
                    this.dataNotification = data;
                });

                window.addEventListener('aplication-letter', (event) => {
                    const data = JSON.parse(localStorage.getItem('aplication-letter'));
                    this.dataAplicationLetter = data;
                });
            },
            deleteNotification(id) {
                const data = JSON.parse(localStorage.getItem('notifications'));
                const index = data.data.findIndex(item => item.id === id);
                data.data.splice(index, 1);
                localStorage.setItem('notifications', JSON.stringify(data));
                this.dataNotification = data;
                const event = new CustomEvent('notification-delete', {
                    detail: {
                        id: id
                    }
                });
                window.dispatchEvent(event);
            },
            deleteAllNotification() {
                const data = JSON.parse(localStorage.getItem('notifications'));
                data.data = [];
                localStorage.setItem('notifications', JSON.stringify(data));
                this.dataNotification = data;
                const event = new CustomEvent('notification-delete-all');
                window.dispatchEvent(event);
            },
            deleteAplicationLetter(id) {
                const data = JSON.parse(localStorage.getItem('aplication-letter'));
                const index = data.data.findIndex(item => item.id === id);
                data.data.splice(index, 1);
                localStorage.setItem('aplication-letter', JSON.stringify(data));
                this.dataAplicationLetter = data;
                const event = new CustomEvent('aplication-letter-delete', {
                    detail: {
                        id: id
                    }
                });
                window.dispatchEvent(event);
            },
            readNotification(id) {
                const data = JSON.parse(localStorage.getItem('notifications'));
                const index = data.data.findIndex(item => item.id == id);
                if (index !== -1) {
                    const now = new Date();
                    const formattedDate = now.getFullYear() + "-" +
                        String(now.getMonth() + 1).padStart(2, '0') + "-" +
                        String(now.getDate()).padStart(2, '0') + " " +
                        String(now.getHours()).padStart(2, '0') + ":" +
                        String(now.getMinutes()).padStart(2, '0') + ":" +
                        String(now.getSeconds()).padStart(2, '0');

                    data.data[index].read_at = formattedDate;
                }
                localStorage.setItem('notifications', JSON.stringify(data));
                this.dataNotification = data;
                const event = new CustomEvent('notification-read', {
                    detail: {
                        id: id
                    }
                });
                window.dispatchEvent(event);
            },
            get dataNotifications() {
                return JSON.parse(localStorage.getItem('notifications'));
            },
            get dataAplicationLetters() {
                return JSON.parse(localStorage.getItem('aplication-letter'));
            },
            changeDate(datetime) {
                const cleanDate = datetime.split('.')[0] + 'Z';
                const date = new Date(cleanDate);

                if (isNaN(date.getTime())) {
                    return "Invalid date";
                }

                return date.toLocaleString(); // atau format sesuai keinginan
            }

        }
    }
</script>
