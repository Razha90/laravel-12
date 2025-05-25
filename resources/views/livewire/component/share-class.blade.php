<?php

use Livewire\Volt\Component;

new class extends Component {
    public $classrooms;
}; ?>

<div aria-hidden="true" x-data="initShareClass" x-cloak x-on:shared-modal.window="(event) => {
    open = true
}"x-show="open"
    class="animate-fade fixed left-0 right-0 top-0 z-50 flex h-screen w-screen items-center justify-center overflow-y-auto overflow-x-hidden bg-black/20 backdrop-blur-sm md:inset-0">
    <div class="relative max-h-full w-full max-w-2xl rounded-xl bg-white p-2" @click.away = "open = false">
        <div class="flex flex-row items-center justify-between">
            <div class="flex flex-row items-center gap-x-3 bg-white p-4">
                <div class="bg-secondary_blue flex- items-center justify-center rounded-xl p-1"><svg
                        class="text-primary_white w-[30px]" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M4 12C4 13.3807 5.11929 14.5 6.5 14.5C7.88071 14.5 9 13.3807 9 12C9 10.6193 7.88071 9.5 6.5 9.5"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M14 6.5L9 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            </path>
                            <path d="M14 17.5L9 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            </path>
                            <path
                                d="M16.5 21C17.8807 21 19 19.8807 19 18.5C19 17.1193 17.8807 16 16.5 16C15.1193 16 14 17.1193 14 18.5"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                            <path
                                d="M18.665 6.74993C17.9746 7.94566 16.4457 8.35535 15.2499 7.66499C14.0542 6.97464 13.6445 5.44566 14.3349 4.24993C15.0252 3.0542 16.5542 2.64451 17.7499 3.33487"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                        </g>
                    </svg></div>
                <p class="text-secondary_blue text-xl">{{ __('class-learn.share') }}</p>
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
        <div>
            <div x-data="{
                get cleanUrl() {
                    const fullUrl = window.location.href; // ambil full url sekarang
                    const cleanUrl = fullUrl.split('?')[0];
                    return cleanUrl;
                },
                copyPaste() {
                    navigator.clipboard.writeText(this.cleanUrl)
                        .then(() => {
                            this.$dispatch('success', [{ message: '{{ __('class-learn.copy_sucess') }}' }]);
                        })
                        .catch(err => {
                            console.error('Gagal menyalin teks: ', err);
                        });
                }
            }" class="mb-10 w-full">
                <div
                    class="bg-primary_white mx-auto flex max-w-sm flex-row items-center justify-between rounded-full px-2 py-2 shadow-xl">
                    <div x-text="cleanUrl" class="text-secondary_blue max-w-[300px] truncate text-lg"></div>
                    <button @click="copyPaste"
                        class="bg-secondary_blue text-primary_white border-primary_white hover:bg-primary_white hover:border-secondary_blue hover:text-secondary_blue cursor-pointer rounded-full border-2 px-4 py-2 text-center text-lg transition-all">{{ __('class-learn.copy') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function initShareClass() {
        return {
            classroom: @entangle('classrooms').live,
            open: false,
        }
    }
</script>