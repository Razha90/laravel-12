<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div x-data="init()" x-init="firstInit" class="">
    <div aria-hidden="true" x-show="open" x-cloak
        class="animate-fade fixed left-0 right-0 top-0 z-50 flex h-screen w-screen items-center justify-center overflow-y-auto overflow-x-hidden bg-black/20 backdrop-blur-sm md:inset-0">
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
                        <template x-init="console.log(dataNotification)" x-for="(content, index) in dataNotification?.data"
                            :key="index">
                            <div x-bind:class="{
                                'bg-yellow-500/10 border-yellow-300': content.read ==
                                    '0',
                                'bg-secondary_blue/10  border-secondary_blue/70': content.read == '1'
                            }"
                                x-data="{ show: false }" class="relative rounded-xl border p-2">
                                <h3 x-text="content.title" class="text-secondary_blue font-bold"></h3>
                                <div x-html="content.body" class="text-secondary_blue mt-3 overflow-hidden"
                                    x-bind:class="{ 'max-h-auto': show, 'max-h-[20px]': !show }"></div>
                                <div x-show="show" x-transition x-text="changeDate(content.created_at)"
                                    class="text-secondary_blue text-right text-sm opacity-50"></div>
                                <div @click="if (content.read == '0') { readNotification(content.id) } show = true"
                                    class="text-secondary_blue absolute bottom-0 left-1/2 -translate-x-1/2 cursor-pointer text-sm opacity-30"
                                    x-cloak x-show="!show">{{ __('nav.more') }}</div>
                                <div @click="show=false" x-cloak
                                    class="text-secondary_blue mt-2 cursor-pointer text-center text-sm opacity-30"
                                    x-show="show">{{ __('nav.little') }}</div>
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
                    <template x-if="dataAplicationLetter?.data?.length > 0">
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
        <div x-show="dataNotification?.data?.filter(item => item.read == '0').length > 0"
            class="absolute right-1 top-1 z-30 h-[10px] w-[10px] animate-pulse rounded-full bg-red-500"></div>
    </div>
</div>

<script>
    function init() {
        return {
            open: false,
            dataNotification: {},
            stopCreateListener: false,
            activeLatter: false,
            askOpen: false,
            dataAplicationLetter: {},
            firstInit() {
                this.createListener();
                this.dataNotification = this.dataNotifications;
                this.dataAplicationLetter = this.dataAplicationLetters;
                console.log(this.dataAplicationLetter);
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
                console.log(data);

                const event = new CustomEvent('aplication-letter-delete', {
                    detail: {
                        id: id
                    }
                });
                window.dispatchEvent(event);
            },
            readNotification(id) {
                const data = JSON.parse(localStorage.getItem('notifications'));
                const index = data.data.findIndex(item => item.id === id);
                data.data[index].read = 1;
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
            get countShowNotification() {
                if (Array.isArray(this.dataNotification.data)) {
                    const countRead = this.dataNotification.data.filter(item => item.read === 1).length;
                    return countRead;
                }
                return 0;
            },
            changeDate(datetime) {
                const date = new Date(datetime);
                const twoDigit = (num) => num.toString().padStart(2, "0");
                const formatted =
                    `${twoDigit(date.getDate())}/${twoDigit(date.getMonth() + 1)}/${date.getFullYear()} ${twoDigit(date.getHours())}:${twoDigit(date.getMinutes())}`;
                return formatted;
            }

        }
    }
</script>
