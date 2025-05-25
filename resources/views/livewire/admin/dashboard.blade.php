<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;
use App\Models\RandomAvatar;
use Carbon\Carbon;

new #[Layout('components.layouts.app-sidebar')] class extends Component {
    use WithPagination;
    use WithFileUploads;

    public $users;
    public $search = '';
    public $image;
    public $randomAvatar = [];

    public function mount()
    {
        $this->updateUser(1);
        $this->getRandomAvatar();
    }

    public function getRandomAvatar()
    {
        $this->randomAvatar = RandomAvatar::all()->toArray();
    }

    public function searching()
    {
        $this->updateUser(1);
    }

    public function updateUser($page)
    {
        $this->users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('origin', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->paginate(10, ['*'], 'page', $page)
            ->toArray();
    }

    public function avatarUpdated($checkImage, $id, $path, $message, $title, $currentPage)
    {
        if ($checkImage) {
            try {
                $this->Validate([
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                ]);

                $filename = $this->image->store(path: 'images/profile', options: 'public');
                $filename = str_replace('public/', '', $filename);
                $image = Storage::url($filename);

                $user = User::find($id);
                $user->profile_photo_path = $image;
                $user->save();
            } catch (\Throwable $th) {
                $errorMessage = [
                    'image.required' => __('profile.image_required'),
                    'image.image' => __('profile.image_image'),
                    'image.mimes' => __('profile.image_mimes'),
                    'image.max' => __('profile.image_max'),
                ];
                $messageKey = $th->getMessage();
                if (array_key_exists($messageKey, $errorMessage)) {
                    $this->dispatch('failed', ['message' => $errorMessage[$messageKey]]);
                } else {
                    $this->dispatch('failed', ['message' => __('profile.default_error')]);
                }
                Log::error('Dashboard Image ', [
                    'error' => $th->getMessage(),
                ]);
                return;
            }
        } else {
            try {
                $validate = Validator::make(
                    [
                        'path' => $path,
                    ],
                    [
                        'path' => 'required',
                    ],
                    [
                        'path.required' => __('admin.image_required'),
                    ],
                );
                if ($validate->fails()) {
                    $this->dispatch('failed', [
                        'message' => $validate->errors()->first(),
                    ]);
                    return;
                }
                $user = User::find($id);
                $user->profile_photo_path = $path;
                $user->save();
            } catch (\Throwable $th) {
                $this->dispatch('failed', ['message' => __('profile.image_failed')]);
                Log::error('Error Dashboard Image ', [
                    'error' => $th->getMessage(),
                ]);
                return;
            }
        }
        $this->updateUser($currentPage);
        $this->sendNotification($id, $message, $title);
    }

    public function sendNotification($userId, $message, $title)
    {
        try {
            $data = [
                'id' => $userId,
                'message' => $message,
                'title' => $title,
            ];

            $validate = Validator::make(
                $data,
                [
                    'id' => 'required|exists:users,id',
                    'message' => 'required|string|max:5000|min:10',
                    'title' => 'required|string|max:255|min:5',
                ],
                [
                    'id.required' => __('admin.id.required'),
                    'id.exists' => __('admin.id.exists'),
                    'message.required' => __('admin.message.required'),
                    'message.string' => __('admin.message.string'),
                    'message.max' => __('admin.message.max'),
                    'message.min' => __('admin.message.min'),
                    'title.required' => __('admin.title.required'),
                    'title.string' => __('admin.title.string'),
                    'title.max' => __('admin.title.max'),
                    'title.min' => __('admin.title.min'),
                ],
            );

            if ($validate->fails()) {
                $this->dispatch('failed', [
                    'message' => $validate->errors()->first(),
                ]);
                return;
            }

            $dataNotif = [
                'title' => $validate->validated()['title'],
                'body' => $validate->validated()['message'],
                'user_id' => $userId,
            ];

            try {
                $notificationController = new NotificationController();
                $notificationController->sendNotification($dataNotif);
            } catch (\Throwable $th) {
                Log::error('ClassroomLearn Error Allowed Teacher Notification: ' . $th->getMessage());
            }

            $this->dispatch('success', [
                'message' => __('admin.notification_success'),
            ]);
            return;
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Error Allowed Teacher Notification: ' . $th->getMessage());
            $this->dispatch('failed', [
                'message' => __('admin.notification_failed'),
            ]);
        }
    }

    public function nameUpdated($id, $name, $message, $title, $currentPage)
    {
        try {
            $validator = Validator::make(
                ['name' => $name],
                [
                    'name' => 'required|string|max:255|min:3',
                ],
                [
                    'name.required' => __('profile.name_required'),
                    'name.string' => __('profile.name_string'),
                    'name.max' => __('profile.name_max'),
                    'name.min' => __('profile.name_min'),
                ],
            );
            if ($validator->fails()) {
                $this->dispatch('failed', ['message' => $validator->errors()->first()]);
                $this->dispatch('name-check', ['condition' => false]);
                return;
            }

            $user = User::find($id);
            $user->name = $validator->validated()['name'];
            $user->save();
        } catch (\Throwable $th) {
            Log::error('Dashboard Name ', [
                'error' => $th->getMessage(),
            ]);
            return;
        }
        $this->updateUser($currentPage);
        $this->sendNotification($id, $message, $title);
    }

    public function roleUpdated($id, $role, $message, $title, $currentPage)
    {
        try {
            $validator = Validator::make(
                ['role' => $role],
                [
                    'role' => 'required|string|in:admin,teacher,guest|max:255|min:3',
                ],
                [
                    'role.required' => __('profile.role_required'),
                    'role.string' => __('profile.role_string'),
                    'role.max' => __('profile.role_max'),
                    'role.min' => __('profile.role_min'),
                    'role.in' => __('profile.role_in'),
                ],
            );
            if ($validator->fails()) {
                $this->dispatch('failed', ['message' => $validator->errors()->first()]);
                return;
            }

            $user = User::find($id);
            $newRole = $validator->validated()['role'];

            if ($user->role === $newRole) {
                $this->dispatch('failed', ['message' => __('admin.role_same')]);
                return;
            }
            $user->role = $newRole;
            $user->save();
        } catch (\Throwable $th) {
            Log::error('Dashboard Role ', [
                'error' => $th->getMessage(),
            ]);
            return;
        }
        $this->updateUser($currentPage);
        $this->sendNotification($id, $message, $title);
    }

    public function birthUpdated($id, $birth, $message, $title, $currentPage)
    {
        try {
            $validator = Validator::make(
                ['birth' => $birth],
                [
                    'birth' => 'required|date',
                ],
                [
                    'birth.required' => __('profile.birth_required'),
                    'birth.date' => __('profile.birth_date'),
                ],
            );
            if ($validator->fails()) {
                $this->dispatch('failed', ['message' => $validator->errors()->first()]);
                return;
            }

            $user = User::find($id);
            $newBirth = $validator->validated()['birth'];

            $existingBirth = Carbon::parse($user->birth_date)->toDateString();
            $newBirthDate = Carbon::parse($newBirth)->toDateString();

            if ($existingBirth === $newBirthDate) {
                $this->dispatch('failed', ['message' => __('profile.birth_same')]);
                return;
            }

            $user->birth_date = $newBirth;
            $user->save();
        } catch (\Throwable $th) {
            Log::error('Dashboard Birth Date ', [
                'error' => $th->getMessage(),
            ]);
            return;
        }
        $this->updateUser($currentPage);
        $this->sendNotification($id, $message, $title);
    }

    public function originUpdated($id, $origin, $message, $title, $currentPage)
    {
        try {
            $validator = Validator::make(
                ['origin' => $origin],
                [
                    'origin' => 'required|string|max:255|min:3',
                ],
                [
                    'origin.required' => __('profile.origin_required'),
                    'origin.string' => __('profile.origin_string'),
                    'origin.max' => __('profile.origin_max'),
                    'origin.min' => __('profile.origin_min'),
                ],
            );
            if ($validator->fails()) {
                $this->dispatch('failed', ['message' => $validator->errors()->first()]);
                return;
            }

            $user = User::find($id);
            $newOrigin = $validator->validated()['origin'];

            if ($user->origin === $newOrigin) {
                $this->dispatch('failed', ['message' => __('admin.origin_same')]);
                return;
            }
            $user->origin = $newOrigin;
            $user->save();
        } catch (\Throwable $th) {
            Log::error('Dashboard Origin ', [
                'error' => $th->getMessage(),
            ]);
            return;
        }
        $this->updateUser($currentPage);
        $this->sendNotification($id, $message, $title);
    }
}; ?>

<flux:main class="h-full overflow-auto bg-white" x-data="initBar" x-init="init">
    <div x-cloak x-data="{ alert: false, message: '' }"
        x-on:failed-center.window="(event) => {
        alert = true;
        message = event.detail[0].message;
        
        setTimeout(() => {
            alert = false;
        }, 4000);
    }"
        x-show="alert" x-transition
        class="absolute left-3 top-3 z-50 mb-4 flex w-full max-w-xs items-center rounded-lg bg-white p-4 text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400"
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

    <div x-show="showNotification" x-cloak
        class="bg-secondary_black/20 animate-fade fixed left-0 right-0 top-0 z-30 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-md p-4" @click.away="showNotification = false">
            <!-- Modal content -->
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5 dark:border-gray-600">
                    <h3 class="text-secondary_blue text-xl font-semibold">
                        {{ __('admin.send.warning') }}
                    </h3>
                    <button type="button" @click="showNotification = false"
                        class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="crud-modal">
                        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <div class="p-4">
                    <div>
                        <label for="title"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.title') }}</label>
                        <input type="text" x-model="notificationTitle"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"
                            id="title" />
                    </div>
                    <div class="mt-5">
                        <label for="message"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.message') }}</label>
                        <textarea rows="6" x-model="notificationMessage" id="message"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"></textarea>
                    </div>
                    <div class="mt-4 w-full text-right">
                        <button @click="senderNotification(); showNotification = false;"
                            class="bg-secondary_blue hover:border-secondary_blue hover:text-secondary_blue cursor-pointer rounded-lg border px-5 py-2 text-white transition-all hover:bg-white">{{ __('admin.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="editImage" x-cloak
        class="bg-secondary_black/20 animate-fade fixed left-0 right-0 top-0 z-30 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-md p-4" @click.away="closeEditImage">
            <!-- Modal content -->
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5 dark:border-gray-600">
                    <h3 class="text-secondary_blue text-xl font-semibold">
                        {{ __('admin.alert.image') }}
                    </h3>
                    <button type="button" @click="closeEditImage"
                        class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="crud-modal">
                        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-4">
                    <div class="relative flex flex-col items-center justify-center">
                        <div class="relative my-3 h-32 w-32">
                            <template x-if="imageUrl">
                                <img x-bind:src="imageUrl" alt="Preview"
                                    class="h-32 w-32 rounded-full object-cover" loading="lazy">
                            </template>
                            <flux:icon.trash x-show="imageUrl" variant="solid"
                                @click="imageUrl = null; editImagePicking = false;"
                                class="absolute! animate-fade bottom-0 right-0 z-30 size-6 cursor-pointer text-red-500 transition-opacity hover:opacity-70" />
                        </div>

                        <!-- <div class="my-3 h-32 w-32" x-show="!imageUrl"></div> -->

                        <div @click="openFile"
                            class="absolute cursor-pointer rounded-full border border-gray-500 bg-gray-400/20 p-3 transition-opacity hover:opacity-50">
                            <flux:icon.plus class="size-5 text-gray-700" />
                            <input type="file" x-ref="fileInput" accept=".jpeg, .jpg, .png, .svg, .webp"
                                class="hidden" wire:model="image" @change="previewImage">
                        </div>


                    </div>
                    <div class="flex flex-row flex-wrap items-center justify-center gap-x-3">
                        <template x-for="(image, index) in randomAvatar" :key="index">
                            <div class="hover:border-secondary_blue h-[30px] w-[30px] cursor-pointer overflow-hidden rounded-full border border-white transition-all"
                                @click="clickImage(image.path)">
                                <img x-bind:src="image.path" x-bind:alt="image.name"
                                    class="h-full w-full object-cover" loading="lazy" />
                            </div>
                        </template>
                    </div>
                    <div>
                        <label for="title"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.title') }}</label>
                        <input type="text" x-model="notificationTitle"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"
                            id="title" />
                    </div>
                    <div class="mt-5">
                        <label for="message"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.message') }}</label>
                        <textarea rows="6" x-model="notificationMessage" id="message"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"></textarea>
                    </div>
                    <div class="mt-4 flex w-full justify-end">
                        <!-- <button @click="senderImage(); editImage = false;"
                            class="bg-secondary_blue hover:border-secondary_blue hover:text-secondary_blue cursor-pointer rounded-lg border px-5 py-2 text-white transition-all hover:bg-white">{{ __('admin.send') }}</button> -->
                        <button @click="senderImage(); closeEditImage();" wire:loading.attr="disabled"
                            wire:target="image"
                            class="bg-secondary_blue hover:border-secondary_blue hover:text-secondary_blue flex cursor-pointer items-center justify-center gap-2 rounded-lg border px-5 py-2 text-white transition-all hover:bg-white">

                            <!-- Saat tidak loading -->
                            <span wire:loading.remove wire:target="image">
                                {{ __('admin.send') }}
                            </span>

                            <!-- Saat loading -->
                            <div wire:loading wire:target="image" class="flex items-center gap-2">
                                <svg class="inline-block h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <span class="inline-block">
                                    {{ __('admin.upload') }}
                                </span>
                            </div>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="editName" x-cloak
        class="bg-secondary_black/20 animate-fade fixed left-0 right-0 top-0 z-30 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-md p-4" @click.away="closeEditName">
            <!-- Modal content -->
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5 dark:border-gray-600">
                    <h3 class="text-secondary_blue text-xl font-semibold">
                        {{ __('admin.alert.name') }}
                    </h3>
                    <button type="button" @click="closeEditName"
                        class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="crud-modal">
                        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-4">
                    <div class="my-4">
                        <label for="name" class="text-secondary_blue text-base font-bold">
                            {{ __('admin.alert.name.title') }}
                        </label>

                        <div class="mt-1 flex items-center gap-2">
                            <input type="text" x-model="nameUpdated"
                                class="border-secondary_blue focus:border-secondary_blue text-secondary_blue w-full rounded-md border px-3 py-2 text-sm focus:outline-none"
                                id="name" />

                            <button type="button" @click="generateRandomName()"
                                class="border-secondary_blue text-secondary_blue hover:bg-secondary_blue cursor-pointer whitespace-nowrap rounded-md border px-3 py-2 text-sm transition hover:text-white">
                                {{ __('admin.random') }}
                            </button>
                        </div>
                    </div>


                    <div>
                        <label for="title"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.title') }}</label>
                        <input type="text" x-model="notificationTitle"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"
                            id="title" />
                    </div>
                    <div class="mt-5">
                        <label for="message"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.message') }}</label>
                        <textarea rows="6" x-model="notificationMessage" id="message"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"></textarea>
                    </div>
                    <div class="mt-4 flex w-full justify-end">
                        <button @click="senderName()"
                            class="bg-secondary_blue hover:border-secondary_blue hover:text-secondary_blue cursor-pointer rounded-lg border px-5 py-2 text-white transition-all hover:bg-white">{{ __('admin.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="editRole" x-cloak
        class="bg-secondary_black/20 animate-fade fixed left-0 right-0 top-0 z-30 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-md p-4" @click.away="closeEditRole">
            <!-- Modal content -->
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5 dark:border-gray-600">
                    <h3 class="text-secondary_blue text-xl font-semibold">
                        {{ __('admin.alert.role') }}
                    </h3>
                    <button type="button" @click="closeEditRole"
                        class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="crud-modal">
                        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-4">
                    <div class="mb-4">
                        <label for="role"
                            class="text-secondary_blue mb-2 block font-semibold">{{ __('admin.choose_role') }}</label>
                        <select id="role" x-model="roleUpdated"
                            class="border-secondary_blue text-secondary_blue w-full rounded-md border px-3 py-2 text-sm focus:outline-none">
                            <option value="admin">{{ __('admin.admin') }}</option>
                            <option value="guest">{{ __('admin.guest') }}</option>
                            <option value="teacher">{{ __('admin.teacher') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="title"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.title') }}</label>
                        <input type="text" x-model="notificationTitle"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"
                            id="title" />
                    </div>
                    <div class="mt-5">
                        <label for="message"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.message') }}</label>
                        <textarea rows="6" x-model="notificationMessage" id="message"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"></textarea>
                    </div>
                    <div class="mt-4 flex w-full justify-end">
                        <button @click="senderRole()"
                            class="bg-secondary_blue hover:border-secondary_blue hover:text-secondary_blue cursor-pointer rounded-lg border px-5 py-2 text-white transition-all hover:bg-white">{{ __('admin.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="editBirth" x-cloak
        class="bg-secondary_black/20 animate-fade fixed left-0 right-0 top-0 z-30 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-md p-4" @click.away="closeBirth">
            <!-- Modal content -->
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5 dark:border-gray-600">
                    <h3 class="text-secondary_blue text-xl font-semibold">
                        {{ __('admin.alert.birth') }}
                    </h3>
                    <button type="button" @click="closeBirth"
                        class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="crud-modal">
                        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-4">
                    <div class="relative mb-4 w-full">
                        <label for="birth_date" class="text-secondary_blue text-base font-bold">
                            {{ __('admin.birth_date') }}
                        </label>
                        <div class="relative" @click="$refs.birthInput.showPicker()">
                            <input type="date" x-model="birthUpdated" id="birth_date" x-ref="birthInput"
                                class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full appearance-none rounded-md border px-3 py-2 pr-10 text-sm focus:outline-none">
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <!-- Ikon kalender -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="text-secondary_blue h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="title"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.title') }}</label>
                        <input type="text" x-model="notificationTitle"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"
                            id="title" />
                    </div>
                    <div class="mt-5">
                        <label for="message"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.message') }}</label>
                        <textarea rows="6" x-model="notificationMessage" id="message"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"></textarea>
                    </div>
                    <div class="mt-4 flex w-full justify-end">
                        <button @click="senderBirth()"
                            class="bg-secondary_blue hover:border-secondary_blue hover:text-secondary_blue cursor-pointer rounded-lg border px-5 py-2 text-white transition-all hover:bg-white">{{ __('admin.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="editOrigin" x-cloak
        class="bg-secondary_black/20 animate-fade fixed left-0 right-0 top-0 z-30 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-md p-4" @click.away="openOrigin">
            <!-- Modal content -->
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5 dark:border-gray-600">
                    <h3 class="text-secondary_blue text-xl font-semibold">
                        {{ __('admin.alert.origin') }}
                    </h3>
                    <button type="button" @click="openOrigin"
                        class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="crud-modal">
                        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-4">
                    <div class="mb-4">

                        <flux:field>
                            <flux:label class="text-secondary_blue! text-base! font-bold!">{{ __('register.origin') }}
                            </flux:label>
                            <flux:input x-model="originUpdated" type="text" required
                                :placeholder="__('register.origin_example')" @input="(event) => {loadSchool(event);}"
                                @focus="(event) => { open = true; loadSchool(event); }" @click.away="open = false"
                                autocomplete="origin" class="border-secondary_blue! rounded-md border" class:input="text-secondary_blue!" />
                            <flux:error name="origin" />
                        </flux:field>

                        <div class="relative z-20" x-show="open && schools.length > 0" x-transition>
                            <div
                                class="absolute left-0 top-0 w-full rounded-2xl border border-gray-500 bg-white p-2 shadow-2xl">
                                <template x-for="(school, index) in schools" :key="index">
                                    <div x-text="school"
                                        class="text-secondary_blue hover:bg-secondary_black/20 flex h-[40px] cursor-pointer items-center truncate rounded-xl border-b border-gray-500/50 px-2 py-3 text-base transition-opacity"
                                        @click="originUpdated = school;"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="title"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.title') }}</label>
                        <input type="text" x-model="notificationTitle"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"
                            id="title" />
                    </div>
                    <div class="mt-5">
                        <label for="message"
                            class="text-secondary_blue text-base font-bold">{{ __('admin.warning.message') }}</label>
                        <textarea rows="6" x-model="notificationMessage" id="message"
                            class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none"></textarea>
                    </div>
                    <div class="mt-4 flex w-full justify-end">
                        <button @click="senderOrigin()"
                            class="bg-secondary_blue hover:border-secondary_blue hover:text-secondary_blue cursor-pointer rounded-lg border px-5 py-2 text-white transition-all hover:bg-white">{{ __('admin.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <flux:sidebar.toggle class="text-secondary_blue! lg:hidden" icon="bars-2" inset="left" />
    <flux:heading size="xl" level="1" class="text-secondary_blue!">{{ __('admin.user.control') }}
    </flux:heading>
    <div class="my-5 max-w-md">
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
                placeholder="{{ __('admin.user.name') }}, {{ __('admin.user.origin') }} {{ __('admin.and') }} {{ __('admin.user.email') }}"
                required />
        </div>
    </div>

    <div class="relative overflow-x-auto">
        <table class="text-secondary_blue w-full text-left text-sm rtl:text-right">
            <thead class="bg-secondary_blue/15 text-secondary_blue text-sm uppercase">
                <tr>
                    <th scope="col" class="px-6 py-3 text-center">{{ __('admin.user.profile_photo') }}</th>
                    <th scope="col" class="px-6 py-3 text-center">{{ __('admin.user.name') }}</th>
                    <th scope="col" class="px-6 py-3 text-center">{{ __('admin.user.email') }}</th>
                    <th scope="col" class="px-6 py-3 text-center">{{ __('admin.user.role') }}</th>
                    <th scope="col" class="px-6 py-3 text-center">{{ __('admin.user.birth_date') }}</th>
                    <th scope="col" class="px-6 py-3 text-center">{{ __('admin.user.origin') }}</th>
                    <th scope="col" class="px-6 py-3 text-center">{{ __('admin.user.action') }}</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(user, index) in users.data" :key="index">
                    <tr class="p-2 odd:bg-gray-200 even:bg-gray-100">
                        <td scope="row" class="p-2">
                            <img x-bind:src="user.profile_photo_path" x-bind:alt="user.name"
                                class="mx-auto h-16 w-16 rounded-full" loading="lazy"/>
                            <template x-if="idChanged == user.id">
                                <div class="animate-fade mt-2">
                                    <flux:separator class="mb-2" />
                                    <div class="flex flex-row justify-center gap-x-3">
                                        <div @click="sendNotification(user.id, 'PHOTO')"
                                            class="flex cursor-pointer flex-row items-center justify-center gap-x-px text-sm text-yellow-500 transition-opacity hover:opacity-65">
                                            <flux:icon.exclamation-circle class="size-5" />
                                            {{ __('admin.warning') }}
                                        </div>
                                        <div class="flex cursor-pointer flex-row items-center justify-center text-sm transition-opacity hover:opacity-65"
                                            @click="showEditImage">
                                            <flux:icon.pencil-square class="size-5" />
                                            {{ __('admin.edit') }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </td>
                        <td scope="row" class="py-3 text-center">
                            <p x-text="user.name"></p>
                            <template x-if="idChanged == user.id">
                                <div class="animate-fade">
                                    <flux:separator class="mb-2" />
                                    <div class="flex flex-row justify-center gap-x-3">
                                        <div @click="sendNotification(user.id, 'NAME')"
                                            class="flex cursor-pointer flex-row items-center justify-center gap-x-px text-sm text-yellow-500 transition-opacity hover:opacity-65">
                                            <flux:icon.exclamation-circle class="size-5" />
                                            {{ __('admin.warning') }}
                                        </div>
                                        <div class="flex cursor-pointer flex-row items-center justify-center text-sm transition-opacity hover:opacity-65"
                                            @click="openEditName">
                                            <flux:icon.pencil-square class="size-5" />
                                            {{ __('admin.edit') }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </td>
                        <td scope="row" class="text-center">
                            <p x-text="user.email"></p>
                        </td>
                        <td scope="row" class="text-center">
                            <p x-text="user.role"></p>
                            <template x-if="idChanged == user.id">
                                <div class="animate-fade">
                                    <flux:separator class="mb-2" />
                                    <div class="flex flex-row justify-center gap-x-3">
                                        <div class="flex cursor-pointer flex-row items-center justify-center text-sm transition-opacity hover:opacity-65"
                                            @click="openEditRole">
                                            <flux:icon.pencil-square class="size-5" />
                                            {{ __('admin.edit') }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </td>
                        <td scope="row" class="text-center">
                            <p x-text="new Date(user.birth_date).toLocaleDateString('id-ID')"></p>
                            <template x-if="idChanged == user.id">
                                <div class="animate-fade">
                                    <flux:separator class="mb-2" />
                                    <div class="flex flex-row justify-center gap-x-3">
                                        <div @click="sendNotification(user.id, 'BIRTH')"
                                            class="flex cursor-pointer flex-row items-center justify-center gap-x-px text-sm text-yellow-500 transition-opacity hover:opacity-65">
                                            <flux:icon.exclamation-circle class="size-5" />
                                            {{ __('admin.warning') }}
                                        </div>
                                        <div class="flex cursor-pointer flex-row items-center justify-center text-sm transition-opacity hover:opacity-65"
                                            @click="openBirth">
                                            <flux:icon.pencil-square class="size-5" />
                                            {{ __('admin.edit') }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </td>
                        <td scope="row" class="text-center">
                            <p x-text="user.origin"></p>
                            <template x-if="idChanged == user.id">
                                <div class="animate-fade">
                                    <flux:separator class="mb-2" />
                                    <div class="flex flex-row justify-center gap-x-3">
                                        <div @click="sendNotification(user.id, 'ORIGIN')"
                                            class="flex cursor-pointer flex-row items-center justify-center gap-x-px text-sm text-yellow-500 transition-opacity hover:opacity-65">
                                            <flux:icon.exclamation-circle class="size-5" />
                                            {{ __('admin.warning') }}
                                        </div>
                                        <div class="flex cursor-pointer flex-row items-center justify-center text-sm transition-opacity hover:opacity-65"
                                            @click="openOrigin">
                                            <flux:icon.pencil-square class="size-5" />
                                            {{ __('admin.edit') }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </td>
                        <td scope="row" class="py-3 text-center">
                            <div x-show="idChanged != user.id"
                                class="animate-fade flex cursor-pointer flex-row items-center justify-center transition-opacity hover:opacity-65"
                                @click="idChanged=user.id">
                                <flux:icon.pencil-square class="size-6" />
                                {{ __('admin.edit') }}
                            </div>
                            <div x-show="idChanged == user.id"
                                class="animate-fade flex cursor-pointer flex-row items-center justify-center gap-x-px text-yellow-500 transition-opacity hover:opacity-65"
                                @click="idChanged=''">
                                <flux:icon.x-circle class="size-6" />
                                {{ __('admin.cancel') }}
                            </div>
                            <div class="mt-4 flex flex-row items-center justify-center text-red-400">
                                <flux:icon.trash class="size-6" />
                                {{ __('admin.delete') }}
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <div x-show="users.data.length == 0" class="text-secondary_blue bg-secondary_blue/10 w-full p-4 text-center">
            {{ __('admin.not_found') }}
        </div>
    </div>

    <div class="mt-5 w-full text-center" x-show="users.data.length != 0 && users.last_page > 1">
        <ul class="inline-flex h-10 h-10 select-none -space-x-px text-base">
            <li>
                <a @click.prevent="Number(users.current_page) == 1 ? null : prevPage()"
                    class="ms-0 flex h-10 items-center justify-center rounded-s-lg border border-e-0 border-gray-300 bg-white px-4 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                    x-bind:class="{
                        'cursor-not-allowed': Number(users.current_page) == 1,
                        'cursor-pointer': Number(users
                            .current_page) != 1
                    }">{{ __('admin.previous') }}
                </a>
            </li>

            <template x-for="(link, index) in pagination" :key="index">
                <li>
                    <a @click="updatePage(link)"
                        class="flex h-10 cursor-pointer items-center justify-center border border-gray-300 px-4 leading-tight hover:bg-gray-100 hover:text-gray-700"
                        x-bind:class="{
                            'bg-secondary_blue/20 text-secondary_blue font-bold': Number(users.current_page) ==
                                link,
                            'bg-white text-gray-500': !(Number(users.current_page) == link)
                        }"
                        x-html="link"></a>
                </li>
            </template>

            <li>
                <a @click.prevent="users.current_page == users.last_page ? null : nextPage()"
                    class="flex h-10 items-center justify-center rounded-e-lg border border-gray-300 bg-white px-4 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                    x-bind:class="{
                        'cursor-not-allowed': users.current_page == users.last_page,
                        'cursor-pointer': users
                            .current_page != users.last_page
                    }">{{ __('admin.next') }}</a>
            </li>

        </ul>
    </div>

</flux:main>
<script>
    function initBar() {
        return {
            showBar: false,
            users: @entangle('users').live,
            search: @entangle('search').live,
            randomAvatar: @entangle('randomAvatar').live,
            idChanged: "",
            notificationMessage: "",
            notificationTitle: "",
            pagination: [],
            nextPage: [],
            prevPage: [],
            showNotification: false,
            links: [],
            initStop: false,
            editImage: false,
            editName: false,
            editBirth: false,
            editRole: false,
            editOrigin: false,
            imageUrl: null,
            nameUpdated: null,
            roleUpdated: null,
            birthUpdated: null,
            originUpdated: null,
            editImagePicking: false,
            openFile() {
                this.$refs.fileInput.click();
            },
            openOrigin() {
                this.editOrigin = true;
                this.addWarningMessage('ORIGIN');
            },
            closeOrigin() {
                this.editOrigin = false;
                this.originUpdated = null;
            },
            openBirth() {
                this.editBirth = true;
                this.addWarningMessage('BIRTH');
            },
            closeBirth() {
                this.editBirth = false;
                this.birthUpdated = null;
            },
            senderBirth() {
                if (this.birthUpdated == null) {
                    this.$dispatch('failed-center', [{
                        message: '{{ __('profile.birth_required') }}'
                    }])
                    return;
                }
                if (this.birthUpdated == this.users.data.find(user => user.id == this.idChanged).birth_date) {
                    this.$dispatch('failed-center', [{
                        message: '{{ __('profile.birth_same') }}'
                    }])
                    return;
                }
                this.$wire.birthUpdated(this.idChanged, this.birthUpdated, this.notificationMessage,
                    this.notificationTitle, this.users.current_page);
                this.editBirth = false;
                this.birthUpdated = null;
            },
            openEditRole() {
                this.editRole = true;
                this.roleUpdated = this.users.data.find(user => user.id == this.idChanged).role;
                this.addWarningMessage('ROLE');
            },
            closeEditRole() {
                this.editRole = false;
                this.roleUpdated = null;
            },
            senderOrigin() {
                if (this.originUpdated == null) {
                    this.$dispatch('failed-center', [{
                        message: '{{ __('profile.origin_required') }}'
                    }])
                    return;
                }
                if (this.originUpdated == this.users.data.find(user => user.id == this.idChanged).origin) {
                    this.$dispatch('failed-center', [{
                        message: '{{ __('admin.origin_same') }}'
                    }])
                    return;
                }
                this.$wire.originUpdated(this.idChanged, this.originUpdated, this.notificationMessage,
                    this.notificationTitle, this.users.current_page);
                this.editOrigin = false;
                this.originUpdated = null;
            },
            senderRole() {
                if (this.roleUpdated == null) {
                    this.$dispatch('failed-center', [{
                        message: '{{ __('admin.role.required') }}'
                    }])
                    return;
                }
                if (this.roleUpdated == this.users.data.find(user => user.id == this.idChanged).role) {
                    this.$dispatch('failed-center', [{
                        message: '{{ __('admin.role_same') }}'
                    }])
                    return;
                }
                this.$wire.roleUpdated(this.idChanged, this.roleUpdated, this.notificationMessage,
                    this.notificationTitle, this.users.current_page);
                this.editRole = false;
                this.roleUpdated = null;
            },
            openEditName() {
                this.editName = true;
                this.addWarningMessage('NAME');
            },
            closeEditName() {
                this.editName = false;
                this.nameUpdated = null;
            },
            generateRandomName(length = 8) {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let result = '';
                for (let i = 0; i < length; i++) {
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                this.nameUpdated = result;
            },
            senderName() {
                if (this.nameUpdated == null) {
                    this.$dispatch('failed-center', [{
                        message: '{{ __('admin.name.min') }}'
                    }])
                    return;
                }
                this.$wire.nameUpdated(this.idChanged, this.nameUpdated, this.notificationMessage,
                    this.notificationTitle, this.users.current_page);
                this.editName = false;
                this.nameUpdated = null;
            },
            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imageUrl = e.target.result;
                    };
                    reader.readAsDataURL(file);
                    this.editImagePicking = true;
                }
            },
            clickImage(path) {
                this.editImagePicking = false;
                this.imageUrl = path;
            },
            init() {
                if (this.initStop) return;
                this.initStop = true;
                this.updatePagination(Number(this.users.current_page), Number(this.users.last_page));
                this.$watch('users', (newValue) => {
                    this.updatePagination(Number(newValue.current_page), Number(newValue.last_page));
                });
                this.$watch('search', (newValue) => {
                    this.$wire.searching(newValue);
                });
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
            senderImage() {
                if (this.imageUrl == null) {
                    this.$dispatch('failed-center', [{
                        message: '{{ __('admin.image_required') }}'
                    }])
                    return;
                }
                this.$wire.avatarUpdated(this.editImagePicking, this.idChanged, this.imageUrl, this.notificationMessage,
                    this.notificationTitle, this.users.current_page);
                this.imageUrl = null;
            },
            showEditImage() {
                this.editImage = true;
                this.imageUrl = null;
                this.addWarningMessage('PHOTO');
            },
            closeEditImage() {
                this.editImage = false;
                this.imageUrl = null;
            },
            addWarningMessage(type) {
                if (type == 'PHOTO') {
                    this.notificationMessage = "{{ __('admin.photo.message') }}";
                    this.notificationTitle = "{{ __('admin.photo.title') }}";
                } else if (type == 'NAME') {
                    this.notificationMessage = "{{ __('admin.name.message') }}";
                    this.notificationTitle = "{{ __('admin.name.title') }}";
                } else if (type == 'BIRTH') {
                    this.notificationMessage = "{{ __('admin.birth_date.message') }}";
                    this.notificationTitle = "{{ __('admin.birth_date.title') }}";
                } else if (type == 'ORIGIN') {
                    this.notificationMessage = "{{ __('admin.origin.message') }}";
                    this.notificationTitle = "{{ __('admin.origin.title') }}";
                } else if (type == 'ROLE') {
                    this.notificationMessage = "{{ __('admin.role.message') }}";
                    this.notificationTitle = "{{ __('admin.role.title') }}";
                } else {
                    this.notificationMessage = "{{ __('admin.something.message') }}";
                    this.notificationTitle = "{{ __('admin.something.title') }}";
                }
            },
            sendNotification(userId, type) {
                if (type == 'PHOTO') {
                    this.notificationMessage = "{{ __('admin.photo.message') }}";
                    this.notificationTitle = "{{ __('admin.photo.title') }}";
                } else if (type == 'NAME') {
                    this.notificationMessage = "{{ __('admin.name.message') }}";
                    this.notificationTitle = "{{ __('admin.name.title') }}";
                } else if (type == 'BIRTH') {
                    this.notificationMessage = "{{ __('admin.birth_date.message') }}";
                    this.notificationTitle = "{{ __('admin.birth_date.title') }}";
                } else if (type == 'ORIGIN') {
                    this.notificationMessage = "{{ __('admin.origin.message') }}";
                    this.notificationTitle = "{{ __('admin.origin.title') }}";
                } else {
                    this.notificationMessage = "{{ __('admin.something.message') }}";
                    this.notificationTitle = "{{ __('admin.something.title') }}";
                }
                this.showNotification = true;
            },
            senderNotification() {
                this.$wire.sendNotification(this.idChanged, this.notificationMessage, this.notificationTitle);
            },
            updatePage(page) {
                if (page == this.users.current_page) return;
                this.$wire.updateUser(page);
            },
            nextPage() {
                if (this.users.current_page == this.users.last_page) return;
                this.$wire.updateUser(this.users.current_page + 1);
            },
            prevPage() {
                if (Number(this.users.current_page) == 1) return;
                this.$wire.updateUser(this.users.current_page - 1);
            },
            schools: [],
            dummySchools: [],
            open: false,
            async loadSchool(event) {
                try {
                    const searchValue = event.target.value.trim().toLowerCase();
                    if (this.dummySchools.length > 0) {
                        const filteredResults = this.dummySchools.filter(school =>
                            school.toLowerCase().includes(searchValue)
                        );

                        // Jika ada hasil yang cocok, langsung gunakan tanpa fetch
                        if (filteredResults.length > 0) {
                            this.schools = filteredResults.slice(0, 10);
                            return;
                        }
                    }
                    const token = document.querySelector('meta[name=\'csrf-token\']').getAttribute('content');
                    const response = await fetch(
                        `{{ route('name-school') }}?search=${encodeURIComponent(searchValue)}`, {
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            }
                        });
                    if (!response.ok) {
                        throw new Error(`HTTP Error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    this.dummySchools = data.data.map(school => school.sekolah);
                    this.schools = this.dummySchools.slice(0, 10);
                } catch (error) {
                    this.schools = [];
                    this.dummySchools = [];
                }
            },

        }
    }
</script>
