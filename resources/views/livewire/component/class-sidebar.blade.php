<?php

use Livewire\Volt\Component;

new class extends Component {}; ?>

<div class="w-full">
    <div class="bg-secondary_blue mt-3 border-accent_blue flex cursor-pointer flex-row items-center justify-center gap-x-1 rounded-xl border-2 p-1 transition-all hover:opacity-70"
        @click="$dispatch('shared-modal')">
        <div class="bg-secondary_blue flex- items-center justify-center rounded-xl p-1"><svg
                class="text-primary_white w-[25px]" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path
                        d="M4 12C4 13.3807 5.11929 14.5 6.5 14.5C7.88071 14.5 9 13.3807 9 12C9 10.6193 7.88071 9.5 6.5 9.5"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                    <path d="M14 6.5L9 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                    <path d="M14 17.5L9 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                    <path
                        d="M16.5 21C17.8807 21 19 19.8807 19 18.5C19 17.1193 17.8807 16 16.5 16C15.1193 16 14 17.1193 14 18.5"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                    <path
                        d="M18.665 6.74993C17.9746 7.94566 16.4457 8.35535 15.2499 7.66499C14.0542 6.97464 13.6445 5.44566 14.3349 4.24993C15.0252 3.0542 16.5542 2.64451 17.7499 3.33487"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                </g>
            </svg></div>
        <p class="text-primary_white text-lg">{{ __('class-learn.share') }}</p>
    </div>
    <div class="relative mt-3 flex items-end" x-data="{ showProfile: false }">
        <div class="bg-primary_white animate-fade-up absolute -top-[95px] flex w-full flex-col gap-y-2 rounded-xl p-2 shadow-xl"
            x-transition x-show="showProfile" x-cloak @click.away="showProfile = false">
            <a class="text-secondary_black/70 bg-primary_white hover:bg-secondary_black/15 hover:text-primary_white w-full cursor-pointer rounded-md p-1 text-center font-bold transition-colors"
                href="{{ route('settings.profile') }}">{{ __('welcome.profile') }}</a>
            <div @click="$dispatch('getout-class')"
                class="hover:bg-accent_red/15 w-full cursor-pointer rounded-md p-1 transition-all">
                <button type="submit" class="flex w-full cursor-pointer flex-row justify-center gap-x-1">
                    <svg class="text-accent_red w-[20px]" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.5 9.56757V14.4324C3.5 16.7258 3.5 17.8724 4.22161 18.5849C4.87719 19.2321 5.89578 19.2913 7.81846 19.2968C7.71686 18.6224 7.69563 17.8168 7.69029 16.8689C7.68802 16.4659 8.01709 16.1374 8.42529 16.1351C8.83348 16.1329 9.16624 16.4578 9.16851 16.8608C9.17451 17.9247 9.20249 18.6789 9.30898 19.2512C9.41158 19.8027 9.57634 20.1219 9.81626 20.3588C10.089 20.6281 10.4719 20.8037 11.1951 20.8996C11.9395 20.9985 12.9261 21 14.3407 21H15.3262C16.7407 21 17.7273 20.9985 18.4717 20.8996C19.1949 20.8037 19.5778 20.6281 19.8505 20.3588C20.1233 20.0895 20.3011 19.7114 20.3983 18.9975C20.4984 18.2626 20.5 17.2885 20.5 15.8919V8.10811C20.5 6.71149 20.4984 5.73743 20.3983 5.0025C20.3011 4.28855 20.1233 3.91048 19.8505 3.6412C19.5778 3.37192 19.1949 3.19635 18.4717 3.10036C17.7273 3.00155 16.7407 3 15.3262 3H14.3407C12.9261 3 11.9395 3.00155 11.1951 3.10036C10.4719 3.19635 10.089 3.37192 9.81626 3.6412C9.57634 3.87807 9.41158 4.19728 9.30898 4.74877C9.20249 5.32112 9.17451 6.07525 9.16851 7.1392C9.16624 7.54221 8.83348 7.8671 8.42529 7.86485C8.01709 7.86261 7.68802 7.53409 7.69029 7.13107C7.69563 6.18322 7.71686 5.37758 7.81846 4.70325C5.89578 4.70867 4.87719 4.76789 4.22161 5.41515C3.5 6.12759 3.5 7.27425 3.5 9.56757ZM5.93385 12.516C5.6452 12.231 5.6452 11.769 5.93385 11.484L7.90484 9.53806C8.19348 9.25308 8.66147 9.25308 8.95011 9.53806C9.23876 9.82304 9.23876 10.2851 8.95011 10.5701L8.24088 11.2703L15.3259 11.2703C15.7341 11.2703 16.0651 11.597 16.0651 12C16.0651 12.403 15.7341 12.7297 15.3259 12.7297L8.24088 12.7297L8.95011 13.4299C9.23876 13.7149 9.23876 14.177 8.95011 14.4619C8.66147 14.7469 8.19348 14.7469 7.90484 14.4619L5.93385 12.516Z"
                                fill="currentColor"></path>
                        </g>
                    </svg>
                    <p class="text-accent_red font-bold">{{ __('welcome.logout') }}</p>
            </div>
        </div>
        <div class="bg-secondary_blue hover:bg-secondary_blue/70 flex w-full cursor-pointer flex-row items-center justify-between rounded-xl p-2 transition-all"
            @click="showProfile = !showProfile">
            <div class="flex flex-row items-center gap-x-2">
                <div class="h-[35px] w-[35px] overflow-hidden rounded-full">
                    <img src="{{ auth()->user()->profile_photo_path }}" alt="{{ auth()->user()->name }}" />
                </div>
                <p class="text-primary_white w-[140px] overflow-hidden truncate whitespace-nowrap">
                    {{ auth()->user()->name }}</p>
            </div>

            <div
                class="border-white-500 flex h-[20px] w-[20px] cursor-pointer items-center justify-center rounded-full border-2">
                <div class="bg-primary_white h-[14px] w-[14px] rounded-full transition-all"
                    :class="{ 'opacity-0': showProfile, 'opacity-100': !showProfile }"></div>
            </div>
        </div>
    </div>
</div>
