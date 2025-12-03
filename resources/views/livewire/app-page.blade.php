<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\Task;
use App\Models\Content;

new #[Layout('components.layouts.app-page')] class extends Component {
    public $classroom;
    public $tasks;
    public $content;
    public $error;
    public function mount()
    {
        $this->getClassroom();
        $this->getTasks();
        $this->getContent();
    }
    public function getClassroom()
    {
        try {
            // $this->classroom = ClassroomMember::with('classroom.content.tasks')
            // ->where('user_id', auth()->id())
            // ->get()
            // ->map(function ($member) {
            //     // $member->classroom->content = collect($member->classroom->content)
            //     //     ->filter(function ($content) {
            //     //         // Lebih ketat: hanya ambil content yang release tidak null dan sudah lewat waktu
            //     //         return false;
            //     //     })
            //     //     ->values();
            //         Log::info('Filtered content for classroom member: ', [
            //             'member_id' => $member->id,
            //             'content_count' => $member->classroom->content,
            //         ]);
            //     return $member;
            // })
            // ->toArray();
            $this->classroom = ClassroomMember::with('classroom.content.tasks')
                ->where('user_id', auth()->id())
                ->get()
                ->filter(function ($member) {
                    // Filter di sini: jangan sertakan jika classroom dimiliki oleh user itu sendiri
                    return $member->classroom && $member->classroom->user_id !== auth()->id();
                })
                ->map(function ($member) {
                    $filteredContent = collect($member->classroom->content)
                        ->filter(function ($content) {
                            return $content->release !== null && now()->greaterThan($content->release) && $content->type == 'task';
                        })
                        ->map(function ($content) {
                            $filteredTasks = collect($content->tasks)
                                ->filter(function ($task) {
                                    return $task->user_id === auth()->id();
                                })
                                ->values();
                            $content->setRelation('tasks', $filteredTasks);
                            return $content;
                        })
                        ->values();
                    $member->classroom->setRelation('content', $filteredContent);
                    return $member->toArray();
                });
        } catch (\Throwable $th) {
            Log::error('Error fetching classroom: ' . $th->getMessage());
            $this->error = $th->getMessage();
        }
    }


    public function getTasks()
    {
        try {
            $tasks = Task::with('content')
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get()
            ;
            if (!empty($tasks)) {
                $this->tasks = $tasks->toArray();
            }
            $this->tasks = [];

        } catch (\Throwable $th) {
            Log::error('Error fetching tasks: ' . $th->getMessage());
            $this->error = $th->getMessage();
        }
    }

    public function getContent()
    {
        try {
        } catch (\Throwable $th) {
            Log::error('Error fetching content: ' . $th->getMessage());
            $this->error = $th->getMessage();
        }
    }
}; ?>

@vite(['resources/js/calendar.js', 'resources/js/day.js'])

<div class="bg-primary_white class:w-[90%] class:w-full max-w-[1600px] class:rounded-xl mx-auto mt-[12vh] min-h-[800px] rounded-none p-2"
    x-data="initAppPage" x-init="initApp">

    <div class="flex h-auto min-h-[800px] app:flex-nowrap flex-wrap gap-x-5 gap-y-5 p-2 justify-around">
        <div
            class="animate-fade-down app:h-[900px] app:h-auto min-h-[150px] min-w-[330px] rounded-md border border-white bg-gradient-to-r from-purple-700 via-indigo-600 to-blue-500 p-3">
            <h2 class="text-3xl text-center !my-5 font-bold text-white">{{ __('app.list_task') }}</h2>
            <div class="mt-3 h-full overflow-y-auto pr-2">
                <template x-if="classroom">
                    <template x-for="(item, index) in classroom" :key="item.id">
                        <div x-data="{ ekse: false }">
                            <template x-if="item && item.classroom && item.classroom.content">
                                <template x-for="(item1, index1) in item.classroom.content" :key="item1.id">
                                    <template x-if="item1 && item1.type == 'task' && !item1.tasks.length > 0">
                                        <div @click="buildClassroomTaskUrl(item.classroom.id, item1.id)"
                                            class="mb-2 w-[300px] rounded-md border border-white p-2"
                                            x-init="ekse = true">
                                            <p class="font-bold text-white" x-text="item.classroom.title">
                                            </p>
                                            <div class="flex flex-row items-center justify-between text-center">
                                                <p class="line-clamp-2 text-white" x-text="item1.title"></p>
                                                <div>
                                                    <div class="flex flex-row items-center gap-x-1">
                                                        <flux:icon.clock class="text-white" />
                                                        <flux:text class="text-lg text-white">
                                                            {{ __('class-learn.deadline') }}
                                                        </flux:text>
                                                    </div>
                                                    <p class="text-white" x-text="getRemainingTime(item1.deadline)">
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </template>
                            <template x-if="!ekse && count == 1">
                                <p class="text-center text-white" x-init="count = 2">{{ __('app.no_task') }}</p>
                            </template>
                        </div>
                    </template>
                </template>
                <template x-if="!classroom">
                    <p class="text-center text-white">{{ __('app.no_task') }}</p>
                </template>
                <!-- <template x-if="tasks">
                    <template x-for="(item, index) in tasks" :key="index">
                        <template x-if="item.content.type == 'task'">
                            <template x-if="!item.answer">
                                <div class="">
                                    <p x-text="item.content.title"></p>
                                </div>
                            </template>
                        </template>
                    </template>
                </template> -->
            </div>
        </div>
        <div class="flex flex-row flex-wrap gap-y-5 gap-x-5 justify-center">
            <div class="animate-fade-right bg-gradient-to-r from-cyan-400 via-blue-500 to-indigo-600
 group w-[350px] rounded-xl p-5 transition-all flex items-center justify-center flex-col">
                <h3 class="text-primary_white text-3xl font-bold text-center">{{ __('app.many_class') }}</h3>
                <p class="text-primary_white mt-2 text-5xl my-5 font-bold text-center" x-text="totalClass"></p>
                <a href="{{ route('classroom') }}"
                    class="mt-3 flex cursor-pointer flex-row items-center justify-between rounded-md border border-white !p-2 text-white group-hover:animate-pulse">
                    <p>{{ __('app.check_class') }}</p>
                    <svg class="h-[25px] w-[25px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.25 12C3.25 11.5858 3.58579 11.25 4 11.25H13.25V12.75H4C3.58579 12.75 3.25 12.4142 3.25 12Z"
                                fill="currentColor"></path>
                            <path
                                d="M13.25 12.75V18C13.25 18.3034 13.4327 18.5768 13.713 18.6929C13.9932 18.809 14.3158 18.7449 14.5303 18.5304L20.5303 12.5304C20.671 12.3897 20.75 12.1989 20.75 12C20.75 11.8011 20.671 11.6103 20.5303 11.4697L14.5303 5.46969C14.3158 5.25519 13.9932 5.19103 13.713 5.30711C13.4327 5.4232 13.25 5.69668 13.25 6.00002V11.25V12.75Z"
                                fill="currentColor"></path>
                        </g>
                    </svg>
                </a>
            </div>
            <div
                class="animate-fade-right group w-[350px] rounded-xl bg-gray-200 bg-gradient-to-r from-orange-400 via-red-500 to-pink-500 p-5 transition-all flex items-center justify-center flex-col">
                <h3 class="text-primary_white text-3xl font-bold text-center">{{ __('app.total_task') }}</h3>
                <div class="mt-2 flex flex-row gap-x-4 justify-center !my-5">
                    <p class="text-primary_white text-4xl font-bold" x-text="totalTask"></p>
                    <p class="text-primary_white text-4xl font-bold">/</p>
                    <div class="flex flex-col items-center justify-center">
                        <p class="text-white">{{ __('app.mean_score') }}</p>
                        <p class="text-white" x-text="meanTask"></p>
                    </div>
                </div>
                <div
                    class="mt-3 flex cursor-pointer flex-row items-center justify-between rounded-md border border-white !p-2 text-white group-hover:animate-pulse">
                    <p class="text-sm">{{ __('app.motivation') }}</p>
                </div>
            </div>
            <div
                class="animate-fade-right group w-[350px] rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 p-5 transition-all flex items-center justify-center flex-col">
                <h3 class="text-primary_white text-3xl font-bold text-center">{{ __('welcome.friend') }}</h3>
                <div class="flex flex-row items-center !my-5 justify-center">
                    <div class="h-[50px] w-[50px] text-white">
                        <svg class="h-full w-full" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M20 18L14 18M17 15V21M7.68213 14C8.63244 14.6318 9.77319 15 10.9999 15C11.7012 15 12.3744 14.8797 13 14.6586M10.5 21H5.6C5.03995 21 4.75992 21 4.54601 20.891C4.35785 20.7951 4.20487 20.6422 4.10899 20.454C4 20.2401 4 19.9601 4 19.4V17C4 15.3431 5.34315 14 7 14H7.5M15 7C15 9.20914 13.2091 11 11 11C8.79086 11 7 9.20914 7 7C7 4.79086 8.79086 3 11 3C13.2091 3 15 4.79086 15 7Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                            </g>
                        </svg>
                    </div>
                    <p class="text-primary_white mt-2 text-4xl font-bold" x-text="countFriend"></p>
                </div>
                <div
                    class="mt-3 flex cursor-pointer flex-row items-center justify-between rounded-md border border-white !p-2 text-white group-hover:animate-pulse">
                    <p>{{ __('welcome.friend.detail') }}</p>
                </div>
            </div>
            <div
                class="animate-fade-right group w-[350px] rounded-xl bg-gradient-to-r from-teal-400 via-emerald-500 to-green-500 p-3 transition-all flex items-center justify-center flex-col">
                <h3 class="text-primary_white text-2xl font-bold text-center">{{ __('welcome.time') }}</h3>
                <div class="w-full h-[120px] flex items-center flex-col justify-center">
                    <p class="text-primary_white mt-2 text-5xl font-bold" x-text="times"></p>
                    <p class="text-white">WIB</p>
                </div>
            </div>
        </div>
        <div class="flex justify-end">
            <div id="calendar" class="max-h-[500px] app-1:min-w-[420px]"></div>
        </div>
    </div>
</div>

<script>
    function initAppPage() {
        return {
            classroom: @entangle('classroom'),
            error: @entangle('error'),
            tasks: @entangle('tasks'),
            content: @entangle('content'),
            isTeacher: "{{ auth()->user()->role }}" == 'teacher',
            count: 1,
            stopCalendar: false,
            times: null,
            initApp() {
                if (this.error) {
                    this.$nextTick(() => {
                        this.$dispatch('failed', [{
                            message: this.error,
                        }])
                    })
                }
                this.initCaldendar();
                this.initTimes();
                console.log('Classroom data:', this.classroom);
                console.log('Tasks data:', this.tasks);
                console.log('Content data:', this.content);

            },
            initTimes() {
                if (this.times) return;
                this.times = dayjs().locale('id').format('HH:mm');
                setInterval(() => {
                    const now = dayjs();
                    this.times = dayjs().locale('id').format('HH:mm');
                }, 1000);
            },
            async initCaldendar() {
                if (this.stopCalendar) return;
                this.stopCalendar = true;
                let holidays = [];

                try {
                    const response = await fetch(
                        'https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/main/calendar.min.json');
                    const data = await response.json();

                    console.log('Holidays data:',
                        data); // Ini akan menampilkan objek seperti {"2025-01-01": {...}, ...}

                    holidays = Object.entries(data).map(([date, value], index) => ({
                        id: index,
                        start: date,
                        title: Array.isArray(value.summary) ? value.summary[0] : 'Hari Libur'
                    }));

                    console.log('Parsed Holidays:', holidays);
                } catch (error) {
                    console.error('Error fetching holidays:', error);
                }
                console.log('Holidays:', holidays);


                let calendarEl = document.getElementById('calendar');
                let calendar = new Calendar(calendarEl, {
                    plugins: [dayGridPlugin, timeGridPlugin],
                    initialView: 'dayGridMonth',
                    locale: 'id',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek'
                    },
                    events: holidays
                });
                calendar.render();

            },
            get totalClass() {
                try {
                    return this.classroom.filter(item => item.status == 'approved').length;
                } catch (error) {
                    return 0;
                }
            },
            get totalTask() {
                return this.tasks.filter(item => item.content.type == 'task').length || 0;
            },
            get meanTask() {
                if (this.tasks.length === 0) return 'Null';
                const totalScore = this.tasks.reduce((sum, task) => {
                    return sum + (task.reading || 0);
                }, 0);
                return (totalScore / this.totalTask).toFixed(2);
            },
            get countFriend() {
                const data = localStorage.getItem('chats');
                if (!data) return 0;
                const chats = JSON.parse(data);
                return chats.length || 0;
            },
            buildClassroomTaskUrl(classroomId, taskId) {
                const routeClassroomTaskTemplate = "{{ route('classroom-task', ['id' => ':id', 'task' => ':task']) }}";
                window.location.href = routeClassroomTaskTemplate
                    .replace(':id', classroomId)
                    .replace(':task', taskId);
            },
            getRemainingTime(deadlineString) {
                if (!deadlineString) return '--:--';
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
            }
        }
    }
</script>