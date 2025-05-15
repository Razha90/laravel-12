<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\NotificationController;
use Livewire\Attributes\Url;

new #[Layout('components.layouts.app-page')] class extends Component {
    public $classrooms = [];
    public $isLoading = true;
    public $search = '';
    public $desc = 'asc';
    #[Url]
    public $code;

    protected $queryString = ['code'];

    public function mount()
    {
        $this->loadData();
    }

    private function makeCode()
    {
        while (true) {
            $letters = Str::upper(Str::random(5));
            $numbers = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $code = str_shuffle($letters . $numbers);
            if (!Classroom::where('code', $code)->exists()) {
                return $code;
            }
        }
    }

    public function addClass()
    {
        try {
            DB::beginTransaction();

            if (Auth::user()->role != 'teacher') {
                Log::error('User ' . Auth::id() . ' is not a teacher, is', ['role' => Auth::user()->role]);
                $this->dispatch('failed', [
                    'message' => __('classroom.not.a.teacher'),
                ]);
                return;
            }

            $randomImage = ['class-1.jpeg', 'class-2.jpeg', 'class-3.jpeg', 'class-4.jpeg', 'class-5.jpeg'];
            $classroom = new Classroom();
            $classroom->user_id = Auth::id();
            $classroom->title = 'Untitled';
            $classroom->description = 'No Description';
            $classroom->position = '';
            $classroom->image = '/img/web/' . $randomImage[rand(0, 4)];
            $classroom->code = $this->makeCode();
            $classroom->password = null;
            $classroom->save();

            $membership = new ClassroomMember();
            $membership->classroom_id = $classroom->id;
            $membership->user_id = Auth::id();
            $membership->role = 'admin';
            $membership->action = 'edit';
            $membership->status = 'approved';
            $membership->joined_at = now();
            $membership->save();

            $this->dispatch('class-response', [
                'status' => true,
                'data' => $classroom,
            ]);
            $this->dispatch('success', [
                'message' => __('classroom.add.success'),
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('ClasroomMain Error Add Class ', ['error' => $th->getMessage()]);
            $this->dispatch('failed', [
                'message' => __('classroom.add.failed'),
            ]);
            $this->dispatch('class-response', [
                'status' => false,
                'data' => null,
            ]);
        }
    }

    public function loadData($search = '', $page = 1)
    {
        try {
            $this->classrooms = ClassroomMember::with('classroom.user:id,name')
                ->where('user_id', Auth::id())
                ->where('status', 'approved')
                ->whereHas('classroom', function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%');
                })
                ->paginate(10, ['*'], 'page', $page)
                ->toArray();
        } catch (\Throwable $th) {
            $this->classrooms = null;
            Log::error($th);
        } finally {
            $this->isLoading = false;
        }
    }

    public function joinClass($code, $password = null)
    {
        try {
            $classroom = Classroom::where('code', $code)->where('status', true)->first();
            if (!$classroom) {
                return [
                    'status' => false,
                    'message' => __('classroom.not_found_classroom'),
                ];
            }

            $check_user = ClassroomMember::where('classroom_id', $classroom->id)->where('user_id', Auth::id())->first();

            if ($check_user) {
                if ($check_user->status == 'rejected') {
                    $this->dispatch('error-code', [
                        'message' => __('classroom.classroom_rejected'),
                        'condition' => 'ERROR',
                    ]);
                    return [
                        'status' => true,
                    ];
                }

                if ($check_user->status == 'pending') {
                    $this->dispatch('error-code', [
                        'message' => __('classroom.classroom_pending'),
                        'condition' => 'PENDING',
                    ]);
                    return [
                        'status' => true,
                    ];
                }

                if ($check_user->status == 'approved') {
                    if ($this->code) {
                        return redirect()->route('classroom');
                    } else {
                        $this->dispatch('success', [
                            'message' => __('classroom.already_joined'),
                            'condition' => 'JOINED',
                        ]);
                    }
                    return [
                        'status' => true,
                    ];
                }
            }

            if ($classroom->password !== null && $classroom->password !== '') {
                if ($password == null) {
                    $this->dispatch('show-password', [
                        'code' => $classroom->code,
                    ]);
                    return [
                        'status' => true,
                    ];
                }

                $validator = Validator::make(
                    ['password' => $password],
                    [
                        'password' => 'required|string|min:8|max:20',
                    ],
                    [
                        'password.required' => __('classroom.classroom_password'),
                        'password.string' => __('classroom.classroom_password'),
                        'password.min' => __('classroom.classroom_password'),
                        'password.max' => __('classroom.classroom_password'),
                    ],
                );
                if ($validator->fails()) {
                    Log::info('ClassroomMain Password Not Valid', [
                        'password' => $password,
                        'classroom_password' => $classroom->password,
                        'validator' => $validator->errors()->first('password'),
                    ]); // Log the password for debugging
                    $this->dispatch('error-code', [
                        'message' => $validator->errors()->first('password'),
                        'condition' => 'ERROR',
                    ]);
                    return [
                        'status' => true,
                    ];
                }

                if ($classroom->password != $password) {
                    Log::info('ClassroomMain Password Not Same', [
                        'password' => $password,
                        'classroom_password' => $classroom->password,
                    ]); // Log the password for debugging
                    $this->dispatch('error-code', [
                        'message' => __('classroom.classroom_password_not_same'),
                        'condition' => 'ERROR',
                    ]);
                    return [
                        'status' => true,
                    ];
                }

                // if (!Hash::check($password, $classroom->password)) {
                //     $this->dispatch('error-code', [
                //         'message' => __('classroom.classroom_password_not_same'),
                //         'condition' => 'ERROR',
                //     ]);
                //     return [
                //         'status' => true,
                //     ];
                // }
            }

            if ($classroom->ask_join) {
                DB::beginTransaction();
                $membership = new ClassroomMember();
                $membership->classroom_id = $classroom->id;
                $membership->user_id = Auth::id();
                $membership->role = 'member';
                $membership->action = 'view';
                $membership->status = 'pending';
                $membership->joined_at = now();
                $membership->save();

                $dataNotif = [
                    'title' => __('classroom.wait_for_approval'),
                    'body' => __('classroom.wait_for_approval_message'),
                    'user_id' => auth()->user()->id,
                ];
                try {
                    $notificationController = new NotificationController();
                    $notificationController->sendNotification($dataNotif);
                } catch (\Throwable $th) {
                    Log::error('Error send notification', [
                        'error' => $th->getMessage(),
                    ]);
                }
                DB::commit();

                $this->dispatch('ask-join');
                return [
                    'status' => true,
                ];
            }

            DB::beginTransaction();
            $membership = new ClassroomMember();
            $membership->classroom_id = $classroom->id;
            $membership->user_id = Auth::id();
            $membership->role = 'member';
            $membership->action = 'view';
            $membership->status = 'approved';
            $membership->joined_at = now();
            $membership->save();

            $this->dispatch('success', [
                'message' => __('classroom.join.success'),
            ]);
            DB::commit();

            $this->loadData();

            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            Log::error($th);
            $this->dispatch('failed', [
                'message' => __('classroom.classroom_join_failed'),
            ]);
            DB::rollBack();
            return [
                'status' => true,
            ];
        }
    }

    public function updated()
    {
        $this->loadData();
    }
}; ?>

<div x-data="play()" class="flex justify-center" x-on:class-response.window="handleEvent($event.detail)">

    <div aria-hidden="true" x-data="{
        open: false,
        passwordClass: '',
        loading: false,
        async sendPassword() {
            this.loading = true;
            alert(this.passwordClass);
            if (this.passwordClass == '') {
                this.loading = false;
                this.$dispatch('failed', [{
                    message: '{{ __('classroom.classroom_password') }}',
                }]);
                return;
            }
            const data = await this.$wire.joinClass(code, this.passwordClass);
            this.loading = false;
        }
    }" x-cloak
        x-on:show-password.window="(event) => {
    open = true
}"x-show="open"
        class="animate-fade fixed left-0 right-0 top-0 z-20 flex h-screen w-screen items-center justify-center overflow-y-auto overflow-x-hidden bg-black/20 backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-md rounded-xl bg-white p-2" @click.away = "open = false">
            <div class="flex flex-row items-center justify-between">
                <div class="flex flex-row items-center gap-x-3 bg-white p-4">
                    <div class="flex- bg-secondary_blue/15 items-center justify-center rounded-xl p-1">
                        <svg class="text-secondary_blue w-[35px]" fill="currentColor" viewBox="0 0 32 32" id="icon"
                            xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <defs>
                                    <style>
                                        .cls-1 {
                                            fill: none;
                                        }
                                    </style>
                                </defs>
                                <path
                                    d="M21,2a8.9977,8.9977,0,0,0-8.6119,11.6118L2,24v6H8L18.3881,19.6118A9,9,0,1,0,21,2Zm0,16a7.0125,7.0125,0,0,1-2.0322-.3022L17.821,17.35l-.8472.8472-3.1811,3.1812L12.4141,20,11,21.4141l1.3787,1.3786-1.5859,1.586L9.4141,23,8,24.4141l1.3787,1.3786L7.1716,28H4V24.8284l9.8023-9.8023.8472-.8474-.3473-1.1467A7,7,0,1,1,21,18Z">
                                </path>
                                <circle cx="22" cy="10" r="2"></circle>
                                <rect id="_Transparent_Rectangle_" data-name="<Transparent Rectangle>" class="cls-1"
                                    width="32" height="32"></rect>
                            </g>
                        </svg>
                    </div>
                    <p class="text-secondary_blue text-2xl font-bold">{{ __('class-learn.class_password') }}</p>
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
            <div class="flex flex-col items-center justify-center">
                <p class="text-secondary_blue mb-5 text-base text-base">{{ __('class-learn.class_have_password') }}</p>
                <input type="password" x-model="passwordClass"
                    class="border-secondary_blue text-secondary_blue w-[70%] rounded-xl border-2 px-4 py-3 focus:border-blue-500 focus:outline-none"
                    placeholder="{{ __('classroom.classroom_password') }}......">
                <button @click="sendPassword"
                    class="bg-secondary_blue border-secondary_blue text-primary_white hover:bg-primary_white hover:text-secondary_blue mb-5 mt-5 cursor-pointer rounded-full border px-4 py-2 text-lg font-bold transition-all">
                    <svg aria-hidden="true" x-show="loading"
                        class="inline h-5 w-5 animate-spin fill-blue-600 text-gray-200 dark:text-gray-600"
                        viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor" />
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill" />
                    </svg>
                    <span x-show="!loading">{{ __('class-learn.save') }}</span>

                </button>
            </div>
        </div>
    </div>

    <div x-cloak x-data="{ alert: false }" x-on:ask-join.window="(event) => {
        alert = true;

    }" x-show="alert"
        class="bg-secondary_black/20 animate-fade fixed left-0 right-0 top-0 z-30 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-md p-4" @click.away="alert = false">
            <!-- Modal content -->
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between rounded-t px-4 pb-2 pt-4 dark:border-gray-600">
                    <h3 class="text-secondary_blue text-xl font-semibold">
                    </h3>
                    <button type="button" @click="alert = false"
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
                <div class="flex flex-col items-center gap-y-2 px-4 pb-4">
                    <div>
                        <svg class="w-[150px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M10 9.66679L11.3846 11.0001L14.5 8.00012M3.02832 10.0001L10.2246 14.8167C10.8661 15.2444 11.1869 15.4582 11.5336 15.5413C11.8399 15.6147 12.1593 15.6147 12.4657 15.5413C12.8124 15.4582 13.1332 15.2444 13.7747 14.8167L20.971 10.0001M10.2981 4.06892L4.49814 7.71139C3.95121 8.05487 3.67775 8.2266 3.4794 8.45876C3.30385 8.66424 3.17176 8.90317 3.09111 9.16112C3 9.45256 3 9.77548 3 10.4213V16.8001C3 17.9202 3 18.4803 3.21799 18.9081C3.40973 19.2844 3.71569 19.5904 4.09202 19.7821C4.51984 20.0001 5.0799 20.0001 6.2 20.0001H17.8C18.9201 20.0001 19.4802 20.0001 19.908 19.7821C20.2843 19.5904 20.5903 19.2844 20.782 18.9081C21 18.4803 21 17.9202 21 16.8001V10.4213C21 9.77548 21 9.45256 20.9089 9.16112C20.8282 8.90317 20.6962 8.66424 20.5206 8.45876C20.3223 8.2266 20.0488 8.05487 19.5019 7.71139L13.7019 4.06891C13.0846 3.68129 12.776 3.48747 12.4449 3.41192C12.152 3.34512 11.848 3.34512 11.5551 3.41192C11.224 3.48747 10.9154 3.68129 10.2981 4.06892Z"
                                    stroke="#2867A4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                </path>
                            </g>
                        </svg>
                    </div>
                    <p class="text-secondary_blue text-center text-lg">{{ __('classroom.send_join_request') }}</p>
                    <button @click="alert = false"
                        class="bg-secondary_blue hover:text-secondary_blue hover:border-secondary_blue mt-2 cursor-pointer rounded-md border-2 px-6 py-2 text-xl text-white transition-all hover:bg-white">
                        {{ __('classroom.ok') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-data="{ alert: false, message: '', condition: '' }"
        x-on:error-code.window="(event) => {
        alert = true;
        message = event.detail[0].message;
        condition = event.detail[0].condition;
    }"
        x-show="alert"
        class="bg-secondary_black/20 animate-fade fixed left-0 right-0 top-0 z-30 flex h-screen max-h-screen w-full items-center justify-center overflow-y-auto overflow-x-hidden backdrop-blur-sm md:inset-0">
        <div class="relative max-h-full w-full max-w-md p-4" @click.away="alert = false">
            <!-- Modal content -->
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between rounded-t p-4 md:p-5 dark:border-gray-600">
                    <h3 class="text-secondary_blue text-xl font-semibold">
                    </h3>
                    <button type="button" @click="alert = false"
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
                <div class="px-4 pb-4">
                    <template x-if="condition == 'ERROR'">
                        <div class="flex flex-col items-center justify-center">
                            <div>
                                <svg class="w-[200px]" viewBox="0 0 16 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M7.493 0.015 C 7.442 0.021,7.268 0.039,7.107 0.055 C 5.234 0.242,3.347 1.208,2.071 2.634 C 0.660 4.211,-0.057 6.168,0.009 8.253 C 0.124 11.854,2.599 14.903,6.110 15.771 C 8.169 16.280,10.433 15.917,12.227 14.791 C 14.017 13.666,15.270 11.933,15.771 9.887 C 15.943 9.186,15.983 8.829,15.983 8.000 C 15.983 7.171,15.943 6.814,15.771 6.113 C 14.979 2.878,12.315 0.498,9.000 0.064 C 8.716 0.027,7.683 -0.006,7.493 0.015 M8.853 1.563 C 9.967 1.707,11.010 2.136,11.944 2.834 C 12.273 3.080,12.920 3.727,13.166 4.056 C 13.727 4.807,14.142 5.690,14.330 6.535 C 14.544 7.500,14.544 8.500,14.330 9.465 C 13.916 11.326,12.605 12.978,10.867 13.828 C 10.239 14.135,9.591 14.336,8.880 14.444 C 8.456 14.509,7.544 14.509,7.120 14.444 C 5.172 14.148,3.528 13.085,2.493 11.451 C 2.279 11.114,1.999 10.526,1.859 10.119 C 1.618 9.422,1.514 8.781,1.514 8.000 C 1.514 6.961,1.715 6.075,2.160 5.160 C 2.500 4.462,2.846 3.980,3.413 3.413 C 3.980 2.846,4.462 2.500,5.160 2.160 C 6.313 1.599,7.567 1.397,8.853 1.563 M7.706 4.290 C 7.482 4.363,7.355 4.491,7.293 4.705 C 7.257 4.827,7.253 5.106,7.259 6.816 C 7.267 8.786,7.267 8.787,7.325 8.896 C 7.398 9.033,7.538 9.157,7.671 9.204 C 7.803 9.250,8.197 9.250,8.329 9.204 C 8.462 9.157,8.602 9.033,8.675 8.896 C 8.733 8.787,8.733 8.786,8.741 6.816 C 8.749 4.664,8.749 4.662,8.596 4.481 C 8.472 4.333,8.339 4.284,8.040 4.276 C 7.893 4.272,7.743 4.278,7.706 4.290 M7.786 10.530 C 7.597 10.592,7.410 10.753,7.319 10.932 C 7.249 11.072,7.237 11.325,7.294 11.495 C 7.388 11.780,7.697 12.000,8.000 12.000 C 8.303 12.000,8.612 11.780,8.706 11.495 C 8.763 11.325,8.751 11.072,8.681 10.932 C 8.616 10.804,8.460 10.646,8.333 10.580 C 8.217 10.520,7.904 10.491,7.786 10.530 "
                                            stroke="none" fill-rule="evenodd" fill="#D1042D"></path>
                                    </g>
                                </svg>
                            </div>
                            <p x-text="message" class="mt-3 text-center text-lg text-red-500"></p>
                        </div>
                    </template>
                    <template x-if="condition == 'PENDING'">
                        <div class="flex flex-col items-center justify-center">
                            <div>
                                <svg class="w-[180px]" fill="#fcc800" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M20,3a1,1,0,0,0,0-2H4A1,1,0,0,0,4,3H5.049c.146,1.836.743,5.75,3.194,8-2.585,2.511-3.111,7.734-3.216,10H4a1,1,0,0,0,0,2H20a1,1,0,0,0,0-2H18.973c-.105-2.264-.631-7.487-3.216-10,2.451-2.252,3.048-6.166,3.194-8Zm-6.42,7.126a1,1,0,0,0,.035,1.767c2.437,1.228,3.2,6.311,3.355,9.107H7.03c.151-2.8.918-7.879,3.355-9.107a1,1,0,0,0,.035-1.767C7.881,8.717,7.227,4.844,7.058,3h9.884C16.773,4.844,16.119,8.717,13.58,10.126ZM12,13s3,2.4,3,3.6V20H9V16.6C9,15.4,12,13,12,13Z">
                                        </path>
                                    </g>
                                </svg>
                            </div>
                            <p x-text="message" class="mt-3 text-center text-lg text-yellow-500"></p>
                        </div>
                    </template>
                    <template x-if="condition == 'JOINED'">
                        <div class="flex flex-col items-center justify-center">
                            <div>
                                <svg class="w-[180px]" fill="#fcc800" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M20,3a1,1,0,0,0,0-2H4A1,1,0,0,0,4,3H5.049c.146,1.836.743,5.75,3.194,8-2.585,2.511-3.111,7.734-3.216,10H4a1,1,0,0,0,0,2H20a1,1,0,0,0,0-2H18.973c-.105-2.264-.631-7.487-3.216-10,2.451-2.252,3.048-6.166,3.194-8Zm-6.42,7.126a1,1,0,0,0,.035,1.767c2.437,1.228,3.2,6.311,3.355,9.107H7.03c.151-2.8.918-7.879,3.355-9.107a1,1,0,0,0,.035-1.767C7.881,8.717,7.227,4.844,7.058,3h9.884C16.773,4.844,16.119,8.717,13.58,10.126ZM12,13s3,2.4,3,3.6V20H9V16.6C9,15.4,12,13,12,13Z">
                                        </path>
                                    </g>
                                </svg>
                            </div>
                            <p x-text="message" class="mt-3 text-center text-lg text-yellow-500"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-primary_white mt-[12vh] min-h-[800px] w-[90%] max-w-[1600px] rounded-xl">
        <div class="h-full w-full">
            <div class="flex w-full justify-between px-3 py-4">
                <div class="flex flex-row gap-x-4">
                    <div class="relative w-full">
                        <input type="text" wire:model.live.debounce.500ms="search" x-on:input="debounching"
                            class="border-secondary_blue text-secondary_blue w-full rounded-xl border-2 px-4 py-3 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('add-class.search') }}......">
                        <svg class="text-secondary_blue absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2"
                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M11 6C13.7614 6 16 8.23858 16 11M16.6588 16.6549L21 21M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z"
                                    stroke="#2867a4" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                </path>
                            </g>
                        </svg>
                    </div>
                    <select wire:model.live="desc"
                        class="text-secondary_blue border-secondary_blue rounded-xl border">
                        <option value="asc">A-Z</option>
                        <option value="desc">Z-A</option>
                    </select>
                </div>
                <button @click="show = true"
                    class="bg-secondary_blue flex cursor-pointer flex-row items-center justify-center gap-x-2 rounded-xl px-4 py-2 text-white transition-opacity hover:opacity-60">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-[30px]">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M11 8C11 7.44772 11.4477 7 12 7C12.5523 7 13 7.44772 13 8V11H16C16.5523 11 17 11.4477 17 12C17 12.5523 16.5523 13 16 13H13V16C13 16.5523 12.5523 17 12 17C11.4477 17 11 16.5523 11 16V13H8C7.44771 13 7 12.5523 7 12C7 11.4477 7.44772 11 8 11H11V8Z"
                                fill="#ffffff"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M23 12C23 18.0751 18.0751 23 12 23C5.92487 23 1 18.0751 1 12C1 5.92487 5.92487 1 12 1C18.0751 1 23 5.92487 23 12ZM3.00683 12C3.00683 16.9668 7.03321 20.9932 12 20.9932C16.9668 20.9932 20.9932 16.9668 20.9932 12C20.9932 7.03321 16.9668 3.00683 12 3.00683C7.03321 3.00683 3.00683 7.03321 3.00683 12Z"
                                fill="#ffffff"></path>
                        </g>
                    </svg>
                    <span>{{ __('classroom.add_class') }}</span>
                </button>
            </div>
            <!-- <div x-show="classrooms == null" class="flex h-[200px] w-full items-center justify-center">
                <div
                    class="animate-pulse rounded-full bg-blue-900 px-4 py-2 text-center text-base font-medium leading-none text-blue-200">
                    {{ __('add-class.loading') }}....</div>
            </div>
            <div x-show="classrooms.length == 0" class="flex h-[200px] w-full items-center justify-center">
                <div
                    class="animate-pulse rounded-full border-2 border-red-500 bg-white px-4 py-2 text-center text-base font-medium leading-none text-red-700">
                    {{ __('classroom.not_found') }}....</div>
            </div> -->
            <template x-if="classrooms && classrooms.data && !(classrooms.data.length> 0)">
                <div class="flex items-center justify-center">
                    <div @click="show = true"
                        class="border-secondary_blue mt-10 flex cursor-pointer flex-row items-center gap-x-2 rounded-full border-2 bg-white px-4 py-2 transition-opacity hover:opacity-70">
                        <svg class="w-[30px]" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink"
                            xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" fill="#2867A4">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <title>plus-circle</title>
                                <desc>Created with Sketch Beta.</desc>
                                <defs> </defs>
                                <g id="Page-1" stroke="none" stroke-width="1" fill="none"
                                    fill-rule="evenodd" sketch:type="MSPage">
                                    <g id="Icon-Set" sketch:type="MSLayerGroup"
                                        transform="translate(-464.000000, -1087.000000)" fill="#2867A4">
                                        <path
                                            d="M480,1117 C472.268,1117 466,1110.73 466,1103 C466,1095.27 472.268,1089 480,1089 C487.732,1089 494,1095.27 494,1103 C494,1110.73 487.732,1117 480,1117 L480,1117 Z M480,1087 C471.163,1087 464,1094.16 464,1103 C464,1111.84 471.163,1119 480,1119 C488.837,1119 496,1111.84 496,1103 C496,1094.16 488.837,1087 480,1087 L480,1087 Z M486,1102 L481,1102 L481,1097 C481,1096.45 480.553,1096 480,1096 C479.447,1096 479,1096.45 479,1097 L479,1102 L474,1102 C473.447,1102 473,1102.45 473,1103 C473,1103.55 473.447,1104 474,1104 L479,1104 L479,1109 C479,1109.55 479.447,1110 480,1110 C480.553,1110 481,1109.55 481,1109 L481,1104 L486,1104 C486.553,1104 487,1103.55 487,1103 C487,1102.45 486.553,1102 486,1102 L486,1102 Z"
                                            id="plus-circle" sketch:type="MSShapeGroup"> </path>
                                    </g>
                                </g>
                            </g>
                        </svg>
                        <p class="text-secondary_blue text-xl font-bold">{{ __('classroom.add_class') }}</p>
                    </div>
                </div>
            </template>
            <template x-if="classrooms && classrooms.data && classrooms.data.length > 0">
                <div
                    class="flex h-[90%] flex-row flex-wrap content-start items-start justify-center gap-x-6 gap-y-7">
                    <template x-for="(classroom, item) in classrooms.data" :key="item">
                        <div @click="window.location.href = '/classroom' + '/' + classroom.classroom.id"
                            class="animate-fade group relative h-[250px] w-[360px] cursor-pointer overflow-hidden rounded-xl shadow-xl">
                            <div class="absolute h-full w-full" x-data="{ loaded: false }">
                                <div x-show="!loaded"
                                    class="flex h-full w-full items-center justify-center bg-gray-200">
                                    <span
                                        class="h-10 w-10 animate-spin rounded-full border-4 border-gray-400 border-t-transparent"></span>
                                </div>
                                <img x-bind:src="classroom.classroom.image" x-bind:alt="classroom.classroom.title"
                                    class="h-full w-full object-cover transition-opacity duration-300 ease-in-out"
                                    x-bind:class="loaded ? 'opacity-100' : 'opacity-0'" @load="loaded = true"
                                    loading="lazy">
                            </div>
                            <div
                                class="absolute h-full w-full translate-y-[70%] bg-gradient-to-t from-black/80 to-black/30 p-4 transition-all duration-300 group-hover:translate-y-[25%] group-hover:bg-black/50 group-hover:from-black/60">
                                <p class="font-semibold text-white" x-text="classroom.classroom.title"></p>
                                <template x-if="classroom.classroom.user_id == '{{ auth()->user()->id }}'">
                                    <p class="text-base text-white">{{ __('classroom.by_you') }}</p>
                                </template>
                                <template x-if="classroom.classroom.user_id != '{{ auth()->user()->id }}'">
                                    <p class="truncate text-base text-white"
                                        x-text="'{{ __('classroom.by') }} ' + classroom.classroom.user.name"></p>
                                </template>
                                <p x-text="classroom.classroom.description"
                                    class="mt-3 line-clamp-5 text-sm text-white"></p>
                            </div>
                        </div>
                    </template>
                </div>

            </template>
        </div>
        <div class="h-[50px]"></div>
    </div>

    <div x-cloak x-show="show" class="fixed inset-0 z-30 flex items-center justify-center bg-black/40"
        @click="show = false">
        <div @click.stop class="animate-fade w-[500px] min-w-[300px] max-w-[500px] rounded-lg bg-white p-5 shadow-lg">
            <div class="flex justify-end">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    class="w-[30px] cursor-pointer" @click="show=false">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <circle cx="12" cy="12" r="10" stroke="#D1042D" stroke-width="1.5">
                        </circle>
                        <path d="M14.5 9.50002L9.5 14.5M9.49998 9.5L14.5 14.5" stroke="#D1042D" stroke-width="1.5"
                            stroke-linecap="round"></path>
                    </g>
                </svg>
            </div>
            <div class="flex flex-col gap-y-3">
                <label for="class"
                    class="text-secondary_blue font-koho inline-block w-full text-center text-2xl font-bold">
                    {{ __('classroom.add_code') }}
                </label>
                <div class="mb-5 flex w-full flex-row items-end gap-x-2">
                    <div class="relative flex-1">
                        <input id="class" type="text" x-model="classroomCode"
                            class="focus:ring-none mt-3 w-full rounded-lg border px-3 py-2 focus:outline-none"
                            x-bind:class="{
                                'border-secondary_blue text-secondary_blue': errorMessage.length ==
                                    0,
                                'border-red-500 text-red-500': errorMessage.length > 0
                            }"
                            placeholder="{{ __('classroom.code') }}" name="class">
                        <p x-show="errorMessage.length > 0" x-text="errorMessage"
                            class="absolute pl-2 text-sm italic text-red-500"></p>
                    </div>
                    <div x-data="{ isJoining: false }">
                        <button type="submit" :disabled="isJoining"
                            @click="isJoining = true; joinClass(classroomCode).finally(() => isJoining = false)"
                            class="bg-secondary_blue text-primary_white rounded-xl px-5 py-2 transition-opacity"
                            :class="{ 'opacity-50 cursor-not-allowed': isJoining, 'hover:opacity-70': !isJoining }">
                            {{ __('classroom.join') }}
                        </button>
                    </div>

                </div>
            </div>
            @auth
                @if (auth()->user()->role == 'teacher')
                    <div class="mb-5 mt-16 pt-5 text-center">
                        <button wire:click="addClass" wire:loading.attr="disabled"
                            class="text-secondary_blue border-secondary_blue hover:border-primary_white hover:bg-secondary_blue hover:outline-secondary_blue hover:text-primary_white border px-5 py-3 text-xl transition-colors hover:outline-2 disabled:cursor-not-allowed disabled:opacity-50">
                            {{ __('classroom.make_class') }}
                        </button>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</div>
<script>
    function play() {
        return {
            show: false,
            classroomUrl: '',
            isLoading: false,
            classrooms: @entangle('classrooms').live,
            search: '',
            classroomCode: '',
            errorMessage: '',
            initStop: false,
            code: @entangle('code').live,
            init() {
                if (this.initStop) return;
                console.log('kelas', this.classrooms);
                this.initStop = true;
                this.classroomUrl = '{{ route('classroom-learn', ['id' => '__ID__']) }}';
                this.$watch('classroomCode', (value) => {
                    if (value.length > 0) {
                        this.errorMessage = '';
                    }
                })
                if (this.code) {
                    this.$wire.joinClass(this.code);
                }
            },
            debounching() {
                this.classrooms = null
            },
            handleEvent(data) {
                const resData = JSON.parse(JSON.stringify(data));
                const datas = resData[0];
                if (datas.status) {
                    setTimeout(() => {
                        window.location.href = this.classroomUrl.replace('__ID__', datas.data.id);
                    }, 1000);
                }
                this.show = false;
            },
            async joinClass() {
                if (this.classroomCode == '') {
                    this.errorMessage = "{{ __('classroom.classroom_code_invalid') }}";
                    return;
                }
                // this.isLoading = true;
                const res = await this.$wire.joinClass(this.classroomCode);
                if (res.status) {
                    this.classroomCode = '';
                    this.show = false;
                } else {
                    this.errorMessage = res.message;
                }
                return;
            }
        }
    }
</script>
