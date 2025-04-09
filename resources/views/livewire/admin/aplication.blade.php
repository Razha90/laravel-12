<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\AplicationLater;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\NotificationController;
use App\Events\AplicationNotification;

new #[Layout('components.layouts.app-sidebar')] class extends Component {
    public $aplication = [];
    public $status = 'pending';
    public $order = 'desc';
    public $search = '';
    public function mount()
    {
        $this->getAplication();
    }

    public function getAplication()
    {
        try {
            if ($this->status == 'pending') {
                $fieldStatus = "FIELD(status, 'pending', 'approved', 'rejected')";
            } elseif ($this->status == 'approved') {
                $fieldStatus = "FIELD(status, 'approved', 'pending', 'rejected')";
            } elseif ($this->status == 'rejected') {
                $fieldStatus = "FIELD(status, 'rejected', 'approved', 'pending')";
            }
            $this->aplication = AplicationLater::where('full_name', 'like', '%' . $this->search . '%')
                ->orderByRaw($fieldStatus)
                ->orderBy('created_at', $this->order)
                ->paginate(10)
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

            event(new AplicationNotification($aplication, $aplication->user_id));

            $dataNotif = [
                'title' => 'New Aplication Approved',
                'body' => 'Your aplication has been approved, let check your aplication for more information.',
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

    public function deleteAplication($id) {
        try {
            $aplication = AplicationLater::find($id);
            $aplication->delete();
            event(new AplicationNotification(["id" => $aplication->id], $aplication->user_id));
            
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

<flux:main x-data="initAplication" class="bg-white!" x-init="init">
    <flux:heading size="xl" level="1" class="text-secondary_blue!">{{ __('admin.aplication_letter') }}
    </flux:heading>

    <div class="my-5 max-w-md" x-init="console.log(aplications)">
        <label for="default-search" class="sr-only mb-2 text-sm font-medium text-gray-900 dark:text-white">Search</label>
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
                            <p x-text="times(aplication.created_at)" class="text-yellow-400"> </p>
                        </div>
                    </template>

                    <template x-if="aplication.status == 'approved'">
                        <div class="absolute right-3 top-2 text-right">
                            <p x-text="aplication.status" class="text-green-400">
                            </p>
                            <p x-text="times(aplication.created_at)" class="text-green-400">
                            </p>
                        </div>
                    </template>

                    <template x-if="aplication.status == 'rejected'">
                        <div class="absolute right-3 top-2 text-right">
                            <p x-text="aplication.status" class="text-red-400"></p>
                            <p x-text="times(aplication.created_at)" class="text-red-400"> </p>
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
                                            <path d="M6 12H18M18 12L13 7M18 12L13 17" stroke="#2867A4" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
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
    </div>

    <div x-cloak x-data="{ alert: false, message: '' }"
        x-on:success.window="(event) => {
        alert = true;
        message = event.detail[0].message;
        
        setTimeout(() => {
            alert = false;
        }, 4000);
    }"
        x-show="alert" x-transition id="toast-success"
        class="absolute bottom-3 left-3 z-30 mb-4 flex w-full max-w-xs items-center rounded-lg bg-white p-4 text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400"
        role="alert">
        <div
            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-500 dark:bg-green-800 dark:text-green-200">
            <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
            </svg>
            <span class="sr-only">Check icon</span>
        </div>
        <div class="ms-3 text-sm font-normal" x-text="message"></div>
        <button @click="alert = false" type="button"
            class="-mx-1.5 -my-1.5 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 focus:ring-2 focus:ring-gray-300 dark:bg-gray-800 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-white"
            data-dismiss-target="#toast-success" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
            </svg>
        </button>
    </div>

    <div x-cloak x-data="{ alert: false, message: '' }"
        x-on:failed.window="(event) => {
        alert = true;
        message = event.detail[0].message;
        
        setTimeout(() => {
            alert = false;
        }, 4000);
    }"
        id="toast-danger" x-show="alert" x-transition
        class="absolute bottom-3 left-3 z-30 mb-4 flex w-full max-w-xs items-center rounded-lg bg-white p-4 text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400"
        role="alert">
        <div
            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-500 dark:bg-red-800 dark:text-red-200">
            <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
            </svg>
            <span class="sr-only">Error icon</span>
        </div>
        <div class="ms-3 text-sm font-normal" x-text="message"></div>
        <button @click="alert = false" type="button"
            class="-mx-1.5 -my-1.5 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 focus:ring-2 focus:ring-gray-300 dark:bg-gray-800 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-white"
            data-dismiss-target="#toast-danger" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
            </svg>
        </button>
    </div>
</flux:main>

<script>
    function initAplication() {
        return {
            aplications: @entangle('aplication').live,
            statusSort: @entangle('status').live,
            initReturn: false,
            search: @entangle('search').live,
            sort: @entangle('order').live,
            init() {
                this.$watch('search', (value) => {
                    this.$wire.getAplication();
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
