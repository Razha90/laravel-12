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
            $this->tasks = Task::with('content')
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
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

<div class="bg-primary_white class:w-[90%] class:w-full class:rounded-xl mx-auto mt-[12vh] min-h-[800px] max-w-[1600px] rounded-none p-2"
    x-data="initAppPage" x-init="initApp">
    <div class="flex flex-row flex-wrap justify-center gap-x-3 p-2 min-h-[800px]">
        <div class="animate-fade-down h-full min-h-[150px] min-w-[300px] rounded-md border border-gray-400 p-3">
            <h2 class="text-xl text-gray-500">{{ __('app.list_task') }}</h2>
            <div class="mt-3 max-h-[500px] overflow-y-auto pr-2">
                <template x-if="classroom">
                    <template x-for="(item, index) in classroom" :key="item.id">
                        <div x-data="{ ekse: false }">
                            <template x-if="item && item.classroom && item.classroom.content">
                                <template x-for="(item1, index1) in item.classroom.content" :key="item1.id">
                                    <template x-if="item1 && item1.type == 'task' && !item1.tasks.length > 0">
                                        <div @click="buildClassroomTaskUrl(item.classroom.id, item1.id)"
                                            class="mb-2 w-[300px] rounded-md border border-gray-300 p-2"
                                            x-init="ekse = true">
                                            <p class="font-bold text-gray-400" x-text="item.classroom.title">
                                            </p>
                                            <div class="flex flex-row items-center justify-between text-center">
                                                <p class="line-clamp-2 text-gray-700" x-text="item1.title"></p>
                                                <div>
                                                    <div class="flex flex-row items-center gap-x-1">
                                                        <flux:icon.clock class="text-gray-500" />
                                                        <flux:text class="text-lg text-gray-500">
                                                            {{ __('class-learn.deadline') }}
                                                        </flux:text>
                                                    </div>
                                                    <p class="text-gray-700" x-text="getRemainingTime(item1.deadline)">
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </template>
                            <template x-if="!ekse">
                                <p class="text-center text-gray-400">{{ __('app.no_task') }}</p>
                            </template>
                        </div>
                    </template>
                </template>
                <template x-if="!classroom">
                    <p class="text-center text-gray-400">{{ __('app.no_task') }}</p>
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
        <div class="flex flex-col gap-y-3 flex-wrap">
            <div
                class="animate-fade-right bg-secondary_blue group h-[160px] w-[350px] rounded-xl bg-gray-200 p-3 transition-all">
                <h3 class="text-primary_white text-xl">{{ __('app.many_class') }}</h3>
                <p class="text-primary_white mt-2 text-4xl font-bold" x-text="totalClass"></p>
                <a href="{{ route('classroom') }}"
                    class="mt-3 flex cursor-pointer flex-row items-center justify-between rounded-md border border-white !p-2 text-white group-hover:animate-pulse">
                    <p>{{ __('app.check_class') }}</p>
                    <svg class="h-[25px] w-[25px]" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
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
                class="animate-fade-right group h-[190px] w-[350px] rounded-xl bg-gray-200 bg-green-600 p-3 transition-all">
                <h3 class="text-primary_white text-xl">{{ __('app.total_task') }}</h3>
                <div class="mt-2 flex flex-row gap-x-4">
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
            initApp() {
                if (this.error) {
                    this.$nextTick(() => {
                        this.$dispatch('failed', [{
                            message: this.error,
                        }])
                    })
                }
                console.log('Classroom data:', this.classroom);
                console.log('Tasks data:', this.tasks);
                console.log('Content data:', this.content);
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
