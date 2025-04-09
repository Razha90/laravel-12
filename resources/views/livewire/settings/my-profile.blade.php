<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\AplicationLater;
use App\Http\Controllers\NotificationController;
use App\Models\RandomAvatar;
use App\Events\AplicationNotification;

new #[Layout('components.layouts.app-flux')] class extends Component {
    use WithFileUploads;
    public $image;
    protected $listeners = ['editName', 'initAsTeacher', 'updateOrigin'];
    public $password;
    public $password_new;
    public $password_confirmation;

    public $name;
    public $message;
    public $role;
    public $origin;

    public $realOrigin;

    public function initAsTeacher()
    {
        $this->name = auth()->user()->name;
        $this->origin = auth()->user()->origin;
    }

    public function mount()
    {
        $this->realOrigin = auth()->user()->origin;
    }

    public function allowedTeacher()
    {
        try {
            $validator = Validator::make(
                [
                    'name' => $this->name,
                    'message' => $this->message,
                    'role' => $this->role,
                    'origin' => $this->origin,
                ],
                [
                    'name' => 'required|string|max:100|min:3',
                    'message' => 'required|string|max:5000|min:15',
                    'role' => 'required|string|max:10|min:3|in:teacher,guest,admin',
                    'origin' => 'required|string|max:255|min:3',
                ],
                [
                    'name.required' => __('profile.name_required'),
                    'name.string' => __('profile.name_string'),
                    'name.max' => __('profile.name_max'),
                    'name.min' => __('profile.name_min'),
                    'message.required' => __('profile.message_required'),
                    'message.string' => __('profile.message_string'),
                    'message.max' => __('profile.message_max'),
                    'message.min' => __('profile.message_min'),
                    'role.required' => __('profile.role_required'),
                    'role.string' => __('profile.role_string'),
                    'role.max' => __('profile.role_max'),
                    'role.min' => __('profile.role_min'),
                    'role.in' => __('profile.role_in'),
                    'origin.required' => __('profile.origin_required'),
                    'origin.string' => __('profile.origin_string'),
                    'origin.max' => __('profile.origin_max'),
                    'origin.min' => __('profile.origin_min'),
                ],
            );

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    foreach ($messages as $message) {
                        $this->addError($field, $message);
                    }
                }
                $this->dispatch('failed', ['message' => $validator->errors()->first()]);
                return;
            }

            $hasPending = AplicationLater::where('user_id', auth()->id())
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                $this->dispatch('failed', ['message' => __('profile.letter_pending')]);
                Log::error("Liska");
                return false;
            }


            $data = $validator->validated();
            $user = User::find(auth()->user()->id);
            $newAplicationLetter = $user->aplicationLetters()->create([
                'request_role' => $data['role'],
                'current_role' => $user->role,
                'full_name' => $data['name'],
                'message' => $data['message'],
                'origin' => $data['origin'],
                'status' => 'pending',
            ]);


            event(new AplicationNotification($newAplicationLetter, $user->id));

            $dataNotif = [
                'title' => 'Request Letter Submission Successful',
                'body' =>
                    '<p><strong>Role:</strong> ' .
                    e($data['role']) .
                    '</p>
                <p><strong>Full Name:</strong> ' .
                    e($data['name']) .
                    '</p>
                <p><strong>Message:</strong> ' .
                    nl2br(e($data['message'])) .
                    '</p>
                <p><strong>Origin:</strong> ' .
                    e($data['origin']) .
                    '</p>
                <p><strong>Status:</strong> Pending</p>',
                'user_id' => $user->id,
            ];

            try {
                $notificationController = new NotificationController();
                $notificationController->sendNotification($dataNotif);
            } catch (\Throwable $th) {
                Log::error('ClassroomLearn Error Allowed Teacher Notification: ' . $th->getMessage());
            }

            $this->dispatch('success', ['message' => __('profile.letter_send')]);
        } catch (\Throwable $th) {
            $this->dispatch('failed', ['message' => __('profile.letter_not_send')]);
            Log::error('ClassroomLearn Error Allowed Teacher: ' . $th->getMessage());
        }
    }

    public function updateOrigin($origin)
    {
        $this->origin = $origin;
    }

    public function updatedImage()
    {
        try {
            $this->Validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            ]);
            $user = auth()->user();
            if ($user->last_change_profile && now()->diffInHours($user->last_change_profile) < 24) {
                $this->dispatch('failed', ['message' => __('profile.image_change')]);
                return;
            }
            $filename = $this->image->store(path: 'images/profile', options: 'public');
            $filename = str_replace('public/', '', $filename);
            $image = Storage::url($filename);

            $oldImage = $user->profile_photo_path;

            if ($oldImage && !Str::startsWith($oldImage, '/img/profile/')) {
                if (Str::startsWith($oldImage, '/storage/images/')) {
                    $this->deletedImage($oldImage);
                }
            }

            $user->update([
                'profile_photo_path' => $image,
                'last_change_profile' => now(),
            ]);

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

    protected function deletedImage($file_old)
    {
        try {
            // Hilangkan '/storage/' dari path karena Laravel menyimpan file di 'public/images/...'
            $filePath = str_replace('/storage/', '', $file_old);

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
                Log::info('File berhasil dihapus: ' . $filePath);
            } else {
                Log::info('File tidak ditemukan: ' . $filePath);
            }
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Error Deleted Image: ' . $th->getMessage());
        }
    }

    public function resolvedImages()
    {
        try {
            $user = auth()->user();
            $randomAvatars = RandomAvatar::all();
            if ($randomAvatars->count() <= 1) {
                $randomProfile = $randomAvatars->first();
            } else {
                do {
                    $randomProfile = $randomAvatars->random();
                } while ($randomProfile->path === $user->profile_photo_path);
            }

            $user->update([
                'profile_photo_path' => $randomProfile->path,
            ]);

            $this->dispatch('success', ['message' => __('profile.image_success')]);
        } catch (\Throwable $th) {
            Log::error('ClassroomLearn Error Resolved Images: ' . $th->getMessage());
            $this->dispatch('failed', ['message' => __('profile.default_error')]);
        }
    }

    public function editName($name)
    {
        try {
            $user = auth()->user();
            if ($user->last_change_name && now()->diffInHours($user->last_change_name) < 24) {
                $this->dispatch('failed', ['message' => __('profile.name_too_soon')]);
                $this->dispatch('name-check', ['condition' => false]);
                return;
            }

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
            $user = auth()->user();
            $user->update([
                'name' => $name,
                'last_change_name' => now(),
            ]);
            $this->dispatch('success', ['message' => __('profile.name_success')]);
            $this->dispatch('name-check', ['name' => $name, 'condition' => true]);
        } catch (\Throwable $th) {
            $this->dispatch('failed', ['message' => __('profile.name_failed')]);
            $this->dispatch('name-check', ['condition' => false]);
            Log::error('ClassroomLearn Error Edit Name: ' . $th->getMessage());
        }
    }

    public function resetPassword()
    {
        try {
            $data = [
                'password' => $this->password,
                'password_new' => $this->password_new,
                'password_confirmation' => $this->password_confirmation,
            ];

            $validatorPass = !Auth::guard('web')->validate([
                'email' => Auth::user()->email,
                'password' => $this->password,
            ]);

            if ($validatorPass) {
                $this->addError('password', __('profile.password_not_same'));
            }

            $validator = Validator::make(
                $data,
                [
                    'password' => ['required', 'string', 'min:8', 'max:100'],
                    'password_new' => ['required', 'string', 'min:8', 'max:100', 'confirmed:password_confirmation'],
                    'password_confirmation' => ['required', 'string', 'min:8', 'max:100', 'confirmed:password_new'],
                ],
                [
                    'password.required' => __('profile.password_required'),
                    'password.string' => __('profile.password_string'),
                    'password.min' => __('profile.password_min'),
                    'password.max' => __('profile.password_max'),
                    'password_new.required' => __('profile.password_new_required'),
                    'password_new.string' => __('profile.password_new_string'),
                    'password_new.confirmed' => __('profile.password_confirmation_confirmed'),
                    'password_new.min' => __('profile.password_new_min'),
                    'password_new.max' => __('profile.password_new_max'),
                    'password_confirmation.required' => __('profile.password_confirmation_required'),
                    'password_confirmation.string' => __('profile.password_confirmation_string'),
                    'password_confirmation.confirmed' => __('profile.password_confirmation_confirmed'),
                    'password_confirmation.min' => __('profile.password_confirmation_min'),
                    'password_confirmation.max' => __('profile.password_confirmation_max'),
                ],
            );

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    foreach ($messages as $message) {
                        $this->addError($field, $message); // Menampilkan error di input terkait
                    }
                }

                $this->dispatch('failed', ['message' => $validator->errors()->first()]);
            }

            if ($validatorPass || $validator->fails()) {
                return;
            }

            $user = Auth::user();
            $user->update([
                'password' => Hash::make($data['password_new']),
            ]);
            $this->password = '';
            $this->password_new = '';
            $this->password_confirmation = '';
            $this->resetValidation([]);
            $this->dispatch('success', ['message' => __('profile.password_success')]);
        } catch (\Throwable $th) {
            $this->dispatch('failed', ['message' => __('profile.default_error')]);
            Log::error('ClassroomLearn Error Reset Password: ' . $th->getMessage());
        }
    }

    public function updateRealOrigin()
    {
        try {
            $user = auth()->user();

            $validator = Validator::make(
                ['realOrigin' => $this->realOrigin],
                [
                    'realOrigin' => 'required|string|max:255|min:3',
                ],
                [
                    'realOrigin.required' => __('profile.origin_required'),
                    'realOrigin.string' => __('profile.origin_string'),
                    'realOrigin.max' => __('profile.origin_max'),
                    'realOrigin.min' => __('profile.origin_min'),
                ],
            );
            if ($validator->fails()) {
                $this->dispatch('failed', ['message' => $validator->errors()->first()]);
                $this->dispatch('origin-check', ['condition' => false]);
                return;
            }
            $origin = $validator->validated()['realOrigin'];
            $user = auth()->user();
            $user->update([
                'origin' => $origin,
            ]);
            $this->dispatch('success', ['message' => __('profile.origin_success')]);
            $this->dispatch('origin-check', ['origin' => $origin, 'condition' => true]);
        } catch (\Throwable $th) {
            $this->dispatch('failed', ['message' => __('profile.origin_failed')]);
            $this->dispatch('origin-check', ['condition' => false]);
            Log::error('ClassroomLearn Error Edit Origin: ' . $th->getMessage());
        }
    }

    public function updateBirth($newBirth)
    {
        try {
            $user = auth()->user();
            $validator = Validator::make(
                ['birth' => $newBirth],
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
            $birth = $validator->validated()['birth'];
            $user = auth()->user();
            $user->update([
                'birth_date' => $birth,
            ]);
            $this->dispatch('success', ['message' => __('profile.birth_success')]);
            return response()->json([
                'status' => 'success',
                'message' => __('profile.birth_success'),
            ]);
        } catch (\Throwable $th) {
            $this->dispatch('failed', ['message' => __('profile.birth_failed')]);
            Log::error('ClassroomLearn Error Edit Birth: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('profile.birth_failed'),
            ]);
        }
    }
}; ?>

<div>
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

    <div x-cloak x-init="Livewire.dispatch('initAsTeacher');" x-data="{ open: false, role: @entangle('role').live, message: @entangle('message').live, letter_teacher:'{{ __('profile.letter_teacher') }}', letter_guest:'{{ __('profile.letter_guest') }}'}" x-show="open"
        x-on:open-teacher.window="(event) => {
        open = true;
        role = event.detail[0].role;
        if (role == 'teacher') {
            message = '{{ __('profile.message_letter_teacher') }}';
        } else {
            message = '{{ __('profile.message_letter_guest') }}';
        }
        
    }" x-transition.opacity
        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div @click.away="open = false" x-transition
            class="relative m-4 w-2/5 min-w-[40%] max-w-[40%] rounded-lg bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between pb-4 text-xl font-medium text-slate-800">
                <span class="text-secondary_blue font-bold" x-text="role == 'teacher' ? letter_teacher : letter_guest;">
                    
                </span>
                <button @click="open = false" class="text-slate-500 hover:text-slate-700">&times;</button>
            </div>

            <div class="border-t border-slate-200 py-4 font-light text-slate-600">
                <form wire:submit.prevent="allowedTeacher" class="flex flex-col gap-6">
                    <flux:field>
                        <flux:label class="!text-secondary_blue ml-3 text-[18px] !font-bold">{{ __('profile.name') }}
                        </flux:label>
                        <flux:input wire:model="name" type="text" required autocomplete="name"
                            :placeholder="__('profile.example_name')" class="!text-secondary_blue !text-lg" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="!text-secondary_blue ml-3 text-[18px] !font-bold">{{ __('profile.role') }}
                        </flux:label>
                        <flux:select class="cursor-not-allowed" x-model="role" disabled>
                            <flux:select.option value="teacher">{{ __('profile.teacher') }}</flux:select.option>
                            <flux:select.option value="guest">{{ __('profile.guest') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="role" />
                    </flux:field>

                    <div x-data="{
                        origin: @entangle('origin').live,
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
                                const response = await fetch(`{{ route('name-school') }}?search=${encodeURIComponent(searchValue)}`, {
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
                                console.log(this.schools);
                            } catch (error) {
                                this.schools = [];
                                this.dummySchools = [];
                                console.error('Fetch error:', error.message);
                            }
                        },
                    }">
                        <flux:field>
                            <flux:label class="!text-secondary_blue ml-3 text-[18px] !font-bold">
                                {{ __('register.origin') }}</flux:label>
                            <flux:input x-model="origin" wire:model="origin" type="text" required
                                :placeholder="__('register.origin_example')" @input="(event) => {loadSchool(event);}"
                                @focus="(event) => { open = true; loadSchool(event); }" @click.away="open = false"
                                autocomplete="origin" class="!text-secondary_blue !text-lg" />
                            <flux:error name="origin" />
                        </flux:field>
                        <div class="relative z-20" x-show="open && schools.length > 0" x-transition>
                            <div
                                class="absolute left-0 top-0 w-full rounded-2xl border border-gray-500 bg-white p-2 shadow-2xl">
                                <template x-for="(school, index) in schools" :key="index">
                                    <div x-text="school"
                                        class="text-secondary_blue hover:bg-secondary_black/20 flex h-[40px] cursor-pointer items-center truncate rounded-xl border-b border-gray-500/50 px-2 py-3 text-base transition-opacity"
                                        @click="origin = school;"></div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <flux:field>
                        <flux:label class="!text-secondary_blue ml-3 text-[18px] !font-bold">
                            {{ __('profile.message_teacher') }}</flux:label>
                        <flux:textarea x-model="message" />
                        <flux:error name="message" />
                    </flux:field>

                    <div class="flex justify-end space-x-2 pt-4">
                        <button @click="open = false" type="button"
                            class="bg-accent_red text-primary_white rounded-md px-4 py-2 text-sm transition hover:bg-red-500">
                            {{ __('profile.cancel') }}
                        </button>
                        <button @click="open = false" type="submit"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white shadow-md transition hover:bg-blue-700">
                            {{ __('profile.confirm') }}
                        </button>
                    </div>

                </form>
            </div>


        </div>
    </div>

    <div class="flex h-screen w-full items-end justify-center" x-data="loadData">
        <div
            class="bg-primary_white animate-fade-down h-[85vh] max-h-[85vh] w-full max-w-[1600px] rounded-t-2xl px-4 py-2 shadow-2xl">
            <h2 class="text-secondary_blue text-3xl my-[1%] font-bold h-[4%] px-10">{{ __('profile.my_profile') }}</h2>
            <div class="flex flex-row h-[92%]">
                <div class="flex h-full w-[25%] flex-col items-center gap-y-7 py-3">
                    <div x-data="{ doubleShow: false }" class="relative h-[200px] w-[200px]">
                        <div class="bg-accent_grey h-[200px] w-[200px] overflow-auto rounded-full shadow-2xl"
                            @mouseenter="doubleShow = true" :class="{ 'opacity-75': doubleShow }">
                            <img src="{{ auth()->user()->profile_photo_path }}" alt="{{ auth()->user()->name }}"
                                class="w-full object-cover h-full" loading="lazy">
                        </div>
                        <div @mouseleave="doubleShow = false" x-show="doubleShow"
                            class="absolute left-0 top-0 z-30 flex h-[200px] w-[200px] cursor-pointer items-center justify-center overflow-auto rounded-full">
                            <div class="bg-secondary_blue flex h-[60px] w-[60px] items-center justify-center rounded-full"
                                wire:click="resolvedImages">
                                <svg class="w-[30px]" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M3.57996 5.15991H17.42C19.08 5.15991 20.42 6.49991 20.42 8.15991V11.4799"
                                            stroke="#ffffff" stroke-width="1.5" stroke-miterlimit="10"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M6.73996 2L3.57996 5.15997L6.73996 8.32001" stroke="#ffffff"
                                            stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                        <path d="M20.42 18.84H6.57996C4.91996 18.84 3.57996 17.5 3.57996 15.84V12.52"
                                            stroke="#ffffff" stroke-width="1.5" stroke-miterlimit="10"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M17.26 21.9999L20.42 18.84L17.26 15.6799" stroke="#ffffff"
                                            stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                    </g>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-secondary_blue absolute bottom-0 right-0 flex cursor-pointer items-center justify-center rounded-full p-2 transition-opacity hover:opacity-70"
                            @click="openFile">
                            <svg class="w-[30px]" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M9.77778 21H14.2222C17.3433 21 18.9038 21 20.0248 20.2646C20.51 19.9462 20.9267 19.5371 21.251 19.0607C22 17.9601 22 16.4279 22 13.3636C22 10.2994 22 8.76721 21.251 7.6666C20.9267 7.19014 20.51 6.78104 20.0248 6.46268C19.3044 5.99013 18.4027 5.82123 17.022 5.76086C16.3631 5.76086 15.7959 5.27068 15.6667 4.63636C15.4728 3.68489 14.6219 3 13.6337 3H10.3663C9.37805 3 8.52715 3.68489 8.33333 4.63636C8.20412 5.27068 7.63685 5.76086 6.978 5.76086C5.59733 5.82123 4.69555 5.99013 3.97524 6.46268C3.48995 6.78104 3.07328 7.19014 2.74902 7.6666C2 8.76721 2 10.2994 2 13.3636C2 16.4279 2 17.9601 2.74902 19.0607C3.07328 19.5371 3.48995 19.9462 3.97524 20.2646C5.09624 21 6.65675 21 9.77778 21ZM12 9.27273C9.69881 9.27273 7.83333 11.1043 7.83333 13.3636C7.83333 15.623 9.69881 17.4545 12 17.4545C14.3012 17.4545 16.1667 15.623 16.1667 13.3636C16.1667 11.1043 14.3012 9.27273 12 9.27273ZM12 10.9091C10.6193 10.9091 9.5 12.008 9.5 13.3636C9.5 14.7192 10.6193 15.8182 12 15.8182C13.3807 15.8182 14.5 14.7192 14.5 13.3636C14.5 12.008 13.3807 10.9091 12 10.9091ZM16.7222 10.0909C16.7222 9.63904 17.0953 9.27273 17.5556 9.27273H18.6667C19.1269 9.27273 19.5 9.63904 19.5 10.0909C19.5 10.5428 19.1269 10.9091 18.6667 10.9091H17.5556C17.0953 10.9091 16.7222 10.5428 16.7222 10.0909Z"
                                        fill="#ffffff"></path>
                                </g>
                            </svg>
                        </div>
                        <input type="file" x-ref="fileInput" accept=".jpeg, .jpg, .png, .svg, .webp"
                            class="hidden" wire:model="image">
                    </div>
                    <div class="text-secondary_blue font-sans text-xl">
                        {{ auth()->user()->email }}
                    </div>
                </div>
                <flux:separator vertical variant="subtle" class="px-0.5" />
                <div class="h-full w-[75%] overflow-auto px-5 pb-20 pt-5">
                    <div x-data="{ edit: false, name: '{{ auth()->user()->name }}', updatedName: '{{ auth()->user()->name }}' }"
                        x-on:name-check.window="(event) => {
                        if (event.detail[0].condition) {
                            name = event.detail[0].name;
                            updatedName = event.detail[0].name;
                        } else {
                            name = updatedName;
                        }
                    }"
                        @click.away="() => {
                                    edit = false;
                                    name = updatedName;
                                }">
                        <p class="text-secondary_blue font-sans text-xl font-bold">{{ __('profile.display_name') }}
                        </p>
                        <div class="relative flex flex-row items-center gap-x-2">
                            <input type="text"
                                class="text-secondary_blue w-[200px] rounded-lg px-2 py-2 text-left text-xl outline-0"
                                x-model="name" :disabled="!edit"
                                :class="edit ? 'border border-secondary_blue' : 'border-none'">
                            <div class="bg-secondary_blue flex h-[35px] w-[35px] cursor-pointer items-center justify-center rounded-full p-2 transition-opacity hover:opacity-75"
                                @click="edit = true" x-transition x-show="!edit">
                                <svg class="w-[17px]" viewBox="0 -0.5 21 21" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    fill="#ffffff">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <title>edit [#1479]</title>
                                        <desc>Created with Sketch.</desc>
                                        <defs> </defs>
                                        <g id="Page-1" stroke="none" stroke-width="1" fill="none"
                                            fill-rule="evenodd">
                                            <g id="Dribbble-Light-Preview"
                                                transform="translate(-99.000000, -400.000000)" fill="#ffffff">
                                                <g id="icons" transform="translate(56.000000, 160.000000)">
                                                    <path
                                                        d="M61.9,258.010643 L45.1,258.010643 L45.1,242.095788 L53.5,242.095788 L53.5,240.106431 L43,240.106431 L43,260 L64,260 L64,250.053215 L61.9,250.053215 L61.9,258.010643 Z M49.3,249.949769 L59.63095,240 L64,244.114985 L53.3341,254.031929 L49.3,254.031929 L49.3,249.949769 Z"
                                                        id="edit-[#1479]"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                            <div class="flex flex-row gap-x-2" x-show="edit" x-transition>
                                <div class="bg-accent_red flex cursor-pointer items-center justify-center rounded-full p-1 px-3 transition-opacity hover:opacity-75"
                                    @click="() => {
                                    edit = false;
                                    name = updatedName;
                                }">
                                    <p>{{ __('profile.close') }}</p>
                                </div>
                                <div class="flex cursor-pointer items-center justify-center rounded-full bg-green-600 p-1 px-3 transition-opacity hover:opacity-75"
                                    @click="Livewire.dispatch('editName', {
                                        name
                                    }); edit = false;">
                                    <p>{{ __('profile.save_changes') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-7">
                        <h3 class="text-secondary_blue font-sans text-xl font-bold">{{ __('profile.status_account') }}
                        </h3>
                        @switch(auth()->user()->role)
                            @case('guest')
                                <p class="text-secondary_blue ml-3 font-sans text-lg">
                                    {{ __('profile.guest') }}
                                </p>
                                <p class="text-accent_blue/70 cursor-pointer text-xs italic hover:underline"
                                    @click="$dispatch('open-teacher', [{role:'teacher'}])">{{ __('profile.change_teacher') }}</p>
                            @break

                            @case('admin')
                                <p class="text-secondary_blue ml-3 font-sans text-lg">
                                    {{ __('profile.admin') }}
                                </p>
                            @break

                            @case('teacher')
                                <p class="text-secondary_blue ml-3 font-sans text-lg">
                                    {{ __('profile.teacher') }}
                                </p>
                                <p class="text-accent_blue/70 cursor-pointer text-xs italic hover:underline"
                                    @click="$dispatch('open-teacher', [{role:'guest'}])">{{ __('profile.change_guest') }}</p>
                            @break

                            @default
                                <p class="text-secondary_blue ml-3 font-sans text-lg">
                                    {{ __('profile.guest') }}
                                </p>
                                <p class="text-accent_blue/70 cursor-pointer text-xs italic hover:underline"
                                    @click="$dispatch('open-teacher', [{role:'teacher'}])">{{ __('profile.change_teacher') }}</p>
                        @endswitch
                    </div>
                    <div class="mt-7">
                        <h3 class="text-secondary_blue font-sans text-xl font-bold">{{ __('profile.origin') }}</h3>
                        <form wire:submit.prevent="updateRealOrigin" class="flex max-w-[500px] flex-col gap-6">
                            <div class="relative"
                                x-on:origin-check.window="(event) => {
                            edited = false;
                        if (event.detail[0].condition) {
                            origin = event.detail[0].origin;
                            updatedOrigin = event.detail[0].origin;
                        } else {
                            origin = updatedOrigin;
                        }
                    }"
                                x-data="{
                                    origin: @entangle('realOrigin').live,
                                    schools: [],
                                    dummySchools: [],
                                    open: false,
                                    edited: false,
                                    updatedOrigin: '{{ auth()->user()->origin }}',
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
                                            const response = await fetch(`{{ route('name-school') }}?search=${encodeURIComponent(searchValue)}`, {
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
                                            console.log(this.schools);
                                        } catch (error) {
                                            this.schools = [];
                                            this.dummySchools = [];
                                            console.error('Fetch error:', error.message);
                                        }
                                    },
                                }">
                                <flux:field
                                    x-bind:class="!edited ? 'w-[90%]' : 'w-[80%] cursor-not-allowed opacity-50'">
                                    <flux:input x-model="origin" wire:model="realOrigin" type="text" required
                                        :placeholder="__('register.origin_example')"
                                        @input="(event) => {loadSchool(event);}" required
                                        @focus="(event) => { open = true; loadSchool(event); }"
                                        @click.away="open = false" autocomplete="realOrigin"
                                        class="!text-secondary_blue !text-lg" x-bind:disabled="!edited"
                                        x-bind:class="{ 'cursor-not-allowed opacity-50': !edited }" />
                                    <flux:error name="realOrigin" />
                                </flux:field>
                                <div x-show="!edited"
                                    class="hover:bg-secondary_blue/60 bg-secondary_blue absolute right-0 top-1 cursor-pointer rounded-3xl p-2 transition-opacity"
                                    @click="edited = true" title="{{ __('profile.edit_origin') }}">
                                    <svg class="w-[17px]" viewBox="0 -0.5 21 21" version="1.1"
                                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        fill="#ffffff">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>edit [#1479]</title>
                                            <desc>Created with Sketch.</desc>
                                            <defs> </defs>
                                            <g id="Page-1" stroke="none" stroke-width="1" fill="none"
                                                fill-rule="evenodd">
                                                <g id="Dribbble-Light-Preview"
                                                    transform="translate(-99.000000, -400.000000)" fill="#ffffff">
                                                    <g id="icons" transform="translate(56.000000, 160.000000)">
                                                        <path
                                                            d="M61.9,258.010643 L45.1,258.010643 L45.1,242.095788 L53.5,242.095788 L53.5,240.106431 L43,240.106431 L43,260 L64,260 L64,250.053215 L61.9,250.053215 L61.9,258.010643 Z M49.3,249.949769 L59.63095,240 L64,244.114985 L53.3341,254.031929 L49.3,254.031929 L49.3,249.949769 Z"
                                                            id="edit-[#1479]"> </path>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div x-show="edited" class="absolute right-0 top-1 flex flex-row gap-x-2"
                                    x-show="edit" x-transition>
                                    <div class="bg-accent_red flex h-[35px] w-[35px] cursor-pointer items-center justify-center rounded-full p-1 px-3 transition-opacity hover:opacity-75"
                                        @click="edited = !edited; origin = updatedOrigin">
                                        <svg class="h-[25px] w-[25px]" viewBox="0 0 24 24" version="1.1"
                                            xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" fill="#ffffff">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <title>cancel</title>
                                                <desc>Created with Sketch.</desc>
                                                <g id="icons" stroke="none" stroke-width="1" fill="none"
                                                    fill-rule="evenodd">
                                                    <g id="ui-gambling-website-lined-icnos-casinoshunter"
                                                        transform="translate(-2168.000000, -158.000000)"
                                                        fill="#ffffff" fill-rule="nonzero">
                                                        <g id="1"
                                                            transform="translate(1350.000000, 120.000000)">
                                                            <path
                                                                d="M821.426657,38.5856848 L830.000001,47.1592624 L838.573343,38.5856848 C839.288374,37.8706535 840.421422,37.8040611 841.267835,38.4653242 L841.414315,38.5987208 C842.195228,39.3796338 842.195228,40.645744 841.414306,41.4266667 L832.840738,50 L841.414315,58.5733429 C842.129347,59.2883742 842.195939,60.4214224 841.534676,61.2678347 L841.401279,61.4143152 C840.620366,62.1952283 839.354256,62.1952283 838.573333,61.4143055 L830.000001,52.8407376 L821.426657,61.4143152 C820.711626,62.1293465 819.578578,62.1959389 818.732165,61.5346758 L818.585685,61.4012792 C817.804772,60.6203662 817.804772,59.354256 818.585694,58.5733333 L827.159262,50 L818.585685,41.4266571 C817.870653,40.7116258 817.804061,39.5785776 818.465324,38.7321653 L818.598721,38.5856848 C819.379634,37.8047717 820.645744,37.8047717 821.426657,38.5856848 Z M820.028674,60.999873 C820.023346,60.9999577 820.018018,61 820.012689,61 Z M820.161408,60.9889406 L820.117602,60.9945129 L820.117602,60.9945129 C820.132128,60.9929912 820.146788,60.9911282 820.161408,60.9889406 Z M819.865274,60.9891349 L819.883098,60.9916147 C819.877051,60.9908286 819.87101,60.9899872 819.864975,60.9890905 L819.865274,60.9891349 Z M819.739652,60.9621771 L819.755271,60.9664589 C819.749879,60.9650278 819.744498,60.9635509 819.739126,60.9620283 L819.739652,60.9621771 Z M820.288411,60.9614133 L820.234515,60.9752112 L820.234515,60.9752112 C820.252527,60.971132 820.270527,60.9665268 820.288411,60.9614133 Z M820.401572,60.921544 L820.359957,60.9380009 L820.359957,60.9380009 C820.373809,60.9328834 820.387743,60.9273763 820.401572,60.921544 Z M819.623655,60.9214803 C819.628579,60.923546 819.626191,60.9225499 819.623806,60.921544 L819.623655,60.9214803 Z M819.506361,60.8625673 L819.400002,60.7903682 C819.444408,60.8248958 819.491056,60.8551582 819.539393,60.8811554 L819.506361,60.8625673 L819.506361,60.8625673 Z M820.51858,60.8628242 L820.486378,60.8809439 L820.486378,60.8809439 C820.496939,60.8752641 820.507806,60.8691536 820.51858,60.8628242 Z M840.881155,60.4606074 L840.862567,60.4936392 L840.862567,60.4936392 L840.790368,60.5999978 C840.824896,60.555592 840.855158,60.5089438 840.881155,60.4606074 Z M840.936494,60.3386283 L840.92148,60.3763453 L840.92148,60.3763453 C840.926791,60.3637541 840.931774,60.3512293 840.936494,60.3386283 Z M840.974777,60.2110466 L840.962177,60.2603479 L840.962177,60.2603479 C840.966711,60.2443555 840.97096,60.2277405 840.974777,60.2110466 Z M840.994445,60.0928727 L840.989135,60.1347261 L840.989135,60.1347261 C840.991174,60.1210064 840.992958,60.1069523 840.994445,60.0928727 Z M839.987311,39.9996529 L830,49.9872374 L820.012689,39.9996529 L819.999653,40.0126889 L829.987237,50 L819.999653,59.9873111 L820.012689,60.0003471 L830,50.0127626 L839.987311,60.0003471 L840.000347,59.9873111 L830.012763,50 L840.000347,40.0126889 L839.987311,39.9996529 Z M840.999873,59.9713258 L840.999916,60.0003193 L840.999916,60.0003193 C841.000041,59.9907089 841.000027,59.9810165 840.999873,59.9713258 Z M840.988941,59.8385918 L840.994513,59.8823981 L840.994513,59.8823981 C840.992991,59.8678719 840.991128,59.8532122 840.988941,59.8385918 Z M840.961413,59.7115886 L840.975211,59.7654853 L840.975211,59.7654853 C840.971132,59.7474727 840.966527,59.7294733 840.961413,59.7115886 Z M840.921544,59.5984278 L840.938001,59.6400431 L840.938001,59.6400431 C840.932883,59.6261908 840.927376,59.612257 840.921544,59.5984278 Z M840.862824,59.4814199 L840.880944,59.5136217 L840.880944,59.5136217 C840.875264,59.503061 840.869154,59.4921939 840.862824,59.4814199 Z M819.119056,40.4863783 L819.134164,40.5134185 C819.128903,40.5043379 819.123796,40.4951922 819.118845,40.4859852 L819.119056,40.4863783 Z M819.061999,40.3599569 L819.075467,40.3944079 C819.070734,40.3829341 819.066223,40.3713901 819.061935,40.3597825 L819.061999,40.3599569 Z M819.024789,40.2345147 L819.033541,40.2701072 C819.030397,40.2582611 819.027473,40.2463686 819.024771,40.234436 L819.024789,40.2345147 Z M819.005077,40.1136164 L819.008385,40.1422797 C819.007138,40.1326872 819.00603,40.12308 819.005061,40.1134615 L819.005077,40.1136164 Z M819.000419,39.9836733 L819,40.0126889 C819,40.002956 819.000141,39.993223 819.000424,39.9834934 L819.000419,39.9836733 Z M819.010865,39.8652739 L819.008385,39.8830981 C819.009171,39.8770511 819.010013,39.8710099 819.010909,39.8649753 L819.010865,39.8652739 Z M819.037823,39.7396521 L819.033541,39.7552707 C819.034972,39.7498794 819.036449,39.7444978 819.037972,39.7391264 L819.037823,39.7396521 Z M819.07852,39.6236547 C819.076454,39.6285788 819.07745,39.6261907 819.078456,39.6238057 L819.07852,39.6236547 Z M819.137433,39.5063608 L819.209632,39.4000022 C819.175104,39.444408 819.144842,39.4910562 819.118845,39.5393926 L819.137433,39.5063608 L819.137433,39.5063608 Z M820.485985,39.1188446 L820.519017,39.1374327 L820.519017,39.1374327 L820.625376,39.2096318 C820.58097,39.1751042 820.534322,39.1448418 820.485985,39.1188446 Z M839.513622,39.1190561 L839.486582,39.1341644 C839.495662,39.128903 839.504808,39.1237964 839.514015,39.1188446 L839.513622,39.1190561 Z M819.539,39.1190561 L819.511959,39.1341644 C819.52104,39.128903 819.530186,39.1237964 819.539393,39.1188446 L819.539,39.1190561 Z M840.460607,39.1188446 L840.493639,39.1374327 L840.493639,39.1374327 L840.599998,39.2096318 C840.555592,39.1751042 840.508944,39.1448418 840.460607,39.1188446 Z M819.661418,39.0634885 L819.63097,39.0754675 C819.641051,39.0713084 819.651187,39.0673212 819.661372,39.0635059 L819.661418,39.0634885 Z M820.359783,39.0619346 L820.401723,39.0785197 L820.401723,39.0785197 C820.387743,39.0726237 820.373809,39.0671166 820.359783,39.0619346 Z M839.640043,39.0619991 L839.605592,39.0754675 C839.617066,39.0707338 839.62861,39.0662229 839.640217,39.0619346 L839.640043,39.0619991 Z M840.338628,39.0635059 L840.376345,39.0785197 L840.376345,39.0785197 C840.363754,39.0732095 840.351229,39.0682261 840.338628,39.0635059 Z M819.789259,39.0251536 L819.755271,39.0335411 C819.766459,39.0305713 819.777688,39.0277987 819.788953,39.0252234 L819.789259,39.0251536 Z M820.234436,39.0247709 L820.288548,39.0386257 L820.288548,39.0386257 C820.270527,39.0334732 820.252527,39.028868 820.234436,39.0247709 Z M839.765485,39.0247888 L839.729893,39.0335411 C839.741739,39.0303966 839.753631,39.0274732 839.765564,39.0247709 L839.765485,39.0247888 Z M840.211047,39.0252234 L840.260348,39.0378229 L840.260348,39.0378229 C840.244356,39.0332892 840.227741,39.0290398 840.211047,39.0252234 Z M819.911404,39.0051132 L819.883098,39.0083853 C819.892432,39.0071719 819.901779,39.0060902 819.911137,39.0051402 L819.911404,39.0051132 Z M820.113462,39.0050614 L820.161342,39.0110494 L820.161342,39.0110494 C820.145468,39.0086743 820.12948,39.006675 820.113462,39.0050614 Z M839.886384,39.005077 L839.85772,39.0083853 C839.867313,39.0071382 839.87692,39.0060303 839.886538,39.0050614 L839.886384,39.005077 Z M840.088863,39.0051402 L840.134726,39.0108651 L840.134726,39.0108651 C840.119676,39.0086288 840.104284,39.0067057 840.088863,39.0051402 Z M839.95834,39.0004173 L840.016507,39.0004238 C839.997122,38.9998609 839.977725,38.9998588 839.95834,39.0004173 Z M819.983493,39.0004238 L820.04166,39.0004173 C820.022275,38.9998588 820.002878,38.9998609 819.983493,39.0004238 Z"
                                                                id="cancel"> </path>
                                                        </g>
                                                    </g>
                                                </g>
                                            </g>
                                        </svg>
                                    </div>
                                    <button type="submit"
                                        class="flex h-[35px] w-[35px] cursor-pointer items-center justify-center rounded-full bg-green-600 p-1 px-3 transition-opacity hover:opacity-75">
                                        <svg class="h-[25px] w-[25px]" fill="#ffffff" version="1.1" id="Capa_1"
                                            xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 335.765 335.765"
                                            xml:space="preserve">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <g>
                                                    <g>
                                                        <polygon
                                                            points="311.757,41.803 107.573,245.96 23.986,162.364 0,186.393 107.573,293.962 335.765,65.795 ">
                                                        </polygon>
                                                    </g>
                                                </g>
                                            </g>
                                        </svg>
                                    </button>
                                </div>
                                <div class="relative z-20" x-show="open && schools.length > 0" x-transition>
                                    <div
                                        class="absolute left-0 top-0 w-full rounded-2xl border border-gray-500 bg-white p-2 shadow-2xl">
                                        <template x-for="(school, index) in schools" :key="index">
                                            <div x-text="school"
                                                class="text-secondary_blue hover:bg-secondary_black/20 flex h-[40px] cursor-pointer items-center truncate rounded-xl border-b border-gray-500/50 px-2 py-3 text-base transition-opacity"
                                                @click="origin = school;"></div>
                                        </template>
                                    </div>
                                </div>

                            </div>

                        </form>
                    </div>
                    <div class="mt-7 w-full max-w-[500px]" x-data="{
                        birth: '{{ \Carbon\Carbon::parse(auth()->user()->birth_date)->format('Y-m-d') }}',
                        birthBefore: '{{ \Carbon\Carbon::parse(auth()->user()->birth_date)->format('Y-m-d') }}',
                        birthInput: null,
                        edited: false,
                        async senderBirth() {
                            this.edited = false;
                            if (this.birth == this.birthBefore) {
                                this.$dispatch('failed', [{
                                    message: '{{ __('profile.birth_same') }}'
                                }])
                                return;
                            }
                            const data = await this.$wire.updateBirth(this.birth);
                            if (data.original.status == 'success') {
                                this.birthBefore = this.birth;
                            } else {
                                this.edited = true;
                            }
                        },
                        cancleBirth() {
                            this.edited = false;
                            this.birth = this.birthBefore;
                        }
                    }">
                        <label for="birth_date" class="text-secondary_blue font-sans text-xl font-bold">
                            {{ __('admin.birth_date') }}
                        </label>
                        <div class="flex w-full flex-row items-center gap-x-4">
                            <div class="relative w-full" @click="edited ? $refs.birthInput.showPicker() : '';">
                                <input type="date" x-model="birth" id="birth_date" x-ref="birthInput"
                                    class="border-secondary_blue focus:border-secondary_blue text-secondary_blue mt-1 w-full select-none rounded-md border px-3 py-2 pr-10 text-sm focus:outline-none"
                                    x-bind:disabled="!edited"
                                    x-bind:class="{ 'cursor-not-allowed opacity-35': !edited }">
                                <div class="absolute inset-y-0 right-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="text-secondary_blue h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div x-show="!edited"
                                class="hover:bg-secondary_blue/60 bg-secondary_blue cursor-pointer rounded-3xl p-2 transition-opacity"
                                @click="edited = true" title="{{ __('profile.edit_origin') }}">
                                <svg class="w-[17px]" viewBox="0 -0.5 21 21" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    fill="#ffffff">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <title>edit [#1479]</title>
                                        <desc>Created with Sketch.</desc>
                                        <defs> </defs>
                                        <g id="Page-1" stroke="none" stroke-width="1" fill="none"
                                            fill-rule="evenodd">
                                            <g id="Dribbble-Light-Preview"
                                                transform="translate(-99.000000, -400.000000)" fill="#ffffff">
                                                <g id="icons" transform="translate(56.000000, 160.000000)">
                                                    <path
                                                        d="M61.9,258.010643 L45.1,258.010643 L45.1,242.095788 L53.5,242.095788 L53.5,240.106431 L43,240.106431 L43,260 L64,260 L64,250.053215 L61.9,250.053215 L61.9,258.010643 Z M49.3,249.949769 L59.63095,240 L64,244.114985 L53.3341,254.031929 L49.3,254.031929 L49.3,249.949769 Z"
                                                        id="edit-[#1479]"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                            <div x-show="edited" class="flex flex-row gap-x-2" x-show="edit" x-transition>
                                <div class="bg-accent_red flex h-[35px] w-[35px] cursor-pointer items-center justify-center rounded-full p-1 px-3 transition-opacity hover:opacity-75"
                                    @click="cancleBirth()">
                                    <svg class="h-[25px] w-[25px]" viewBox="0 0 24 24" version="1.1"
                                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        fill="#ffffff">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>cancel</title>
                                            <desc>Created with Sketch.</desc>
                                            <g id="icons" stroke="none" stroke-width="1" fill="none"
                                                fill-rule="evenodd">
                                                <g id="ui-gambling-website-lined-icnos-casinoshunter"
                                                    transform="translate(-2168.000000, -158.000000)" fill="#ffffff"
                                                    fill-rule="nonzero">
                                                    <g id="1" transform="translate(1350.000000, 120.000000)">
                                                        <path
                                                            d="M821.426657,38.5856848 L830.000001,47.1592624 L838.573343,38.5856848 C839.288374,37.8706535 840.421422,37.8040611 841.267835,38.4653242 L841.414315,38.5987208 C842.195228,39.3796338 842.195228,40.645744 841.414306,41.4266667 L832.840738,50 L841.414315,58.5733429 C842.129347,59.2883742 842.195939,60.4214224 841.534676,61.2678347 L841.401279,61.4143152 C840.620366,62.1952283 839.354256,62.1952283 838.573333,61.4143055 L830.000001,52.8407376 L821.426657,61.4143152 C820.711626,62.1293465 819.578578,62.1959389 818.732165,61.5346758 L818.585685,61.4012792 C817.804772,60.6203662 817.804772,59.354256 818.585694,58.5733333 L827.159262,50 L818.585685,41.4266571 C817.870653,40.7116258 817.804061,39.5785776 818.465324,38.7321653 L818.598721,38.5856848 C819.379634,37.8047717 820.645744,37.8047717 821.426657,38.5856848 Z M820.028674,60.999873 C820.023346,60.9999577 820.018018,61 820.012689,61 Z M820.161408,60.9889406 L820.117602,60.9945129 L820.117602,60.9945129 C820.132128,60.9929912 820.146788,60.9911282 820.161408,60.9889406 Z M819.865274,60.9891349 L819.883098,60.9916147 C819.877051,60.9908286 819.87101,60.9899872 819.864975,60.9890905 L819.865274,60.9891349 Z M819.739652,60.9621771 L819.755271,60.9664589 C819.749879,60.9650278 819.744498,60.9635509 819.739126,60.9620283 L819.739652,60.9621771 Z M820.288411,60.9614133 L820.234515,60.9752112 L820.234515,60.9752112 C820.252527,60.971132 820.270527,60.9665268 820.288411,60.9614133 Z M820.401572,60.921544 L820.359957,60.9380009 L820.359957,60.9380009 C820.373809,60.9328834 820.387743,60.9273763 820.401572,60.921544 Z M819.623655,60.9214803 C819.628579,60.923546 819.626191,60.9225499 819.623806,60.921544 L819.623655,60.9214803 Z M819.506361,60.8625673 L819.400002,60.7903682 C819.444408,60.8248958 819.491056,60.8551582 819.539393,60.8811554 L819.506361,60.8625673 L819.506361,60.8625673 Z M820.51858,60.8628242 L820.486378,60.8809439 L820.486378,60.8809439 C820.496939,60.8752641 820.507806,60.8691536 820.51858,60.8628242 Z M840.881155,60.4606074 L840.862567,60.4936392 L840.862567,60.4936392 L840.790368,60.5999978 C840.824896,60.555592 840.855158,60.5089438 840.881155,60.4606074 Z M840.936494,60.3386283 L840.92148,60.3763453 L840.92148,60.3763453 C840.926791,60.3637541 840.931774,60.3512293 840.936494,60.3386283 Z M840.974777,60.2110466 L840.962177,60.2603479 L840.962177,60.2603479 C840.966711,60.2443555 840.97096,60.2277405 840.974777,60.2110466 Z M840.994445,60.0928727 L840.989135,60.1347261 L840.989135,60.1347261 C840.991174,60.1210064 840.992958,60.1069523 840.994445,60.0928727 Z M839.987311,39.9996529 L830,49.9872374 L820.012689,39.9996529 L819.999653,40.0126889 L829.987237,50 L819.999653,59.9873111 L820.012689,60.0003471 L830,50.0127626 L839.987311,60.0003471 L840.000347,59.9873111 L830.012763,50 L840.000347,40.0126889 L839.987311,39.9996529 Z M840.999873,59.9713258 L840.999916,60.0003193 L840.999916,60.0003193 C841.000041,59.9907089 841.000027,59.9810165 840.999873,59.9713258 Z M840.988941,59.8385918 L840.994513,59.8823981 L840.994513,59.8823981 C840.992991,59.8678719 840.991128,59.8532122 840.988941,59.8385918 Z M840.961413,59.7115886 L840.975211,59.7654853 L840.975211,59.7654853 C840.971132,59.7474727 840.966527,59.7294733 840.961413,59.7115886 Z M840.921544,59.5984278 L840.938001,59.6400431 L840.938001,59.6400431 C840.932883,59.6261908 840.927376,59.612257 840.921544,59.5984278 Z M840.862824,59.4814199 L840.880944,59.5136217 L840.880944,59.5136217 C840.875264,59.503061 840.869154,59.4921939 840.862824,59.4814199 Z M819.119056,40.4863783 L819.134164,40.5134185 C819.128903,40.5043379 819.123796,40.4951922 819.118845,40.4859852 L819.119056,40.4863783 Z M819.061999,40.3599569 L819.075467,40.3944079 C819.070734,40.3829341 819.066223,40.3713901 819.061935,40.3597825 L819.061999,40.3599569 Z M819.024789,40.2345147 L819.033541,40.2701072 C819.030397,40.2582611 819.027473,40.2463686 819.024771,40.234436 L819.024789,40.2345147 Z M819.005077,40.1136164 L819.008385,40.1422797 C819.007138,40.1326872 819.00603,40.12308 819.005061,40.1134615 L819.005077,40.1136164 Z M819.000419,39.9836733 L819,40.0126889 C819,40.002956 819.000141,39.993223 819.000424,39.9834934 L819.000419,39.9836733 Z M819.010865,39.8652739 L819.008385,39.8830981 C819.009171,39.8770511 819.010013,39.8710099 819.010909,39.8649753 L819.010865,39.8652739 Z M819.037823,39.7396521 L819.033541,39.7552707 C819.034972,39.7498794 819.036449,39.7444978 819.037972,39.7391264 L819.037823,39.7396521 Z M819.07852,39.6236547 C819.076454,39.6285788 819.07745,39.6261907 819.078456,39.6238057 L819.07852,39.6236547 Z M819.137433,39.5063608 L819.209632,39.4000022 C819.175104,39.444408 819.144842,39.4910562 819.118845,39.5393926 L819.137433,39.5063608 L819.137433,39.5063608 Z M820.485985,39.1188446 L820.519017,39.1374327 L820.519017,39.1374327 L820.625376,39.2096318 C820.58097,39.1751042 820.534322,39.1448418 820.485985,39.1188446 Z M839.513622,39.1190561 L839.486582,39.1341644 C839.495662,39.128903 839.504808,39.1237964 839.514015,39.1188446 L839.513622,39.1190561 Z M819.539,39.1190561 L819.511959,39.1341644 C819.52104,39.128903 819.530186,39.1237964 819.539393,39.1188446 L819.539,39.1190561 Z M840.460607,39.1188446 L840.493639,39.1374327 L840.493639,39.1374327 L840.599998,39.2096318 C840.555592,39.1751042 840.508944,39.1448418 840.460607,39.1188446 Z M819.661418,39.0634885 L819.63097,39.0754675 C819.641051,39.0713084 819.651187,39.0673212 819.661372,39.0635059 L819.661418,39.0634885 Z M820.359783,39.0619346 L820.401723,39.0785197 L820.401723,39.0785197 C820.387743,39.0726237 820.373809,39.0671166 820.359783,39.0619346 Z M839.640043,39.0619991 L839.605592,39.0754675 C839.617066,39.0707338 839.62861,39.0662229 839.640217,39.0619346 L839.640043,39.0619991 Z M840.338628,39.0635059 L840.376345,39.0785197 L840.376345,39.0785197 C840.363754,39.0732095 840.351229,39.0682261 840.338628,39.0635059 Z M819.789259,39.0251536 L819.755271,39.0335411 C819.766459,39.0305713 819.777688,39.0277987 819.788953,39.0252234 L819.789259,39.0251536 Z M820.234436,39.0247709 L820.288548,39.0386257 L820.288548,39.0386257 C820.270527,39.0334732 820.252527,39.028868 820.234436,39.0247709 Z M839.765485,39.0247888 L839.729893,39.0335411 C839.741739,39.0303966 839.753631,39.0274732 839.765564,39.0247709 L839.765485,39.0247888 Z M840.211047,39.0252234 L840.260348,39.0378229 L840.260348,39.0378229 C840.244356,39.0332892 840.227741,39.0290398 840.211047,39.0252234 Z M819.911404,39.0051132 L819.883098,39.0083853 C819.892432,39.0071719 819.901779,39.0060902 819.911137,39.0051402 L819.911404,39.0051132 Z M820.113462,39.0050614 L820.161342,39.0110494 L820.161342,39.0110494 C820.145468,39.0086743 820.12948,39.006675 820.113462,39.0050614 Z M839.886384,39.005077 L839.85772,39.0083853 C839.867313,39.0071382 839.87692,39.0060303 839.886538,39.0050614 L839.886384,39.005077 Z M840.088863,39.0051402 L840.134726,39.0108651 L840.134726,39.0108651 C840.119676,39.0086288 840.104284,39.0067057 840.088863,39.0051402 Z M839.95834,39.0004173 L840.016507,39.0004238 C839.997122,38.9998609 839.977725,38.9998588 839.95834,39.0004173 Z M819.983493,39.0004238 L820.04166,39.0004173 C820.022275,38.9998588 820.002878,38.9998609 819.983493,39.0004238 Z"
                                                            id="cancel"> </path>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <button type="button" @click="senderBirth()"
                                    class="flex h-[35px] w-[35px] cursor-pointer items-center justify-center rounded-full bg-green-600 p-1 px-3 transition-opacity hover:opacity-75">
                                    <svg class="h-[25px] w-[25px]" fill="#ffffff" version="1.1" id="Capa_1"
                                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        viewBox="0 0 335.765 335.765" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <g>
                                                    <polygon
                                                        points="311.757,41.803 107.573,245.96 23.986,162.364 0,186.393 107.573,293.962 335.765,65.795 ">
                                                    </polygon>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>
                    <div class="mt-7">
                        <h3 class="text-secondary_blue font-sans text-xl font-bold">
                            {{ __('profile.change_password') }}
                        </h3>
                        <form wire:submit.prevent="resetPassword" class="flex max-w-[500px] flex-col gap-6">
                            <!-- Current Password -->
                            <flux:field>
                                <flux:label class="!text-secondary_blue">{{ __('profile.current_password') }}
                                </flux:label>
                                <flux:input type="password" autocomplete="password"
                                    :placholder="__('profile.placholder_password')" required wire:model="password" />
                                <flux:error name="password" />
                            </flux:field>

                            <!-- Password -->
                            <flux:field>
                                <flux:label class="!text-secondary_blue">{{ __('profile.password_new') }}</flux:label>
                                <flux:input type="password" autocomplete="password_new"
                                    :placholder="__('profile.placholder_password')" required
                                    wire:model="password_new" />
                                <flux:error name="password_new" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="!text-secondary_blue">{{ __('profile.password_confirmation') }}
                                </flux:label>
                                <flux:input type="password" autocomplete="password_confirmation"
                                    :placholder="__('profile.placholder_password')" required
                                    wire:model="password_confirmation" />
                                <flux:error name="password_confirmation" />
                            </flux:field>

                            <div class="flex items-center justify-center">
                                <flux:button type="submit" variant="primary" class="max-w-[200px]">
                                    {{ __('profile.change_password') }}
                                </flux:button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function loadData() {
            return {
                openFile() {
                    this.$refs.fileInput.click();
                },
            }
        }
    </script>
</div>
