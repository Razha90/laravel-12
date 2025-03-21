<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Classroom;

new #[Layout('components.layouts.app-page')] class extends Component {
    public $classrooms = [];
    public $isLoading = true;
    public $search = '';
    public $desc = 'asc';

    public function mount()
    {
        $this->loadData();
    }

    public function addClass()
    {
        try {
            Log::info('Session User:', ['user' => Auth::user()]);

            if (!Auth::check()) {
                session()->flash('FAILED', 'You need to login first');
                return redirect()->route('login');
            }
            $randomImage = ['class-1.jpeg', 'class-2.jpeg', 'class-3.jpeg', 'class-4.jpeg', 'class-5.jpeg'];
            $classroom = new Classroom();
            $classroom->user_id = Auth::id();
            $classroom->title = 'Untitled';
            $classroom->description = 'No Description';
            $classroom->position = '';
            $classroom->image = '/img/web/' . $randomImage[rand(0, 4)];

            $classroom->save();
            session()->flash('SUCCESS', __('classroom.add.success'));
            $this->dispatch('class-response', [
                'status' => true,
                'data' => $classroom,
            ]);
        } catch (\Throwable $th) {
            Log::error('ClasroomMain Error Add Class ' . $th);
            session()->flash('FAILED', __('classroom.add.failed'));
            $this->dispatch('class-response', [
                'status' => false,
                'data' => null,
            ]);
        }
    }

    public function loadData()
    {
        try {
            $query = Classroom::where('user_id', auth()->user()->id);
            if (!empty($this->search)) {
                // $query->where('title', 'like', '%' . $this->search . '%');
                $query->where('title', 'like', '%' . $this->search . '%');
            }
            $query->orderBy('title', $this->desc);
            $result = $query->get();

            if ($result->isEmpty()) {
                $this->classrooms = [];
            } else {
                $this->classrooms = $result->toArray();
            }
        } catch (\Throwable $th) {
            $this->classrooms = null;
            Log::error($th);
        } finally {
            $this->isLoading = false;
        }
    }

    public function updated()
    {
        $this->loadData();
    }
}; ?>

<div>
    <x-layouts.app.app-nav />
    <div x-data="play()" class="flex justify-center" x-on:class-response.window="handleEvent($event.detail)">
        @if (session()->has(key: 'SUCCESS'))
            <div x-data="{ alert: true }" x-init="setTimeout(() => alert = false, 5000)" x-show="alert" x-transition
                class="flex items-start left-5 bottom-5 flex-row p-4 mb-4 text-sm rounded-lg bg-gray-800 animate-fade-up text-green-400 absolute"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Info</span>
                <div>
                    <span class="font-medium">{{ session('SUCCESS') }}</span>
                </div>
            </div>
        @endif
        @if (session()->has(key: 'FAILED'))
            <div x-data="{ alert: true }" x-init="setTimeout(() => alert = false, 5000)" x-show="alert" x-transition
                class="flex items-start left-5 bottom-5 flex-row p-4 mb-4 text-sm rounded-lg bg-gray-800 animate-fade-up text-red-400 absolute"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Info</span>
                <div>
                    <span class="font-medium">{{ session('FAILED') }}</span>
                </div>
            </div>
        @endif
        <div class="bg-primary_white max-w-[1600px] w-[90%] h-[88vh] mt-[12vh] rounded-xl ">
            <div class="w-full h-full">
                <div class="flex w-full justify-between px-3 py-4">
                    <div class="flex flex-row gap-x-4">
                        <div class="relative w-full">
                            <input type="text" wire:model.live.debounce.500ms="search" x-on:input="debounching"
                                class="w-full rounded-xl border-2 border-secondary_blue text-secondary_blue px-4 py-3 focus:border-blue-500 focus:outline-none"
                                placeholder="{{ __('add-class.search') }}......">
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary_blue"
                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path
                                        d="M11 6C13.7614 6 16 8.23858 16 11M16.6588 16.6549L21 21M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z"
                                        stroke="#2867a4" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </g>
                            </svg>
                        </div>
                        <select wire:model.live="desc"
                            class="border text-secondary_blue border-secondary_blue rounded-xl">
                            <option value="asc">A-Z</option>
                            <option value="desc">Z-A</option>
                        </select>
                    </div>
                    <button @click="show = true"
                        class="bg-secondary_blue text-white rounded-xl px-4 py-2 flex flex-row gap-x-2 items-center justify-center cursor-pointer hover:opacity-60 transition-opacity">
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
                <div x-show="classrooms == null" class="w-full h-[200px] flex items-center justify-center">
                    <div
                        class="px-4 py-2 text-base font-medium leading-none text-center rounded-full animate-pulse bg-blue-900 text-blue-200">
                        {{ __('add-class.loading') }}....</div>
                </div>
                <div x-show="classrooms.length == 0" class="w-full h-[200px] flex items-center justify-center">
                    <div
                        class="px-4 py-2 text-base font-medium leading-none text-center rounded-full animate-pulse bg-white border-2 border-red-500 text-red-700">
                        {{ __('classroom.not_found') }}....</div>
                </div>
                <div x-show="classrooms.length != 0"
                    class="overflow-y-auto h-[90%] flex flex-wrap flex-row justify-center gap-x-6 gap-y-7 items-start content-start">
                    <template x-for="classroom in classrooms" :key="classroom.id">
                        <div @click="window.location.href = '/classroom' + '/' + classroom.id"
                            class="relative w-[360px] h-[250px] rounded-xl overflow-hidden shadow-xl group cursor-pointer animate-fade">
                            <div class="absolute w-full h-full" x-data="{ loaded: false }">

                                <div x-show="!loaded"
                                    class="w-full h-full flex items-center justify-center bg-gray-200">
                                    <span
                                        class="animate-spin h-10 w-10 border-4 border-gray-400 border-t-transparent rounded-full"></span>
                                </div>
                                <img x-bind:src="classroom.image" x-bind:alt="classroom.title"
                                    class="w-full h-full object-cover transition-opacity duration-300 ease-in-out"
                                    x-bind:class="loaded ? 'opacity-100' : 'opacity-0'" @load="loaded = true"
                                    loading="lazy">
                            </div>
                            <div
                                class="absolute w-full p-4 transition-all duration-300 h-full 
        translate-y-[70%] group-hover:translate-y-[25%] 
        bg-gradient-to-t from-black/80 to-black/30 group-hover:from-black/60 group-hover:bg-black/50">
                                <p class="text-white font-semibold" x-text="classroom.title"></p>
                            </div>
                        </div>

                    </template>
                </div>

            </div>
        </div>
        <div x-cloak x-show="show" class="fixed inset-0 bg-black/40 flex items-center justify-center z-30"
            @click="show = false">

            <!-- Notifikasi (Tengah) -->
            <div @click.stop
                class="bg-white p-5 rounded-lg shadow-lg max-w-[500px] min-w-[300px] w-[500px] animate-fade">
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
                <form>
                    <label for="class"
                        class="text-2xl font-bold text-secondary_blue text-center w-full pb-10 pt-4 inline-block font-koho">
                        {{ __('classroom.add_code') }}
                    </label>
                    <div class="flex flex-row gap-x-2 items-end w-full">
                        <div class="flex-1">
                            <input id="class" type="text"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-3"
                                placeholder="{{ __('classroom.code') }}" name="class">
                        </div>
                        <button type="submit"
                            class=" px-5 py-2 rounded-xl bg-secondary_blue text-primary_white hover:opacity-70 transition-opacity">
                            {{ __('classroom.join') }}
                        </button>
                    </div>

                </form>
                <div class="mt-16 pt-5 mb-5 text-center">
                    <button wire:click="addClass" wire:loading.attr="disabled"
                        class="px-5 py-3 text-secondary_blue border-secondary_blue border text-xl 
               hover:border-primary_white hover:bg-secondary_blue hover:outline-secondary_blue 
               transition-colors hover:outline-2 hover:text-primary_white 
               disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('classroom.make_class') }}
                    </button>
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
                    init() {
                        this.classroomUrl = '{{ route('classroom-learn', ['id' => '__ID__']) }}';
                    },
                    debounching() {
                        this.classrooms = null
                    },
                    check() {
                        console.log(this.classrooms);
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
                    }
                }
            }
        </script>
    </div>
</div>
