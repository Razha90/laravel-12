<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Content;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Validate;
use App\Models\Classroom;
use App\Models\ClassroomMember;

new #[Layout('components.layouts.classroom-learn')] class extends Component {
    use WithFileUploads;
    public $id;
    public $classrooms = [];
    public $isLoading = false;
    public $isTeacher = false;
    public $contents = [];
    public $secureIsTeacher;

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
            if (!$this->secureIsTeacher) {
                $this->dispatch('failed', ['message' => __('class-learn.error_auth')]);
                return;
            }
            $maxOrder = Content::where('classroom_id', $this->id)->max('order');
            $createContent = Content::create([
                'classroom_id' => $this->id,
                'title' => '',
                'description' => 'Description',
                'content' => '',
                'visibility' => false,
                'release' => null,
                'type' => 'task',
                'order' => $maxOrder + 1,
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
            if (!$this->secureIsTeacher) {
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
            $this->dispatch('success', ['message' => __('class-learn.success')]);
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
            $data = '';
            if (auth()->user()->id == $this->classrooms[0]['user_id']) {
                $this->isTeacher = true;
                $this->secureIsTeacher = true;
                // $data = Content::where('classroom_id', $this->id)->get();
                $data = Content::where('classroom_id', $this->id)->orderByRaw('ISNULL(`order`), `order` DESC, `created_at` DESC')->get();
            } else {
                $data = Content::where('classroom_id', $this->id)->where('visibility', true)->orderByRaw('ISNULL(`order`), `order` DESC, `created_at` DESC')->get();
            }
            $this->contents = $data->toArray();
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Load Content ' . $th);
        }
    }

    public function savedRuleClass($is_password, $password = null, $status, $ask_join)
    {
        try {
            $classroom = Classroom::find($this->id);
            if ($classroom) {
                $classroom->is_password = $is_password;
                if ($is_password) {
                    $classroom->password = $password;
                } else {
                    $classroom->password = null;
                }
                $classroom->status = $status;
                $classroom->ask_join = $ask_join;
                $classroom->save();
                $this->dispatch('success', ['message' => __('class-learn.success')]);
                $this->loadData();
                return [
                    'status' => true,
                ];
            } else {
                Log::error('Classroom not found');
                $this->dispatch('failed', ['message' => __('class-learn.not_found')]);
                return [
                    'status' => false,
                ];
            }
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Saved Rule Class' . $th);
            $this->dispatch('failed', ['message' => __('class-learn.error_server')]);
            return [
                'status' => false,
            ];
        }
    }

    public function getOutClass($id)
    {
        try {
            $classroom = ClassroomMember::where('classroom_id', $id)
                ->where('user_id', auth()->user()->id)
                ->first();
            if ($classroom) {
                $classroom->delete();
                $this->dispatch('success', ['message' => __('class-learn.success')]);
                return redirect()->route('classroom');
            } else {
                Log::error('Classroom not found');
                $this->dispatch('failed', ['message' => __('class-learn.not_found')]);
                return [
                    'status' => false,
                ];
            }
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Get Out Class' . $th);
            $this->dispatch('failed', ['message' => __('class-learn.error_server')]);
            return [
                'status' => false,
            ];
        }
    }

    public function deleteContent($id)
    {
        try {
            if (!$this->secureIsTeacher) {
                $this->dispatch('failed', ['message' => __('class-learn.error_auth')]);
                return [
                    'status' => false,
                ];
            }
            $content = Content::find($id);
            if ($content) {
                $content->delete();
                $this->dispatch('success', ['message' => __('class-learn.success')]);
                $this->loadContent();
                return [
                    'status' => true,
                ];
            } else {
                Log::error('Classroom not found');
                $this->dispatch('failed', ['message' => __('class-learn.not_found')]);
                return [
                    'status' => false,
                ];
            }
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Delete Content' . $th);
            $this->dispatch('failed', ['message' => __('class-learn.error_server')]);
            return [
                'status' => false,
            ];
        }
    }

    public function upContent($id, $direction = 'up')
    {
        try {
            if (!$this->secureIsTeacher) {
                $this->dispatch('failed', ['message' => __('class-learn.error_auth')]);
                return;
            }

            $content = Content::find($id);
            if (!$content) {
                throw new \Exception(__('class-learn.not_found'));
            }
            $currentOrder = $content->order;
            $targetOrder = $direction === 'up' ? $currentOrder + 1 : $currentOrder - 1;
            $swapContent = Content::where('order', $targetOrder)->first();
            if (!$swapContent) {
                return ['status' => false];
            }
            $content->order = $targetOrder;
            $content->save();

            $swapContent->order = $currentOrder;
            $swapContent->save();

            $this->dispatch('success', ['message' => 'Urutan berhasil diperbarui']);
            $this->loadContent();

            return ['status' => true];
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Eroor Up Content' . $th);
            $this->dispatch('failed', ['message' => __('class-learn.error_server')]);
            return [
                'status' => false,
            ];
        }
    }
}; ?>

<flux:main class="relative bg-white" x-data="starting">
    <flux:sidebar.toggle
        class="text-gray-500! cursor-pointer border transition-all hover:border-gray-400/50 hover:shadow-md lg:hidden"
        icon="bars-2" inset="left" />

    @vite(['resources/js/editor.js'])

    <div x-cloak x-data="{ alert: false, message: '' }"
        x-on:show-failed.window="(event) => { 
        message = event.detail[0].message;
        alert = true;
        setTimeout(() => alert = false, 5000);
        editProfile = true;
        $refs.fileInput.value = '';
        previewImage = classrooms[0].image;
    }"
        x-show="alert" x-transition
        class="animate-fade-up absolute bottom-5 left-5 z-30 mb-4 flex flex-row items-start rounded-lg bg-gray-800 p-4 text-sm text-red-400"
        role="alert">

        <svg class="me-3 inline h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
            fill="currentColor" viewBox="0 0 20 20">
            <path
                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
        </svg>
        <span class="sr-only">Error</span>
        <div>
            <span class="font-medium" x-text="message"></span>
        </div>
    </div>

    <div x-cloak x-data="{ alert: false, message: '' }"
        x-on:failed-content.window="(event) => { 
        message = event.detail[0].message;
        alert = true;
        setTimeout(() => alert = false, 5000);
    }"
        x-show="alert" x-transition
        class="animate-fade-up absolute bottom-5 left-5 z-30 mb-4 flex flex-row items-start rounded-lg bg-gray-800 p-4 text-sm text-red-400"
        role="alert">

        <svg class="me-3 inline h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
            fill="currentColor" viewBox="0 0 20 20">
            <path
                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
        </svg>
        <span class="sr-only">Error</span>
        <div>
            <span class="font-medium" x-text="message"></span>
        </div>
    </div>

    <div x-cloak x-show="error.condition" x-transition:enter="transition-transform duration-500 ease-in-out"
        x-transition:enter-start="scale-x-0" x-transition:enter-end="scale-x-100"
        x-transition:leave="transition-transform duration-500 ease-in-out" x-transition:leave-start="scale-x-100"
        x-transition:leave-end="scale-x-0"
        class="absolute left-3 top-3 z-30 origin-left overflow-hidden border-l-4 border-orange-500 bg-orange-100 p-4 text-orange-700"
        role="alert">
        <p class="font-bold" x-text="error.title"></p>
        <p x-text="error.message"></p>
    </div>

    <div aria-hidden="true" x-data="{ open: false }" x-cloak
        x-on:shared-modal.window="(event) => {
    open = true
}"x-show="open" x-transition
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
                    <p class="text-secondary_blue text-2xl">{{ __('class-learn.share') }}</p>
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
                        const fullUrl = '{{ route('classroom') }}';
                        const final = fullUrl + '?code=' + classrooms[0].code;
                        return final;
                    },
                    copyPaste() {
                        navigator.clipboard.writeText(this.cleanUrl)
                            .then(() => {
                                this.$dispatch('success', [{ message: '{{ __('class-learn.copy_sucess') }}' }]);
                            })
                            .catch(err => {
                                this.$dispatch('failed', [{ message: '{{ __('class-learn.copy_failed') }}' }]);
                            });
                    },
                    copyCode() {
                        navigator.clipboard.writeText(classrooms[0].code)
                            .then(() => {
                                this.$dispatch('success', [{ message: '{{ __('class-learn.copy_sucess') }}' }]);
                            })
                            .catch(err => {
                                this.$dispatch('failed', [{ message: '{{ __('class-learn.copy_failed') }}' }]);
                            });
                    },
                    shareData: {
                        status: false,
                        password: '',
                        ask_join: false,
                        is_password: false,
                    },
                    initShareClass() {
                        this.shareData.status = classrooms[0].status == 1 ? true : false;
                        this.shareData.password = classrooms[0].password;
                        this.shareData.is_password = classrooms[0].is_password == 1 ? true : false;
                        this.shareData.ask_join = classrooms[0].ask_join == 1 ? true : false;
                    },
                    tab: 'share',
                    errormsg: '',
                    async sendSaved() {
                        if (this.shareData.is_password) {
                            if (!this.shareData.password || this.shareData.password.length < 8) {
                                this.$dispatch('failed', [{ message: '{{ __(key: 'class-learn.password_rule') }}' }]);
                                this.errormsg = '{{ __('class-learn.password_rule') }}';
                                this.shareData.is_password = false;
                                return;
                            }
                        }
                
                        const datas = await this.$wire.savedRuleClass(this.shareData.is_password, this.shareData.password,
                            this.shareData.status, this.shareData.ask_join);
                        if (!datas.status) {
                            this.shareData = {
                                status: classrooms[0].status == 1 ? true : false,
                                password: classrooms[0].password,
                                is_password: classrooms[0].is_password == 1 ? true : false,
                                ask_join: classrooms[0].ask_join == 1 ? true : false,
                            }
                        } else {
                            this.errormsg = '';
                        }
                        return;
                    }
                }" class="mb-10 w-full" x-init="initShareClass">

                    <div class="mb-5 flex justify-center gap-x-4" x-show="isTeacher">
                        <button @click="tab = 'share'"
                            :class="tab === 'share' ?
                                'text-primary_white text-lg border-2 rounded-xl border-secondary_blue flex items-center justify-center py-2 px-4 bg-secondary_blue hover:bg-secondary_blue/20 hover:text-secondary_blue cursor-pointer transition-all' :
                                'text-secondary_blue border-2 border-secondary_blue rounded-xl bg-primary_white flex items-center justify-center py-2 px-4 cursor-pointer hover:bg-secondary_blue/20 transition-all text-lg'"
                            class="pb-2">
                            {{ __('class-learn.share') }}
                        </button>
                        <button @click="tab = 'setting'"
                            :class="tab === 'setting' ?
                                'text-primary_white text-lg border-2 rounded-xl border-secondary_blue flex items-center justify-center py-2 px-4 bg-secondary_blue hover:bg-secondary_blue/20 hover:text-secondary_blue cursor-pointer transition-all' :
                                'text-secondary_blue border-2 border-secondary_blue rounded-xl bg-primary_white flex items-center justify-center py-2 px-4 cursor-pointer hover:bg-secondary_blue/20 transition-all text-lg'"
                            class="pb-2">
                            {{ __('class-learn.setting') }}
                        </button>
                    </div>
                    <div class="relative h-auto w-full">
                        <div x-show="tab === 'share'"
                            class="text-secondary_blue animate-fade flex h-full w-full flex-col items-center justify-center bg-white">
                            <div class="flex w-full items-center justify-center">
                                <div @click="copyCode"
                                    class="relative cursor-pointer select-none text-4xl before:absolute before:bottom-0 before:left-0 before:h-full before:w-full before:origin-bottom before:scale-y-[0.35] before:bg-sky-200 before:transition-transform before:duration-500 before:ease-in-out hover:before:scale-y-100">
                                    <span x-text="classrooms[0].code"
                                        class="text-secondary_blue relative w-full text-center text-4xl font-bold uppercase tracking-widest"></span>
                                </div>
                            </div>
                            <div
                                class="bg-primary_white mx-auto mt-10 flex max-w-sm flex-row items-center justify-between rounded-full px-2 py-2 shadow-xl">
                                <div x-text="cleanUrl" class="text-secondary_blue max-w-[300px] truncate text-lg">
                                </div>
                                <button @click="copyPaste"
                                    class="bg-secondary_blue text-primary_white border-primary_white hover:bg-primary_white hover:border-secondary_blue hover:text-secondary_blue cursor-pointer rounded-full border-2 px-4 py-2 text-center text-lg transition-all">{{ __('class-learn.copy') }}</button>
                            </div>
                        </div>

                        <div x-show="tab === 'setting'"
                            class="text-secondary_blue animate-fade flex h-full w-full flex-col justify-center bg-white">
                            <div>
                                <div class="flex flex-col items-center gap-x-2">
                                    <label for="statusclass"
                                        class="text-secondary_blue text-xl font-bold">{{ __('class-learn.status_class') }}</label>
                                    <div x-show="shareData.status" @click="shareData.status = false"
                                        class="bg-primary_white cursor-pointer rounded-full border-2 border-green-500 px-4 py-px text-lg text-green-500 transition-all hover:bg-green-200">
                                        {{ __('class-learn.status_active') }}</div>
                                    <div x-show="!shareData.status" @click="shareData.status = true"
                                        class="bg-primary_white cursor-pointer rounded-full border-2 border-red-500 px-4 py-px text-lg text-red-500 transition-all hover:bg-red-200">
                                        {{ __('class-learn.status_inactive') }}</div>

                                </div>
                                <div class="flex flex-row items-center justify-center gap-x-2">
                                    <p class="text-sm">{{ __('class-learn.status_class_detail') }} </p>
                                    <div class="group relative">
                                        <p
                                            class="flex h-[20px] w-[20px] cursor-pointer items-center justify-center rounded-full bg-blue-500 p-1 text-sm text-white">
                                            i
                                        </p>
                                        <div
                                            class="absolute left-1/2 top-full mt-2 w-[200px] -translate-x-1/2 scale-0 transform rounded bg-black p-2 text-xs text-white transition-all group-hover:scale-100">
                                            {{ __('class-learn.status_class_message') }}
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="mt-5 flex flex-col items-center justify-center">
                                <div class="text-secondary_blue text-lg font-bold">{{ __('class-learn.ask_join') }}
                                </div>
                                <div x-show="shareData.ask_join" @click="shareData.ask_join = false"
                                    class="bg-primary_white cursor-pointer rounded-full border-2 border-green-500 px-4 py-px text-lg text-green-500 transition-all hover:bg-green-200">
                                    {{ __('class-learn.yes') }}</div>
                                <div x-show="!shareData.ask_join" @click="shareData.ask_join = true"
                                    class="bg-primary_white cursor-pointer rounded-full border-2 border-red-500 px-4 py-px text-lg text-red-500 transition-all hover:bg-red-200">
                                    {{ __('class-learn.no') }}</div>
                            </div>
                            <div class="mt-5 flex flex-col items-center justify-center">
                                <div class="text-secondary_blue text-lg font-bold">{{ __('class-learn.password') }}
                                </div>
                                <div x-show="shareData.is_password" @click="shareData.is_password = false"
                                    class="bg-primary_white cursor-pointer rounded-full border-2 border-green-500 px-4 py-px text-lg text-green-500 transition-all hover:bg-green-200">
                                    {{ __('class-learn.yes') }}</div>
                                <div x-show="!shareData.is_password" @click="shareData.is_password = true"
                                    class="bg-primary_white cursor-pointer rounded-full border-2 border-red-500 px-4 py-px text-lg text-red-500 transition-all hover:bg-red-200">
                                    {{ __('class-learn.no') }}</div>
                                <input
                                    class="text-secondary_blue border-secondary_blue focus:border-secondary_blue mt-2 border-2 px-4 py-2 focus:outline-none"
                                    x-model="shareData.password" x-show="shareData.is_password" maxlength="20" />
                                <p x-show="errormsg.length > 0" x-text="errormsg"
                                    class="text-sm italic text-red-500"></p>
                            </div>
                            <div class="mt-5 flex justify-center">
                                <button @Click="sendSaved"
                                    class="bg-secondary_blue border-secondary_blue text-primary_white hover:bg-primary_white hover:text-secondary_blue cursor-pointer rounded-full border px-4 py-2 text-lg font-bold transition-all">{{ __('class-learn.save') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div aria-hidden="true" x-data="{
        open: false,
        loading: false,
        async getOutClass() {
            this.loading = true;
            await this.$wire.getOutClass(classrooms[0].id);
            this.loading = false;
        }
    }" x-cloak
        x-on:getout-class.window="(event) => {
    open = true
}"x-show="open"
        class="animate-fade fixed left-0 right-0 top-0 z-50 flex h-screen w-screen items-center justify-center overflow-y-auto overflow-x-hidden bg-black/20 backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-md rounded-xl bg-white p-2" @click.away = "open = false">
            <div class="flex flex-row items-center justify-between">
                <div class="flex flex-row items-center gap-x-3 bg-white p-4">
                    <div class="flex- items-center justify-center rounded-xl bg-red-200 p-1">
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
                    </div>
                    <p class="text-accent_red text-2xl font-bold">{{ __('welcome.logout') }}</p>
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
                <p class="text-accent_red mb-5 text-center text-lg">{{ __('class-learn.confirm_getout') }}</p>
                <div class="flex flex-row justify-center gap-x-3">
                    <button @click="open = false"
                        class="bg-secondary_blue text-primary_white border-secondary_blue hover:bg-primary_white hover:text-secondary_blue cursor-pointer rounded-2xl border-2 px-4 py-1 transition-all">{{ __('class-learn.cancel') }}</button>
                    <button @click="getOutClass"
                        class="bg-accent_red text-primary_white border-accent_red hover:bg-primary_white hover:text-accent_red cursor-pointer rounded-2xl border-2 px-4 py-1 transition-all">
                        <svg aria-hidden="true" x-show="loading"
                            class="inline h-5 w-5 animate-spin fill-pink-600 text-gray-200 dark:text-gray-600"
                            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                fill="currentColor" />
                            <path
                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                fill="currentFill" />
                        </svg>
                        <span x-show="!loading">{{ __('welcome.logout') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full">
        <div class="mx-auto max-w-[1200px]">
            <template x-if="isLoading">
                <div class="flex h-[200px] w-full items-center justify-center">
                    <div
                        class="animate-pulse rounded-full bg-blue-900 px-4 py-2 text-center text-base font-medium leading-none text-blue-200">
                        {{ __('add-class.loading') }}....</div>
                </div>
            </template>

            <!-- Not Found -->
            <template x-if="!isLoading && !classrooms.length">
                <div class="flex h-full w-full flex-col items-center justify-center gap-y-4">
                    <div
                        class="animate-pulse rounded-full border-2 border-red-500 bg-white px-4 py-2 text-center text-5xl font-medium leading-none text-red-700">
                        {{ __('class-learn.not_found') }}....</div>
                    <p @click="window.location.href = '{{ route('classroom') }}'"
                        class="text-secondary_blue cursor-pointer underline">Kembali</p>
                </div>
            </template>

            <!-- Content template -->
            <template x-if="classrooms.length > 0">
                <div class="flex min-h-[400px] w-full flex-col justify-start gap-y-3 px-5 pt-5">
                    <!-- Wrapper Profile Class -->
                    <div x-bind:style="'background-image: url(' + previewImage + '); background-position: center ' + positionImage +
                        ';'"
                        class="relative flex max-h-[400px] min-h-[300px] w-full flex-col items-end justify-end rounded-xl bg-cover bg-center bg-no-repeat p-3 before:absolute before:inset-0 before:rounded-xl before:bg-black before:opacity-15 hover:before:opacity-35 hover:before:transition-opacity"
                        x-ref="imageContainer">

                        <!-- Title -->
                        <div class="relative flex min-h-[100px] w-full items-center rounded-md bg-gray-400/10 bg-clip-padding px-3 backdrop-blur-sm backdrop-filter"
                            x-show="!editProfile">
                            <p x-text="classrooms[0].title" class="absolute z-50 text-4xl font-bold text-white">
                            </p>
                        </div>

                        <!-- Button Edit Profile -->
                        <div class="bg-primary_white absolute right-2 top-2 cursor-pointer rounded-full p-3 transition-opacity hover:opacity-70"
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
                        <div class="absolute right-2 top-2 flex flex-col gap-y-4" x-show="editProfile && !editImage">
                            <div class="bg-primary_white cursor-pointer rounded-full p-3 transition-opacity hover:opacity-70"
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
                            <div class="bg-primary_white cursor-pointer rounded-full p-3 transition-opacity hover:opacity-70"
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
                        <div class="relative flex min-h-[100px] w-full items-center rounded-md bg-gray-400/10 bg-clip-padding px-3 backdrop-blur-sm backdrop-filter"
                            x-show="editProfile && !editImage" x-init="initData">
                            <!-- <p x-text="classrooms[0].title" class="absolute z-50 text-4xl font-bold text-white"></p> -->
                            <input type="text" x-model="editTitle"
                                class="border-secondary_blue text-secondary_black w-full rounded-xl bg-white p-2 text-2xl font-bold"
                                placeholder="{{ __('class-learn.title') }}" />
                        </div>

                        <!-- Button Edit Image -->
                        <div class="border-secondary_blue outline-primary_white absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 cursor-pointer items-center justify-center rounded-xl border-4 border-dashed bg-white px-3 py-2 outline-8 transition-colors duration-300 hover:opacity-60"
                            x-show="editProfile && !editImage" @click="openEditImage">
                            <svg class="w-[35px]" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path
                                        d="M11 4.00023H6.8C5.11984 4.00023 4.27976 4.00023 3.63803 4.32721C3.07354 4.61483 2.6146 5.07377 2.32698 5.63826C2 6.27999 2 7.12007 2 8.80023V17.2002C2 18.8804 2 19.7205 2.32698 20.3622C2.6146 20.9267 3.07354 21.3856 3.63803 21.6732C4.27976 22.0002 5.11984 22.0002 6.8 22.0002H15.2C16.8802 22.0002 17.7202 22.0002 18.362 21.6732C18.9265 21.3856 19.3854 20.9267 19.673 20.3622C20 19.7205 20 18.8804 20 17.2002V13.0002M7.99997 16.0002H9.67452C10.1637 16.0002 10.4083 16.0002 10.6385 15.945C10.8425 15.896 11.0376 15.8152 11.2166 15.7055C11.4184 15.5818 11.5914 15.4089 11.9373 15.063L21.5 5.50023C22.3284 4.6718 22.3284 3.32865 21.5 2.50023C20.6716 1.6718 19.3284 1.6718 18.5 2.50022L8.93723 12.063C8.59133 12.4089 8.41838 12.5818 8.29469 12.7837C8.18504 12.9626 8.10423 13.1577 8.05523 13.3618C7.99997 13.5919 7.99997 13.8365 7.99997 14.3257V16.0002Z"
                                        stroke="#2867a4" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                    </path>
                                </g>
                            </svg>
                            <p class="text-secondary_blue">{{ __('class-learn.butoon_edit_image') }}</p>
                            <!-- <input type="file" accept="image/*" class="hidden" x-ref="fileInput"
                                @change="const file = $event.target.files[0]; previewImage = file ? URL.createObjectURL(file) : ''"> -->
                        </div>

                        <!-- Button Chose Edit Image -->
                        <div x-show="editImage && !isEditPosition"
                            class="bg-primary_white absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 flex-col gap-y-3 rounded-xl p-3">
                            <div class="flex flex-row gap-x-2">
                                <div class="hover:bg-secondary_blue/20 flex cursor-pointer flex-col items-center justify-center rounded-lg p-1 transition-colors duration-300"
                                    @click="$refs.fileInput.click()">
                                    <svg class="w-[35px]" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path
                                                d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H12M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125"
                                                stroke="#2867a4" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                            <path d="M17.5 21L17.5 15M17.5 15L20 17.5M17.5 15L15 17.5" stroke="#2867a4"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                        </g>
                                    </svg>
                                    <p class="text-secondary_blue text-xl">{{ __('class-learn.upload_image') }}
                                    </p>
                                    <input wire:model.live="image" type="file"
                                        accept="image/png, image/jpg, image/jpeg, image/webp" class="hidden"
                                        x-ref="fileInput" @change="handleFileChange">

                                </div>
                                <div class="flex cursor-pointer flex-col items-center justify-center rounded-lg p-1 transition-colors duration-300 hover:bg-red-500/20"
                                    @click="deletedImage">
                                    <svg class="w-[35px]" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
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
                                            <path d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z"
                                                stroke="#dc2626" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                    <p class="text-xl text-red-500">{{ __('class-learn.button_delete') }}</p>
                                </div>
                                <div class="flex cursor-pointer flex-col items-center justify-center rounded-lg p-1 transition-colors duration-300 hover:bg-orange-200/20"
                                    @click="openEditPosition">
                                    <svg class="w-[35px]" fill="#fb923c" viewBox="0 0 16 16"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
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
                            <div class="flex flex-row justify-center gap-x-2">
                                <div class="flex cursor-pointer flex-col items-center justify-center rounded-lg p-1 transition-colors duration-300 hover:bg-green-500/20"
                                    @click="editImage=false">
                                    <svg class="w-[25px]" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M4 12.6111L8.92308 17.5L20 6.5" stroke="#22c55e" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="flex cursor-pointer flex-col items-center justify-center rounded-lg p-1 transition-colors duration-300 hover:bg-red-500/20"
                                    @click="cancelEditImage">
                                    <svg class="w-[25px]" viewBox="-0.5 0 25 25" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
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
                            class="bg-primary_white absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 select-none rounded-xl p-3">
                            <div class="flex flex-row gap-x-3">

                                <div class="bg-primary_white flex cursor-pointer flex-col items-center justify-center rounded-lg p-1 transition-colors duration-300 hover:bg-black/20"
                                    @click="positionImage = 'top'">
                                    <svg class="w-[25px]" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"
                                        fill="none">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
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
                                <div class="bg-primary_white flex cursor-pointer flex-col items-center justify-center rounded-lg p-1 transition-colors duration-300 hover:bg-black/20"
                                    @click="positionImage = 'center'">
                                    <svg class="w-[35px]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                        fill="#2867a4">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
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
                                <div class="bg-primary_white flex cursor-pointer flex-col items-center justify-center rounded-lg p-1 transition-colors duration-300 hover:bg-black/20"
                                    @click="positionImage = 'bottom'">
                                    <svg class="w-[25px]" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"
                                        fill="none">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
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
                            <div class="flex flex-row justify-center gap-x-2">
                                <div class="flex cursor-pointer flex-col items-center justify-center rounded-lg p-1 transition-colors duration-300 hover:bg-green-500/20"
                                    @click="isEditPosition = false">
                                    <svg class="w-[25px]" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M4 12.6111L8.92308 17.5L20 6.5" stroke="#22c55e" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="flex cursor-pointer flex-col items-center justify-center rounded-lg p-1 transition-colors duration-300 hover:bg-red-500/20"
                                    @click="closedEditPosition">
                                    <svg class="w-[25px]" viewBox="-0.5 0 25 25" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
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

                    <div class="border-secondary_blue bg-primary_white outline-primary_white w-full rounded-lg border border-dashed p-3 outline-8"x-show="isTeacher"
                        wire:click="addContent" wire:target="addContent" wire:loading.attr="disabled"
                        wire:loading.class="opacity-50">
                        <p class="text-secondary_blue text-center text-2xl font-bold">
                            {{ __('class-learn.add_content') }}</p>
                    </div>

                    <div class="mt-5 flex w-full flex-col gap-y-5">
                        <template x-for="(content, index) in Object.values(contents)" :key="content.id">
                            <div class="flex flex-row items-start gap-x-2">
                                <template x-if="content.type == 'task'">
                                    <div
                                        class="flex w-full flex-row items-start justify-between gap-x-2 rounded-2xl border border-gray-300 p-3">
                                        <div class="flex w-full flex-row items-center justify-between gap-x-2">
                                            <div class="flex flex-col gap-y-2">
                                                <div class="flex flex-row items-center gap-x-1">
                                                    <flux:icon.clipboard-document-list class="text-gray-500" />
                                                    <flux:text class="text-lg text-gray-500">
                                                        {{ __('class-learn.task') }}</flux:text>
                                                </div>
                                                <p x-text="content.title.length > 0 ? content.title : '[ {{ __('class-learn.no_title') }} ]'"
                                                    class="line-clamp-1 text-xl text-gray-500"></p>
                                            </div>
                                            <template x-if="content.visibility == '1' && !isTeacher">
                                                <div class="flex w-[135px] flex-col items-center justify-start">
                                                    <div class="flex flex-row items-center gap-x-1">
                                                        <flux:icon.clock class="text-gray-500" />
                                                        <flux:text class="text-lg text-gray-500">
                                                            {{ __('class-learn.deadline') }}</flux:text>
                                                    </div>
                                                    <div class="w-full text-center">
                                                        <p class="text-gray-500"
                                                            x-text="getRemainingTime(content.deadline)"></p>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="content.visibility == '0'">
                                                <div class="px-3">
                                                    <p class="bg-secondary_blue/20 text-secondary_blue rounded-xl px-3 py-1">
                                                        {{ __('add-task.draft') }}</p>
                                                </div>
                                            </template>
                                            <template x-if="content.visibility == '1' && isTeacher">
                                                <div class="px-3">
                                                    <p class="bg-green-500/20 text-green-500 rounded-xl px-3 py-1">
                                                        {{ __('class-learn.publish') }}</p>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex items-center">
                                            <flux:button @click="goTaskPage(content.id)" icon="eye">
                                                {{ __('class-learn.detail') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="content.type == 'notification'">
                                    <div
                                        class="text-secondary_blue flex w-full justify-center gap-x-2 rounded-2xl border border-gray-300 p-3 min-h-[80px] relative">
                                        <template x-if="content.visibility == '0'">
                                                <div class="px-3 absolute top-3 right-3">
                                                    <p class="bg-secondary_blue/20 text-secondary_blue rounded-xl px-3 py-1">
                                                        {{ __('add-task.draft') }}</p>
                                                </div>
                                            </template>
                                            <template x-if="content.visibility == '1' && isTeacher">
                                                <div class="px-3 absolute top-3 right-3">
                                                    <p class="bg-green-500/20 text-green-500 rounded-xl px-3 py-1">
                                                        {{ __('class-learn.publish') }}</p>
                                                </div>
                                            </template>
                                        <div class="prose prose:xl prose-h2:mb-0 prose-h2:mt-2 prose-h1:mb-0 w-2xl max-w-2xl prose-p:mb-0 prose-p:mt-0"
                                            x-html='editorHtml(content.content)'>

                                        </div>
                                    </div>
                                </template>
                                <template x-if="content.type == 'material'">
                                    <div
                                        class="flex w-full flex-row items-start justify-between gap-x-2 rounded-2xl border border-gray-300 p-3">
                                        <div class="flex w-full flex-row items-center justify-between gap-x-2">
                                            <div class="flex flex-col gap-y-2">
                                                <div class="flex flex-row items-center gap-x-1">
                                                    <flux:icon.book-open class="text-gray-500" />
                                                    <flux:text class="text-lg text-gray-500">
                                                        {{ __('class-learn.material_read') }}</flux:text>
                                                </div>
                                                <p x-text="content.title.length > 0 ? content.title : '[ {{ __('class-learn.no_title') }} ]'"
                                                    class="line-clamp-1 text-xl text-gray-500"></p>
                                            </div>
                                            <template x-if="content.visibility == '1' && !isTeacher">
                                                <div class="flex w-[135px] flex-col items-center justify-start">
                                                    <div class="flex flex-row items-center gap-x-1">
                                                        <flux:icon.bolt class="text-gray-500" />
                                                        <flux:text class="text-lg text-gray-500">
                                                            {{ __('class-learn.progress') }}</flux:text>
                                                    </div>
                                                    <div x-data="circularProgress(75)" class="relative h-[50px] w-[50px]">
                                                        <svg class="h-full w-full -rotate-90 transform"
                                                            viewBox="0 0 50 50">
                                                            <circle class="text-gray-300" stroke-width="4"
                                                                stroke="currentColor" fill="transparent" r="22"
                                                                cx="25" cy="25" />

                                                            <circle class="text-blue-500 transition-all duration-300"
                                                                stroke-width="4" stroke-dasharray="138.2"
                                                                :stroke-dashoffset="138.2 - (percent / 100) * 138.2"
                                                                stroke-linecap="round" stroke="currentColor"
                                                                fill="transparent" r="22" cx="25"
                                                                cy="25" />
                                                        </svg>
                                                        <div
                                                            class="absolute inset-0 flex items-center justify-center text-xs font-semibold text-blue-600">
                                                            <span x-text="`${percent}%`"></span>
                                                        </div>
                                                    </div>
                                                    <script>
                                                        function circularProgress(initial) {
                                                            return {
                                                                percent: initial,
                                                            }
                                                        }
                                                    </script>
                                                </div>
                                            </template>
                                            <template x-if="content.visibility == '0'">
                                                <div class="px-3">
                                                    <p class="bg-secondary_blue/20 text-secondary_blue rounded-xl px-3 py-1">
                                                        {{ __('add-task.draft') }}</p>
                                                </div>
                                            </template>
                                            <template x-if="content.visibility == '1' && isTeacher">
                                                <div class="px-3">
                                                    <p class="bg-green-500/20 text-green-500 rounded-xl px-3 py-1">
                                                        {{ __('class-learn.publish') }}</p>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex items-center">
                                            <flux:button @click="goReadPage(content.id)" icon="eye">
                                                {{ __('class-learn.detail') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="isTeacher">
                                    <div class="mb-5 flex flex-col justify-center gap-y-2">
                                        <template x-if="content.visibility == '1'">
                                            <div class="flex flex-row justify-center gap-x-2">
                                                <button x-show="Number(content.order) != minOrder"
                                                    @click="downContent(content.id)"
                                                    class="text-secondary_blue border-secondary_blue/20 hover:border-secondary_blue/80 hover:bg-secondary_blue/30 flex w-[50px] cursor-pointer flex-col items-center rounded-xl border p-2 transition-all">
                                                    <svg class="w-[25px] -rotate-90" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                            stroke-linejoin="round"></g>
                                                        <g id="SVGRepo_iconCarrier">
                                                            <path
                                                                d="M16.1795 3.26875C15.7889 2.87823 15.1558 2.87823 14.7652 3.26875L8.12078 9.91322C6.94952 11.0845 6.94916 12.9833 8.11996 14.155L14.6903 20.7304C15.0808 21.121 15.714 21.121 16.1045 20.7304C16.495 20.3399 16.495 19.7067 16.1045 19.3162L9.53246 12.7442C9.14194 12.3536 9.14194 11.7205 9.53246 11.33L16.1795 4.68297C16.57 4.29244 16.57 3.65928 16.1795 3.26875Z"
                                                                fill="currentColor"></path>
                                                        </g>
                                                    </svg>
                                                    <p class="text-sm">{{ __('class-learn.down') }}</p>
                                                    </butto>
                                                    <button x-show="Number(content.order) != maxOrder"
                                                        @click="upContent(content.id)"
                                                        class="text-secondary_blue border-secondary_blue/20 hover:border-secondary_blue/80 hover:bg-secondary_blue/30 flex w-[50px] cursor-pointer flex-col items-center rounded-xl border p-2 transition-all">
                                                        <svg class="w-[25px] rotate-90" viewBox="0 0 24 24"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                                stroke-linejoin="round"></g>
                                                            <g id="SVGRepo_iconCarrier">
                                                                <path
                                                                    d="M16.1795 3.26875C15.7889 2.87823 15.1558 2.87823 14.7652 3.26875L8.12078 9.91322C6.94952 11.0845 6.94916 12.9833 8.11996 14.155L14.6903 20.7304C15.0808 21.121 15.714 21.121 16.1045 20.7304C16.495 20.3399 16.495 19.7067 16.1045 19.3162L9.53246 12.7442C9.14194 12.3536 9.14194 11.7205 9.53246 11.33L16.1795 4.68297C16.57 4.29244 16.57 3.65928 16.1795 3.26875Z"
                                                                    fill="currentColor"></path>
                                                            </g>
                                                        </svg>
                                                        <p class="text-sm">{{ __('class-learn.up') }}</p>
                                                    </button>
                                            </div>
                                        </template>
                                        <div class="animate-fade flex flex-row gap-x-2">
                                            <button @click="goEditPage(content.id)"
                                                class="text-secondary_blue/80 bg-secondary_blue/20 hover:bg-secondary_blue/60 hover:text-primary_white flex cursor-pointer flex-col items-center justify-center rounded-xl p-3 transition-all">
                                                <div class="">
                                                    <svg class="h-[25px] w-[25px]" viewBox="0 0 24 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                            stroke-linejoin="round"></g>
                                                        <g id="SVGRepo_iconCarrier">
                                                            <path
                                                                d="M20.1497 7.93997L8.27971 19.81C7.21971 20.88 4.04971 21.3699 3.27971 20.6599C2.50971 19.9499 3.06969 16.78 4.12969 15.71L15.9997 3.84C16.5478 3.31801 17.2783 3.03097 18.0351 3.04019C18.7919 3.04942 19.5151 3.35418 20.0503 3.88938C20.5855 4.42457 20.8903 5.14781 20.8995 5.90463C20.9088 6.66146 20.6217 7.39189 20.0997 7.93997H20.1497Z"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                            </path>
                                                            <path d="M21 21H12" stroke="currentColor"
                                                                stroke-width="1.5" stroke-linecap="round"
                                                                stroke-linejoin="round"></path>
                                                        </g>
                                                    </svg>
                                                </div>
                                                <p>{{ __('class-learn.button_edit') }}</p>
                                            </button>
                                            <button x-data="{ asking: false, deleteContent(id) { $wire.deleteContent(id) } }"
                                                class="text-accent_red/80 bg-accent_red/20 hover:bg-accent_red/60 hover:text-primary_white animate-fade flex h-full w-full cursor-pointer flex-col items-center justify-center rounded-xl p-3 transition-all"
                                                @click="asking=true" @click.away="asking=false">
                                                <div x-show="!asking">
                                                    <svg class="h-[25px] w-[25px]" viewBox="0 0 1024 1024"
                                                        class="icon" version="1.1"
                                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                            stroke-linejoin="round"></g>
                                                        <g id="SVGRepo_iconCarrier">
                                                            <path
                                                                d="M960 160h-291.2a160 160 0 0 0-313.6 0H64a32 32 0 0 0 0 64h896a32 32 0 0 0 0-64zM512 96a96 96 0 0 1 90.24 64h-180.48A96 96 0 0 1 512 96zM844.16 290.56a32 32 0 0 0-34.88 6.72A32 32 0 0 0 800 320a32 32 0 1 0 64 0 33.6 33.6 0 0 0-9.28-22.72 32 32 0 0 0-10.56-6.72zM832 416a32 32 0 0 0-32 32v96a32 32 0 0 0 64 0v-96a32 32 0 0 0-32-32zM832 640a32 32 0 0 0-32 32v224a32 32 0 0 1-32 32H256a32 32 0 0 1-32-32V320a32 32 0 0 0-64 0v576a96 96 0 0 0 96 96h512a96 96 0 0 0 96-96v-224a32 32 0 0 0-32-32z"
                                                                fill="currentColor"></path>
                                                            <path
                                                                d="M384 768V352a32 32 0 0 0-64 0v416a32 32 0 0 0 64 0zM544 768V352a32 32 0 0 0-64 0v416a32 32 0 0 0 64 0zM704 768V352a32 32 0 0 0-64 0v416a32 32 0 0 0 64 0z"
                                                                fill="currentColor"></path>
                                                        </g>
                                                    </svg>
                                                </div>
                                                <p x-show="!asking" class="animate-fade">
                                                    {{ __('class-learn.button_delete') }}</p>
                                                <p class="h-full w-full p-3" @click="deleteContent(content.id)"
                                                    x-show="asking" @click="">
                                                    {{ __('class-learn.yes') }}</p>
                                            </button>

                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            <!-- <div class="flex h-full flex-col items-center justify-center" x-init="console.log(classrooms)">
                    <p class="text-primary_black mt-5 text-2xl font-bold">Classroom Found</p>
                    <button x-data="{ disabled: false }" :disabled="disabled" @click="disabled = true"
                        wire:click="addContent" wire:target="addContent" wire:loading.attr="disabled"
                        wire:loading.class="opacity-50">
                        Add Content
                    </button>
                </div> -->
        </div>
    </div>
</flux:main>
<script>
    function starting() {
        return {
            isNav: false,
            classrooms: @entangle('classrooms').live,
            isLoading: @entangle('isLoading').live,
            isTeacher: @entangle('isTeacher'),
            contents: @entangle('contents').live,
            idClassrooom: @entangle('id'),
            editProfile: false,
            savedTemp: {},
            savedEditImage: {},
            editTitle: "",
            previewImage: "",
            positionImage: "",
            editImage: false,
            isEditPosition: false,
            saveEditPosition: {},
            stopUpDown: false,
            errorMax: "{{ __('class-learn.image.max') }}",
            errorImage: "{{ __('class-learn.image.image') }}",
            error: {
                condition: false,
                message: "",
                title: ""
            },
            get maxOrder() {
                return Math.max(...Object.values(this.contents).map(c => Number(c.order)));
            },
            get minOrder() {
                return Math.min(
                    ...Object.values(this.contents)
                    .map(c => Number(c.order))
                    .filter(order => order >= 1)
                );
            },
            editorHtml(data) {
                const parsedData = JSON.parse(data);
                // const edjsParser = window.edtsHTML();
                const edjsParser = window.edtsHTML({
                    raw: (block) => {
                        return block.data?.html || '';
                    },
                    attaches: (block) => {
                        const {
                            file,
                            title,
                        } = block.data;
                        console.log(block);
                        return `
      <div class="my-4 p-4 border rounded bg-gray-100 text-sm flex flex-row items-center gap-x-px">
      <svg class="w-[35px] h-[35px] text-secondary_blue" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.18C2 19.83 4.17 22 7.81 22H16.18C19.82 22 21.99 19.83 21.99 16.19V7.81C22 4.17 19.83 2 16.19 2ZM16.37 14.35L14.15 16.57C13.61 17.11 12.91 17.37 12.21 17.37C11.51 17.37 10.8 17.1 10.27 16.57C9.75 16.05 9.46 15.36 9.46 14.63C9.46 13.9 9.75 13.2 10.27 12.69L11.68 11.28C11.97 10.99 12.45 10.99 12.74 11.28C13.03 11.57 13.03 12.05 12.74 12.34L11.33 13.75C11.09 13.99 10.96 14.3 10.96 14.63C10.96 14.96 11.09 15.28 11.33 15.51C11.82 16 12.61 16 13.1 15.51L15.32 13.29C16.59 12.02 16.59 9.96 15.32 8.69C14.05 7.42 11.99 7.42 10.72 8.69L8.3 11.11C7.79 11.62 7.51 12.29 7.51 13C7.51 13.71 7.79 14.39 8.3 14.89C8.59 15.18 8.59 15.66 8.3 15.95C8.01 16.24 7.53 16.24 7.24 15.95C6.44 15.18 6 14.13 6 13.01C6 11.89 6.43 10.84 7.22 10.05L9.64 7.63C11.49 5.78 14.51 5.78 16.36 7.63C18.22 9.48 18.22 12.5 16.37 14.35Z" fill="currentColor"></path> </g></svg>
      <a href="${file.url}" target="_blank" rel="noopener noreferrer" class="text-secondary_blue text-base line-clamp-1">
            ${ title || file.url}
        </a>
      </div>
    `;
                    }
                });
                const html = edjsParser.parse(parsedData);
                return html;
            },
            async upContent(id) {
                if (this.stopUpDown) return;
                this.stopUpDown = true;
                try {
                    await this.$wire.upContent(id);
                } finally {
                    this.stopUpDown = false;
                }
            },
            async downContent(id) {
                if (this.stopUpDown) return;
                this.stopUpDown = true;
                try {
                    await this.$wire.upContent(id, 'down');
                } finally {
                    this.stopUpDown = false;
                }
            },
            goEditPage(task) {
                let myUrl = "{{ route('task-add', ['id' => '__ID__', 'task' => '__TASK__']) }}";
                myUrl = myUrl.replace('__ID__', this.idClassrooom);
                myUrl = myUrl.replace('__TASK__', task);
                window.location.href = myUrl;
            },
            goTaskPage(task) {
                let myUrl = "{{ route('classroom-task', ['id' => '__ID__', 'task' => '__TASK__']) }}";
                myUrl = myUrl.replace('__ID__', this.idClassrooom);
                myUrl = myUrl.replace('__TASK__', task);
                window.location.href = myUrl;
            },
            goReadPage(task) {
                let myUrl = "{{ route('classroom-read', ['id' => '__ID__', 'task' => '__TASK__']) }}";
                myUrl = myUrl.replace('__ID__', this.idClassrooom);
                myUrl = myUrl.replace('__TASK__', task);
                window.location.href = myUrl;
            },
            getRemainingTime(deadlineString) {
                const deadline = new Date(deadlineString);
                const now = new Date();
                const diffMs = deadline - now;

                if (diffMs <= 0) return '{{ __('class-learn.end') }}';

                const diffSeconds = Math.floor(diffMs / 1000);
                const days = Math.floor(diffSeconds / (3600 * 24));
                const hours = Math.floor((diffSeconds % (3600 * 24)) / 3600);
                const minutes = Math.floor((diffSeconds % 3600) / 60);

                let result = '';

                if (days > 0) {
                    result += `${days} {{ __('class-learn.day') }} `;
                    if (minutes > 0) result += `${minutes} {{ __('class-learn.minute') }}`;
                } else {
                    if (hours > 0) result += `${hours} {{ __('class-learn.hour') }} `;
                    if (minutes > 0) result += `${minutes} {{ __('class-learn.minute') }}`;
                }

                if (result === '') result = '1 {{ __('class-learn.minute') }}';

                return result.trim();
            },
            isBeforeDeadline(deadlineString) {
                const deadline = new Date(deadlineString);
                const now = new Date();
                return now < deadline;
            },
            initData() {
                this.editTitle = this.classrooms[0].title;
                this.previewImage = this.classrooms[0].image;
                this.positionImage = this.classrooms[0].position;
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
