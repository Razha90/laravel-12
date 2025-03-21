<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Content;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Validate;
use App\Models\Classroom;

new #[Layout('components.layouts.app-page')] class extends Component {
    use WithFileUploads;
    public $id;
    public $classrooms = [];
    public $isLoading = false;
    public $isTeacher = false;
    public $contents = [];

    #[Validate('image', message: 'image', onUpdate: true)]
    #[Validate('mimes:jpeg,jpg,webp,png', message: 'image', onUpdate: true)]
    #[Validate('max:10240', message: 'max', onUpdate: true)]
    public $image;

    public function mount($id)
    {
        $this->id = $id;
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $data = Classroom::where('id', $this->id)->get();
            $this->classrooms = $data->toArray();
            $this->isLoading = false;
            if (empty($this->contents)) {
                $this->loadContent();
            }
        } catch (\Throwable $th) {
            $this->classrooms = null;
            $this->isLoading = false;
            session()->flash('FAILED', __('class-learn.failed_class'));
            Log::error('ClassroomLearn Eroor Load Data' . $th);
        }
    }

    public function addContent()
    {
        try {
            $createContent = Content::create([
                'classroom_id' => $this->id,
                'title' => '',
                'description' => 'Description',
                'content' => '',
                'visibility' => false,
                'release' => null,
                'type' => 'task',
            ]);
            $createContent->save();
            return redirect()->route('task-add', ['id' => $this->id, 'task' => $createContent->id]);
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Add Content' . $th);
            $this->dispatch('failed-content', ['message' => __('class-learn.error.add_content')]);
        }
    }

    public function savedContent($data)
    {
        try {
            if (auth()->user()->id != $this->classrooms[0]['user_id']) {
                $this->dispatch('show-failed', ['message' => __('class-learn.error_auth')]);
                return;
            }

            $validator = Validator::make(
                $data,
                [
                    'position' => 'string|max:20',
                    'title' => 'required|string|max:100|min:3',
                    'action' => 'required|boolean',
                ],
                [
                    'position.string' => __('class-learn.position.string'),
                    'position.max' => __('class-learn.position.max'),

                    'title.required' => __('class-learn.title.required'),
                    'title.string' => __('class-learn.title.string'),
                    'title.max' => __('class-learn.title.max'),
                    'title.min' => __('class-learn.title.min'),

                    'action.required' => __('class-learn.action.required'),
                    'action.boolean' => __('class-learn.action.boolean'),
                ],
            );

            if ($validator->fails()) {
                $this->dispatch('show-failed', ['message' => $validator->errors()->first()]);
                return;
            }

            $action = $data['action'];
            $position = $data['position'];
            $title = $data['title'];
            $image = '';

            if ($action) {
                $this->validate();
                $filename = $this->image->store(path: 'images', options: 'public');
                $filename = str_replace('public/', '', $filename);
                $image = Storage::url($filename);
                $file_old = str_replace('/storage/', '', $this->classrooms[0]['image']);
                try {
                    if (Storage::disk('public')->exists($file_old)) {
                        Storage::disk('public')->delete($file_old);
                        if (!Storage::disk('public')->exists($file_old)) {
                        } else {
                            Log::warning('File gagal dihapus ClassroomLearn, User Melakukannya : ' . auth()->user()->id . '' . $file_old);
                        }
                    } else {
                        Log::info('File tidak ditemukan ClassroomLearn : User Melakukannya' . auth()->user()->id . '' . $file_old);
                    }
                } catch (\Throwable $th) {
                    Log::error('ClassroomLearn Error Deleted Image: User Melakukannya ' . auth()->user()->id . ' ' . $th->getMessage());
                }
            } else {
                $validator = Validator::make(
                    $data,
                    [
                        'image_path' => 'required|string',
                    ],
                    [
                        'image_path.required' => __('class-learn.image.required'),
                        'image_path.string' => __('class-learn.image.string'),
                    ],
                );
                if ($validator->fails()) {
                    $this->dispatch('show-failed', ['message' => $validator->errors()->first()]);
                    return;
                }
                $image = $data['image_path'];
            }

            $oldImage = $this->classrooms[0]['image'];

            if (Str::startsWith($oldImage, '/storage/images/')) {
                $this->deletedImage($oldImage);
            }

            Classroom::where('id', $this->id)->update([
                'image' => $image,
                'position' => $position,
                'title' => $title,
            ]);
            $this->dispatch('show-success', ['message' => __('class-learn.success')]);
            $this->loadData();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi gagal!', $e->errors());
            $errors = $e->errors();
            if (isset($errors['image'])) {
                $errorMessage = $errors['image'][0];
                if ($errorMessage === 'image') {
                    $this->dispatch('show-failed', ['message' => __('class-learn.image.image')]);
                } elseif ($errorMessage === 'max') {
                    $this->dispatch('show-failed', ['message' => __('class-learn.image.max')]);
                } else {
                    $this->dispatch('show-failed', ['message' => __('class-learn.image.image')]); // Default error
                }
            }
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Saved Content' . $th);
            $this->dispatch('show-failed', ['message' => __('class-learn.error_server')]);
        }
    }

    public function deletedImage($file_old)
    {
        try {
            // $file_old = str_replace('/storage/', '', $this->classrooms[0]['image']);
            // $file_old = $this->classrooms[0]['image'];
            if (Storage::disk('public')->exists($file_old)) {
                Storage::disk('public')->delete($file_old);
                Log::info('File berhasil dihapus: ' . $file_old);
            } else {
                Log::info('File tidak ditemukan: ' . $file_old);
            }
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Deleted Image' . $th);
        }
    }

    public function loadContent()
    {
        try {
            $data;
            if (auth()->user()->id == $this->classrooms[0]['user_id']) {
                $this->isTeacher = true;
                $data = Content::where('classroom_id', $this->id)->get();
            } else {
                $data = Content::where('classroom_id', $this->id)->where('visibility', true)->get();
            }
            $this->contents = $data->toArray();
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Load Content ' . $th);
        }
    }
}; ?>

<div>
    <div x-data="{ alert: false, message: '' }"
        x-on:show-success.window="(event) => { 
        message = event.detail[0].message;
        alert = true;
        setTimeout(() => alert = false, 5000);
    }"
        x-show="alert" x-transition
        class="flex items-start left-5 bottom-5 flex-row p-4 mb-4 text-sm rounded-lg bg-gray-800 animate-fade-up text-green-400 absolute z-30"
        role="alert">

        <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
            viewBox="0 0 20 20">
            <path
                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
        </svg>
        <span class="sr-only">{{ __('class-learn.info') }}</span>
        <div>
            <span class="font-medium" x-text="message"></span>
        </div>
    </div>

    <div class="flex w-full flex-row h-screen min-h-[600px]" x-data="starting">
        <div x-data="{ alert: false, message: '' }"
            x-on:show-failed.window="(event) => { 
        message = event.detail[0].message;
        alert = true;
        setTimeout(() => alert = false, 5000);
        editProfile = true;
        $refs.fileInput.value = '';
        previewImage = classrooms[0].image;
    }"
            x-show="alert" x-transition
            class="flex items-start left-5 bottom-5 flex-row p-4 mb-4 text-sm rounded-lg bg-gray-800 animate-fade-up text-red-400 absolute z-30"
            role="alert">

            <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <span class="sr-only">Error</span>
            <div>
                <span class="font-medium" x-text="message"></span>
            </div>
        </div>

        <div class="flex w-full flex-row h-screen min-h-[600px]" x-data="starting">
            <div x-data="{ alert: false, message: '' }"
                x-on:failed-content.window="(event) => { 
        message = event.detail[0].message;
        alert = true;
        setTimeout(() => alert = false, 5000);
    }"
                x-show="alert" x-transition
                class="flex items-start left-5 bottom-5 flex-row p-4 mb-4 text-sm rounded-lg bg-gray-800 animate-fade-up text-red-400 absolute z-30"
                role="alert">

                <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Error</span>
                <div>
                    <span class="font-medium" x-text="message"></span>
                </div>
            </div>

            <div x-show="error.condition" x-transition:enter="transition-transform duration-500 ease-in-out"
                x-transition:enter-start="scale-x-0" x-transition:enter-end="scale-x-100"
                x-transition:leave="transition-transform duration-500 ease-in-out"
                x-transition:leave-start="scale-x-100" x-transition:leave-end="scale-x-0"
                class="bg-orange-100 border-l-4 absolute left-3 top-3 z-30 border-orange-500 text-orange-700 p-4 overflow-hidden origin-left"
                role="alert">
                <p class="font-bold" x-text="error.title"></p>
                <p x-text="error.message"></p>
            </div>

            <nav class="min-w-[65px] bg-accent_blue h-full text-secondary_blue pl-3 pr-6 py-3 animate-fade-right overflow-hidden transition-all duration-300 ease-in-out"
                x-bind:class="{
                    'w-[6%]': isNav,
                    'w-[25%]': !isNav
                }">
                <div class="px-4 py-2 bg-secondary_blue items-center flex flex-row justify-between group cursor-pointer"
                    @click="window.location.href = '{{ route('classroom') }}'"
                    x-bind:class="{
                        'px-2 py-1 rounded-lg': isNav,
                        'px-4 py-2 rounded-2xl': !isNav
                    }">
                    <div class="flex flex-row gap-x-3 items-center transition-all ease-in-out">
                        <img src="{{ url('/img/web/logo.png') }}" loading="lazy"
                            x-bind:class="{
                                'w-[60px] justify-center': isNav,
                                'w-[100px]': !isNav
                            }" />
                        <p x-show="!isNav" class="text-primary_white text-2xl animate-fade">{{ config('app.name') }}</p>
                    </div>
                    <svg x-show="!isNav"
                        class="group-hover:animate-infinite w-[40px] group-hover:animate-fade-right group-hover:animate-reverse animate-fill-both"
                        fill="#ffffff" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 70 70" enable-background="new 0 0 70 70"
                        xml:space="preserve">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <g>
                                <g>
                                    <path
                                        d="M35.221,7c0.404,0,0.816,0.044,1.227,0.138c2.882,0.652,4.671,3.441,3.998,6.229L29.668,34.37l10.777,22.262 c0.673,2.789-1.116,5.576-3.998,6.23C36.037,62.955,35.627,63,35.223,63c-2.434,0-4.636-1.615-5.214-4.006L18.623,34.437h-0.036 l0.019-0.066l-0.019-0.066h0.036l11.386-23.299C30.587,8.614,32.788,7,35.221,7 M35.221,3c-4.183,0-7.802,2.684-8.971,6.585 L15.186,32.228c-0.375,0.614-0.581,1.314-0.594,2.025c-0.038,0.812,0.173,1.619,0.609,2.313l11.036,23.803 C27.391,64.295,31.023,67,35.223,67c0.707,0,1.416-0.08,2.107-0.236c2.479-0.563,4.568-2.045,5.89-4.174 c1.29-2.078,1.686-4.527,1.114-6.896c-0.067-0.277-0.164-0.547-0.288-0.805l-9.909-20.467l9.867-19.229 c0.145-0.282,0.255-0.58,0.33-0.888c0.571-2.369,0.176-4.818-1.115-6.897c-1.322-2.129-3.412-3.61-5.888-4.171 C36.64,3.079,35.929,3,35.221,3L35.221,3z">
                                    </path>
                                </g>
                                <g>
                                    <path
                                        d="M24.411,31.365c-0.149,0-0.303-0.034-0.446-0.105c-0.494-0.247-0.694-0.848-0.447-1.342l5-10 c0.246-0.494,0.846-0.692,1.342-0.447c0.494,0.247,0.694,0.848,0.447,1.342l-5,10C25.131,31.163,24.778,31.365,24.411,31.365z M31.411,17.365c-0.149,0-0.303-0.034-0.446-0.105c-0.494-0.247-0.694-0.848-0.447-1.342l1-2c0.246-0.494,0.848-0.693,1.342-0.447 c0.494,0.247,0.694,0.848,0.447,1.342l-1,2C32.131,17.163,31.778,17.365,31.411,17.365z">
                                    </path>
                                </g>
                                <g>
                                    <path
                                        d="M47.412,31.325c2.209,0,4,1.791,4,4s-1.791,4-4,4s-4-1.791-4-4S45.203,31.325,47.412,31.325 M47.412,27.325 c-4.411,0-8,3.589-8,8s3.589,8,8,8s8-3.589,8-8S51.823,27.325,47.412,27.325L47.412,27.325z">
                                    </path>
                                </g>
                            </g>
                        </g>
                    </svg>
                </div>

                <!-- Profile -->
                <div x-data="{ isClicked: false }"
                    class="flex flex-row justify-between items-center absolute bottom-3 bg-secondary_blue px-3 py-2 rounded-xl hover:animate-pulse cursor-pointer"
                    @click.prevent="if (!isClicked) { isClicked = true; window.location.href = '{{ route('settings.profile') }}'; }"
                    :class="{ 'pointer-events-none opacity-50': isClicked }"
                    x-bind:class="{
                        'w-auto': isNav,
                        'w-[90%]': !isNav
                    }">
                    <div class="flex flex-row gap-x-3 items-center">
                        <a href="{{ route('settings.profile') }}"
                            class="w-[40px] h-[40px] overflow-hidden rounded-full border border-secondary_blue p-1  hover:opacity-50 transition-opacity">
                            <img src="{{ asset('/img/profile/' . auth()->user()->profile_photo_path) }}" loading="lazy"
                                alt="Profile Photo">
                        </a>
                        <p x-show="!isNav" class="text-primary_white text-xl">{{ auth()->user()->name }}</p>
                    </div>
                    <div x-show="!isNav">
                        <svg class="w-[30px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M21.2799 6.40005L11.7399 15.94C10.7899 16.89 7.96987 17.33 7.33987 16.7C6.70987 16.07 7.13987 13.25 8.08987 12.3L17.6399 2.75002C17.8754 2.49308 18.1605 2.28654 18.4781 2.14284C18.7956 1.99914 19.139 1.92124 19.4875 1.9139C19.8359 1.90657 20.1823 1.96991 20.5056 2.10012C20.8289 2.23033 21.1225 2.42473 21.3686 2.67153C21.6147 2.91833 21.8083 3.21243 21.9376 3.53609C22.0669 3.85976 22.1294 4.20626 22.1211 4.55471C22.1128 4.90316 22.0339 5.24635 21.8894 5.5635C21.7448 5.88065 21.5375 6.16524 21.2799 6.40005V6.40005Z"
                                    stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                </path>
                                <path
                                    d="M11 4H6C4.93913 4 3.92178 4.42142 3.17163 5.17157C2.42149 5.92172 2 6.93913 2 8V18C2 19.0609 2.42149 20.0783 3.17163 20.8284C3.92178 21.5786 4.93913 22 6 22H17C19.21 22 20 20.2 20 18V13"
                                    stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                </path>
                            </g>
                        </svg>
                    </div>
                </div>
            </nav>
            <div class="w-[75%] h-full relative flex justify-center bg-white"
                x-bind:class="{
                    'w-[94%]': isNav,
                    'w-[75%]': !isNav
                }">
                <!-- ArrowNavigation -->
                <div class="absolute z-40 top-1/2 bg-secondary_blue p-3 rounded-xl -left-8 cursor-pointer hover:animate-wiggle"
                    @click="toggle">
                    <svg class="w-[40px] rotate-180" fill="#ffffff" version="1.1" id="Layer_1"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="0 0 72 72" enable-background="new 0 0 72 72" xml:space="preserve">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <g>
                                <path
                                    d="M48.252,69.253c-2.271,0-4.405-0.884-6.011-2.489L17.736,42.258c-1.646-1.645-2.546-3.921-2.479-6.255 c-0.068-2.337,0.833-4.614,2.479-6.261L42.242,5.236c1.605-1.605,3.739-2.489,6.01-2.489c2.271,0,4.405,0.884,6.01,2.489 c3.314,3.314,3.314,8.707,0,12.021L35.519,36l18.743,18.742c3.314,3.314,3.314,8.707,0,12.021 C52.656,68.369,50.522,69.253,48.252,69.253z M48.252,6.747c-1.202,0-2.332,0.468-3.182,1.317L21.038,32.57 c-0.891,0.893-0.833,2.084-0.833,3.355c0,0.051,0,0.101,0,0.151c0,1.271-0.058,2.461,0.833,3.353l24.269,24.506 c0.85,0.85,1.862,1.317,3.063,1.317c1.203,0,2.273-0.468,3.123-1.317c1.755-1.755,1.725-4.61-0.03-6.365L31.292,37.414 c-0.781-0.781-0.788-2.047-0.007-2.828L51.438,14.43c1.754-1.755,1.753-4.61-0.001-6.365C50.587,7.215,49.454,6.747,48.252,6.747z">
                                </path>
                            </g>
                        </g>
                    </svg>
                </div>

                <!-- Content -->
                <template x-if="isLoading">
                    <div class="w-full h-[200px] flex items-center justify-center">
                        <div
                            class="px-4 py-2 text-base font-medium leading-none text-center rounded-full animate-pulse bg-blue-900 text-blue-200">
                            {{ __('add-class.loading') }}....</div>
                    </div>
                </template>

                <!-- Not Found -->
                <template x-if="!isLoading && !classrooms.length">
                    <div class="w-full h-full flex items-center justify-center flex-col gap-y-4">
                        <div
                            class="px-4 py-2 text-5xl font-medium leading-none text-center rounded-full animate-pulse bg-white border-2 border-red-500 text-red-700">
                            {{ __('class-learn.not_found') }}....</div>
                        <p @click="window.location.href = '{{ route('classroom') }}'"
                            class="underline text-secondary_blue cursor-pointer">Kembali</p>
                    </div>
                </template>

                <!-- Content template -->
                <template x-if="classrooms.length > 0">
                    <div class="w-full flex flex-col justify-start gap-y-3 pt-5 px-5 max-w-[1200px] min-h-[400px]">
                        <!-- Wrapper Profile Class -->
                        <div x-bind:style="'background-image: url(' + previewImage + '); background-position: center ' + positionImage +
                            ';'"
                            class="bg-no-repeat w-full bg-cover bg-center p-3 rounded-xl relative before:absolute before:inset-0 before:bg-black before:opacity-15 before:rounded-xl min-h-[300px] max-h-[400px] flex flex-col items-end justify-end hover:before:opacity-35 hover:before:transition-opacity "
                            x-ref="imageContainer">

                            <!-- Title -->
                            <div class="min-h-[100px] flex items-center px-3 w-full bg-gray-400/10 rounded-md bg-clip-padding backdrop-filter backdrop-blur-sm  relative"
                                x-show="!editProfile">
                                <p x-text="classrooms[0].title" class="text-4xl font-bold text-white absolute z-50">
                                </p>
                            </div>

                            <!-- Button Edit Profile -->
                            <div class="bg-primary_white absolute top-2 right-2 p-3 rounded-full cursor-pointer hover:opacity-70 transition-opacity"
                                x-show="isTeacher && !editProfile" @click="initEdit"
                                title="{{ __('class-learn.button_edit') }}">
                                <svg class="w-[30px]" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M21.2799 6.40005L11.7399 15.94C10.7899 16.89 7.96987 17.33 7.33987 16.7C6.70987 16.07 7.13987 13.25 8.08987 12.3L17.6399 2.75002C17.8754 2.49308 18.1605 2.28654 18.4781 2.14284C18.7956 1.99914 19.139 1.92124 19.4875 1.9139C19.8359 1.90657 20.1823 1.96991 20.5056 2.10012C20.8289 2.23033 21.1225 2.42473 21.3686 2.67153C21.6147 2.91833 21.8083 3.21243 21.9376 3.53609C22.0669 3.85976 22.1294 4.20626 22.1211 4.55471C22.1128 4.90316 22.0339 5.24635 21.8894 5.5635C21.7448 5.88065 21.5375 6.16524 21.2799 6.40005V6.40005Z"
                                            stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                        </path>
                                        <path
                                            d="M11 4H6C4.93913 4 3.92178 4.42142 3.17163 5.17157C2.42149 5.92172 2 6.93913 2 8V18C2 19.0609 2.42149 20.0783 3.17163 20.8284C3.92178 21.5786 4.93913 22 6 22H17C19.21 22 20 20.2 20 18V13"
                                            stroke="#2867A4" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                        </path>
                                    </g>
                                </svg>
                            </div>

                            <!-- Button Profile Edit Save Or Delete -->
                            <div class="absolute top-2 right-2 flex flex-col gap-y-4"
                                x-show="editProfile && !editImage">
                                <div class="bg-primary_white p-3 rounded-full cursor-pointer hover:opacity-70 transition-opacity"
                                    @click="cancelEdit" title="{{ __('class-learn.button_edit') }}">
                                    <svg class="w-[35px]" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M19.207 6.207a1 1 0 0 0-1.414-1.414L12 10.586 6.207 4.793a1 1 0 0 0-1.414 1.414L10.586 12l-5.793 5.793a1 1 0 1 0 1.414 1.414L12 13.414l5.793 5.793a1 1 0 0 0 1.414-1.414L13.414 12l5.793-5.793z"
                                                fill="#E52020"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="bg-primary_white p-3 rounded-full cursor-pointer hover:opacity-70 transition-opacity"
                                    x-show="(previewImage != classrooms[0].image) || (editTitle != classrooms[0].title) || (positionImage != classrooms[0].position)"
                                    @click="savedContent" title="{{ __('class-learn.button_edit') }}">
                                    <svg class="w-[35px]" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
                                        fill="#22c55e">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <rect x="0" fill="none" width="20" height="20"></rect>
                                            <g>
                                                <path d="M15.3 5.3l-6.8 6.8-2.8-2.8-1.4 1.4 4.2 4.2 8.2-8.2"></path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                            </div>

                            <!-- Edit Title -->
                            <div class="min-h-[100px] flex items-center px-3 w-full bg-gray-400/10 rounded-md bg-clip-padding backdrop-filter backdrop-blur-sm  relative"
                                x-show="editProfile && !editImage" x-init="initData">
                                <!-- <p x-text="classrooms[0].title" class="text-4xl font-bold text-white absolute z-50"></p> -->
                                <input type="text" x-model="editTitle"
                                    class="rounded-xl  font-bold text-2xl p-2 border-secondary_blue w-full bg-white text-secondary_black"
                                    placeholder="{{ __('class-learn.title') }}" />
                            </div>

                            <!-- Button Edit Image -->
                            <div class="border-4 rounded-xl border-dashed border-secondary_blue px-3 py-2 flex items-center justify-center absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white outline-8 outline-primary_white cursor-pointer hover:opacity-60 transition-colors duration-300"
                                x-show="editProfile && !editImage" @click="openEditImage">
                                <svg class="w-[35px]" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M11 4.00023H6.8C5.11984 4.00023 4.27976 4.00023 3.63803 4.32721C3.07354 4.61483 2.6146 5.07377 2.32698 5.63826C2 6.27999 2 7.12007 2 8.80023V17.2002C2 18.8804 2 19.7205 2.32698 20.3622C2.6146 20.9267 3.07354 21.3856 3.63803 21.6732C4.27976 22.0002 5.11984 22.0002 6.8 22.0002H15.2C16.8802 22.0002 17.7202 22.0002 18.362 21.6732C18.9265 21.3856 19.3854 20.9267 19.673 20.3622C20 19.7205 20 18.8804 20 17.2002V13.0002M7.99997 16.0002H9.67452C10.1637 16.0002 10.4083 16.0002 10.6385 15.945C10.8425 15.896 11.0376 15.8152 11.2166 15.7055C11.4184 15.5818 11.5914 15.4089 11.9373 15.063L21.5 5.50023C22.3284 4.6718 22.3284 3.32865 21.5 2.50023C20.6716 1.6718 19.3284 1.6718 18.5 2.50022L8.93723 12.063C8.59133 12.4089 8.41838 12.5818 8.29469 12.7837C8.18504 12.9626 8.10423 13.1577 8.05523 13.3618C7.99997 13.5919 7.99997 13.8365 7.99997 14.3257V16.0002Z"
                                            stroke="#2867a4" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                    </g>
                                </svg>
                                <p class="text-secondary_blue">{{ __('class-learn.butoon_edit_image') }}</p>
                                <!-- <input type="file" accept="image/*" class="hidden" x-ref="fileInput"
                                @change="const file = $event.target.files[0]; previewImage = file ? URL.createObjectURL(file) : ''"> -->
                            </div>

                            <!-- Button Chose Edit Image -->
                            <div x-show="editImage && !isEditPosition"
                                class="absolute bg-primary_white top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 rounded-xl p-3 flex flex-col gap-y-3">
                                <div class="flex flex-row gap-x-2">
                                    <div class="flex flex-col justify-center items-center hover:bg-secondary_blue/20 rounded-lg cursor-pointer transition-colors duration-300 p-1"
                                        @click="$refs.fileInput.click()">
                                        <svg class="w-[35px]" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H12M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125"
                                                    stroke="#2867a4" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                                <path d="M17.5 21L17.5 15M17.5 15L20 17.5M17.5 15L15 17.5"
                                                    stroke="#2867a4" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                </path>
                                            </g>
                                        </svg>
                                        <p class="text-xl text-secondary_blue">{{ __('class-learn.upload_image') }}
                                        </p>
                                        <input wire:model.live="image" type="file"
                                            accept="image/png, image/jpg, image/jpeg, image/webp" class="hidden"
                                            x-ref="fileInput" @change="handleFileChange">

                                    </div>
                                    <div class="flex flex-col justify-center items-center hover:bg-red-500/20 rounded-lg cursor-pointer transition-colors duration-300 p-1"
                                        @click="deletedImage">
                                        <svg class="w-[35px]" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path d="M10 11V17" stroke="#dc2626" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M14 11V17" stroke="#dc2626" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M4 7H20" stroke="#dc2626" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path
                                                    d="M6 7H12H18V18C18 19.6569 16.6569 21 15 21H9C7.34315 21 6 19.6569 6 18V7Z"
                                                    stroke="#dc2626" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                                <path
                                                    d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z"
                                                    stroke="#dc2626" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                            </g>
                                        </svg>
                                        <p class="text-xl text-red-500">{{ __('class-learn.button_delete') }}</p>
                                    </div>
                                    <div class="flex flex-col justify-center items-center hover:bg-orange-200/20 rounded-lg cursor-pointer transition-colors duration-300 p-1"
                                        @click="openEditPosition">
                                        <svg class="w-[35px]" fill="#fb923c" viewBox="0 0 16 16"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="m9 15.46 2.74-4-1-.71-2.1 3.09V2.16l2.1 3.09 1-.71L9 .54a1.25 1.25 0 0 0-2 0l-2.74 4 1 .71 2.12-3.09v11.68l-2.11-3.09-1 .71 2.74 4a1.25 1.25 0 0 0 1.99 0z">
                                                </path>
                                            </g>
                                        </svg>
                                        <p class="text-xl text-orange-400">{{ __('class-learn.button_position') }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-row gap-x-2 justify-center">
                                    <div class="flex flex-col justify-center items-center hover:bg-green-500/20 rounded-lg cursor-pointer transition-colors duration-300 p-1"
                                        @click="editImage=false">
                                        <svg class="w-[25px]" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path d="M4 12.6111L8.92308 17.5L20 6.5" stroke="#22c55e"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                </path>
                                            </g>
                                        </svg>
                                    </div>
                                    <div class="flex flex-col justify-center items-center hover:bg-red-500/20 rounded-lg cursor-pointer transition-colors duration-300 p-1"
                                        @click="cancelEditImage">
                                        <svg class="w-[25px]" viewBox="-0.5 0 25 25" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path d="M3 21.32L21 3.32001" stroke="#ef4444" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M3 3.32001L21 21.32" stroke="#ef4444" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                            </g>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Button Change Position -->
                            <div x-show="isEditPosition"
                                class="absolute bg-primary_white top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 rounded-xl p-3 select-none">
                                <div class="flex flex-row gap-x-3">

                                    <div class="flex flex-col items-center justify-center bg-primary_white p-1 rounded-lg cursor-pointer hover:bg-black/20 transition-colors duration-300"
                                        @click="positionImage = 'top'">
                                        <svg class="w-[25px]" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"
                                            fill="none">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <g fill="#2867a4">
                                                    <path
                                                        d="M2.5 2.5a.75.75 0 010-1.5H13a.75.75 0 010 1.5H2.5zM2.985 9.795a.75.75 0 001.06-.03L7 6.636v7.614a.75.75 0 001.5 0V6.636l2.955 3.129a.75.75 0 001.09-1.03l-4.25-4.5a.75.75 0 00-1.09 0l-4.25 4.5a.75.75 0 00.03 1.06z">
                                                    </path>
                                                </g>
                                            </g>
                                        </svg>
                                        <p class="text-secondary_blue text-base">{{ __('class-learn.top') }}</p>
                                    </div>
                                    <div class="flex flex-col items-center justify-center bg-primary_white p-1 rounded-lg cursor-pointer hover:bg-black/20 transition-colors duration-300"
                                        @click="positionImage = 'center'">
                                        <svg class="w-[35px]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                            fill="#2867a4">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M12.501 14.792l3.854 3.854-.707.707L13 16.705V23h-1v-6.293l-2.646 2.646-.707-.707zM8.647 6.354l3.854 3.854 3.854-3.854-.707-.707L13 8.295V2h-1v6.293L9.354 5.647zM6 13h13v-1H6z">
                                                </path>
                                                <path fill="none" d="M0 0h24v24H0z"></path>
                                            </g>
                                        </svg>
                                        <p class="text-secondary_blue text-base">{{ __('class-learn.center') }}</p>
                                    </div>
                                    <div class="flex flex-col items-center justify-center bg-primary_white p-1 rounded-lg cursor-pointer hover:bg-black/20 transition-colors duration-300"
                                        @click="positionImage = 'bottom'">
                                        <svg class="w-[25px]" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"
                                            fill="none">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <g fill="#2867a4">
                                                    <path
                                                        d="M7.75 1a.75.75 0 01.75.75v7.614l2.955-3.129a.75.75 0 011.09 1.03l-4.25 4.5a.747.747 0 01-.533.235h-.024a.747.747 0 01-.51-.211l-.004-.005a.862.862 0 01-.02-.02l-4.25-4.499a.75.75 0 011.091-1.03L7 9.364V1.75A.75.75 0 017.75 1zM2.5 13.5a.75.75 0 000 1.5H13a.75.75 0 000-1.5H2.5z">
                                                    </path>
                                                </g>
                                            </g>
                                        </svg>
                                        <p class="text-secondary_blue text-base">{{ __('class-learn.bottom') }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-row gap-x-2 justify-center">
                                    <div class="flex flex-col justify-center items-center hover:bg-green-500/20 rounded-lg cursor-pointer transition-colors duration-300 p-1"
                                        @click="isEditPosition = false">
                                        <svg class="w-[25px]" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path d="M4 12.6111L8.92308 17.5L20 6.5" stroke="#22c55e"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                </path>
                                            </g>
                                        </svg>
                                    </div>
                                    <div class="flex flex-col justify-center items-center hover:bg-red-500/20 rounded-lg cursor-pointer transition-colors duration-300 p-1"
                                        @click="closedEditPosition">
                                        <svg class="w-[25px]" viewBox="-0.5 0 25 25" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path d="M3 21.32L21 3.32001" stroke="#ef4444" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M3 3.32001L21 21.32" stroke="#ef4444" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                            </g>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="w-full border border-secondary_blue border-dashed rounded-lg bg-primary_white outline-8 outline-primary_white p-3"
                            wire:click="addContent" wire:target="addContent" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50">
                            <p class="text-center text-secondary_blue text-2xl font-bold">
                                {{ __('class-learn.add_content') }}</p>
                        </div>

                        <div class="w-full">
                            <template x-for="(content, index) in Object.values(contents)" :key="index">
                                <div>
                                    <template x-if="content.type == 'task'">
                                        <div>

                                        </div>
                                    </template>
                                    <template x-if="content.type == 'notification'">
                                        <div>
                                            notification
                                        </div>
                                    </template>
                                    <template x-if="content.type == 'material'">
                                        <div>
                                            material
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                    </div>
                </template>
                <!-- <div class="flex flex-col items-center justify-center h-full" x-init="console.log(classrooms)">
                    <p class="text-primary_black text-2xl font-bold mt-5">Classroom Found</p>
                    <button x-data="{ disabled: false }" :disabled="disabled" @click="disabled = true"
                        wire:click="addContent" wire:target="addContent" wire:loading.attr="disabled"
                        wire:loading.class="opacity-50">
                        Add Content
                    </button>
                </div> -->

            </div>
        </div>
        <script>
            function starting() {
                return {
                    isNav: false,
                    classrooms: @entangle('classrooms').live,
                    isLoading: @entangle('isLoading').live,
                    isTeacher: @entangle('isTeacher').live,
                    initContents: @entangle('contents').live,
                    contents: [],
                    editProfile: false,
                    savedTemp: {},
                    savedEditImage: {},
                    editTitle: "",
                    previewImage: "",
                    positionImage: "",
                    editImage: false,
                    isEditPosition: false,
                    saveEditPosition: {},
                    errorMax: "{{ __('class-learn.image.max') }}",
                    errorImage: "{{ __('class-learn.image.image') }}",
                    error: {
                        condition: false,
                        message: "",
                        title: ""
                    },
                    initData() {
                        this.editTitle = this.classrooms[0].title;
                        this.previewImage = this.classrooms[0].image;
                        this.positionImage = this.classrooms[0].position;
                        this.initContentsLive();
                    },
                    initContentsLive() {
                        if (this.initContents.length > 0) {
                            this.contents = this.initContents;
                        }
                    },
                    toggle() {
                        this.isNav = !this.isNav
                    },
                    check() {
                        console.log(this.classrooms)
                    },
                    initEdit() {
                        this.savedTemp = {
                            image: this.previewImage,
                            position: this.positionImage,
                            title: this.editTitle
                        }
                        this.editProfile = true;
                    },
                    cancelEdit() {
                        this.previewImage = this.savedTemp.image;
                        this.positionImage = this.savedTemp.position;
                        this.editTitle = this.savedTemp.title;
                        this.editImage = false;
                        this.editProfile = false;
                    },
                    openEditImage() {
                        this.savedEditImage = {
                            image: this.previewImage,
                            position: this.positionImage
                        }
                        this.editImage = true;
                    },
                    deletedImage() {
                        this.previewImage = "";
                        this.$refs.fileInput.value = "";
                    },
                    cancelEditImage() {
                        this.previewImage = this.savedEditImage.image;
                        this.positionImage = this.savedEditImage.position;
                        this.editImage = false;
                    },
                    openEditPosition() {
                        this.saveEditPosition = {
                            position: this.positionImage
                        }
                        this.isEditPosition = true;
                    },
                    closedEditPosition() {
                        this.positionImage = this.saveEditPosition.position;
                        this.isEditPosition = false;
                    },
                    handleFileChange(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.previewImage = URL.createObjectURL(file);
                        } else {
                            this.$refs.fileInput.value = ""; // Reset input jika batal
                            alert("Tidak ada file yang dipilih.");
                        }
                    },
                    showError(condition, message, title) {
                        this.error.condition = condition;
                        this.error.message = message;
                        this.error.title = title;
                        setTimeout(() => {
                            this.error.condition = false;
                        }, 3000);
                    },
                    savedContent() {
                        this.editProfile = false;
                        if (this.editTitle == "") {
                            this.showError(true, "{{ __('class-learn.title_can_not_empty') }}",
                                "{{ __('class-learn.warn') }}");
                            return;
                        }

                        if (this.editTitle.length <= 3) {
                            this.showError(true, "{{ __('class-learn.title_min') }}", "{{ __('class-learn.warn') }}");
                            return;
                        }

                        if (this.editTitle.length > 100) {
                            this.showError(true, "{{ __('class-learn.title_max') }}", "{{ __('class-learn.warn') }}");
                            return;
                        }

                        if (this.previewImage == this.classrooms[0].image || this.previewImage == "") {
                            const data = this.savedTemp = {
                                image_path: this.previewImage,
                                position: this.positionImage,
                                title: this.editTitle,
                                action: false
                            }
                            this.$wire.call('savedContent', data);
                        } else {
                            const data = this.savedTemp = {
                                image_path: this.previewImage,
                                position: this.positionImage,
                                title: this.editTitle,
                                action: true
                            }
                            this.$wire.call('savedContent', data);

                        }
                    }
                }
            }
        </script>
    </div>
