<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\RandomAvatar;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.layouts.app-sidebar')] class extends Component {
    use WithFileUploads;
    public $image;
    public $images = [];

    public function mount()
    {
        $this->images = RandomAvatar::all()->toArray();
    }

    public function updatedImage()
    {
        try {
            $this->Validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            ]);

            $filename = $this->image->store(path: 'images/profile', options: 'public');
            $filename = str_replace('public/', '', $filename);
            $image = Storage::url($filename);

            RandomAvatar::create([
                'name' => $this->image->getClientOriginalName(),
                'path' => $image,
            ]);

            $this->mount();

            $this->dispatch('success', ['message' => __('profile.image_success')]);
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
            Log::error('My Profile ', [
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function deletedImage($id)
    {
        try {
            $image = RandomAvatar::find($id);
            if ($image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
                $this->mount();
                $this->dispatch('success', ['message' => __('admin.image_delete')]);
            } else {
                $this->dispatch('failed', ['message' => __('profile.image_not_found')]);
            }
        } catch (\Throwable $th) {
            Log::error('My Profile ', [
                'error' => $th->getMessage(),
            ]);
        }
    }
}; ?>

<flux:main class="bg-white" x-data="initAvatar" x-init="init">
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

    <div x-show="showImage" x-cloak
        class="bg-secondary_black/20 animate-fade fixed left-0 right-0 top-0 z-30 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-2xl p-4" @click.away="showImage = false">
            <!-- Modal content -->
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5 dark:border-gray-600">
                    <button type="button" @click="showImage = false"
                        class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="crud-modal">
                        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-4">
                    <img loading="lazy" x-bind:src="pathImage" alt="Full Image" class="h-full w-full rounded-lg object-cover" />
                </div>
            </div>
        </div>
    </div>

    <flux:heading size="xl" level="1" class="text-secondary_blue! font-sans">
        {{ __('admin.default_avatar') }}
    </flux:heading>
    <div class="mt-4 flex flex-row flex-wrap gap-x-4">
        <template x-show="images.length != 0" x-for="(image, index) in images" :key="index">
            <div class="relative h-[100px] w-[100px]" @mouseenter="show = true" @mouseleave="show = false"
                @click="show = true" x-data="{ show: false }" @click.away="show = false">
                <div class="relative h-[100px] w-[100px] overflow-hidden rounded-full">
                    <div class="absolute inset-0 z-10 h-full w-full bg-transparent" x-show="show"
                        @click="if(show){pathImage=image.path; showImage=true;}"></div>
                    <img loading="lazy" x-bind:src="image.path" x-bind:alt="image.name"
                        class="h-full w-full object-cover" />
                </div>
                <flux:icon.trash x-show="show" variant="solid" @click="deleteImage(image.id)"
                    class="absolute! animate-fade bottom-0 right-0 z-30 size-6 cursor-pointer text-red-500 transition-opacity hover:opacity-70" />
            </div>
        </template>
        <div class="flex h-[100px] w-[100px] items-center justify-center">
            <div @click="openFile"
                class="cursor-pointer rounded-full border border-gray-500 bg-gray-400/20 p-3 transition-opacity hover:opacity-50">
                <flux:icon.plus class="size-5 text-gray-700" />
                <input type="file" x-ref="fileInput" accept=".jpeg, .jpg, .png, .svg, .webp" class="hidden"
                    wire:model="image">
            </div>
        </div>
    </div>
</flux:main>
<script>
    function initAvatar() {
        return {
            images: @entangle('images').live,
            showImage: false,
            pathImage: "",
            init() {
                console.log(this.images);
            },
            openFile() {
                this.$refs.fileInput.click();
            },
            deleteImage(id) {
                this.$wire.deletedImage(id);
            },

        }
    }
</script>
