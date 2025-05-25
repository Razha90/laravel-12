<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\AplicationLater;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\NotificationController;
use App\Events\AplicationNotification;

new #[Layout('components.layouts.app-sidebar')] class extends Component {
    public $aplication = [];
    public function mount()
    {
        $this->getAplication();
    }

    public function getAplication($page = 1, $search = '', $order = 'asc', $status = 'pending')
    {
        try {
            if ($status == 'pending') {
                $fieldStatus = "FIELD(status, 'pending', 'approved', 'rejected')";
            } elseif ($status == 'approved') {
                $fieldStatus = "FIELD(status, 'approved', 'pending', 'rejected')";
            } elseif ($status == 'rejected') {
                $fieldStatus = "FIELD(status, 'rejected', 'approved', 'pending')";
            }
            $this->aplication = AplicationLater::where('full_name', 'like', '%' . $search . '%')
                ->orderByRaw($fieldStatus)
                ->orderBy('created_at', $order)
                ->paginate(10, ['*'], 'page', $page)
                ->toArray();
        } catch (\Throwable $th) {
            Log::error('Aplication Data: ' . $th->getMessage());
            $this->dispatch('failed', [
                'message' => __('aplication.aplication_error'),
            ]);
        }
    }

    public function approve($id)
    {
        try {
            $aplication = AplicationLater::find($id);
            $aplication->status = 'approved';
            $aplication->approved_at = now();
            $aplication->save();

            $user = $aplication->user;
            $user->role = $aplication->request_role;
            $user->save();

            $this->dispatch('success', [
                'message' => __('aplication.aplication_approve'),
            ]);

            $this->getAplication();
        } catch (\Throwable $th) {
            Log::error('Aplication Approve: ' . $th->getMessage());
            $this->dispatch('failed', [
                'message' => __('admin.aplication_error'),
            ]);
        }
    }

    public function reject($id)
    {
        try {
            $aplication = AplicationLater::find($id);
            $aplication->status = 'rejected';
            $aplication->rejected_at = now();
            $aplication->save();

            event(new AplicationNotification($aplication, $aplication->user_id));

            $dataNotif = [
                'title' => 'New Aplication Rejected',
                'body' => 'Your aplication has been rejected, let check your aplication for more information.',
                'user_id' => $aplication->user_id,
            ];

            try {
                $notificationController = new NotificationController();
                $notificationController->sendNotification($dataNotif);
            } catch (\Throwable $th) {
                Log::error('ClassroomLearn Error Allowed Teacher Notification: ' . $th->getMessage());
            }

            $this->dispatch('success', [
                'message' => __('aplication.aplication_approve'),
            ]);
            $this->getAplication();
        } catch (\Throwable $th) {
            Log::error('Aplication Reject: ' . $th->getMessage());
            $this->dispatch('failed', [
                'message' => __('aplication.aplication_error'),
            ]);
        }
    }

    public function deleteAplication($id)
    {
        try {
            $aplication = AplicationLater::find($id);
            $aplication->delete();
            // event(new AplicationNotification(['id' => $aplication->id], $aplication->user_id));

            $this->dispatch('success', [
                'message' => __('admin.aplication_delete'),
            ]);
            $this->getAplication();
        } catch (\Throwable $th) {
            Log::error('Aplication Delete: ' . $th->getMessage());
            $this->dispatch('failed', [
                'message' => __('aplication.aplication_error'),
            ]);
        }
    }
}; ?>

<flux:main x-data="initAplication" class="bg-white! overflow-auto" x-init="init">
<flux:sidebar.toggle class="text-secondary_blue! lg:hidden" icon="bars-2" inset="left" />

    <flux:heading size="xl" level="1" class="text-secondary_blue!">{{ __('admin.aplication_letter') }}
    </flux:heading>

    <div class="flex mb-5 flex-row flex-wrap items-center gap-x-3 gap-y-3">
        <div class="mt-5 max-w-md">
            <label for="default-search"
                class="sr-only mb-2 text-sm font-medium text-gray-900 dark:text-white">Search</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
                <input type="search" id="default-search" x-model.debounce.500ms="search"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-4 ps-10 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500"
                    placeholder="{{ __('admin.user.name') }}" required />
            </div>
        </div>
        <div class="relative select-none">
            <div @click="showDropdown = true"
                class="hover:bg-secondary_blue/15 flex cursor-pointer flex-row gap-x-2 rounded-md px-4 py-2 shadow-md">
                <template x-if="sort == 'desc'">
                    <div class="text-secondary_blue text-base">
                        {{ __('classroom.old') }}
                    </div>
                </template>
                <template x-if="sort == 'asc'">
                    <div class="text-secondary_blue text-base">
                        {{ __('classroom.new') }}
                    </div>
                </template>
                <div>
                    <svg class="animate-fade w-[20px]" x-show="sort == 'asc'" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path d="M4 8H13" stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M6 13H13" stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M8 18H13" stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M17 20V4L20 8" stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round"></path>
                        </g>
                    </svg>
                    <svg x-show="sort == 'desc'" class="animate-fade w-[20px]" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path d="M4 16L13 16" stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M6 11H13" stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M8 6L13 6" stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M17 4L17 20L20 16" stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round"></path>
                        </g>
                    </svg>
                </div>
            </div>
            <div class="absolute top-[110%] z-30 flex flex-col gap-y-3 rounded-md bg-white px-4 py-2 shadow-md"
                x-show="showDropdown" @click.away="closeDropdown">
                <div class="text-secondary_blue hover:bg-secondary_blue/20 cursor-pointer rounded-md px-3 py-1 transition-opacity"
                    @click="closeDropdown(); sort = 'asc'; ">
                    {{ __('classroom.new') }}
                </div>
                <div class="text-secondary_blue hover:bg-secondary_blue/20 cursor-pointer rounded-md px-3 py-1 transition-opacity"
                    @click="closeDropdown(); sort = 'desc';">
                    {{ __('classroom.old') }}
                </div>
            </div>
        </div>
        <div>
            <div x-show="statusSort == 'pending'"
                class="select-none animate-fade flex cursor-pointer flex-row gap-x-2 rounded-md border-2 border-yellow-400 bg-yellow-300/20 px-4 py-2 transition-opacity hover:opacity-70"
                @click="statusSort = 'approved'">
                <p class="text-base text-yellow-500">{{ __('classroom.pending') }}</p>
                <svg class="w-[20px]" fill="#fcc800" viewBox="0 0 24 24" id="Outline"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <title>194 restore</title>
                        <path
                            d="M12,6a1,1,0,0,0-1,1v5a1,1,0,0,0,.293.707l3,3a1,1,0,0,0,1.414-1.414L13,11.586V7A1,1,0,0,0,12,6Z M23.812,10.132A12,12,0,0,0,3.578,3.415V1a1,1,0,0,0-2,0V5a2,2,0,0,0,2,2h4a1,1,0,0,0,0-2H4.827a9.99,9.99,0,1,1-2.835,7.878A.982.982,0,0,0,1,12a1.007,1.007,0,0,0-1,1.1,12,12,0,1,0,23.808-2.969Z">
                        </path>
                    </g>
                </svg>
            </div>
            <div x-show="statusSort == 'approved'"
                class="select-none animate-fade flex cursor-pointer flex-row gap-x-2 rounded-md border-2 border-green-500 bg-green-300/20 px-4 py-2 transition-opacity hover:opacity-70"
                @click="statusSort = 'rejected'">
                <p class="text-base text-green-500">{{ __('classroom.approved') }}</p>
                <svg class="w-[20px]" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 344.963 344.963" xml:space="preserve"
                    fill="#00c951">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <g>
                            <path style="fill:#00c951;"
                                d="M321.847,86.242l-40.026-23.11l-23.104-40.02h-46.213l-40.026-23.11l-40.026,23.11H86.239 l-23.11,40.026L23.11,86.242v46.213L0,172.481l23.11,40.026v46.213l40.026,23.11l23.11,40.026h46.213l40.02,23.104l40.026-23.11 h46.213l23.11-40.026l40.026-23.11v-46.213l23.11-40.026l-23.11-40.026V86.242H321.847z M156.911,243.075 c-3.216,3.216-7.453,4.779-11.671,4.72c-4.219,0.06-8.455-1.504-11.671-4.72l-50.444-50.444c-6.319-6.319-6.319-16.57,0-22.889 l13.354-13.354c6.319-6.319,16.57-6.319,22.889,0l25.872,25.872l80.344-80.35c6.319-6.319,16.57-6.319,22.889,0l13.354,13.354 c6.319,6.319,6.319,16.57,0,22.889L156.911,243.075z">
                            </path>
                        </g>
                    </g>
                </svg>
            </div>
            <div x-show="statusSort == 'rejected'"
                class="select-none animate-fade flex cursor-pointer flex-row gap-x-2 rounded-md border-2 border-red-500 bg-red-300/20 px-4 py-2 transition-opacity hover:opacity-70"
                @click="statusSort = 'pending'">
                <p class="text-base text-red-500">{{ __('classroom.rejected') }}</p>
                <svg fill="#fb2c36" class="w-[20px]" version="1.1" id="Capa_1"
                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 329.328 329.328" xml:space="preserve">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path
                            d="M164.666,0C73.871,0,0.004,73.871,0.004,164.672c0.009,90.792,73.876,164.656,164.662,164.656 c90.793,0,164.658-73.865,164.658-164.658C329.324,73.871,255.459,0,164.666,0z M164.666,30c31.734,0,60.933,11.042,83.975,29.477 L59.478,248.638c-18.431-23.04-29.471-52.237-29.474-83.967C30.004,90.413,90.413,30,164.666,30z M164.666,299.328 c-31.733,0-60.934-11.042-83.977-29.477L269.854,80.691c18.431,23.043,29.471,52.244,29.471,83.979 C299.324,238.921,238.917,299.328,164.666,299.328z">
                        </path>
                    </g>
                </svg>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-y-3">
        <template x-if="aplications.data.length != 0">
            <template x-for="(aplication, index) in aplications.data">
                <div class="relative rounded-xl border-2 p-3"
                    x-bind:class="{
                        'border-red-300 bg-red-300/20': aplication.status == 'rejected',
                        'border-green-300 bg-green-300/20': aplication.status ==
                            'approved',
                        'border-yellow-300 bg-yellow-300/20': aplication.status == 'pending'
                    }">
                    <template x-if="aplication.status == 'pending'">
                        <div class="absolute right-3 top-2 text-right">
                            <p x-text="aplication.status" class="text-yellow-400"></p>
                            <p x-text="times(aplication.updated_at)" class="text-yellow-400"> </p>
                        </div>
                    </template>

                    <template x-if="aplication.status == 'approved'">
                        <div class="absolute right-3 top-2 text-right">
                            <p x-text="aplication.status" class="text-green-400">
                            </p>
                            <p x-text="times(aplication.updated_at)" class="text-green-400">
                            </p>
                        </div>
                    </template>

                    <template x-if="aplication.status == 'rejected'">
                        <div class="absolute right-3 top-2 text-right">
                            <p x-text="aplication.status" class="text-red-400"></p>
                            <p x-text="times(aplication.updated_at)" class="text-red-400"> </p>
                        </div>
                    </template>

                    <div class="flex flex-row flex-wrap gap-x-12 gap-y-4">
                        <div class="flex flex-col gap-y-4">
                            <div>
                                <h2 class="text-secondary_blue text-lg font-bold">
                                    {{ __('aplication.user.name') }}</h2>
                                <p x-text="aplication.full_name" class="text-secondary_blue text-base">
                                </p>
                            </div>
                            <div>
                                <h2 class="text-secondary_blue text-lg font-bold">
                                    {{ __('aplication.user.origin') }}</h2>
                                <p x-text="aplication.origin" class="text-secondary_blue text-base">
                                </p>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-secondary_blue text-lg font-bold">{{ __('aplication.request_role') }}</h2>
                            <p class="flex flex-col items-center">
                                <span x-text="aplication.current_role"
                                    class="text-secondary_blue text-base italic"></span>
                                <span class="text-secondary_blue"><svg class="w-[25px] rotate-90" viewBox="0 0 24 24"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M6 12H18M18 12L13 7M18 12L13 17" stroke="#2867A4"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                        </g>
                                    </svg></span>
                                <span x-text="aplication.request_role"
                                    class="text-secondary_blue text-base italic"></span>
                            </p>
                        </div>

                    </div>

                    <div class="mt-10">
                        <h2 class="text-secondary_blue text-lg font-bold">{{ __('aplication.request_message') }}</h2>
                        <p x-text="aplication.message" class="text-secondary_blue text-base">
                        </p>
                    </div>

                    <template x-if="aplication.status == 'pending'">
                        <div class="mb-3">
                            <button @click="approved(aplication.id)"
                                class="mt-5 cursor-pointer rounded-lg bg-green-500 px-4 py-2 text-white hover:bg-green-600">
                                {{ __('aplication.approve') }}
                            </button>
                            <button @click="rejected(aplication.id)"
                                class="mt-5 cursor-pointer rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                                {{ __('aplication.reject') }}
                            </button>
                        </div>
                    </template>

                    <template x-if="aplication.status == 'approved' || aplication.status == 'rejected'">
                        <div class="text-right">
                            <button @click="$wire.deleteAplication(aplication.id)"
                                class="cursor-pointer rounded-xl bg-red-500 px-4 py-2 text-white transition-opacity hover:opacity-50">
                                {{ __('aplication.delete') }}
                            </button>
                        </div>
                    </template>

                </div>
            </template>
        </template>
        <div x-show="aplications.data.length < 1"
            class="text-secondary_blue bg-secondary_blue/10 w-full p-4 text-center">
            {{ __('admin.not_found') }}
        </div>
    </div>

    <div class="mt-5 w-full text-center">
        <template x-if="aplications.data.length != 0 && aplications.last_page > 1">
            <ul class="inline-flex h-10 h-10 select-none -space-x-px text-base">
                <li>
                    <a @click.prevent="Number(aplications.current_page) == 1 ? null : prevPage()"
                        class="ms-0 flex h-10 items-center justify-center rounded-s-lg border border-e-0 border-gray-300 bg-white px-4 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                        x-bind:class="{
                            'cursor-not-allowed': Number(aplications.current_page) == 1,
                            'cursor-pointer': Number(aplications
                                .current_page) != 1
                        }">{{ __('admin.previous') }}
                    </a>
                </li>

                <template x-for="(link, index) in pagination" :key="index">
                    <li>
                        <a @click="updatePagePagination(link)"
                            class="flex h-10 cursor-pointer items-center justify-center border border-gray-300 px-4 leading-tight hover:bg-gray-100 hover:text-gray-700"
                            x-bind:class="{
                                'bg-secondary_blue/20 text-secondary_blue font-bold': Number(aplications
                                        .current_page) ==
                                    link,
                                'bg-white text-gray-500': !(Number(aplications.current_page) == link)
                            }"
                            x-html="link"></a>
                    </li>
                </template>

                <li>
                    <a @click.prevent="aplications.current_page == aplications.last_page ? null : nextPage()"
                        class="flex h-10 items-center justify-center rounded-e-lg border border-gray-300 bg-white px-4 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                        x-bind:class="{
                            'cursor-not-allowed': aplications.current_page == aplications.last_page,
                            'cursor-pointer': aplications
                                .current_page != aplications.last_page
                        }">{{ __('admin.next') }}</a>
                </li>

            </ul>
        </template>
    </div>

</flux:main>

<script>
    function initAplication() {
        return {
            aplications: @entangle('aplication').live,
            statusSort: 'pending',
            initReturn: false,
            search: "",
            sort: 'asc',
            pagination: [],
            stopInit: false,
            page: 1,
            showDropdown: false,
            closeDropdown() {
                this.showDropdown = false;
            },
            openDropdown() {
                this.showDropdown = true;
            },
            updatePagination(currentPage, lastPage) {
                const maxVisible = 7;
                let half = Math.floor(maxVisible / 2);
                let start = Math.max(1, Math.min(currentPage - half, lastPage - maxVisible + 1));
                let end = Math.min(start + maxVisible - 1, lastPage);
                this.pagination = Array.from({
                    length: end - start + 1
                }, (_, i) => start + i);
            },
            updatePagePagination(page) {
                if (page == this.aplications.current_page) return;
                this.page = page;
                this.updatePage();
            },
            updatePage() {
                this.$wire.getAplication(this.page, this.search, this.sort, this.statusSort);
            },
            init() {
                if (this.stopInit) return;
                this.stopInit = true;
                this.updatePagination(this.aplications.current_page, this.aplications.last_page);
                let oldSearch = '';
                this.$watch('search', (value) => {
                    if (value == oldSearch) return;
                    oldSearch = value;
                    this.updatePage();
                });
                let oldPage = 'asc';
                this.$watch('sort', (value) => {
                    if (value == oldPage) return;
                    oldPage = value;
                    this.updatePage();
                });

                let debounceTimeout;
                let oldSort = 'pending';
                this.$watch('statusSort', (value) => {

                    clearTimeout(debounceTimeout);
                    debounceTimeout = setTimeout(() => {
                        this.updatePage();
                    }, 500);
                });
            },
            approved(id) {
                this.$wire.approve(id);
            },
            rejected(id) {
                this.$wire.reject(id);
            },
            times(datetime) {
                const date = new Date(datetime);
                const twoDigit = (num) => num.toString().padStart(2, "0");
                const formatted =
                    `${twoDigit(date.getDate())}/${twoDigit(date.getMonth() + 1)}/${date.getFullYear()} ${twoDigit(date.getHours())}:${twoDigit(date.getMinutes())}`;
                return formatted;
            }

        }
    }
</script>
