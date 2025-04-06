<?php

use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Http\Controllers\NotificationController;
use App\Models\RandomAvatar;


new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $birth_date = '';
    public string $origin = '';

    /**
     * Handle an incoming registration request.
     */
    // public function register(): void
    // {
    //     $validated = $this->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
    //         'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
    //         'birth_date' => ['required', 'date', 'before:today'],
    //     ]);
    //     $randomImage = ['profile-1.svg', 'profile-2.svg', 'profile-3.svg'];
    //     $randomProfile = $randomImage[array_rand($randomImage)];
    //     $language = Session::get('locale');
    //     $validated['password'] = Hash::make($validated['password']);
    //     $user = User::create([
    //         'name' => $validated['name'],
    //         'email' => $validated['email'],
    //         'language' => $language,
    //         'profile_photo_path' => '/img/profile/' . $randomProfile,
    //         'password' => $validated['password'],
    //         'role' => 'guest',
    //         'birth_date' => $validated['birth_date'],
    //     ]);
    //     event(new Registered($user));

    //     Auth::login($user);

    //     // $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    //     $this->redirect(route('verification.notice', absolute: true));
    // }

    public function register()
    {
        try {
            $data = [
                'password' => $this->password,
                'email' => $this->email,
                'password_confirmation' => $this->password_confirmation,
                'name' => $this->name,
                'birth_date' => $this->birth_date,
                'origin' => $this->origin,
            ];

            $validator = Validator::make(
                $data,
                [
                    'password' => ['required', 'string', 'min:8', 'max:100'],
                    'name' => ['required', 'string', 'max:100', 'min:3'],
                    'email' => ['required', 'string', 'lowercase', 'email', 'max:120', 'unique:' . User::class],
                    'birth_date' => ['required', 'date', 'before:today'],
                    'password_confirmation' => ['required', 'string', 'min:8', 'max:100', 'confirmed:password'],
                    'origin' => ['required', 'string', 'max:100', 'min:3'],
                ],
                [
                    'password.required' => __('register.password_required'),
                    'password.string' => __('register.password_string'),
                    'password.min' => __('register.password_min'),
                    'password.max' => __('register.password_max'),
                    'name.required' => __('register.name_required'),
                    'name.string' => __('register.name_string'),
                    'name.max' => __('register.name_max'),
                    'name.min' => __('register.name_min'),
                    'email.required' => __('register.email_required'),
                    'email.string' => __('register.email_string'),
                    'email.lowercase' => __('register.email_lowercase'),
                    'email.email' => __('register.email_email'),
                    'email.max' => __('register.email_max'),
                    'email.unique' => __('register.email_unique'),
                    'birth_date.required' => __('register.birth_date_required'),
                    'birth_date.date' => __('register.birth_date_date'),
                    'birth_date.before' => __('register.birth_date_before'),
                    'password_confirmation.required' => __('register.password_confirmation_required'),
                    'password_confirmation.string' => __('register.password_confirmation_string'),
                    'password_confirmation.min' => __('register.password_confirmation_min'),
                    'password_confirmation.max' => __('register.password_confirmation_max'),
                    'password_confirmation.confirmed' => __('register.password_confirmation_confirmed'),
                    'origin.required' => __('register.origin_required'),
                    'origin.string' => __('register.origin_string'),
                    'origin.max' => __('register.origin_max'),
                    'origin.min' => __('register.origin_min'),
                ],
            );

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    foreach ($messages as $message) {
                        $this->addError($field, $message);
                    }
                }
                $this->dispatch('failed', ['message' => $validator->errors()->first()]);
            }

            if ($validator->fails()) {
                Log::error('ClassroomLearn Error Register: ' . $validator->errors()->first());
                return;
            }

            $randomAvatars = RandomAvatar::all();
            $randomProfile = $randomAvatars->random();
            $language = Cookie::get('locale', config('app.locale'));
            $data['password'] = Hash::make($data['password']);
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'language' => $language,
                'profile_photo_path' => $randomProfile->path,
                'password' => $data['password'],
                'role' => 'guest',
                'birth_date' => $data['birth_date'],
                'origin' => $data['origin'],
            ]);
            event(new Registered($user));

            Auth::login($user);
            $user = Auth::user();

            $dataNotif = [
                'title' => __('register.notification_title'),
                'body' => __('register.notification_message'),
                'user_id' => $user->id,
            ];

            try {
                $notificationController = new NotificationController();
                $notificationController->sendNotification($dataNotif);
            } catch (\Throwable $th) {
                Log::error('ClassroomLearn Error Allowed Teacher Notification: ' . $th->getMessage());
            }

            $this->redirect(route('verification.notice', absolute: true));
        } catch (\Throwable $th) {
            $this->dispatch('failed', ['message' => __('profile.default_error')]);
            Log::error('Register: ' . $th->getMessage());
        }
    }
}; ?>

<div class="flex flex-col gap-6">

    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit.prevent="register" class="flex flex-col gap-6">

        <!-- Name -->
            <flux:input wire:model="name" :label="__('register.name')" type="text" required autofocus
                autocomplete="name" :placeholder="__('register.example_name')" />

        <!-- Email Address -->
        <flux:input wire:model="email" :label="__('register.email')" type="email" required autocomplete="email"
            :placeholder="__('register.example_email')" />

        <!-- Password -->
        <flux:input wire:model="password" :label="__('register.password')" type="password" required
            autocomplete="new-password" :placeholder="__('register.example_password')">
        </flux:input>


        <!-- Confirm Password -->
        <flux:input wire:model="password_confirmation" :label="__('register.comfirm_password')" type="password" required
            autocomplete="new-password" :placeholder="__('register.example_password')" />


        <style>
            ::-webkit-calendar-picker-indicator {
                background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M3 9H21M7 3V5M17 3V5M6 12H8M11 12H13M16 12H18M6 15H8M11 15H13M16 15H18M6 18H8M11 18H13M16 18H18M6.2 21H17.8C18.9201 21 19.4802 21 19.908 20.782C20.2843 20.5903 20.5903 20.2843 20.782 19.908C21 19.4802 21 18.9201 21 17.8V8.2C21 7.07989 21 6.51984 20.782 6.09202C20.5903 5.71569 20.2843 5.40973 19.908 5.21799C19.4802 5 18.9201 5 17.8 5H6.2C5.0799 5 4.51984 5 4.09202 5.21799C3.71569 5.40973 3.40973 5.71569 3.21799 6.09202C3 6.51984 3 7.07989 3 8.2V17.8C3 18.9201 3 19.4802 3.21799 19.908C3.40973 20.2843 3.71569 20.5903 4.09202 20.782C4.51984 21 5.07989 21 6.2 21Z" fill="%23ffffff" stroke="%232867A4" stroke-width="2" stroke-linecap="round"/></svg>');
                background-size: contain;
                background-repeat: no-repeat;
                width: 24px;
                height: 24px;
                background-color: transparent
            }
        </style>

        <flux:input wire:model="birth_date" :label="__('register.birth_date')" type="date" required
            autocomplete="bday" class="text-secondary_blue" />


        <!-- Birth Date -->
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
            <flux:input x-model="origin" wire:model="origin" :label="__('register.origin')" type="text" required
                :placeholder="__('register.origin_example')"
                @input="(event) => {loadSchool(event);}"
                @focus="(event) => { open = true; loadSchool(event); }" @click.away="open = false"
                autocomplete="origin" />
            <div class="relative z-20" x-show="open && schools.length > 0" x-transition>
                <div class="absolute top-0 left-0 w-full bg-white border border-gray-500 shadow-2xl rounded-2xl p-2">
                    <template x-for="(school, index) in schools" :key="index">
                        <div x-text="school"
                            class="text-secondary_blue text-base h-[40px] py-3 hover:bg-secondary_black/20 transition-opacity cursor-pointer flex items-center truncate px-2 border-b border-gray-500/50 rounded-xl"
                            @click="origin = school;"></div>
                    </template>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end z-10">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
